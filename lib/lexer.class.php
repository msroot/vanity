<?php
class Vanity_SimpleXMLExtended extends SimpleXMLElement
{
	public function addCDATA($cdata_text)
	{
		$node = dom_import_simplexml($this);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}

class Vanity_Lexer
{
	private $linkmap;
	private $options;
	private $partials;
	private $documents;
	private $examples;
	private $groups;

	public function __construct()
	{
		$this->linkmap = $GLOBALS['LINKMAP'];
		$this->options = $GLOBALS['OPTIONS'];
		$this->partials = $GLOBALS['PARTIALS'];
		$this->documents = array();
		$this->examples = Util::read_examples('examples.yml');
		$this->groups = Util::parse_groups();
		$this->collect_file_contents();
	}

	public function collect_file_contents()
	{
		if (isset($this->linkmap['map']) && is_array($this->linkmap['map']))
		{
			foreach ($this->linkmap['map'] as $key => $value)
			{
				$class = new ReflectionClass($key);
				if ($filepath = $class->getFileName())
				{
					$this->documents[$class->getName()] = file($filepath);
				}
			}
		}
	}

	public function parse_class($class_name, $dir_output)
	{
		$xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><vanity xmlns="http://vanitydoc.org"></vanity>', 'Vanity_SimpleXMLExtended', LIBXML_NOCDATA);

		// Collect class data
		$rclass = new ReflectionClass($class_name);
		$rclass_properties = $rclass->getDefaultProperties();
		$rclass_constants = $rclass->getConstants();
		$rclass_methods = $rclass->getMethods();
		$rclass_comments = $rclass->getDocComment();
		ksort($rclass_constants);
		ksort($rclass_properties);
		sort($rclass_methods);

		// Store copies of the files in memory...
		$long_filename = $rclass->getFileName();
		$short_filename = str_replace(WORKING_DIR, '', $long_filename);

		/*****************************************************************************************/

		/*
		<file>
			<name>AmazonS3</name>
			<version>Fri Dec 03 16:26:16 PST 2010</version>
			<description>
				<![CDATA[]]>
			</description>
		</file>
		*/

		// <file />
		$xfile = $xml->addChild('file');

		/*****************************************************************************************/

		/*
		<class>
			<name></name>
			<file></file>
			<url></url>
			<description>
				<![CDATA[]]>
			</description>
			<metadata>
				<@tag></@tag>
				<@tag></@tag>
			</metadata>
			<inheritance>
				<class>
					<name></name>
					<file></file>
				</class>
			</inheritance>
			<implements>
				<interface>
					<name></name>
					<file></file>
				</interface>
			</implements>
		</class>
		*/

		// <class />
		$xclass = $xml->addChild('class');

			/*****************************************************************************************/

			// <name />
			$xclass->addChild('name', $rclass->getName());

			// <file />
			$xclass->addChild('file', $short_filename);

			// <url />
			$filename = $rclass->getFileName();
			$url = isset($filename) ? ('../' . strtolower($rclass->getName()) . '/index.html') : ('http://php.net/' . strtolower($rclass->getName()));
			$xclass->addChild('url', $url);

			// <description />
			$pclassdescription = new DocblockParser($rclass->getDocComment());
			$xclassdescription = $xclass->addChild('description');
			$pclasscomments = Util::apply_linkmap($rclass->name, $pclassdescription->getComments());
			$xclassdescription->addCDATA(SmartyPants(Util::htmlify_text($pclasscomments)));

			// <metadata />
			$xtags = $pclassdescription->getTags();
			if (is_array($xtags))
			{
				$xmetadata = $xclass->addChild('metadata');
				foreach ($xtags as $tag => $value)
				{
					if (is_string($value))
					{
						$value = array($value);
					}

					foreach ($value as $val)
					{
						if (trim($tag) !== '')
						{
							$xtag = $xmetadata->addChild($tag);

							// Handle special cases
							if ($tag === 'author')
							{
								$value = DocblockParser::parse_author($val);
							}

							$xtag->addCDATA($val);
						}
					}
				}
			}

			// <inheritance>
			//   <class>
			//     <name></name>
			//     <file></file>
			//   </class>
			// </inheritance>
			if ($pclasses = Util::get_parent_classes($rclass->getName()))
			{
				$xinheritance = $xclass->addChild('inheritance');
				foreach ($pclasses as $pclass)
				{
					$xinheritanceclass = $xinheritance->addChild('class');
					$xinheritanceclass->addChild('name', $pclass);

					$rclass_lookup = new ReflectionClass($pclass);
					if ($rclass_file = $rclass_lookup->getFileName())
					{
						$xinheritanceclass->addChild('file', str_replace(WORKING_DIR, '', $rclass_file));
					}

					if ($rclass_file && isset($this->linkmap['map'][$rclass_lookup->getName()]))
					{
						$xinheritanceclass->addChild('url', '../' . strtolower($rclass_lookup->getName()) . '/index.html');
					}
					if (!$rclass_file && !isset($this->linkmap['map'][$rclass_lookup->getName()]))
					{
						$xinheritanceclass->addChild('url', 'http://php.net/' . strtolower($rclass_lookup->getName()));
					}
				}
			}

			// <implements>
			//   <interface>
			//     <name></name>
			//     <file></file>
			//   </interface>
			// </implements>
			if ($pinterfaces = $rclass->getInterfaces())
			{
 				$ximplements = $xclass->addChild('implements');
				foreach ($pinterfaces as $pinterface)
				{
					$ximplementsinterface = $ximplements->addChild('interface');
					$ximplementsinterface->addChild('name', $pinterface->getName());

					if ($rinterface_file = $pinterface->getFileName())
					{
						$ximplementsinterface->addChild('file', str_replace(WORKING_DIR, '', $rinterface_file));
					}

					if ($rinterface_file && isset($this->linkmap['map'][$pinterface->getName()]))
					{
						$ximplementsinterface->addChild('url', '../' . strtolower($pinterface->getName()) . '/index.html');
					}
					if (!$rinterface_file && !isset($this->linkmap['map'][$pinterface->getName()]))
					{
						$ximplementsinterface->addChild('url', 'http://php.net/' . strtolower($pinterface->getName()));
					}
				}
			}

			/*****************************************************************************************/

			// <methods />
			$xmethods = $xclass->addChild('methods');
			$xmethods->addAttribute('count', sizeof($rclass_methods));

			// Filter out excluded methods
			$parse_methods = array();
			foreach ($rclass_methods as $rmethod)
			{
				$add_method = true;
				if (isset($this->options['exclude-methods']))
				{
					if (is_string($this->options['exclude-methods']))
					{
						$this->options['exclude-methods'] = array($this->options['exclude-methods']);
					}

					foreach ($this->options['exclude-methods'] as $exclusion)
					{
						if (preg_match('/' . $exclusion . '/i', $rmethod->getName()))
						{
							$add_method = false;
						}
					}
				}
				if ($add_method)
				{
					$parse_methods[] = $rmethod;
				}
			}
			$rclass_methods = $parse_methods;

			// Loop through each method object
			foreach ($rclass_methods as $rmethod)
			{
				// Should we look this method up from the PHP reference?
				$method_xml = null;
				if (!$rmethod->getFileName())
				{
					$method_xml = Util::get_reference_xml($rmethod->getDeclaringClass()->getName() . '::' . $rmethod->getName());
				}

				$pcomment = null;
				$ptags = null;

				// Handle native PHP methods
				if ($method_xml)
				{
					// Equivalent of @-tags
					$ptags = array();

					// Parameters
					$params = $method_xml->xpath('descendant-or-self::refsect1[@role="parameters"]//variablelist/varlistentry');
					if (is_array($params) && count($params))
					{
						foreach ($params as $param)
						{
							$param_description = (string) $param->listitem->asXML();
							$param_description_stripped = Util::strip_root_element($param_description);
							$ptags['param'][(string) $param->term->parameter]['description'] = Util::clean_docbook($param_description_stripped);
						}
					}
					$mtypes = $method_xml->xpath('descendant-or-self::refsect1[@role="description"]/methodsynopsis/methodparam[type and parameter]');
					if (is_array($mtypes) && count($mtypes))
					{
						// Match types to existing parameters
						foreach ($mtypes as $mtype)
						{
							if (isset($ptags['param'][(string) $mtype->parameter]))
							{
								$ptags['param'][(string) $param->term->parameter]['type'] = Util::elongate_type((string) $mtype->type);
							}
						}
					}

					// Return types
					$return_type = $method_xml->xpath('descendant-or-self::refsect1[@role="description"]/methodsynopsis/type');
					$return_paras = $method_xml->xpath('descendant-or-self::refsect1[@role="returnvalues"]/para');
					if (is_array($return_paras))
					{
						$return_comment = array();

						foreach ($return_paras as $para)
						{
							// Strip off the root XML element.
							$return_comment[] = trim(Util::clean_docbook($para->asXML()));
						}

						$return_comment = implode("\n", $return_comment);
					}

					$ptags['return'] = array(
						'type' => Util::elongate_type((string) $return_type[0]),
						'description' => $return_comment,
					);

					// Description
					$paras = $method_xml->xpath('descendant-or-self::refsect1[@role="description"]/para');
					if (is_array($paras))
					{
						$pcomment = array();

						foreach ($paras as $para)
						{
							$pcomment[] = trim(Util::clean_docbook($para->asXML()));
						}

						$pcomment = implode("\n", $pcomment);
					}

					// Related methods
					$related_methods = $method_xml->xpath('descendant-or-self::refsect1[@role="seealso"]//member/function');
					if ($related_methods && is_array($related_methods))
					{
						$ptags['see'] = array_map(function($method, $rclassname)
						{
							return Util::apply_linkmap(
								$rclassname,
								'php:' . (string) $method
							);

						}, $related_methods, array($rclass->name));
					}
				}

				// Handle hand-written docblocks containing information
				elseif ($rcomment = $rmethod->getDocComment())
				{
					$pcomment = new DocblockParser($rcomment);
					$ptags = $pcomment->getTags();

					if (isset($ptags['see']))
					{
						if (is_string($ptags['see']))
						{
							$ptags['see'] = array($ptags['see']);
						}

						$a = array();

						foreach ($ptags['see'] as $see)
						{
							// If this is `Class::method()` syntax pointing to the same class, remove the `Class::`.
							if (strpos($see, '::') !== false)
							{
								$t = explode('::', $see);
								if (in_array($t[0], Util::get_parent_classes($rclass->name)))
								{
									$a[] = $t[1];
								}
							}
							else
							{
								$a[] = $see;
							}
						}

						$ptags['see'] = $a;
					}

					if (isset($ptags['return']))
					{
						$ptags['return'] = DocblockParser::parse_return($ptags['return']);
					}

					if (isset($ptags['param']))
					{
						if (is_string($ptags['param']))
						{
							$ptags['param'] = array($ptags['param']);
						}

						foreach ($ptags['param'] as $tparam)
						{
							$tparam = DocblockParser::parse_param($tparam);
							$tarray[$tparam['name']] = $tparam;
							unset($tarray[$tparam['name']]['name']);
						}
						$ptags['param'] = $tarray;
						unset($tarray);
					}

					if (isset($ptags['author']))
					{
						if (is_string($ptags['author']))
						{
							$ptags['author'] = array($ptags['author']);
						}

						$tarray = array();
						foreach ($ptags['author'] as $tparam)
						{
							$tarray[] = DocblockParser::parse_author($tparam);
						}

						$ptags['author'] = $tarray;
						unset($tarray);
					}

					$pcomment = Util::htmlify_text(
						Util::apply_linkmap($rclass->name, $pcomment->getComments())
					);
				}

				// Convert any strings to arrays
				if (isset($ptags['see']) && is_string($ptags['see']))
				{
					$ptags['see'] = array($ptags['see']);
				}

				// Merge in groups
				if (isset($this->groups[$rclass->name][$rmethod->name]))
				{
					if (isset($ptags['see']))
					{
						$ptags['see'] = array_unique(array_merge($ptags['see'], $this->groups[$rclass->name][$rmethod->name]));
					}
					else
					{
						$ptags['see'] = $this->groups[$rclass->name][$rmethod->name];
					}
				}

				// <method />
				$xmethod = $xmethods->addChild('method');

					// <name />
					$tmethod_name = $rmethod->getName();
					$xmethod->addChild('name', $tmethod_name);

					// <modifier />
					$xmethod->addChild('modifier', implode(' ', Util::access($rmethod)));

					// <since />
					if (isset($ptags['since']))
					{
						$xmethod->addChild('since', $ptags['since']);
					}

					// <inheritance>
					//   <class>
					//     <name></name>
					//     <file></file>
					//   </class>
					// </inheritance>
					$tmethod_class_name = $rclass->name; // Current class
					if ($rmethod->getDeclaringClass()->getName() != $rclass->name)
					{
						$xinheritance = $xmethod->addChild('inheritance');
						$xinheritanceclass = $xinheritance->addChild('class');
						$xinheritanceclass->addChild('name', $rmethod->getDeclaringClass()->getName());
					}

					$lower_class_name = strtolower($rclass->name);
					$lower_method_name = strtolower($rmethod->name);

					// <description />
					if ($pcomment ||
					    isset($this->partials[$lower_class_name][$lower_method_name]['prepend']) ||
					    isset($this->partials[$lower_class_name][$lower_method_name]['append']))
					{
						$xdescription = $xmethod->addChild('description');

						// Add "prepend" partials
						if (isset($this->partials[$lower_class_name][$lower_method_name]['prepend']))
						{
							$pcomment = $this->partials[$lower_class_name][$lower_method_name]['prepend'] . PHP_EOL . $pcomment;
						}

						// Add "append" partials
						if (isset($this->partials[$lower_class_name][$lower_method_name]['append']))
						{
							$pcomment .= PHP_EOL . $this->partials[$lower_class_name][$lower_method_name]['append'];
						}

						$pcomment = Util::apply_linkmap($rclass->name, $pcomment);
						$xdescription->addCDATA(SmartyPants($pcomment));
					}

					// <metadata />
					if (is_array($ptags))
					{
						unset($xmetadata);

						foreach ($ptags as $tag => $value)
						{
							if (!in_array($tag, array('param', 'return', 'since', 'see', 'link'), true) && trim($tag) !== '')
							{
								if (!isset($xmetadata))
								{
									$xmetadata = $xmethod->addChild('metadata');
								}

								if (is_string($value))
								{
									$value = array($value);
								}

								foreach ($value as $val)
								{
									$ptag = $xmetadata->addChild($tag);
									$ptag->addCDATA(SmartyPants(Util::apply_linkmap($rclass->name, $val)));
								}
							}
						}
					}

					// <parameters />
					$rparameters = $rmethod->getParameters();
					if ($rparameters)
					{
						$xparameters = $xmethod->addChild('parameters');
						foreach ($rparameters as $rparameter)
						{
							// <parameter />
							$xparameter = $xparameters->addChild('parameter');
							$req = $rparameter->isOptional() ? 'opt' : 'req';
							$xparameter->addAttribute('choice', $req);

								// <name />
								$xname = $xparameter->addChild('name', $rparameter->getName());

								// <type />
								if (isset($ptags['param']) &&
								    isset($ptags['param'][$rparameter->getName()]) &&
								    isset($ptags['param'][$rparameter->getName()]['type'])
								)
								{
									$ttypes = $ptags['param'][$rparameter->getName()]['type'];
									$ttypes = explode('|', $ttypes);
									$new_ttypes = array();

									if (is_string($ttypes))
									{
										$ttypes = array($ttypes);
									}

									foreach ($ttypes as $ttype)
									{
										$new_ttypes[] = Util::elongate_type($ttype);
									}

									$xparameter->addChild('type', implode('|', $new_ttypes));
								}

								// <description />
								if (isset($ptags['param']) && isset($ptags['param'][$rparameter->getName()]))
								{
									$xdescription = $xparameter->addChild('description');
									$xdescription->addCDATA(
										SmartyPants(
											Util::htmlify_text(
												Util::apply_linkmap($rclass->name, $ptags['param'][$rparameter->getName()]['description'])
											)
										)
									);
									unset($xdescription);
								}

								// <initializer>
								//   <type>string</type>
								//   <value>mystring</value>
								// </initializer>
								if ($rparameter->isDefaultValueAvailable())
								{
									$dvalue = $rparameter->getDefaultValue();
									$dtype = strtolower(gettype($dvalue));
									switch ($dtype)
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

									$xinitializer = $xparameter->addChild('initializer');
									$xinitializervalue = $xinitializer->addChild('value', $dvalue);
									$xinitializervalue->addAttribute('type', $dtype);
								}
						}
					}

					// <returnvalue type="CFResponse">
					//   <![CDATA[]]>
					// </returnvalue>
					$xreturnvalue = $xmethod->addChild('returnvalue');
					if (isset($ptags['return']) && isset($ptags['return']['type']))
					{
						$xreturnvalue->addAttribute('type', $ptags['return']['type']);
					}
					if (isset($ptags['return']) && isset($ptags['return']['description']))
					{
						$xreturnvalue->addCDATA(
							SmartyPants(
								Util::htmlify_text(
									Util::apply_linkmap($rclass->name, $ptags['return']['description'])
								)
							)
						);
					}

					// <source file="services/s3.class.php" start="474" end="485" lines="12">
					//   <![CDATA[]]>
					// </source>
					if ($rmethod->getStartLine())
					{
						$xsource = $xmethod->addChild('source');
						$xsource->addAttribute('file', str_replace(WORKING_DIR, '', $rmethod->getFileName()));

						$xsource->addAttribute('start', $rmethod->getStartLine());
						if ($rmethod->getEndLine())
						{
							$xsource->addAttribute('end', $rmethod->getEndLine());
							$xsource->addAttribute('lines', ($rmethod->getEndLine() - $rmethod->getStartLine()) + 1);

							// Grab the source code
							if (isset($this->documents[$rmethod->getDeclaringClass()->getName()]) && is_array($this->documents[$rmethod->getDeclaringClass()->getName()]))
							{
								$tcode = implode('', array_slice(
									$this->documents[$rmethod->getDeclaringClass()->getName()],
									($rmethod->getStartLine() - 1),
									($rmethod->getEndLine() - $rmethod->getStartLine() + 1)
								));

								$tcode = preg_replace("/^\t/", '', $tcode); // Clean initial Tab
								$tcode = preg_replace("/\n\t/", "\n", $tcode); // Clean off the first tab per line
								$tcode = str_replace("\t", '    ', $tcode); // Convert all tabs to 4 spaces.
								$tcode = Util::entitize($tcode);

								$xsource->addCDATA(trim($tcode));
							}
						}
					}

					// <example>
					//   <title>This is a sample something.</title>
					//   <description>
					//     <![CDATA[]]>
					//   </description>
					//   <code>
					//     <![CDATA[]]>
					//   </code>
					//   <result>
					//     <![CDATA[]]>
					//   </result>
					// </example>

					if ($method_xml)
					{
						// Define class entry if it doesn't exist.
						if (!isset($this->examples[(string) $rmethod->getDeclaringClass()->getName()]))
						{
							$this->examples[(string) $rmethod->getDeclaringClass()->getName()] = array();
						}

						// Define method entry if it doesn't exist.
						if (!isset($this->examples[(string) $rmethod->getDeclaringClass()->getName()][(string) $rmethod->getName()]))
						{
							$this->examples[(string) $rmethod->getDeclaringClass()->getName()][(string) $rmethod->getName()] = array();
						}

						if ($tphpexamples = $method_xml->xpath('descendant-or-self::refsect1[@role="examples"]//example'))
						{
							$this->examples[(string) $rmethod->getDeclaringClass()->getName()][(string) $rmethod->getName()] = $tphpexamples;
						}

						unset($tphpexamples);
					}

					if (isset($this->examples[(string) $rmethod->getDeclaringClass()->getName()][(string) $rmethod->getName()]) &&
					    is_array($this->examples[(string) $rmethod->getDeclaringClass()->getName()][(string) $rmethod->getName()]))
					{
						$xexamples = $xmethod->addChild('examples');

						foreach ($this->examples[(string) $rmethod->getDeclaringClass()->getName()][(string) $rmethod->getName()] as $path)
						{
							$texample = new Example($path);
							$tsections = $texample->sections();

							$xexample = $xexamples->addChild('example');

							if (isset($tsections['TEST']))
							{
								$xtitle = $xexample->addChild('title');
								$xtitle->addCDATA(
									trim(
										Util::apply_linkmap($rclass->name, Markdown($tsections['TEST']))
									)
								);
							}

							if (isset($tsections['DESCRIPTION']))
							{
								$xdescription = $xexample->addChild('description');
								$xdescription->addCDATA(
									SmartyPants(
										Util::htmlify_text(
											Util::apply_linkmap($rclass->name, Markdown($tsections['DESCRIPTION']))
										)
									)
								);
							}

							if (isset($tsections['FILE']))
							{
								$xcode = $xexample->addChild('code');
								$xcode->addCDATA(Example::display($tsections['FILE']));
							}

							if (isset($tsections['EXPECT']))
							{
								$xresult = $xexample->addChild('result');

								if ($method_xml)
								{
									$xresult->addCDATA(htmlentities($tsections['EXPECT'], ENT_COMPAT, 'UTF-8'));
								}
								else
								{
									$xresult->addCDATA($tsections['EXPECT']);
								}
							}
						}
					}

					// <related>
					//   <method></method>
					// </related>
					if (isset($ptags['see']))
					{
						$xmethodrelated = $xmethod->addChild('related');

						if (is_string($ptags['see']))
						{
							$ptags['see'] = array($ptags['see']);
						}

						foreach ($ptags['see'] as $see)
						{
							if ($see !== $rmethod->getName() . '()')
							{
								$xmethodrelatedmethod = $xmethodrelated->addChild('method');
								$xmethodrelatedmethod->addCDATA(Util::apply_linkmap($rclass->name, '<' . $see . '>'));
							}
						}
					}

					// <seealso>
					//   <link></link>
					// </seealso>
					if (isset($ptags['link']))
					{
						$xmethodrelated = $xmethod->addChild('seealso');

						if (is_string($ptags['link']))
						{
							$ptags['link'] = array($ptags['link']);
						}

						foreach ($ptags['link'] as $link)
						{
							$xmethodseelink = $xmethodrelated->addChild('link');
							$xmethodseelink->addCDATA(DocblockParser::parse_link($link));
						}
					}
			}


			/*****************************************************************************************/

			/*
			<constants count="1">
				<constant>
					<name><![CDATA[BUILD]]></name>
					<value type="string">20101203002835</value>
				</constant>
			</constants>
			*/

			// <constants />
			$xconstants = $xclass->addChild('constants');
			$xconstants->addAttribute('count', sizeof($rclass_constants));

			foreach ($rclass_constants as $rconstant => $rvalue)
			{
				// <constant />
				$xconstant = $xconstants->addChild('constant');

					// <name />
					$xname = $xconstant->addChild('name', $rconstant);

					// <value />
					$xvalue = $xconstant->addChild('value', $rvalue);
					$xvalue->addAttribute('type', gettype($rvalue));
			}

			/*****************************************************************************************/

			/*
			<properties count="1">
				<property>
					<name>account_id</name>
					<inheritance>
						<class>
							<name></name>
							<file></file>
						</class>
					</inheritance>
					<modifier>public</modifier>
					<initializer>
						<type>string</type>
						<value>mystring</value>
					</initializer>
					<metadata>
						<author>Ryan Parman</author>
					</metadata>
					<description>
						<![CDATA[<p>Description.</p>]]>
					</description>
				</property>
			</properties>
			*/

			// <properties>
			$xproperties = $xclass->addChild('properties');
			$xproperties->addAttribute('count', sizeof($rclass_properties));

			foreach ($rclass_properties as $rproperty => $rvalue)
			{
				$rproperty = new ReflectionProperty($rclass->name, $rproperty);

				// <property />
				$xproperty = $xproperties->addChild('property');

					// <name />
					$xproperty->addChild('name', $rproperty->getName());

					// <inheritance>
					//   <class>
					//     <name></name>
					//     <file></file>
					//   </class>
					// </inheritance>
					if ($rproperty->getDeclaringClass()->getName() != $rclass->getName())
					{
						$xinheritance = $xproperty->addChild('inheritance');
						$xinheritanceclass = $xinheritance->addChild('class');
						$xinheritanceclass->addChild('name', $rproperty->getDeclaringClass()->getName());

						if ($rclass_file = $rproperty->getDeclaringClass()->getFileName())
						{
							$xinheritanceclass->addChild('file', str_replace(WORKING_DIR, '', $rclass_file));
						}
					}

					// <modifier />
					$xproperty->addChild('modifier', implode(' ', Util::access($rproperty)));

					// <initializer />
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
								$adjusted_rvalue = $rvalue;
								break;

							case 'integer':
								$adjusted_rvalue = (integer) $rvalue;
								break;

							case 'array':
								$adjusted_rvalue = Util::unwrap_array($rvalue);
								break;
						}

						$xinitializer = $xproperty->addChild('initializer');
						$xinitializer->addAttribute('type', gettype($rvalue));
						$xinitializer->addAttribute('value', $adjusted_rvalue);
					}

					if ($rcomment = $rproperty->getDocComment())
					{
						$property_docs = new DocblockParser($rproperty->getDocComment());

						// <metadata />
						$xtags = $property_docs->getTags();
						if (is_array($xtags))
						{
							$xmetadata = $xproperty->addChild('metadata');
							foreach ($xtags as $tag => $value)
							{
								if (!is_array($value))
								{
									$value = array($value);
								}

								if (trim($tag) !== '')
								{
									foreach ($value as $v)
									{
										$xtag = $xmetadata->addChild($tag);
										$xtag->addCDATA(Util::apply_linkmap($rclass->name, $v));
									}
								}
							}
						}

						// <description />
						$tcomments = $property_docs->getComments();
						if (trim($tcomments) !== '')
						{
							$xdescription = $xproperty->addChild('description');
							$xdescription->addCDATA(
								SmartyPants(
									Util::htmlify_text(
										Util::apply_linkmap($rclass->name, $tcomments)
									)
								)
							);
						}
					}
			}

		/*****************************************************************************************/

		// Write XML output
		$xml_output = $xml->asXML();
		$xml_write_path = $dir_output . 'xml' . DIRECTORY_SEPARATOR;

		// Write JSON output
		$json_output = json_encode(new SimpleXMLElement($xml->asXML(), LIBXML_NOCDATA));
		$json_write_path = $dir_output . 'json' . DIRECTORY_SEPARATOR;

		// Write Serialized PHP output
		$sphp_output = serialize(json_decode($json_output, true));
		$sphp_write_path = $dir_output . 'php' . DIRECTORY_SEPARATOR;

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

		if (!is_writable($sphp_write_path))
		{
			mkdir($sphp_write_path, 0777, true);
			chmod($sphp_write_path, 0777);
		}

		$xml_path = $xml_write_path . $class_name . '.xml';
		$xml_success = file_put_contents($xml_path, (string) $xml_output);

		$json_path = $json_write_path . $class_name . '.js';
		$json_success = file_put_contents($json_path, (string) $json_output);

		$sphp_path = $sphp_write_path . $class_name . '.php';
		$sphp_success = file_put_contents($sphp_path, (string) $sphp_output);

		if ($xml_success) echo TAB . 'Created ' . $xml_path . PHP_EOL;
		else echo TAB . 'Failed to write ' . $xml_path . PHP_EOL;

		if ($json_success) echo TAB . 'Created ' . $json_path . PHP_EOL;
		else echo TAB . 'Failed to write ' . $json_path . PHP_EOL;

		if ($sphp_success) echo TAB . 'Created ' . $sphp_path . PHP_EOL;
		else echo TAB . 'Failed to write ' . $sphp_path . PHP_EOL;
	}
}
