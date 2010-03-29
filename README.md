# NDocs

NDocs is a very simple, lightweight, non-robust parser for the [NaturalDocs](http://naturaldocs.org) source code comment format. It is currently of a beta quality.

It also uses [reflection](http://php.net/reflection) to learn about the code and generate more information about the class.

## Why?

Although I believe that NaturalDocs has a superior way of commenting code over JavaDoc-like solutions (PHPDoc, et al), NaturalDocs doesn't treat PHP like a first-class citizen. Along with that, NaturalDocs makes the same mistake that nearly all documentation generators suffer from -- no way to easily write your own HTML output.

It also appears that work on NaturalDocs 2.0 has stalled, so I'm taking matters into my own hands. This is my first attempt at this kind of project, and I'm open to people who'd like to join the fun.

## High-level goals

### Goals

* Support the NaturalDocs commenting format.
* Support grabbing the actual source code for the methods/properties.
* Support all of the fancier features of reflection to allow the code to self-document.
* It will produce an XML and JSON representation of the code comments and reflection data.
* Make it easy to write/design your own templates and generate docs with them.

### Explicit non-goals

* Will not support parsing languages other than PHP. This is simply because NDocs uses reflection rather than language definitions.

## Requirements

* PHP 5.3.0
* Enabled PHP short tags.
* Command-line access
* `shell_exec()`, `mkdir()`, and `chmod()`.
* Mac OS X or *nix systems, because I'm currently using forward slashes for paths.

## Todo

* Generate the `tree.js` file.
* Generate the `searchdoc.js` file.
* Handle crosslinking via the <link> syntax.
* Support constants.
* Connect a Markdown/Textile parser for content.
* Connect the individual pages to the framed UI.
* Connect Disqus commenting system for web version.
* Generate a `.tgz` and `.zip` bundle of the docs automatically.

## Documentation and usage

You can poke around the code if you like, but I've not written anything up yet. It's still pretty quick-and-dirty.

## License & Copyright

This code is Copyright (c) 2010, Ryan Parman. However, I'm licensing this code for others to use under the [MIT license](http://www.opensource.org/licenses/mit-license.php).
