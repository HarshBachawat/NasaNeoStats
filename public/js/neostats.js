const ctx = document.getElementById('chart_canvas').getContext('2d');
let count_chart;

$('#neo-form').on('submit', function(e) {
	e.preventDefault();
	$('#submit').prop('disabled', true).html('Processing.. <i class="fas fa-circle-notch fa-spin"></i>');


	let form = $(this)
	let actionUrl = form.attr('action')

	$.ajax({
		url: actionUrl,
		type: "POST",
		data: form.serializeArray(),
		success: function(data) {
			if(data.status == 'success' && data.payload) {
				$('#data-container').removeClass('d-none');
				payload = data.payload
				$('#fastest').html(`${payload.fastest.id} (${payload.fastest.speed} km/hr)`)
				$('#closest').html(`${payload.closest.id} (${payload.closest.distance} km)`)
				$('#avg_size').html(`${payload.avg_size} km`)

				sorted_count = Object.keys(payload.count).sort().reduce((obj, key) => { 
									obj[key] = payload.count[key];
									return obj;
								}, {}
							);

				if(! count_chart) {
					count_chart = new Chart(ctx, {
						type: 'line',
						data: {
							labels: Object.keys(sorted_count),
							datasets: [{
								label: 'Asteroids Frequency',
								backgroundColor: '#007bff',
								borderColor: '#007bff',
								data: Object.values(sorted_count)
							}]
						}
					});
				}
				else {
					replaceChartData(count_chart, Object.keys(sorted_count), Object.values(sorted_count))
				}
			}
		},
		error: function(err) {
			error = err?.responseJSON
			message = error?.errors?.[Object.keys(error?.errors)?.[0]]?.[0]
			Swal.fire({
				icon: 'error',
				title: error?.message || 'Error',
				text: message || 'Some Error Occured',
			})
		},
		complete: function() {
			$('#submit').prop('disabled', false).html('Submit');
		}
	})
});

function replaceChartData(chart, new_labels, new_data) {
	chart.data.labels = new_labels;
	chart.data.datasets[0].data = new_data;
	chart.update();
}