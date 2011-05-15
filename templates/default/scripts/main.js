$(function() {
	var anchors = document.getElementsByTagName("a"),
		i;
	for (i = anchors.length - 1; i >= 0; i--) {
		var anchor = anchors[i];
		if (anchor.href && anchor.href.substr(0,7) === "http://" ||
		    anchor.href && anchor.href.substr(0,8) === "https://") {
			anchor.target = "_blank";
		}
	}
});
