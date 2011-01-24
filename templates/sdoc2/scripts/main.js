window.highlight = function(url) {
	var hash = url.match(/#([^#]+)$/);
	if (hash) {
		$('a[name=' + hash[1] + ']').parent().effect('highlight', {}, 'slow')
	}
}

$(function() {
	var rel;

	$('a').live('click', function(evt) {
		rel = $(this).attr('rel');
		if (rel) {
			top.location.href = rel;
			evt.preventDefault();
		}
	});

	(function() {
		var anchors = document.getElementsByTagName("a"),
			i;
		for (i = anchors.length - 1; i >= 0; i--) {
			var anchor = anchors[i];
			if (anchor.href && anchor.href.substr(0,7) === "http://" ||
			    anchor.href && anchor.href.substr(0,8) === "https://") {
				anchor.target = "_blank";
			}
		}
	})();


	highlight('#' + location.hash);
});
