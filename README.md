# Vanity 2 (Beta): Now with less suck!

**Vanity generates API reference documentation from PHP source code, and is intended to be a replacement for PHPDocumentor.**

Vanity was born out of a need to provide users of my [CloudFusion](http://getcloudfusion.com) project a vastly improved
documentation experience. After jumping from PHPDocumentor, to Doxygen, to NaturalDocs, I decided to sit down and write
something completely from scratch. Thus, Vanity 1.0 was born.

The biggest problem I had with existing API reference generators was the inability to write my own templates. Most of them
offer alternate "styles" (as in, different CSS files). PHPDocumentor supposedly enabled users to write custom templates, but
the documentation was so obtuse that I gave up after a couple of weeks of trying to decypher what the hell was going on.

During this time, I learned a lot about how to make a better documentation generator. At the same time,
[Amazon Web Services](http://aws.amazon.com) forked CloudFusion and hired me to work on the
[AWS SDK for PHP](http://aws.amazon.com/sdkforphp). As the software grew, Vanity 1.0 started showing its cracks. So I took
the lessons I'd learned the first time around, and built something better. I call this **Vanity 2**.

I would encourage you to peruse the [wiki](https://github.com/skyzyx/vanity/wiki) for deeper details.

<http://vanitydoc.org> | [@VanityDoc](http://twitter.com/vanitydoc)


## Features

* Fast generation of documentation.
* Has an _attractive_ default template.
	* Has a well-defined approach to designing and defining your own HTML templates.
* Produces documentation in HTML, JSON, XML and Serialized PHP by default. This enables third-party developers to build even cooler stuff on top of your documentation.
	* Vanity keeps a cached copy of the PHP.net documentation on-hand. If you have a class that inherits from one of PHP's built-in classes, those inherited methods are also documented using the PHP.net content.
	* You can inject custom "partials" into the default template without touching the default template itself. This is perfect for metric tracking tools, copyright notices and similar project-specific things.
* Lover of YAML for configuration; Markdown and HTML for content.
	* Also generates content with "smart punctuation" -- curly quotes, curly apostrophes, em- and en-dashes, ellipses and more!
* You are **strongly** encouraged to use real, live unit tests as code samples in your documentation.
	* Usage examples and method source code is parsed by a syntax highlighter for prettier code.
	* Has built-in support for the simple, lightweight PHPT unit test format.
	* If you're already invested in PHPUnit or SimpleTest, you can keep using those for tests. However, all usage examples are defined using the simple, lightweight PHPT test syntax.
	* Support for Examplify! enables you to leverage a comment-based syntax for hiding certain lines of your tests, or replacing strings in your tests before they are used for example/instructional purposes in your documentation.
* Vanity is sensitive to projects that have generated code instead of hand-written code. As such, you can plug-in custom data without having to edit your generated PHP code.
	* You can define how to group together related methods with the `@see` tag or an external `groups.yml` file.
	* You can define _See Also_ links with the `@link` tag or an external `seealso.yml` file.
	* You can define changes to specific methods (e.g., backwards-incompatible changes) using the `@changelog` tag or an external `changelog.yml` file.
	* You can also pass a series of configuration options via the command line, or you can store a set in a `config.yml` file.
* Has built-in support for GitHub projects. This is relied upon for viewing the source code for an entire file.
* Supports excluding classes and methods from being documented.
* Describes each class' inheritance chain and implemented interfaces, if any.


## DocBlock Tags

### PHPDocumentor tags

Technically, Vanity supports any tag your little heart could ever wish to create. However, Vanity doesn't necessarily
_understand_ all tags. _Understood_ tags are specifically accounted for and handled. Tags that are not understood are
still pulled in, but are treated as metadata.

These are PHPDocumentor tags that Vanity _understands_.

<dl>
	<dt><a href="http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.author.pkg.html">@author</a></dt>
	<dd>Used to define the author of a class.</dd>
	<dt><a href="http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.link.pkg.html">@link</a></dt>
	<dd>Used to define an off-site link.</dd>
	<dt><a href="http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.param.pkg.html">@param</a></dt>
	<dd>Used to define a method parameter.</dd>
	<dt><a href="http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.return.pkg.html">@return</a></dt>
	<dd>Used to define the return type of a method.</dd>
	<dt><a href="http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.see.pkg.html">@see</a></dt>
	<dd>Used to reference another method within the software you're documenting, or a native PHP function/method.</dd>
	<dt><a href="http://manual.phpdoc.org/HTMLSmartyConverter/HandS/phpDocumentor/tutorial_tags.since.pkg.html">@since</a></dt>
	<dd>Used to define when a method came to exist.</dd>
</dl>

### Differently-handled tags

These are _ideas_ that PHPDocumentor implements with tags, but Vanity handles in a different way.

<dl>
	<dt><code>@example</code></dt>
	<dd>Uses a file called <code>examples.yml</code> to refer to either real, live <a href="http://qa.php.net/write-test.php">PHPT</a> unit tests, or a PHPT-formatted file.</dd>
	<dt><code>@filesource</code></dt>
	<dd>All methods have their source, file, and starting/ending line numbers pulled into the documentation. This is unavoidable.</dd>
	<dt><code>@ignore</code></dt>
	<dd>Any classes or methods that you want to ignore are defined in the <code>config.yml</code> file, and supports PCRE regular expression matching.</dd>
	<dt><code>@tutorial</code></dt>
	<dd>Treated the same way as <code>@example</code>.</dd>
	<dt><code>@uses</code></dt>
	<dd>Use <code>@see</code> instead.</dd>
	<dt><code>@version</code></dt>
	<dd>Use <code>@changelog</code> instead. You can also define changelogs for specific methods in a <code>changelog.yml</code> file.</dd>
</dl>

### TODO Tags (Maybe)

These are tags I _may_ add support for eventually. I've not made up my mind one way or another. Want me to add it?
Convince me why it's a worthwhile investment of my time and I'll consider it.

<dl>
	<dt><code>@deprecated</code></dt>
	<dd>The word "deprecated" will show up somewhere in the page, and can be styled independently via a CSS classname hook of "deprecated". Maybe it should have a bright red background?</dd>
	<dt><code>@category</code></dt>
	<dt><code>@copyright</code></dt>
	<dt><code>@global</code></dt>
	<dd>Since Vanity uses PHP's Reflection API, it doesn't pick up global variables and functions.</dd>
	<dt><code>@internal</code></dt>
	<dt><code>@license</code></dt>
	<dt><code>@package</code></dt>
	<dt><code>@property</code></dt>
	<dt><code>@subpackage</code></dt>
	<dt><code>@todo</code></dt>
	<dd>I will definitely add support for some sort of list of <code>@todo</code>s.</dd>
	<dt><code>@var</code></dt>
	<dd>Couldn't hurt. Not a bad idea.</dd>
</dl>

### Tags Vanity will never, ever support

These are tags that are _completely_ off the discussion board because they're no longer needed in PHP 5 with the advent
of the Reflection API.

* `@abstract`
* `@access`
* `@final`
* `@method`
* `@static`
* `@staticvar`


## Known Issues & TODOs

* Currently, Vanity determines which classes to document by diffing <code>get_declared_classes()</code> before and after loading all classes into memory. There are much better ways of doing this.
* I haven't yet tested any packages that leverage PHP 5.3 namespaces. I'm not even sure what would happen. I haven't _intentionally_ done anything to support namespaces yet.
* While Vanity tries to be responsible with the memory it uses, it still relies on loading a bunch of code into memory. This makes large projects explode violently. (e.g., Zend Framework)
* Vanity doesn't do a very good job of handling code with a crap-ton of dependencies, complex autoloading rules, or things that rely on the magic of `PATH` definitions. (e.g., Zend Framework)
* As of this moment, I've only tested Vanity on the AWS SDK for PHP, SimplePie and Zend Framework. Zend Framework exploded. I still have a lot more testing to do.

A list of other TODOs is here: <https://github.com/skyzyx/vanity/issues>
