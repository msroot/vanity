$(function() {

	// jquery.layout seems to have issues if the iframe src isn't properly loaded yet.
	$('iframe.west-center').load(function() {

		// parent layout
		var outerLayout = $('body').layout({
			defaults: {
				minSize: 100,
				useStateCookie: false
			},
			west: {
				size: 300,
				paneSelector: ".ui-layout-west"
			},
			center: {
				paneSelector: ".ui-layout-center"
			}
		}),

		// center + north
	 	centerLayout = $('div.ui-layout-center').layout({
			defaults: {
				minSize: 40
			},
			center: {
				paneSelector: ".center-center"
			},
			north: {
				paneSelector: ".center-north",
				size: 41,
				closable: false,
				resizable: false,
				slidable: false,
				spacing_open: 0,
				spacing_closed: 0
			}
		}),

		// west + south
		westLayout = $('div.ui-layout-west').layout({
			minSize: 50,
			center: {
				paneSelector: ".west-center"
			},
			south: {
				paneSelector: ".west-south",
				resizable: true,
				slidable: true,
				minSize: 41,
				maxSize: 300,
				size: 200
			}
		});

		// Focus on search field
	    $('#search').focus();
	});
});
