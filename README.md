# Vanity ∞

Re-designing Vanity. Again.


## Requirements

The following software is required for Vanity to run:

* [PHP](http://php.net) 5.3+


### Extras

* [pandoc](http://johnmacfarlane.net/pandoc/) (for generating **anything** besides Desktop/Mobile HTML)
* [pdfTeX](http://www.tug.org/applications/pdftex/) (for generating PDFs)
* [KindleGen](http://www.amazon.com/gp/feature.html?ie=UTF8&docId=1000234621) (for generating Kindle-compatible eBooks)


## Organization of Vanity content in your project

This is how your project should organize Vanity assets.

	{Project}:
	    - _vanity:
	        - config.yml
	        - bootstrap.php
	        - groups.yml
	        - toolbar.html
	        - wiki:
	            - index.md
	            - other.md
	            - chapters.yml
	    - tests:
	        - {directory}:
	            - examples.yml
