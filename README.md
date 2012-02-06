# Vanity ∞
Re-designing Vanity. Again.

Vanity enables you to create wonderfully useful documentation for your projects. Designed from the ground-up to support templates and all sorts of other customizations, Vanity makes it easy for any developer to provide a world-class documentation experience for their customers.

Vanity supports a complete documentation experience, and supports the generation of an API reference, user guides, cookbooks & tutorials, screencasts and more! Vanity also supports full-text search of all of your documentation content.


## How to use
Using simple YAML configuration files, running Vanity can be as simple as typing `vanity generate` in your project directory.

While these configuration files are not strictly required, they certainly simplify the ongoing maintenance of the documentation experience. Anything specified in a configuration file can be overridden at runtime by passing alternate values to the CLI prompt.

A typical Vanity configuration directory looks something like this:

	* project/
		* src/
		* tests/
		* ...
		* _vanity/
			* config.yml
			* changelog.yml
			* groups.yml
			* wiki/
				* chapter-01.md
				* ...
				* chapter-xx.md
				* chapters.yml


## Installation

### Required
The following software is **required** for Vanity to run:

* [PHP](http://php.net) 5.3.2+

### Optional
This software is **optional**, and is only used if you need to generate additional output formats.

* [pandoc](http://johnmacfarlane.net/pandoc/) (for generating **anything** besides Desktop/Mobile HTML)
* [pdfTeX](http://www.tug.org/applications/pdftex/) (for generating PDFs)
* [KindleGen](http://www.amazon.com/gp/feature.html?ie=UTF8&docId=1000234621) (for generating Kindle-compatible eBooks)

### Installing
#### For current user

	wget --quiet http://vanitydoc.org/-/vanity.phar

### Global installation (manual)
* Change into a directory in your path like `cd /usr/local/bin` or `cd /opt/local/bin`
* Get Vanity: `http://vanitydoc.org/-/vanity.phar`
* Make the phar executable: `chmod a+x vanity.phar`
* Rename vanity: `mv vanity.phar vanity`
* Change into a project directory: `cd /path/to/my/project`
* Use Vanity as you normally would.

### Global installation of composer (via Homebrew)
Installing with this Homebrew formula will always get you the latest version.

	brew uninstall vanity &&
	brew install --HEAD https://{domain}/brew/vanity.rb

_You will see a warning "Warning: Cannot verify package integrity"; however, this is benign and expected._


## Development
### Install source from GitHub
To install the source code and dependencies, you'll need [Composer](https://github.com/composer/composer):

	git clone git://github.com/skyzyx/vanity.git &&
	cd vanity &&
	wget --quiet http://getcomposer.org/composer.phar
	php composer.phar install

To simplify many aspects of development, we also have a `build.xml` for Phing. The easiest way to install Phing and any other dependencies is to install [Phix](http://phix-project.org/#install).


## Tests
Tests are written in [PHPT](http://qa.php.net/phpt_details.php) format. You can run them with either the PEAR Test Runner or with PHPUnit 3.6+.

	cd tests/
	pear run-tests .

...or...

	cd tests/
	phpunit .


## Authors, Copyright & Licensing
* Copyright (c) 2010-2012 [Ryan Parman](http://ryanparman.com).
* Copyright (c) 2011-2012 [Ryan McCue](http://ryanmccue.info).

See also the list of [contributors](./contributors) who participated in this project.

Licensed for use under the terms of the [MIT license](http://www.opensource.org/licenses/mit-license.php).
