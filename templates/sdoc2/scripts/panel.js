function placeholder() {
    if ('placeholder' in document.createElement('input')) return;

    $('#search-label').click(function () {
        $('#search').focus();
        $('#search-label').hide();
    });

    $('#search').focus(function () {
        $('#search-label').hide();
    });

    $('#search').blur(function () {
        this.value == '' && $('#search-label').show()
    });

    $('#search')[0].value == '' && $('#search-label').show();
}

$(function () {
    placeholder();
    var panel = new Searchdoc.Panel($('#panel'), search_data, tree, top.frames[1]);
    $('#search').focus();

	var s = null;
	if (window.parent.location.href) {
		s = window.parent.location.href.match(/\?q=([^&]+)/);
	}
    if (s) {
        s = decodeURIComponent(s[1]).replace(/\+/g, ' ');
        if (s.length > 0) {
            $('#search').val(s);
            panel.search(s, true);
        }
    }

	$(window).bind('hashchange', function(e) {
		var method = $.bbq.getState('m'),
			property = $.bbq.getState('p'),
			constant = $.bbq.getState('c'),
			classIndex = $.bbq.getState('i'),
			defaultIndex = $.bbq.getState('d'),
			$class = [], i = 0,
			$item, $klass, $parent_element, j;

		if (defaultIndex) {
			$item = $('div.tree li.level_0 h1 span').filter(function() {
				return $(this).text().toLowerCase() === defaultIndex.toLowerCase();
			}).closest('li.level_0');
			panel.tree.select($item);
		}
		else if (method) {
			method = method.split('/');

			while ($class.length === 0) {
				$class = $('div.tree li.level_' + i + ' h1 span').filter(function() {
					return $(this).text().toLowerCase() === method[0].toLowerCase();
				}).closest('li.level_' + i);

				if ($class.length === 0) {
					i++;
				}
			}

			panel.tree.toggle($class); // Parent
			if (i > 0) {
				for (j = i; j >= 0; j--) {
					$prev = $class.prevUntil('li.level_' + j);

					if ($prev.length > 1) {
						if (j === 0) {
							$parent_element = $prev.prev().first();
							panel.tree.toggle($parent_element); // Grandparent(s)
						}
					}
					else if ($prev.length === 1) {
						$parent_element = $prev.first();
						panel.tree.toggle($parent_element); // Grandparent(s)
					}
				}
			}

			$item = $class.nextUntil('div.tree li.level_' + (i - 1)).find('span').filter(function() {
				return $(this).text().toLowerCase() === method[1].toLowerCase();
			}).closest('li');

			panel.tree.select($item);
		}
		else if (property) {
			property = property.split('/');

			while ($class.length === 0) {
				$class = $('div.tree li.level_' + i + ' h1 span').filter(function() {
					return $(this).text().toLowerCase() === property[0].toLowerCase();
				}).closest('li.level_' + i);

				if ($class.length === 0) {
					i++;
				}
			}

			panel.tree.toggle($class); // Parent
			if (i > 0) {
				for (j = i; j >= 0; j--) {
					$prev = $class.prevUntil('li.level_' + j);

					if ($prev.length > 1) {
						if (j === 0) {
							$parent_element = $prev.prev().first();
							panel.tree.toggle($parent_element); // Grandparent(s)
						}
					}
					else if ($prev.length === 1) {
						$parent_element = $prev.first();
						panel.tree.toggle($parent_element); // Grandparent(s)
					}
				}
			}
			panel.tree.select($class.next().next());
		}
		else if (constant) {
			constant = constant.split('/');

			while ($class.length === 0) {
				$class = $('div.tree li.level_' + i +' h1 span').filter(function() {
					return $(this).text().toLowerCase() === constant[0].toLowerCase();
				}).closest('li.level_' + i);

				if ($class.length === 0) {
					i++;
				}
			}

			panel.tree.toggle($class); // Parent
			if (i > 0) {
				for (j = i; j >= 0; j--) {
					$prev = $class.prevUntil('li.level_' + j);

					if ($prev.length > 1) {
						if (j === 0) {
							$parent_element = $prev.prev().first();
							panel.tree.toggle($parent_element); // Grandparent(s)
						}
					}
					else if ($prev.length === 1) {
						$parent_element = $prev.first();
						panel.tree.toggle($parent_element); // Grandparent(s)
					}
				}
			}
			panel.tree.select($class.next());
		}
		else if (classIndex) {

			while ($class.length === 0) {
				$class = $('div.tree li.level_' + i + ' h1 span').filter(function() {
					return $(this).text().toLowerCase() === classIndex.toLowerCase();
				}).closest('li.level_' + i);

				if ($class.length === 0) {
					i++;
				}
			}

			panel.tree.toggle($class); // Parent
			if (i > 0) {
				for (j = i; j >= 0; j--) {
					$prev = $class.prevUntil('li.level_' + j);

					if ($prev.length > 1) {
						if (j === 0) {
							$parent_element = $prev.prev().first();
							panel.tree.toggle($parent_element); // Grandparent(s)
						}
					}
					else if ($prev.length === 1) {
						$parent_element = $prev.first();
						panel.tree.toggle($parent_element); // Grandparent(s)
					}
				}
			}
			panel.tree.select($class);
		}
	});
	$(window).trigger('hashchange');

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
