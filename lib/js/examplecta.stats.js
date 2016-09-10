//******************************************************************************
// Let's start the engine.
//******************************************************************************
jQuery(document).ready( function($) {

	$.ajax({
		method: 'GET',
		url: exampleCtaVars.apiBaseUrl+'/examplecta/v1/stats',
		cache: false,
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', exampleCtaVars.nonce );
		},
		success: function(response) {
			var loggedInLabels = [];
			var loggedInValues = [];

			$.each(response, function(key, value) {
				$('.example-cta-stats .'+key).html(value);

				if (key=='logged_in' || key=='logged_out') {				
					loggedInLabels.push(key);
					loggedInValues.push(value);
				};
			});

			var ctx = $("#examplectaStats");
			var data = {
				labels: loggedInLabels,
				datasets: [
				{
					data: loggedInValues,
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