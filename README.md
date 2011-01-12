# Vanity 2 (Alpha)

**Vanity is a tool that generates documentation from PHP source code by using _reflection_.**

Vanity was created as a much-improved replacement for [NaturalDocs](http://naturaldocs.org). Now, however, I'm rewriting it to use PHPDoc syntax instead.

<http://vanitydoc.org> | [@VanityDoc](http://twitter.com/vanitydoc)

## Why?

* The project was started because it appears that work on NaturalDocs 2.0 has stalled, and even then, PHP is not treated as a first-class citizen.
* I also wanted something that allowed me to write my own HTML templates. None of the current solutions make that easy (e.g. PHPDocumentor, NaturalDocs, Doxygen).
* I'm inspired by how good the third-party Ruby on Rails and jQuery documentation systems are. I wanted to build something that had my favorite features (e.g. search, browse, keyboard navigation, inline examples, comments).

## Features

What sets Vanity apart from other API reference generators?

* A default template that doesn't completely suck.
* The ability to easily design and build your own custom templates.
* Leverages PHP's [Reflection](http://php.net/reflection) API, allowing your code to self-document.
* Supplements the Reflection data using information from docblocks. PHPDoc support is forthcoming.
* Properly documents class inheritance (extending and overriding), which is something that NaturalDocs doesn't support for PHP.

### Other features

* Default template features a search/browse panel for finding whatever you're looking for.
* Link directly to a specific page in the documentation by copy-pasting directly from the address bar.
* Supports the ability to add inline code examples by referencing your PHPT unit tests.
* Produces XML & JSON representations of the code docblocks and reflection data.
* Use your Markdown-formatted READMEs from GitHub as the documentation README.
* Documents object-oriented PHP code (e.g. classes, methods, properties, and class constants).

### Explicit non-goals

* Will not support parsing languages other than PHP. This is simply because Vanity uses reflection rather than language definitions.

## Requirements

* PHP 5.3.0 (will try to drop this to 5.2.x at some point).
* PHP short tags enabled.
* Command-line access
* `shell_exec()`, `mkdir()`, and `chmod()`.
* Mac OS X or *nix systems, because I'm currently using forward slashes for most paths.

## Documentation and usage

	git clone git://github.com/skyzyx/vanity.git
	cd vanity

...or...

	svn co http://svn.github.com/skyzyx/vanity.git vanity
	cd vanity

It might be helpful to create a symlink of `vanity` to `/usr/local/bin/vanity`.

Move into your PHP project directory (with NaturalDocs-formatted docblocks), and run `vanity --match="*.php" --title="My Awesome Project"`.

## License, copyright, and standing on the shoulders of giants...

Vanity itself is Copyright (c) 2010, Ryan Parman, and is licensed for use under the [MIT license](http://www.opensource.org/licenses/mit-license.php). That being said, Vanity wouldn't have been made possible without the following projects:

- [SDoc](http://github.com/voloko/sdoc/) &mdash; The UI template with browse/search support. (MIT-licensed)
- [Spyc](http://code.google.com/p/spyc/) &mdash; Pure PHP YAML parser. (MIT-licensed)
- [Chili](http://code.google.com/p/jquery-chili-js/) &mdash; Syntax highlighter for source code. (MIT-licensed)
- [jQuery](http://jquery.com) &mdash; A fast and concise JavaScript Library that simplifies HTML document traversing, event handling, animating, and Ajax interactions for rapid web development. (MIT-licensed)
- [PHPT Parser](http://github.com/skyzyx/phpt/) &mdash; Parses PHPT unit test files. (MIT-licensed)
- [PHP Markdown](http://michelf.com/projects/php-markdown/) &mdash; Useful for converting large, free-form blocks of text into HTML. (BSD-licensed)

All code inside Vanity is BSD, MIT, PHP or Apache 2.0 licensed, and I avoid GPL code like the plague. This should make it friendly to those who want to avoid legal issues with using GPL software.
