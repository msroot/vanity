$(function () {
    var panel = new Searchdoc.Panel($('#panel'), search_data, tree);
    $('#search').focus();

	// Scroll position
	if ($('li.current:first').length) {
		setTimeout(function() {
			$('div.tree:first').animate({
				'scrollTop': $('li.current:first').position().top -= 100
			}, {
				'duration': 500,
				'easing': 'easeOutQuad'
			});
		}, 500);
	}
});
