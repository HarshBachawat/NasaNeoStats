<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Nasa Neo Stats</title>

		<!-- Styles -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0-14/css/all.min.css" integrity="sha512-YVm6dLGBSj6KG3uUb1L5m25JXXYrd9yQ1P7RKDSstzYiPxI2vYLCCGyfrlXw3YcN/EM3UJ/IAqsCmfdc6pk/Tg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

		<!-- Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

		<!-- Scripts -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script src="https://cdn.jsdelivr.net/npm/chart.js@3.8.0/dist/chart.min.js"></script>
	</head>
	<body>
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
			<a class="navbar-brand" href="/">NeoStats</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
			</button>

			<div class="collapse navbar-collapse" id="navbarSupportedContent">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item active">
						<a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
					</li>
				</ul>
			</div>
		</nav>

		<div class="container mt-4">
			<form id="neo-form" action="{{ route('getNeoStats') }}">
				@csrf
				<div class="form-row">
					<div class="col-md-6 form-group">
						<label for="start_date">Start Date</label>
						<input type="date" name="start_date" id="start_date" class="form-control" required>
					</div>
					<div class="col-md-6 form-group">
						<label>End Date</label>
						<input type="date" name="end_date" id="end_date" class="form-control" required>
					</div>
				</div>
				<div class="form-group text-center">
					<button type="submit" class="btn btn-primary" id="submit">Submit</button>
				</div>
			</form>
			<br>
			<div id="data-container" class="d-none">
				<div class="card col-lg-6 col-md-8 px-0">
					<div class="card-header bg-primary text-white">
						Summary
					</div>
					<ul class="list-group list-group-flush">
						<li class="list-group-item">Fastest Asteroid: <span id="fastest"></span></li>
						<li class="list-group-item">Closest Asteroid: <span id="closest"></span></li>
						<li class="list-group-item">Average Size of Asteroids: <span id="avg_size"></span></li>
					</ul>
				</div>
				<div class="mt-3">
					<canvas id="chart_canvas" class="w-100"></canvas>
				</div>
			</div>
		</div>
	</body>
	<script type="text/javascript" src="{{asset('/js/neostats.js')}}"></script>
</html>
