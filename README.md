# Vanity

Vanity uses PHP's [Reflection](http://php.net/reflection) API to generate source code reference documentation.

It has a very simple, lightweight, non-robust parser for the [NaturalDocs](http://naturaldocs.org) source code comment format. PHPDoc support is coming soon. It is currently of a beta quality.

## Why?

* The project was started because it appears that work on NaturalDocs 2.0 has stalled, and even then, PHP is not treated as a first-class citizen.
* I also wanted something that allowed me to write my own HTML templates. None of the current solutions make that easy (e.g. PHPDocumentor, NaturalDocs, Doxygen).
* I'm inspired by how good the third-party Ruby on Rails and jQuery documentation systems are. I wanted to build something that had my favorite features (e.g. search, browse, keyboard navigation, inline examples, comments).

## Features

* Supports the NaturalDocs commenting format. (PHPDoc support is forthcoming.)
* Leverages PHP's Reflection API, allowing your code to self-document.
* Makes it easy to write/design your own templates and generate docs with them.
* Features a search/browse panel for finding whatever you're looking for.
* Link directly to a specific page in the documentation by copy-pasting directly from the address bar.
* Supports the ability to add inline code examples by referencing your PHPT unit tests.
* Produces XML & JSON representations of the code docblocks and reflection data.
* Use your Markdown-formatted READMEs from GitHub as the documentation README.
* Documents object-oriented PHP code (e.g. classes, methods, properties, and class constants).

### Explicit non-goals

* Will not support parsing languages other than PHP. This is simply because Vanity uses reflection rather than language definitions.
* (That being said, you will soon be able to generate docs with this tool if you can provide an XML representation of your source code, and write your templates against that XML.)

## Requirements

* PHP 5.3.0
* PHP short tags enabled.
* Command-line access
* `shell_exec()`, `mkdir()`, and `chmod()`.
* Mac OS X or *nix systems, because I'm currently using forward slashes for most paths.

## Todo, by priority

1. Ensure that cross-links update the location hash.
2. Enable the ability to custom-block certain keywords in class names.
3. Add support for [PHPDoc](http://phpdoc.org) syntax.
4. Add slip-in support for commenting systems like Disqus, Intense Debate, or something custom.
5. Support the ability to skip the lexer and jump right into generating docs from the templates and existing XML documents.
6. Switch over to using `DIRECTORY_SEPARATOR` instead of forward slashes, to improve Windows support.

## On the shoulders of giants...

Vanity wouldn't have been made possible without the following projects:

- [SDoc](http://github.com/voloko/sdoc/) &mdash; The UI template with browse/search support. (MIT-licensed)
- [Spyc](http://code.google.com/p/spyc/) &mdash; Pure PHP YAML parser. (MIT-licensed)
- [Chili](http://code.google.com/p/jquery-chili-js/) &mdash; Syntax highlighter for source code. (MIT-licensed)
- [jQuery](http://jquery.com) &mdash; A fast and concise JavaScript Library that simplifies HTML document traversing, event handling, animating, and Ajax interactions for rapid web development. (MIT-licensed)
- [PHPT Parser](http://github.com/skyzyx/phpt/) &mdash; Parses PHPT unit test files. (MIT-licensed)
- [PHP Markdown](http://michelf.com/projects/php-markdown/) &mdash; Useful for converting large, free-form blocks of text into HTML. (BSD-licensed)

## Documentation and usage

	git clone git://github.com/skyzyx/vanity.git
	cd vanity

...or...

	svn co http://svn.github.com/skyzyx/vanity.git vanity
	cd vanity

It might be helpful to create a symlink of `vanity` to `/usr/local/bin/vanity`.

Move into your PHP project directory (with NaturalDocs-formatted docblocks), and run `vanity --match="*.php" --title="My Awesome Project"`.

### Notes

- Running `./vanity` will give you the list of options.
- `--match="*.class.php"` will use glob to find all files that match the `*.class.php` pattern.
- `--title="Reference Documentation"` will add the title to (a) the browser's titlebar, (b) the title of the README page, and (c) will be the name of the decompressed offline docs directory.
- `--readme="README.md"` will use the `./README.md` file for the documentation's README page, and will parse the contents with Markdown. If this is left out, the default README content will be used instead.
- Examples are pulled from `examples.yml` files that are found within the tree structure. They are YAML documents, and the paths they point to are relative to the location of the `examples.yml` file. All of the data from these files is merged together. Methods that are inherited from parent classes will contain the examples defined for the parent class.
	- Code examples are pulled directly from PHPT unit tests. (In the future, I'll add support for freeform examples.)
	- I find that by using my own unit tests as examples, they're sure to be up to date.
	- I prefer the PHPT format because they're easy to read, easy to write, and easy to re-use as examples.
	- See the [RequestCore](http://github.com/skyzyx/requestcore/blob/master/examples.yml) file or the [CloudFusion](http://github.com/skyzyx/cloudfusion/blob/master/examples.yml) file for an example of the format.

## License & Copyright

This code is Copyright (c) 2010, Ryan Parman. However, I'm licensing this code for others to use under the [MIT license](http://www.opensource.org/licenses/mit-license.php).
