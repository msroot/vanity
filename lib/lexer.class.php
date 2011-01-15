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
	private $options;

	public function __construct($linkmap)
	{
		$this->linkmap = $linkmap;
		$this->options = $GLOBALS['options'];
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

		// Store copies of the files in memory...
		$long_filename = $rclass->getFileName();
		$short_filename = str_replace(WORKING_DIR, '', $long_filename);
		$documents = array();
		$documents[$rclass->name] = file($long_filename);

		// http://php.net/manual/en/simplexmliterator.key.php
		// http://php.net/manual/en/function.str-replace.php
		// http://php.net/manual/en/function.simplexml-load-file.php
		// http://svn.php.net/repository/phpdoc/en/trunk/reference/spl/arrayobject/count.xml

		/*****************************************************************************************/

		// <file />
		$xfile = $xml->addChild('file');

		/*****************************************************************************************/

		/*
		<class>
			<name>AmazonS3</name>
			<version>Fri Dec 03 16:26:16 PST 2010</version>
			<description>
				<para>Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.</para>
				<para>Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.</para>
				<para>Neither the name of the SimplePie Team nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.</para>
			</description>
		</class>
		*/

		// <class />
		$xclass = $xml->addChild('class');

		// Remove these
		$xclasscontent = $xclass->addChild('content');
		$xclasscontent->addCDATA(print_r((string) $rclass, true));

			/*****************************************************************************************/

			// <methods />
			$xmethods = $xclass->addChild('methods');
			$xmethods->addAttribute('count', sizeof($rclass_methods));

			foreach ($rclass_methods as $rmethod)
			{
				$rcomment = $rmethod->getDocComment();

				// <method />
				$xmethod = $xmethods->addChild('method');

					// <name />
					$xmethod->addChild('name', $rmethod->getName());
					$tmethod_name = $rmethod->getName();

					// <modifier />
					$xmethod->addChild('modifier', implode(' ', Util::access($rmethod)));

					// <availability />

					// <inheritance>
					//   <class>
					//     <name></name>
					//     <file></file>
					//   </class>
					// </inheritance>
					$tmethod_class_name = $rclass->name; // Current class
					if ($rmethod->class != $rclass->name)
					{
						$xinheritance = $xmethod->addChild('inheritance');
						$xinheritanceclass = $xinheritance->addChild('class');
						$xinheritanceclass->addChild('name', $rmethod->class);

						$tmethod_class_name = $rmethod->class; // Parent class
					}

					// <description />
					$xdescription = $xmethod->addChild('description');
					$xdescription->addCDATA($rcomment);

					// <implements>
					//   <interface>
					//     <name>ICacheCore</name>
					//     <file>cachecore.interface.php</file>
					//   </interface>
					// </implements>

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
									$xinitializer->addChild('type', $dtype);
									$xinitializer->addChild('value', $dvalue);
								}

								// <description />
						}
					}

					// <returnvalue>
					//   <type>CFResponse</type>
					//   <description>
					//     <![CDATA[]]>
					//   </description>
					// </returnvalue>

					// <source>
					//   <code file="services/s3.class.php" start="474" end="485" lines="12">
					//     <![CDATA[]]>
					//   </code>
					// </source>
					$xsource = $xmethod->addChild('source');
					if ($rmethod->getStartLine())
					{
						$xsourcecode = $xsource->addChild('code');
						$xsourcecode->addAttribute('file', $short_filename);

						$xsourcecode->addAttribute('start', $rmethod->getStartLine());
						if ($rmethod->getEndLine())
						{
							$xsourcecode->addAttribute('end', $rmethod->getEndLine());
							$xsourcecode->addAttribute('lines', ($rmethod->getEndLine() - $rmethod->getStartLine()) + 1);
						}
					}

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
						$tcode = Util::entitize($tcode);

						$xsourcecode->addCDATA(trim($tcode));
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
					$texamples = Util::read_examples('examples.yml', $tmethod_class_name, $tmethod_name);
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
								$xcode->addCDATA($texample->display($tsections['FILE'], 'code'));
							}

							if (isset($tsections['EXPECT']))
							{
								$xresult = $xexample->addChild('result');
								$xresult->addCDATA($texample->display($tsections['EXPECT'], 'result'));
							}
						}
					}

					// <related>
					//   <method></method>
					// </related>

					// <seealso>
					//   <link url=""></link>
					// </seealso>
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
					<inherited from="CFRuntime"/>
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
				// <property />
				$xproperty = $xproperties->addChild('property');

					// <name />
					$xproperty->addChild('name', $rproperty);

					// <inherited />
					if (isset($rproperty->class) && isset($rclass->name) && ($rproperty->class != $rclass->name))
					{
						$xinherited = $xproperty->addChild('inherited');
						$xinherited->addAttribute('from', $rproperty->class);
					}

					// <modifier />
					$rproperty = new ReflectionProperty($rclass->name, $rproperty);
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
								$xtag = $xmetadata->addChild($tag);
								$xtag->addCDATA($value);
							}
						}

						// <description />
						$xcomments = $property_docs->getComments();
						if (trim($xcomments) !== '')
						{
							$xdescription = $xproperty->addChild('description');
							$xdescription->addCDATA($xcomments);
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

		$xml_path = $xml_write_path . $class_name . '.xml';
		$xml_success = file_put_contents($xml_path, (string) $xml_output);

		$json_path = $json_write_path . $class_name . '.js';
		$json_success = file_put_contents($json_path, (string) $json_output);

		if ($xml_success) echo TAB . 'Created ' . $xml_path . PHP_EOL;
		else echo TAB . 'Failed to write ' . $xml_path . PHP_EOL;

		if ($json_success) echo TAB . 'Created ' . $json_path . PHP_EOL;
		else echo TAB . 'Failed to write ' . $json_path . PHP_EOL;
	}
}
