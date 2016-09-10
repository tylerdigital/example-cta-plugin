//******************************************************************************
// Let's start the engine.
//******************************************************************************
jQuery(document).ready( function($) {

	$.ajax({
		method: 'GET',
		url: exampleCtaVars.apiBaseUrl+'/examplecta/v1/stats',
		cache: false,
		success: function(response) {
			$.each(response, function(key, value) {
				$('.example-cta-stats .'+key).html(value);
			});

			var ctx = $("#examplectaStats");

			var data = {
				labels: [
				"Red",
				"Blue",
				"Yellow"
				],
				datasets: [
				{
					data: [300, 50, 100],
					backgroundColor: [
					"#FF6384",
					"#36A2EB",
					"#FFCE56"
					],
					hoverBackgroundColor: [
					"#FF6384",
					"#36A2EB",
					"#FFCE56"
					]
				}]
			};

			var myPieChart = new Chart(ctx,{
				type: 'pie',
				data: data,
			});
		}
	});


//******************************************************************************
// And...that's all folks. We're done here.
//******************************************************************************
});