<?php
	// Get api keys
	include "../../outerScripts/keys.php";

	// Company tag for stock and article lookup
	if(isset($_GET["tag"])){
		$tag= strip_tags(trim($_GET["tag"]));
	} else $tag = "MSFT";

	// Get historical data for company stock
	$url = "https://www.alphavantage.co/query?" . 
		"function=TIME_SERIES_DAILY&" . 
		"symbol=" . $tag .
		"&apikey=" . $alphaVantageApiKey;
	$json = file_get_contents($url);
	$json_data = json_decode($json, true);
	$raw = $json_data["Time Series (Daily)"];

	// Generate inputs for google charts
	// Record date ranges for chart data
	$timeRangeStart = "";
	$timeRangeEnd = "";
	foreach($raw as $key => $sample){
		$open = strip_tags(trim($sample["1. open"]));
		$key = strip_tags(trim($key));
		$googChartDate = dateAdjust($key, "-", "/", "-1");
		$date = "new Date(" . str_replace("-", ", ", $googChartDate) . ")";
		$chartData .= "[" . $date . ", " . $open . "],";

		if($timeRangeEnd == "") $timeRangeEnd = $googChartDate;
		if($timeRangeStart == "") $timeRangeStart = $googChartDate;
		elseif($timeRangeStart < $date) $timeRangeStart = $googChartDate;
	}

	// Record date ranges for news articles
	if(isset($_GET["min"]) && isset($_GET["max"])){
		$min = strip_tags(trim($_GET["min"]));
		$max = strip_tags(trim($_GET["max"]));
		$newsStart = dateAdjust($min, "-", "/", "-1");
		$newsEnd = dateAdjust($max, "-", "/", "-1");
	} else {
		$newsStart = $timeRangeStart;
		$newsEnd = $timeRangeEnd;
	}

	// Get news articles specific to company tag and date ranges
	// Generate list of news articles
	$url = "https://newsapi.org/v2/everything?" .
          "q=" . $tag . "&" .
          "from=" . dateAdjust($newsStart, "-", "/", "+1") . "&" .
          "to=" . dateAdjust($newsEnd, "-", "/", "+1") . "&" .
          "sortBy=popularity&" .
          "apiKey=" . $newsApiKey;
    $json = file_get_contents($url);
	$json_data = json_decode($json, true);
	$raw = $json_data["articles"];
    foreach($raw as $key => $sample){
		$articles .= "" . 
		"<a class = \"noSelect\" href = \"" . $sample["url"] . "\">" . 
			"<div class = \"articleContainer\">" .
				"<img class = \"articleImage\" src = '" . $sample["urlToImage"] . "'>" .
				"<div class = \"articleTextContainer\">" .
					"<p><b>" . $sample["title"] . "</b></p>" . 
					"<p>" . $sample["description"] . "</p>" . 
				"</div>" . 
			"</div>" . 
		"</a><br>";
	}

	// Modifies date string to account for differences 
	// in goog charts, javascript, php, etc...
	function dateAdjust($date, $symbol1, $symbol2, $inc){
		$date = date('Y-m-d', strtotime(str_replace($symbol1, $symbol2, $date)));
		$date = date('Y-m-d', strtotime($inc . " months", strtotime($date)));
		return $date;
	}
?><!DOCTYPE HTML>

<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
		<script src="jQRangeSlider/jQDateRangeSlider-min.js"></script>
		<link rel="stylesheet" href="jQRangeSlider/css/iThing.css" type="text/css"/>

		<!-- Function basically copied directly from google's dev library. -->
    	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	    <script type="text/javascript">
	     	google.charts.load('current', {'packages':['corechart']});
	      	google.charts.setOnLoadCallback(drawChart);

	      	function drawChart() {
	        	var data = google.visualization.arrayToDataTable([
	          		['Time', 'Open'],
	          		<?php echo $chartData; ?>
	        	]);
	        	var options = {
	          		title: 'Open Price',
	          		hAxis: {gridlines: {count: 5}},
	        	};
	        	var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
	        	chart.draw(data, options);
	      	}
	    </script>
 	</head>

	<body>
		<?php include "banner.php"; ?>
		<div class = "centerContainer">
			<div class = "pageTitle">
				<?php
					$date1 = dateAdjust($timeRangeStart, "", "", "+1");
					$date2 = dateAdjust($timeRangeEnd, "", "", "+1");
					$date3 = dateAdjust($newsStart, "", "", "+1");
					$date4 = dateAdjust($newsEnd, "", "", "+1");
				?>
				<br><br>
				<p>
					<b><?php echo $tag . " - From " . $date1 . " to " . $date2; ?></b><br>
					<b><?php echo $tag . " News - From " . $date3 . " to " . $date4; ?></b>
				</p>
			</div>
			<div id="chart_div" style="width: 100%; height: 500px;"></div>
			<div id="slider" class = "customSlider"></div>
		    <div>
		    	<form action="index.php" id="sliderForm" method="GET" enctype="multipart/form-data">
                    <input type="hidden" id="min" name="min" value="">
                    <input type="hidden" id="max" name="max" value="">
                    <input type="hidden" id="tag" name="tag" value="<?php echo $tag; ?>">
                    <button type = "submit" name = "submit" class = "submitButton" id = "submitButton" value = "submit">
                        Search News
                    </button>
                </form>
		    </div>
			<div><?php echo $articles; ?></div>
		</div>
	</body>

	<script>
		<?php/*
			http://ghusse.github.io/jQRangeSlider/events.html#bindingEvents 
			Function basically copied directly from their dev notes with a couple small changes
			for php inserts. However, CSS was heavily modifed to give current look. 
		*/?>

	  	$("#slider").dateRangeSlider({
	  		<?php
	  			$minDate = "new Date(" . str_replace("-", ", ", $timeRangeStart) . ")";
	  			$maxDate = "new Date(" . str_replace("-", ", ", $timeRangeEnd) . ")";
	  			$minNews = "new Date(" . str_replace("-", ", ", $newsStart) . ")";
	  			$maxNews = "new Date(" . str_replace("-", ", ", $newsEnd) . ")";

	  			echo "
	  				bounds: {min: " . $minDate . ", max: " . $maxDate . "},
	    			defaultValues: {min: " . $minNews . ", max: " . $maxNews . "},
	  			";
	  		?>
	    	scales: [{
	      		first: function(value){ return value; },
	      		end: function(value) {return value; },
	      		next: function(value){
	        		var next = new Date(value);
	        		return new Date(next.setMonth(value.getMonth() + 1));
	      		},
		      	format: function(tickContainer, tickStart, tickEnd){
		        	tickContainer.addClass("myCustomClass");
		      	}
	    	}]
	  	});

		$("#slider").on("valuesChanging", function(e, data){
		  	var min = data.values.min;
		  	var max = data.values.max;
		  	document.getElementById("min").value = min.getFullYear() + '-' + 
		  		(min.getMonth() + 1) + '-' + min.getDate();
		  	document.getElementById("max").value = max.getFullYear() + '-' + 
		  		(max.getMonth() + 1) + '-' + max.getDate();
		});
    </script>
</html>