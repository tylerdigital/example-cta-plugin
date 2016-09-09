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
		}
	});


//******************************************************************************
// And...that's all folks. We're done here.
//******************************************************************************
});