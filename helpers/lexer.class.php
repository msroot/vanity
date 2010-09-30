<?php
include_once 'ndocs.class.php';
include_once 'utilities.class.php';

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

						// File: or Method:
						if ($headline === $headlines[0])
						{
							$pheadline = NDocs::parse_headline($headlines[0], $docblocks[0], $headlines[1]);
						}
						else
						{
							$pheadline = NDocs::parse_headline($headline, $docblocks[0]);
						}

						Util::htmlize($pheadline['content'], $xcontents, $this->linkmap, (string) $rclass->name);
					}

				// <summary />
				$xinfo = $xclass->addChild('summary');

					// <file />
					$temp = explode(getcwd() . DIRECTORY_SEPARATOR, $rclass->getFileName());
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
							$xpclass = $xparentClass->addChild('class');
							$xpclass->addChild('name', $class_ref->getParentClass()->name);

							$temp = explode(getcwd() . DIRECTORY_SEPARATOR, $class_ref->getParentClass()->getFileName());
							if (is_array($temp) && isset($temp[1]))
							{
								$xpclass->addChild('file', $temp[1]);
							}

							$class_ref = $class_ref->getParentClass();

							// Add the parent files to memory as well...
							$documents[$class_ref->name] = @file($class_ref->getFileName());
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

						Util::htmlize(@$pheadline['content'], $xcontents, $this->linkmap, (string) $rclass->name);
					}

				// <constants />
				$xconstants = $xclass->addChild('constants');
				$xconstants->addAttribute('count', sizeof($rclass_constants));

				foreach ($rclass_constants as $rconstant => $rvalue)
				{
					// <constant />
					$xconstant = $xconstants->addChild('constant');

						// <name />
						$xname = $xconstant->addChild('name');
						$xname->addCDATA($rconstant);

						// <value />
						$xvalue = $xconstant->addChild('value', $rvalue);
						$xvalue->addAttribute('type', gettype($rvalue));
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
							$adjusted_rvalue = null;
							switch (strtolower(gettype($rvalue)))
							{
								case 'boolean':
									$adjusted_rvalue = ($rvalue == 1) ? 'true' : 'false';
									break;

								case 'null':
									$adjusted_rvalue = 'null';
									break;

								case 'string':
									$adjusted_rvalue = '"' . $rvalue . '"';
									break;

								case 'array':
									$adjusted_rvalue = Util::unwrap_array($rvalue);
									break;
							}

							$xvalue = $xproperty->addChild('defaultValue');
							$xvalue->addCDATA($adjusted_rvalue);
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
							if (isset($nproperty_docs['content']) && is_array($nproperty_docs['content']))
							{
								foreach ($nproperty_docs['content'] as $content)
								{
									$xline = $xdescription->addChild('line');
									$xline->addCDATA(Util::apply_linkmap($this->linkmap, (string) $rclass->name, $content));
								}
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
						$tmethod_name = $rmethod->getName();

						// <inherited />
						$tmethod_class_name = $rclass->name; // Current class
						if ($rmethod->class != $rclass->name)
						{
							$xinherited = $xmethod->addChild('inherited');
							$xinherited->addAttribute('from', $rmethod->class);
							$tmethod_class_name = $rmethod->class; // Parent class
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

											case 'string':
												$adjusted_rvalue = '"' . $dvalue . '"';
												break;

											case 'array':
												$dvalue = Util::unwrap_array($dvalue);
												break;
										}

										$xdefaultValue = $xparameter->addChild('defaultValue');
										$xdefaultValue->addCDATA(Util::apply_linkmap($this->linkmap, (string) $rclass->name, $dvalue));
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

							if (isset($pheadline['content']))
							{
								Util::htmlize($pheadline['content'], $xcontents, $this->linkmap, (string) $rclass->name);
							}
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

						// <source />
						$xsource = $xmethod->addChild('source');
						$xsource->addChild('startLine', $rmethod->getStartLine());
						$xsource->addChild('endLine', $rmethod->getEndLine());
						$xsource->addChild('numberOfLines', ($rmethod->getEndLine() - $rmethod->getStartLine()) + 1);

						$xcode = $xsource->addChild('code');

						// Grab the source code
						if (isset($documents[$rmethod->class]) && is_array($documents[$rmethod->class]))
						{
							$tcode = implode('', array_slice(
								$documents[$rmethod->class],
								($rmethod->getStartLine() - 1),
								($rmethod->getEndLine() - $rmethod->getStartLine() + 1)
							));

							$tcode = preg_replace("/^\t/", '', $tcode); // Clean initial Tab
							$tcode = preg_replace("/\n\t/", "\n", $tcode); // Clean off the first tab per line
							$tcode = str_replace("\t", '    ', $tcode); // Convert all tabs to 4 spaces.

							$xcode->addCDATA(Util::entitize($tcode));
						}

						// <examples />
						$texamples = Util::read_examples();
						if (isset($texamples[$tmethod_class_name][$tmethod_name]) && is_array($texamples[$tmethod_class_name][$tmethod_name]))
						{
							$xexamples = $xmethod->addChild('examples');

							foreach ($texamples[$tmethod_class_name][$tmethod_name] as $path)
							{
								$texample = new Example($path);
								$tsections = $texample->sections();

								$xexample = $xexamples->addChild('example');

								if (isset($tsections['TEST']))
								{
									$xtitle = $xexample->addChild('title');
									$xtitle->addCDATA($tsections['TEST']);
								}

								if (isset($tsections['DESCRIPTION']))
								{
									$xtitle = $xexample->addChild('description');
									$xtitle->addCDATA($tsections['DESCRIPTION']);
								}

								if (isset($tsections['FILE']))
								{
									$xcode = $xexample->addChild('code');
									$xcode->addCDATA($texample->display($tsections['FILE']));
								}

								if (isset($tsections['EXPECT']))
								{
									$xresult = $xexample->addChild('result');
									$xresult->addCDATA($texample->display($tsections['EXPECT']));
								}
							}
						}
				}

		$xml_output = $xml->asXML();
		$json_output = json_encode(new SimpleXMLElement($xml->asXML(), LIBXML_NOCDATA));

		$xml_write_path = $dir_output . '/xml';
		$json_write_path = $dir_output . '/json';

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
