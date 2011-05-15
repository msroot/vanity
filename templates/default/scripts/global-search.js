$(function() {
	var searchForm = $('#global-search');
	searchForm.submit(function(evt) {
		if ($('#global-search select[name=query]').val() === 'site:forums.aws.amazon.com') {
			window.open('https://forums.aws.amazon.com/search.jspa?objID=f80&q=' + $('input[name=q]').val());
			evt.preventDefault();
		}
		else if ($('#global-search select[name=query]').val() === 'site:stackoverflow.com') {
			window.open('http://stackoverflow.com/search?q=' + $('input[name=q]').val());
			evt.preventDefault();
		}
		else if ($('#global-search select[name=query]').val() === 'site:php.net') {
			window.open('http://php.net/search.php?pattern=' + $('input[name=q]').val());
			evt.preventDefault();
		}
	});
});
