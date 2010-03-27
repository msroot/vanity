<?php
header('HTTP/1.1 200 OK');
header('Content-type: text/xml; charset=utf-8');

include 'ndocs.class.php';

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

	public static function tagify($s)
	{
		$s = preg_replace("/[^A-Za-z0-9\s]/", '', $s);
		$s = ucwords($s);
		$s = str_replace(' ', '', $s);
		$s[0] = strtolower($s[0]);
		return $s;
	}

	public static function line_numbers($lnum, $content)
	{
		return str_pad($lnum + 1, strlen((string) sizeof($content)), '0', STR_PAD_LEFT);
	}

	public static function htmlize($data, $xml)
	{
		if (is_array($data))
		{
			foreach ($data as $d)
			{
				if (gettype($d) === 'string')
				{
					$line = $xml->addChild('line');
					$line->addCDATA($d);
				}
				else
				{
					$line = $xml->addChild('entry');
					foreach ($d as $k => $v)
					{
						$xk = $line->addChild($k);
						Util::htmlize($v, $xk);
					}
				}
			}
		}
		elseif (gettype($data) === 'string')
		{
			$xml->addCDATA($data);
		}

		return $xml;
	}

	public static function rglob($pattern, $flags = 0, $path = '')
	{
		if (!$path && ($dir = dirname($pattern)) != '.')
		{
			if ($dir == '\\' || $dir == '/')
			{
				$dir = '';
			}

			return Util::rglob(basename($pattern), $flags, $dir . '/');
		}

		$paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
		$files = glob($path . $pattern, $flags);

		foreach ($paths as $p)
		{
			$files = array_merge($files, Util::rglob($pattern, $flags, $p . '/'));
		}

		return $files;
	}
}

class Lexer
{
	public static function parse_class($class_name, $pwd, $dir_output)
	{
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><ndocs xmlns="http://github.com/skyzyx/ndocs"></ndocs>', 'SimpleXMLExtended', LIBXML_NOCDATA);

			// Collect class data
			$rclass = new ReflectionClass($class_name);
			$rclass_properties = $rclass->getDefaultProperties();
			$rclass_methods = $rclass->getMethods();
			$rclass_comments = $rclass->getDocComment();
			ksort($rclass_properties);
			sort($rclass_methods);

			// <class />
			$xclass = $xml->addChild('class');

				$rcomment = $rclass->getFileName();
				$content = file_get_contents($rcomment);
				$docblocks = NDocs::get_comment_sections($content);
				$headlines = NDocs::get_headlines($docblocks[0]);

				// <name />
				$xclass->addChild('name', $rclass->name);

				// <fileData />
				$xfileData = $xclass->addChild('fileData');

					// <docBlock />
					$xdocBlock = $xfileData->addChild('docBlock');

					foreach ($headlines as $headline)
					{
						$xsection = $xdocBlock->addChild('section');
						$xsection->addChild('headline', $headline);
						$xcontents = $xsection->addChild('contents');

						$pheadline = NDocs::parse_headline($headline, $docblocks[0]);

						Util::htmlize($pheadline['content'], $xcontents);
					}

				// <summary />
				$xinfo = $xclass->addChild('summary');

					// <file />
					$temp = explode('cloudfusion/', $rclass->getFileName());
					$xfile = $xinfo->addChild('file', $temp[1]);

					// Store copies of the files in memory...
					$documents = array();
					$documents[$rclass->name] = file($rclass->getFileName());

					if ($rclass->getParentClass())
					{
						$xparentClass = $xinfo->addChild('parentClasses');
						$class_ref = $rclass;

						while ($class_ref->getParentClass())
						{
							$xparentClass->addChild('class', $class_ref->getParentClass()->name);
							$class_ref = $class_ref->getParentClass();

							// Add the parent files to memory as well...
							$documents[$class_ref->name] = file($class_ref->getFileName());
						}
					}

					$rcomment = $rclass->getDocComment();
					$headlines = NDocs::get_headlines($rcomment);

					// <docBlock />
					$xdocBlock = $xinfo->addChild('docBlock');

					foreach ($headlines as $headline)
					{
						$cheadline = strtolower($headline);

						$xsection = $xdocBlock->addChild('section');
						$xsection->addChild('headline', $headline);
						$xcontents = $xsection->addChild('contents');

						$pheadline = NDocs::parse_headline($headline, $rcomment);

						Util::htmlize($pheadline['content'], $xcontents);
					}

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

							$xdescription = $xproperty->addChild('description');
							$nproperty_docs = NDocs::parse_headline('Property', $rcomment);
							foreach ($nproperty_docs['content'] as $content)
							{
								$xline = $xdescription->addChild('line');
								$xline->addCDATA($content);
							}
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
					$rcomment = $rmethod->getDocComment();
					$headlines = NDocs::get_headlines($rcomment);

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

						// <access />
						$xmethod->addChild('access', implode(' ', Util::access($rmethod)));

						// <docBlock />
						$xdocBlock = $xmethod->addChild('docBlock');

						foreach ($headlines as $headline)
						{
							$cheadline = strtolower($headline);

							$xsection = $xdocBlock->addChild('section');
							$xsection->addChild('headline', $headline);
							$xcontents = $xsection->addChild('contents');

							$pheadline = NDocs::parse_headline($headline, $rcomment);

							Util::htmlize($pheadline['content'], $xcontents);
						}

						// <documented />
						if ($rcomment)
						{
							$xmethod->addChild('documented', 'true');
						}
						else
						{
							$xmethod->addChild('documented', 'false');
						}

						$xsource = $xmethod->addChild('source');
						$xsource->addAttribute('start', $rmethod->getStartLine());
						$xsource->addAttribute('end', $rmethod->getEndLine());

						$xsource->addCDATA(
							implode('', array_slice(
								$documents[$rmethod->class],
								($rmethod->getStartLine() - 1),
								($rmethod->getEndLine() - $rmethod->getStartLine() + 1)
							))
						);
				}

		$xml_output = $xml->asXML();
		$json_output = json_encode(new SimpleXMLElement($xml->asXML(), LIBXML_NOCDATA));

		$xml_write_path = $pwd . '/' . $dir_output . '/xml';
		$json_write_path = $pwd . '/' . $dir_output . '/json';

		if (!is_writable($xml_write_path))
		{
			mkdir($xml_write_path, 0777, true);
			chmod($xml_write_path, 0777);
		}

		if (!is_writable($json_write_path))
		{
			mkdir($json_write_path, 0777, true);
			chmod($json_write_path, 0777);
		}

		$xml_path = $xml_write_path . '/' . $class_name . '.xml';
		$xml_success = file_put_contents($xml_path, (string) $xml_output);

		$json_path = $json_write_path . '/' . $class_name . '.js';
		$json_success = file_put_contents($json_path, (string) $json_output);

		if ($xml_success) echo '    Created ' . $xml_path . PHP_EOL;
		else echo '    Failed to write ' . $xml_path . PHP_EOL;

		if ($json_success) echo '    Created ' . $json_path . PHP_EOL;
		else echo '    Failed to write ' . $json_path . PHP_EOL;
	}
}
