# NDocs

NDocs is a very simple, lightweight, non-robust parser for the NaturalDocs source code comment format. It is currently of a beta quality.

Although I believe that NaturalDocs has a superior way of commenting code over JavaDoc-like solutions (PHPDoc, et al), NaturalDocs doesn't treat PHP like a first-class citizen. Along with that, NaturalDocs makes the same mistake that nearly all documentation generators suffer from -- no way to easily write your own HTML output.

It also appears that work on NaturalDocs 2.0 has stalled, so I'm taking matters into my own hands. This is my first attempt at this kind of project, and I'm open to people who'd like to join the fun.

## High-level goals

### Goals

* This is intended to be a low-level, parser-only engine.
* It will produce an XML and JSON representation of the code comments.
	* This will allow others to take the XML and generate their own documentation templates.
	* Should run from the command-line and write an XML/JSON file to disk.
* Support grabbing the actual source code for the methods/properties.
* Generate various indexes.
* Will generate documentation from very simple, easily-modifiable templates.

### Explicit non-goals

* Will not support parsing languages other than PHP.

## License & Copyright

This code is Copyright (c) 2010, Ryan Parman. However, I'm licensing this code for others to use under the [MIT license](http://www.opensource.org/licenses/mit-license.php).
