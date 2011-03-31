$(function() {

	// Load the correct page into the frame.
	$(window).bind('hashchange', function(e) {
		var method = $.bbq.getState('m'),
			property = $.bbq.getState('p'),
			constant = $.bbq.getState('c'),
			classIndex = $.bbq.getState('i'),
			defaultIndex = $.bbq.getState('d');

		if (method) {
			method = method.split('/');
			$('#mainContent').attr('src', 'class/' + method[0].toLowerCase() + '/' + method[1] + '.html');
		}
		else if (property) {
			property = property.split('/');
			$('#mainContent').attr('src', 'class/' + property[0].toLowerCase() + '/properties.html#' + property[1]);
		}
		else if (constant) {
			constant = constant.split('/');
			$('#mainContent').attr('src', 'class/' + constant[0].toLowerCase() + '/constants.html#' + constant[1]);
		}
		else if (classIndex) {
			$('#mainContent').attr('src', 'class/' + classIndex.toLowerCase() + '/index.html');
		}
		else if (defaultIndex) {
			$('#mainContent').attr('src', 'files/included/' + defaultIndex.toLowerCase() + '.html');
		}
		else {
			$('#mainContent').attr('src', 'files/included/readme.html');
		}

		// Write the parent hash to the panel's URL.
		$('#panelContent').get(0).src += location.hash;

	});
	$(window).trigger('hashchange');

	// Modify the history and URL
	// ** Still needs more work before it's working correctly! **
	// (function() {
	// 	if (history.replaceState && location.href.indexOf('#') !== -1) {
	// 		var method = $.bbq.getState('m'),
	// 			property = $.bbq.getState('p'),
	// 			constant = $.bbq.getState('c'),
	// 			classIndex = $.bbq.getState('i'),
	// 			defaultIndex = $.bbq.getState('d');
	//
	// 		if (method) {
	// 			history.replaceState({}, '', 'class/' + method.toLowerCase() + '.html#');
	// 		}
	// 		else if (property) {
	// 			history.replaceState({}, '', 'class/' + property.toLowerCase() + '/properties.html#');
	// 		}
	// 		else if (constant) {
	// 			history.replaceState({}, '', 'class/' + constant.toLowerCase() + '/constants.html#');
	// 		}
	// 		else if (classIndex) {
	// 			history.replaceState({}, '', 'class/' + classIndex.toLowerCase() + '/index.html#');
	// 		}
	// 		else if (defaultIndex) {
	// 			history.replaceState({}, '', 'files/included/' + defaultIndex.toLowerCase() + '.html#');
	// 		}
	// 	}
	// })();
});
