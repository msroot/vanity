<?php
class SimpleXMLExtended extends SimpleXMLElement
{
	public function addCDATA($cdata_text)
	{
		$node = dom_import_simplexml($this);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}

class Lexer
{
	private $linkmap;

	public function __construct($linkmap)
	{
		$this->linkmap = $linkmap;
	}

	public function parse_class($class_name, $dir_output)
	{
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><vanity xmlns="http://vanitydoc.org"></vanity>', 'SimpleXMLExtended', LIBXML_NOCDATA);

		// Collect class data
		$rclass = new ReflectionClass($class_name);
		$rclass_properties = $rclass->getDefaultProperties();
		$rclass_constants = $rclass->getConstants();
		$rclass_methods = $rclass->getMethods();
		$rclass_comments = $rclass->getDocComment();
		ksort($rclass_constants);
		ksort($rclass_properties);
		sort($rclass_methods);

		/* Re-write lexing code from scratch! */

	}
}
