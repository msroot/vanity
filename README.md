# Vanity 2 (Alpha)

**Vanity is a tool that generates documentation from PHP source code by using _reflection_.**

Vanity was created as a much-improved replacement for [NaturalDocs](http://naturaldocs.org). Now, however, I'm rewriting it to use PHPDoc syntax instead.

<http://vanitydoc.org> | [@VanityDoc](http://twitter.com/vanitydoc)

## Why?

* The project was started because it appears that work on NaturalDocs 2.0 has stalled, and even then, PHP is not treated as a first-class citizen.
* I also wanted something that allowed me to write my own HTML templates. None of the current solutions make that easy (e.g. PHPDocumentor, NaturalDocs, Doxygen).
* I'm inspired by how good the third-party Ruby on Rails and jQuery documentation systems are. I wanted to build something that had my favorite features (e.g. search, browse, keyboard navigation, inline examples, comments).

## Documentation

A list of to-dos is here: <https://github.com/skyzyx/vanity/issues>

Documentation is not ready yet, as this branch is in the middle of a complete rewrite. However, here is a list of configuration options:

### Working

* help
* match
* output
* version

### Not working yet

<table>
	<tr><td>exclude-access</td><td>Simple, but haven't gotten to it yet.</td></tr>
	<tr><td>exclude-classes</td><td>Simple, but haven't gotten to it yet.</td></tr>
	<tr><td>markdown (all)</td><td>Markdown is already in the core. I just need to determine when/where to call it.</td></tr>
	<tr><td>readme</td><td>This depends on Markdown being hooked up.</td></tr>
	<tr><td>template</td><td>This depends on the lexer being hooked up.</td></tr>
	<tr><td>title</td><td>This depends on having one or more templates.</td></tr>
	<tr><td>add-group</td><td>This depends on having one or more templates.</td></tr>
	<tr><td>add-files</td><td>This depends on having one or more templates.</td></tr>
	<tr><td>offline</td><td>This depends on having one or more templates.</td></tr>
	<tr><td>skip-lexer</td><td>This depends on the lexer being hooked up and a template existing before I can add/test this.</td></tr>
	<tr><td>cache</td><td>Pretty easy, but will likely be one of the very last things I implement.</td></tr>
	<tr><td>fresh</td><td>This depends on caching.</td></tr>
</table>
