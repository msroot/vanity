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

		// http://php.net/manual/en/simplexmliterator.key.php
		// http://php.net/manual/en/function.str-replace.php
		// http://php.net/manual/en/function.simplexml-load-file.php
		// http://svn.php.net/repository/phpdoc/en/trunk/reference/spl/arrayobject/count.xml

		/*****************************************************************************************/

		// <file />
		$xfile = $xml->addChild('file');

		/*****************************************************************************************/

		// <class />
		$xclass = $xml->addChild('class');

		// Remove these
		$xclasscontent = $xclass->addChild('content');
		$xclasscontent->addCDATA(print_r((string) $rclass, true));

			/*****************************************************************************************/

			// <methods />
			$xmethods = $xclass->addChild('methods');
			$xmethods->addAttribute('count', sizeof($rclass_methods));

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
					<description>
						<para><![CDATA[Description.]]></para>
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

					// <description />
					if ($rcomment = $rproperty->getDocComment())
					{
						$xdescription = $xproperty->addChild('description');
						$property_docs = new DocblockParser($rproperty->getDocComment());
						// $xdescription->addCDATA(print_r(array($property_docs->getTags(), $property_docs->getComments()), true));
						$xdescription->addCDATA(Markdown($property_docs->makeMarkdownFriendly($rproperty->getDocComment())));
					}
			}

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
				<methods count="50">
					<method>
						<name>__construct</name>
						<modifier>public</modifier>
						<availability>1.0</availability>
						<inherited from="CFRuntime"/>
						<description>
							<para>Create an instance of the class with the input data</para>
							<!-- EMBEDDED CONTENT -->
						</description>
						<inheritance>
							<class>
								<name>AmazonS3</name>
								<file>services/s3.class.php</file>
							</class>
							<class>
								<name>CFRuntime</name>
								<file>sdk.class.php</file>
							</class>
						</inheritance>
						<implements>
							<interface>
								<name>ICacheCore</name>
								<file>cachecore.interface.php</file>
							</interface>
						</implements>
						<parameters>
							<parameter choice="req">
								<varname>var1</varname>
								<type>string</type>
								<description>
									<para>Create an instance of the class with the input data</para>
								</description>
							</parameter>
							<parameter choice="opt">
								<varname>var2</varname>
								<type>string</type>
								<initializer>
									<type>string</type>
									<value>mystring</value>
								</initializer>
								<description>
									<para>Create an instance of the class with the input data</para>
								</description>
							</parameter>
						</parameters>
						<returnvalue>
							<type>CFResponse</type>
							<description>
								<para>Create an instance of the class with the input data</para>
							</description>
						</returnvalue>
						<source>
							<code filename="services/s3.class.php" language="php" start="474" end="485" lines="12">
								<![CDATA[]]>
							</code>
						</source>
						<examples>
							<example>
								<title>This is a sample something.</title>
								<description>
									<para>Paragraphs in DocBook may contain almost all inlines and most block elements.</para>
									<itemizedlist>
										<listitem>
											<para>Paragraphs in DocBook may contain almost all inlines and most block elements.</para>
										</listitem>
									</itemizedlist>
								</description>
								<code language="php">
									<![CDATA[]]>
								</code>
							</example>
							<example>
								<title>This is a sample something.</title>
								<description>
									<para>Paragraphs in DocBook may contain almost all inlines and most block elements.</para>
									<itemizedlist>
										<listitem>
											<para>Paragraphs in DocBook may contain almost all inlines and most block elements.</para>
										</listitem>
									</itemizedlist>
								</description>
								<code language="php">
									<![CDATA[]]>
								</code>
							</example>
						</examples>
						<related>
							<method></method>
							<method></method>
							<method></method>
						</related>
						<seealso>
							<link url=""></link>
							<link url=""></link>
						</seealso>
					</method>
				</methods>
			*/

		/*****************************************************************************************/

		// Write XML output
		$xml_output = $xml->asXML();
		$xml_write_path = $dir_output . 'xml' . DIRECTORY_SEPARATOR;

		if (!is_writable($xml_write_path))
		{
			mkdir($xml_write_path, 0777, true);
			chmod($xml_write_path, 0777);
		}

		$xml_path = $xml_write_path . $class_name . '.xml';
		$xml_success = file_put_contents($xml_path, (string) $xml_output);

		if ($xml_success) echo TAB . 'Created ' . $xml_path . PHP_EOL;
		else echo TAB . 'Failed to write ' . $xml_path . PHP_EOL;
	}
}
