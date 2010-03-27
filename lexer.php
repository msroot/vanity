<?php
header('HTTP/1.1 200 OK');
header('Content-type: text/xml; charset=utf-8');

include 'ndocs.class.php';
include 'cloudfusion/cloudfusion.class.php';

class SimpleXMLExtended extends SimpleXMLElement
{
	public function addCDATA($cdata_text)
	{
		$node = dom_import_simplexml($this);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}

class Util
{
	public static function access($o)
	{
		$accesses = array();

		if (method_exists($o, 'isFinal')) {
			if ($o->isFinal()) $accesses[] = 'final';
		}
		if (method_exists($o, 'isAbstract')) {
			if ($o->isAbstract()) $accesses[] = 'abstract';
		}
		if (method_exists($o, 'isPrivate')) {
			if ($o->isPrivate()) $accesses[] = 'private';
		}
		if (method_exists($o, 'isProtected')) {
			if ($o->isProtected()) $accesses[] = 'protected';
		}
		if (method_exists($o, 'isPublic')) {
			if ($o->isPublic()) $accesses[] = 'public';
		}
		if (method_exists($o, 'isStatic')) {
			if ($o->isStatic()) $accesses[] = 'static';
		}

		return $accesses;
	}
}

$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><ndocs xmlns="http://github.com/skyzyx/ndocs"></ndocs>', 'SimpleXMLExtended');

	// Collect class data
	$iclass = new AmazonSQSQueue();
	$rclass = new ReflectionClass($iclass);
	$rclass_properties = $rclass->getDefaultProperties();
	$rclass_methods = $rclass->getMethods();
	$rclass_comments = $rclass->getDocComment();
	ksort($rclass_properties);
	sort($rclass_methods);

	// <class />
	$xclass = $xml->addChild('class');
	$xclass->addAttribute('name', $rclass->name);

		// <summary />
		$xinfo = $xclass->addChild('summary');

			// <file />
			$fpieces = explode(DIRECTORY_SEPARATOR, $rclass->getFileName());
			$xfile = $xinfo->addChild('file', array_pop($fpieces));

			if ($rclass->getParentClass())
			{
				$xparentClass = $xinfo->addChild('parentClasses');
				$class_ref = $rclass;

				while ($class_ref->getParentClass())
				{
					$xparentClass->addChild('class', $class_ref->getParentClass()->name);
					$class_ref = $class_ref->getParentClass();
				}
			}

			// <comments />
			$xcomments = $xinfo->addChild('comments');
			$xcomments->addCDATA($rclass->getDocComment());

		// <properties />
		$xproperties = $xclass->addChild('properties');
		$xproperties->addAttribute('count', sizeof($rclass_properties));

		foreach ($rclass_properties as $rproperty => $rvalue)
		{
			// <property />
			$xproperty = $xproperties->addChild('property');

				// <name />
				$xproperty->addChild('name', $rproperty);

				// <defaultValue />
				if ($rvalue)
				{
					switch (strtolower(gettype($rvalue)))
					{
						case 'boolean':
							$adjusted_rvalue = ($rvalue == 1) ? 'true' : 'false';
							break;
						case 'null':
							$adjusted_rvalue = 'null';
							break;
						default:
							$adjusted_rvalue = $rvalue;
							break;
					}

					$xvalue = $xproperty->addChild('defaultValue', $adjusted_rvalue);
					$xvalue->addAttribute('type', gettype($rvalue));
				}

				$rproperty = new ReflectionProperty($rclass->name, $rproperty);

				// <inherited />
				if ($rproperty->class != $rclass->name)
				{
					$xinherited = $xproperty->addChild('inherited');
					$xinherited->addAttribute('from', $rproperty->class);
				}

				// <access />
				$xproperty->addChild('access', implode(' ', Util::access($rproperty)));

				// <description />
				// <documented />
				if ($rcomment = $rproperty->getDocComment())
				{
					$xproperty->addChild('documented', 'true');

					$xcomments = $xproperty->addChild('description');
					$nproperty_docs = NDocs::parse_headline('Property', $rcomment);
					$nproperty_docs_content = '';
					foreach ($nproperty_docs['content'] as $content)
					{
						$nproperty_docs_content .= '<p>' . $content . '</p>';
					}
					$xcomments->addCDATA($nproperty_docs_content);
				}
				else
				{
					$xproperty->addChild('documented', 'false');
				}
		}

		// <methods />
		$xmethods = $xclass->addChild('methods');
		$xmethods->addAttribute('count', sizeof($rclass_methods));

		foreach ($rclass_methods as $rmethod)
		{
			// <method />
			$xmethod = $xmethods->addChild('method');

			// <name />
			$xmethod->addChild('name', $rmethod->getName());

			// <inherited />
			if ($rmethod->class != $rclass->name)
			{
				$xinherited = $xmethod->addChild('inherited');
				$xinherited->addAttribute('from', $rmethod->class);
			}

			$rparameters = $rmethod->getParameters();

			// <parameters />
			if ($rparameters)
			{
				$xparameters = $xmethod->addChild('parameters');
				foreach ($rparameters as $rparameter)
				{
					// <parameter />
					$xparameter = $xparameters->addChild('parameter');

						// <name />
						$xname = $xparameter->addChild('name', $rparameter->getName());

						// <required />
						$req = $rparameter->isOptional() ? 'false' : 'true';
						$xname = $xparameter->addChild('required', $req);

						// <defaultValue />
						if ($rparameter->isDefaultValueAvailable())
						{
							$dvalue = $rparameter->getDefaultValue();
							switch (strtolower(gettype($dvalue)))
							{
								case 'boolean':
									$dvalue = ($dvalue === 1) ? 'true' : 'false';
									break;

								case 'null':
									$dvalue = 'null';
									break;
							}

							$xdefaultValue = $xparameter->addChild('defaultValue', $dvalue);
						}
				}
			}

			// <documented />
			// <comment />
			if ($rcomment = $rmethod->getDocComment())
			{
				$xmethod->addChild('documented', 'true');

				$xcomment = $xmethod->addChild('comment');
				$xcomment->addCDATA($rcomment);
			}
			else
			{
				$xmethod->addChild('documented', 'false');
			}
		}

$output = $xml->asXML();

$tidy = tidy_parse_string($output, array(
	'add-xml-decl' => true,
	'assume-xml-procins' => true,
	'char-encoding' => 'UTF-8',
	'indent' => true,
	'indent-cdata' => true,
	'input-encoding' => 'UTF-8',
	'indent-spaces' => 4,
	'input-xml' => true,
	'numeric-entities' => true,
	'output-encoding' => 'UTF-8',
	'output-xml' => true,
	'wrap' => 10000
), 'UTF8');

echo $tidy;
file_put_contents('_output.xml', (string) $tidy);
