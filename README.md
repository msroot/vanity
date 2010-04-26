# Vanity

Vanity uses PHP's [Reflection](http://php.net/reflection) API to generate source code reference documentation.

It has a very simple, lightweight, non-robust parser for the [NaturalDocs](http://naturaldocs.org) source code comment format. PHPDoc support is coming soon. It is currently of a beta quality.

## Why?

* The project was started because it appears that work on NaturalDocs 2.0 has stalled, and even then, PHP is not treated as a first-class citizen.
* I also wanted something that allowed me to write my own HTML templates. None of the current solutions make that easy (e.g. PHPDocumentor, NaturalDocs, Doxygen).
* I'm inspired by how good the third-party Ruby on Rails and jQuery documentation systems are. I wanted to build something that had my favorite features (e.g. search, browse, keyboard navigation, inline examples, comments).

## High-level goals

### Goals

* <del>Support the NaturalDocs commenting format.</del> (Done)
* Support the PHPDoc commenting format.
* <del>Support grabbing the actual source code for the methods/properties.</del> (Done)
* <del>Support all of the fancier features of reflection to allow the code to self-document.</del> (Done)
* <del>It will produce an XML and JSON representation of the code comments and reflection data.</del> (Done)
* <del>Make it easy to write/design your own templates and generate docs with them.</del> (Done)
* <del>Integrate or leverage the best features from <http://railsapi.com>.</del> (Done)
* <del>Support recursively pulling in examples using YAML-based `examples.yml` files.</del> (Done)
* Add slip-in support for commenting systems like Disqus, Intense Debate, or something custom.

### Explicit non-goals

* Will not support parsing languages other than PHP. This is simply because Vanity uses reflection rather than language definitions.
* (That being said, you will soon be able to generate docs with this tool if you can provide an XML representation of your source code, and write your templates against that XML.)

## Requirements

* PHP 5.3.0
* PHP short tags enabled.
* Command-line access
* `shell_exec()`, `mkdir()`, and `chmod()`.
* Mac OS X or *nix systems, because I'm currently using forward slashes for most paths.

## Todo

* Add support for [PHPDoc](http://phpdoc.org) syntax.
* Handle crosslinking via the &lt;link> syntax.
* Pre-select a page based on a query string parameter.
* Switch over to using `DIRECTORY_SEPARATOR` instead of forward slashes, to improve Windows support.
* Update the example code so that it becomes part of the XML/JSON output instead of being post-processed in the template.

## On the shoulders of giants...

Vanity wouldn't have been made possible without the following projects:

- [SDoc](http://github.com/voloko/sdoc/) &mdash; The UI template with browse/search support. (MIT-licensed)
- [Spyc](http://code.google.com/p/spyc/) &mdash; Pure PHP YAML parser. (MIT-licensed)
- [PHPT Parser](http://github.com/skyzyx/phpt/) &mdash; Parses PHPT unit test files. (MIT-licensed)
- [PHP Markdown](http://michelf.com/projects/php-markdown/) &mdash; Useful for converting large, free-form blocks of text into HTML. (BSD-licensed)
- [Syntax Highlighter](http://alexgorbatchev.com/wiki/SyntaxHighlighter) &mdash; Used when displaying source code. (LGPL-licensed)

## Documentation and usage

	git clone git://github.com/skyzyx/vanity.git
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
