(function() {

	// Expose the SearchProvider class.
	window.SearchProvider = function() {

		// Store initial properties
		var self = this;
		self.$searchForm = $('#global-search');
		self.$selectable = this.$searchForm.find('select');
		self.$query = this.$searchForm.find('input[name=q]');
		self.map = {};

		// Clear any existing search providers
		this.clearProviders = function() {
			self.$selectable.html('');
			return self;
		};

		// Add a new search provider
		this.addProvider = function(name, querystring) {
			var _ = DOMBuilder;
			self.$selectable.get(0).appendChild(_.DOM(
				_('option', {
					'value': querystring
				}).html(name)
			));
			return self;
		};

		// Execute on DOM ready
		$(function() {

			// When a search is submitted, execute any existing search providers
			self.$searchForm.submit(function(evt) {
				window.open(self.$selectable.val().replace(/\{query\}/, self.$query.val()));
				evt.preventDefault();
			});

			// When the selectable is changed, shift focus to the query
			self.$selectable.change(function(evt) {
				self.$query.focus();
				self.$query.select();
			});
		});
	};
})();
