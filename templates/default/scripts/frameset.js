(function() {
	if (top.location === self.location) {
		var path = document.location.href.split('/'),
			file = path.pop().replace(/\.html/i, ''),
			klass = path.pop(),
			idx;

		switch (file) {
			case 'properties':
				self.location.replace('../../index.html#p=' + klass);
				break;
			case 'constants':
				self.location.replace('../../index.html#c=' + klass);
				break;
			case 'index':
				self.location.replace('../../index.html#i=' + klass);
				break;
			default:
				if (klass === 'included') {
					self.location.replace('../../index.html#d=' + file);
					break;
				}
				else {
					self.location.replace('../../index.html#m=' + klass + '/' + file);
					break;
				}
		}
	}
})();
