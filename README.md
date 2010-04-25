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
* <del>Support pulling in PHPT unit tests as examples using a YAML-based `examples.yml` file.</del> (Done)
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

* Update the example code so that it becomes part of the XML/JSON output instead of being post-processed in the template.
* Support class constants.
* Handle crosslinking via the &lt;link> syntax.
* Add support for PHPDoc syntax.
* Provide a reference to a parent class' file location.
* Generate a `.tgz` and `.zip` bundle of the docs automatically.
* Pre-select a page based on a query string parameter.
* Switch over to using `DIRECTORY_SEPARATOR` instead of forward slashes, to improve Windows support.

## On the shoulders of giants...

Vanity wouldn't have been made possible without the following projects:

- [SDoc](http://github.com/voloko/sdoc/) &mdash; The UI template with browse/search support. (MIT-licensed)
- [Spyc](http://code.google.com/p/spyc/) &mdash; Pure PHP YAML parser. (MIT-licensed)
- [PHP Markdown](http://michelf.com/projects/php-markdown/) &mdash; Useful for converting large, free-form blocks of text into HTML. (BSD-licensed)
- [PHPT Parser](http://github.com/skyzyx/phpt/) &mdash; Parses PHPT unit test files. (MIT-licensed)
- [Syntax Highlighter](http://alexgorbatchev.com/wiki/SyntaxHighlighter) &mdash; Used when displaying source code. (LGPL-licensed)

## Documentation and usage

You can poke around the code if you like, but I've not written anything up yet. It's still pretty quick-and-dirty.

## License & Copyright

This code is Copyright (c) 2010, Ryan Parman. However, I'm licensing this code for others to use under the [MIT license](http://www.opensource.org/licenses/mit-license.php).
