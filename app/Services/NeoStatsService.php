<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class NeoStatsService {

	const API_URL = 'https://api.nasa.gov/neo/rest/v1/feed';

	public static function getStats($start_date, $end_date) {
		$api_key = env('NEO_API_KEY','DEMO_KEY');
		
		try {
			$client = new Client();

			$requests = function($start_date, $end_date) use($client, $api_key) {
				$intervals = CarbonInterval::days(8)->toPeriod($start_date, $end_date);
				foreach($intervals as $interval) {
					$start = $interval->format('Y-m-d');
					$end = min($interval->addDays(7)->format('Y-m-d'), $end_date);
					yield function() use ($client, $api_key, $start, $end) {
						return $client->getAsync(self::API_URL, [
							'query' => [
								'api_key' => $api_key,
								'start_date' => $start,
								'end_date' => $end
							]
						]);
					};
				}
			};

			$summaries = [];
			$pool = new Pool($client, $requests($start_date, $end_date), [
				'concurrency' => 6,
				'fulfilled' => function(Response $response, $index) use(&$summaries) {
					$contents = $response->getBody()->getContents();
					$summary = self::getSummarizedData(json_decode($contents));
					array_push($summaries, $summary);
					Log::info('Fullfilled '. $index.' : '.json_encode($summary));
				},
				'rejected' => function(RequestException $reason, $index) {
					Log::error('Neo Stats Api Error2: '. $reason->getMessage());
				}
			]);

			$promises = $pool->promise();

			$promises->wait();

			return self::mergeSummaries($summaries);

			$response = $client->request('GET', self::API_URL, [
				'query' => [
					'api_key' => $api_key,
					'start_date' => $start_date,
					'end_date' => $end_date
				]
			]);

			if($response->getStatusCode() == 200) {
				$contents = $response->getBody()->getContents();
				return self::getSummarizedData(json_decode($contents));
			}
			else {
				return json_decode($response->getBody()->getContents());
			}
		} catch (\Exception $e) {
			Log::error('Neo Stats Api Error: '. $e->getMessage());
			return [
				'errors' => [
					'api_error' => [
						"An error occured"
					]
				]
			];
		}
	}

	private static function getSummarizedData($contents) {
		$count = [];
		$total = 0;
		$fastest = null;
		$max_speed = 0;
		$closest = null;
		$min_distance = INF;
		$size_sum = 0;
		$contents_coll = collect($contents->near_earth_objects);

		foreach($contents_coll as $date => $asteroids) {
			$count[$date] = 0;
			foreach($asteroids as $asteroid) {
				$speed = $asteroid->close_approach_data[0]->relative_velocity->kilometers_per_hour;
				$distance = $asteroid->close_approach_data[0]->miss_distance->kilometers;
				$size_sum += $asteroid->estimated_diameter->kilometers->estimated_diameter_max;

				$count[$date]++;
				$total++;

				if($speed > $max_speed) {
				    $max_speed = $speed;
				    $fastest = $asteroid->id;
				}

				if($distance < $min_distance) {
				    $min_distance = $distance;
				    $closest = $asteroid->id;
				}
			}
		}
		return [
            'count' => $count,
            'fastest' => [
                'id' => $fastest,
                'speed' => $max_speed
            ],
            'closest' => [
                'id' => $closest,
                'distance' => $min_distance
            ],
            'avg_size' => $size_sum/$total,
            'total' => $total
        ];
	}

	private static function mergeSummaries($summaries) {
		$summ = $summaries[0];
		$final_summary = $summ;
		$size_sum = $summ['avg_size'] * $summ['total'];
		$total = $summ['total'];
		foreach(array_slice($summaries, 1) as $summary) {
			$final_summary['count'] = array_merge($final_summary['count'], $summary['count']);
			if($final_summary['fastest']['speed'] < $summary['fastest']['speed']) {
				$final_summary['fastest'] = $summary['fastest'];
			}
			if($final_summary['closest']['distance'] > $summary['closest']['distance']) {
				$final_summary['closest'] = $summary['closest'];
			}
			$size_sum += $summary['avg_size'] * $summary['total'];
			$total += $summary['total'];
		}
		$final_summary['avg_size'] = $size_sum/$total;
		$final_summary['total'] = $total;
		return $final_summary;
	}
}
?>