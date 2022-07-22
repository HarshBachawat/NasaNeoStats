<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\NeoStatsRequest;
use App\Services\NeoStatsService;

class NeoStatsController extends Controller
{
    public function index(Request $request) {
        return view('app');
    }
    
    public function getStats(NeoStatsRequest $request) {
        if($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $response = NeoStatsService::getStats($start_date, $end_date);
            if(array_key_exists('errors', $response)) {
                return response()->json($response, 422);
            }
            return response()->json([
                'status' => 'success',
                'payload' => $response
            ], 200);
        }
    }
}
