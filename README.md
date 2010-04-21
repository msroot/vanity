# Vanity

Vanity uses PHP's [Reflection](http://php.net/reflection) API to generate source code reference documentation.

It has a very simple, lightweight, non-robust parser for the [NaturalDocs](http://naturaldocs.org) source code comment format. PHPDoc support is coming soon. It is currently of a beta quality.

## Why?

* The project was started because it appears that work on NaturalDocs 2.0 has stalled, and even then, PHP is not treated as a first-class citizen.
* I also wanted something that allowed me to write my own HTML templates. None of the current solutions make that easy (e.g. PHPDocumentor, NaturalDocs, Doxygen).
* I'm inspired by how good the third-party Ruby on Rails and jQuery documentation systems are. I wanted to build something that had my favorite features (e.g. search, browse, keyboard navigation, inline examples, comments).

## High-level goals

### Goals

* Support the NaturalDocs commenting format. (Done)
* Support the PHPDoc commenting format.
* Support grabbing the actual source code for the methods/properties. (Done)
* Support all of the fancier features of reflection to allow the code to self-document. (Done)
* It will produce an XML and JSON representation of the code comments and reflection data. (Done)
* Make it easy to write/design your own templates and generate docs with them. (Done)
* Integrate or leverage the best features from <http://railsapi.com>.

### Explicit non-goals

* Will not support parsing languages other than PHP. This is simply because Vanity uses reflection rather than language definitions.
* (That being said, you can generate docs with this tool if you can provide an XML representation of your source code, and write your templates against that XML.)

## Requirements

* PHP 5.3.0
* Enabled PHP short tags.
* Command-line access
* `shell_exec()`, `mkdir()`, and `chmod()`.
* Mac OS X or *nix systems, because I'm currently using forward slashes for paths.

## Todo

* Handle crosslinking via the &lt;link> syntax.
* Add support for PHPDoc syntax.
* Provide a reference to a parent class' file location.
* Post-process the example markers.
* Support class constants.
* Connect Disqus commenting system for web version.
* Generate a `.tgz` and `.zip` bundle of the docs automatically.

## Documentation and usage

You can poke around the code if you like, but I've not written anything up yet. It's still pretty quick-and-dirty.

## License & Copyright

This code is Copyright (c) 2010, Ryan Parman. However, I'm licensing this code for others to use under the [MIT license](http://www.opensource.org/licenses/mit-license.php).
