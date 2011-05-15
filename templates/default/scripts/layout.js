$(function() {
	var outerLayout = $('body').layout({
		minSize: 100,
		west__size: 300,
		east__size: 300,
		useStateCookie: false
	}),
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
		// },
		// east: {
		// 	paneSelector: ".center-east",
		// 	minSize: 50,
		// 	size: 300
		}
	}),
	westLayout = $('div.ui-layout-west').layout({
		minSize: 50,
		center__paneSelector: ".west-center",
		south__paneSelector: ".west-south",
		south__size: 41
	});
});
