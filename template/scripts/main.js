window.highlight = function(url) {
	var hash = url.match(/#([^#]+)$/);
	if (hash) {
		$('a[name=' + hash[1] + ']').parent().effect('highlight', {}, 'slow')
	}
}

$(function() {
	highlight('#' + location.hash);
});
