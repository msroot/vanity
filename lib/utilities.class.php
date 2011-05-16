<?php
class Util
{
	/**
	 *
	 */
	public static function normalize_path($path)
	{
		$path = preg_replace('/\w+\/\.\.\//', '', $path);
		$path = str_replace(array('//', '/./'), '/', $path);
		return $path;
	}
	/**
	 *
	 */
	public static function access($o)
	{
		$accesses = array();

		if (method_exists($o, 'isFinal'))
		{
			if ($o->isFinal()) $accesses[] = 'final';
		}
		if (method_exists($o, 'isAbstract'))
		{
			if ($o->isAbstract()) $accesses[] = 'abstract';
		}
		if (method_exists($o, 'isPrivate'))
		{
			if ($o->isPrivate()) $accesses[] = 'private';
		}
		if (method_exists($o, 'isProtected'))
		{
			if ($o->isProtected()) $accesses[] = 'protected';
		}
		if (method_exists($o, 'isPublic'))
		{
			if ($o->isPublic()) $accesses[] = 'public';
		}
		if (method_exists($o, 'isStatic'))
		{
			if ($o->isStatic()) $accesses[] = 'static';
		}

		return $accesses;
	}

	/**
	 *
	 */
	public static function get_reference_xml($function)
	{
		$function = preg_replace('/::__/', '::', $function);
		$function = str_replace('::', DIRECTORY_SEPARATOR, $function);
		$function = str_replace('_', '-', $function);

		$letters = str_split($function);
		$pattern = array();

		foreach ($letters as $letter)
		{
			if (preg_match('/[a-z]/i', $letter))
			{
				$pattern[] = '[' . strtolower($letter) . strtoupper($letter) . ']';
			}
			else
			{
				$pattern[] = $letter;
			}
		}

		$pattern = implode('', $pattern);

		$results = self::rglob(PHPREF_DIR . '**' . DIRECTORY_SEPARATOR . $pattern . '.*');
		$filepath = array_shift($results);

		if ($filepath)
		{
			$contents = file_get_contents($filepath);
			$contents = str_replace('xmlns=', 'ns=', $contents);

			// Handle entities embedded inside entities
			while (preg_match('/&([^;]*);/i', $contents))
			{
				$contents = preg_replace_callback('/&([^;]*);/i', function($matches)
				{
					if (strpos($matches[1], '#') === false)
					{
						$ENTITY_MAP = $GLOBALS['ENTITY_MAP'];
						return $ENTITY_MAP[$matches[1]];
					}

					return $matches[1];

				}, $contents);
			}

			return simplexml_load_string($contents, 'Vanity_SimpleXMLExtended', LIBXML_NOCDATA & LIBXML_NOBLANKS & LIBXML_NOENT);
		}

		// Null if we can't find anything (e.g. SimpleXMLElement::__toString()).
		return null;
	}

	/**
	 *
	 */
	public static function rglob($pattern, $flags = 0, $path = '')
	{
		if (!$path && ($dir = dirname($pattern)) != '.')
		{
			if ($dir == '\\' || $dir == '/')
			{
				$dir = '';
			}

			return self::rglob(basename($pattern), $flags, $dir . '/');
		}

		$paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
		$files = glob($path . $pattern, $flags);

		foreach ($paths as $p)
		{
			$files = array_merge($files, self::rglob($pattern, $flags, $p . '/'));
		}

		return $files;
	}

	/**
	 *
	 */
	public static function indent($content)
	{
		$contents = explode("\n", $content);

		$contents = array_map(function($line)
		{
			if (trim($line) !== '')
			{
				return TAB . '| ' . $line;
			}

		}, $contents);

		return implode("\n", $contents);
	}

	/**
	 *
	 */
	public static function regex_token($token)
	{
		$token = str_replace('/', '\/', $token);
		$token = quotemeta($token);
		return str_replace('\\\\', '\\', $token);
	}

	/**
	 *
	 */
	public static function line_numbers($lnum, $content)
	{
		return str_pad($lnum + 1, strlen((string) sizeof($content)), '0', STR_PAD_LEFT);
	}

	/**
	 *
	 */
	public static function entitize($s)
	{
		return htmlspecialchars($s, ENT_COMPAT, 'UTF-8');
	}

	/**
	 *
	 */
	public static function size_readable($size, $unit = null, $default = null)
	{
		// Units
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		$mod = 1024;
		$ii = count($sizes) - 1;

		// Max unit
		$unit = array_search((string) $unit, $sizes);
		if ($unit === null || $unit === false)
		{
			$unit = $ii;
		}

		// Return string
		if ($default === null)
		{
			$default = '%01.2f %s';
		}

		// Loop
		$i = 0;
		while ($unit != $i && $size >= 1024 && $i < $ii)
		{
			$size /= $mod;
			$i++;
		}

		return sprintf($default, $size, $sizes[$i]);
	}

	/**
	 *
	 */
	public static function unwrap_array($array)
	{
		$out = 'array(';
		$collect = array();
		foreach ($array as $k => $v)
		{
			$key = '';
			if (!is_int($k))
			{
				$key = '"' . $k . '" => ';
			}

			switch (gettype($v))
			{
				case 'integer':
					$collect[] = $key . $v;
					break;

				case 'string':
					$collect[] = $key . '"' . $v . '"';
					break;

				case 'array':
					$collect[] = $key . Util::unwrap_array($v);
					break;

				case 'object':
					$collect[] = $key . get_class($v);
					break;

				default:
					$collect[] = $key . gettype($v);
			}
		}

		$values = implode(', ', $collect);

		$out .= $values ? ' ' : '';
		$out .= $values;
		$out .= $values ? ' ' : '';
		$out .= ')';

		return $out;
	}

	/**
	 *
	 */
	public static function time_hms($seconds)
	{
		$time = '';

		// First pass
		$hours = (integer) ($seconds / 3600);
		$seconds = $seconds % 3600;
		$minutes = (integer) ($seconds / 60);
		$seconds = $seconds % 60;

		// Cleanup
		$time .= ($hours) ? $hours . ':' : '';
		$time .= ($minutes < 10 && $hours > 0) ? '0' . $minutes : $minutes;
		$time .= ':';
		$time .= ($seconds < 10) ? '0' . $seconds : $seconds;

		return $time;
	}

	/**
	 *
	 */
	public static function read_examples($yml = 'examples.yml')
	{
		$examples = array();
		$all_examples = Util::rglob($yml);

		foreach ($all_examples as $example)
		{
			$example = realpath($example);
			$yaml = spyc_load_file($example);

			foreach ($yaml as $class => $methods)
			{
				if ($methods)
				{
					foreach ($methods as $method => $tests)
					{
						if ($tests)
						{
							foreach ($tests as $index => $test)
							{
								$yaml[$class][$method][$index] = dirname($example) . DIRECTORY_SEPARATOR . $test;
							}
						}
					}
				}
			}
			$examples = array_merge($examples, $yaml);
		}

		return $examples;
	}

	/**
	 *
	 */
	public static function get_parent_classes($rclass)
	{
		$class_list = array();
		$rclass = new ReflectionClass($rclass);

		while ($parent_class = $rclass->getParentClass())
		{
			$class_list[] = $parent_class->getName();
			$rclass = $parent_class;
		}

		return $class_list;
	}

	/**
	 *
	 */
	public static function htmlify_text($text)
	{
		if (strpos(trim($text), '<') !== 0)
		{
			return trim(Markdown($text));
		}

		return trim($text);
	}

	/**
	 *
	 */
	public static function clean_docbook($content)
	{
		$content = preg_replace('/(\s+)/m', ' ', $content);
		$content = preg_replace('/\s?<(\/?)(para)([^>]*)>\s?/i', '<\\1p\\3>', $content);
		$content = preg_replace('/<(\/?)(literal)([^>]*)>/i', '<\\1code\\3>', $content);
		$content = preg_replace('/<(\/?)(orderedlist)([^>]*)>/i', '<\\1ol\\3>', $content);
		$content = preg_replace('/<(\/?)(itemizedlist)([^>]*)>/i', '<\\1ul\\3>', $content);
		$content = preg_replace('/<(\/?)(listitem)([^>]*)>/i', '<\\1li\\3>', $content);
		$content = preg_replace('/<constant([^>]*)>(\w*)<\/constant>/i', '<code>\\2</code>', $content);
		$content = preg_replace('/<type([^>]*)>(\w*)<\/type>/i', '<a href="http://php.net/\\2"><code>\\2</code></a>', $content);
		$content = preg_replace('/<classname([^>]*)>(\w*)<\/classname>/i', '<a href="http://php.net/\\2"><code>\\2</code></a>', $content);
		$content = preg_replace('/<methodname([^>]*)>(\w*)::(\w*)<\/methodname>/i', '<a href="http://php.net/\\2.\\3"><code>\\2::\\3</code></a>', $content);
		$content = preg_replace('/<link linkend="([^"]*)">([^>]*)<\/link>/i', '<a href="http://php.net/\\1"><code>\\2</code></a>', $content);

		$content = str_replace('<pmeter>', ' <code>', $content);
		$content = str_replace('</pmeter>', '</code> ', $content);
		$content = str_replace('<row>', '<tr>', $content);
		$content = str_replace('</row>', '</tr>', $content);
		$content = str_replace('<entry>', '<td>', $content);
		$content = str_replace('</entry>', '</td>', $content);

		return trim($content);
	}

	/**
	 *
	 */
	public static function elongate_type($type)
	{
		$types = array(
			'int' => 'integer',
			'bool' => 'boolean',
		);

		if (isset($types[strtolower($type)]))
		{
			return $types[strtolower($type)];
		}

		return $type;
	}

	/**
	 *
	 */
	public static function strip_root_element($xml, $element = 'listitem')
	{
		$xml = preg_replace('/^<' . $element . '>/i', '', trim($xml));
		$xml = preg_replace('/<\/' . $element . '>$/i', '', $xml);
		return trim($xml);
	}

	/**
	 *
	 */
	public static function generate_entity_map()
	{
		$master_map = array();
		$glob = array_merge(
			Util::rglob(ENTITY_GLOBAL_DIR . '**.ent'),
			Util::rglob(ENTITY_LANG_DIR . '**.ent')
		);

		foreach ($glob as $file)
		{
			$entities = file_get_contents($file);
			preg_match_all('/<!ENTITY\s+([^\s]*)\s+("|\')([^\\2]*)\\2\s*>/Ui', $entities, $m);

			for ($i = 0, $max = count($m[0]); $i < $max; $i++)
			{
				$v = str_replace(array("\r\n", "\n"), ' ', $m[3][$i]);
				$map[$m[1][$i]] = $v;
			}

			$master_map = array_merge($master_map, $map);
		}

		ksort($master_map);
		return $master_map;
	}

	/**
	 *
	 */
	public static function content_partials($paths)
	{
		$map = array();

		foreach ($paths as $full_path)
		{
			$path = str_replace(PARTIALS_DIR, '', $full_path);
			$path = explode(DIRECTORY_SEPARATOR, strtolower($path));

			if (count($path) === 3)
			{
				$class = $path[0];
				$method = $path[1];
				$content = $path[2];
			}
			elseif (count($path) === 2)
			{
				$class = $path[0];
				$method = null;
				$content = $path[1];
			}

			if (!isset($map[$class]))
			{
				$map[$class] = array();
			}

			if ($method && !isset($map[$class][$method]))
			{
				$map[$class][$method] = array();
			}

			$content = explode('.', $content);
			if (is_array($content))
			{
				$filename = $content[0];
				$extension = $content[1];
			}
			else
			{
				$filename = $content;
				$extension = '';
			}

			if (!isset($map[$path[0]][$path[1]][$content[0]]))
			{
				if (!$method) $method = '';
				$map[$class][$method][$filename] = Util::convert_to_html($full_path);
			}

		}

		return $map;
	}

	public static function convert_to_html($path)
	{
		$pathinfo = pathinfo($path);
		$extension = strtolower($pathinfo['extension']);

		switch ($extension)
		{
			// Markdown
			case 'md':
			case 'mdown':
			case 'markdown':
				return trim(SmartyPants(Markdown(file_get_contents($path))));
				break;

			// PHP-infused HTML
			case 'phtml':
				Generator::start();
				include $path;
				$phtml_content = Generator::end();
				return SmartyPants(trim($phtml_content));
				break;

			// Pre-formatted text
			case '':
			case 'txt':
			case 'text':
				return '<pre>' . trim(file_get_contents($path)) . '</pre>';
				break;

			// Plain ol' HTML
			default:
				return trim(SmartyPants(file_get_contents($path)));
				break;
		}
	}

	/**
	 *
	 */
	public static function apply_linkmap($current, $s, $linkmap = null)
	{
		$i = 0;
		$map = $linkmap ? $linkmap : $GLOBALS['LINKMAP'];
		$matches = preg_match_all('/<([^>]*)>/', $s, $m);

		foreach ($m[1] as $match)
		{
			if (preg_match('/(https?|ftp):\/\//', $match)) continue; // Don't match links.
			elseif (strpos($match, '::'))
			{
				$pieces = explode('::', $match);
				if (strpos($pieces[0], 'php:') !== false)
				{
					$pieces[0] = str_replace('php:', '', $pieces[0]);
					$s = str_replace($m[0][$i], '<a href="http://php.net/' . strtolower($pieces[0]) . '.' . str_replace('()', '', strtolower($pieces[1])) . '" class="code"><code>' . $pieces[0] . '::' . $pieces[1] . '</code></a>', $s);
				}
				elseif (isset($map['map'][$pieces[0]][$pieces[1]]))
				{
					$s = str_replace($m[0][$i], '<a href="../' . strtolower($map['map'][$pieces[0]][$pieces[1]]) . '" class="code"><code>' . $pieces[0] . '::' . $pieces[1] . '</code></a>', $s);
				}
			}
			else
			{
				if (strpos($match, 'php:') !== false)
				{
					$match = str_replace('php:', '', $match);
					$s = str_replace($m[0][$i], '<a href="http://php.net/' . str_replace('()', '', strtolower($match)) . '" class="code"><code>' . $match . '</code></a>', $s);
				}
				elseif (isset($map['map'][$current][$match])) // Match same-class methods
				{
					$s = str_replace($m[0][$i], '<a href="../' . strtolower($map['map'][$current][$match]) . '" class="code"><code>' . $match . '</code></a>', $s);
				}
				elseif (isset($map['map'][$match]['index'])) // Match same class index
				{
					$s = str_replace($m[0][$i], '<a href="../' . strtolower($map['map'][$match]['index']) . '" class="code"><code>' . $match . '</code></a>', $s);
				}
			}

			$i++;
		}

		return $s;
	}

	/**
	 *
	 */
	public static function parse_groups()
	{
		if (file_exists(CONFIG_DIR . 'groups.yml'))
		{
			$groups = spyc_load_file(CONFIG_DIR . 'groups.yml');
			$out = array();

			foreach ($groups as $group)
			{
				foreach ($group as $k => $v)
				{
					if (is_int($k))
					{
						$group[$k] = str_replace('.', '::', $v);
					}
				}

				foreach ($group as $k => $v)
				{
					if (is_int($k))
					{
						$t = explode('::', $v);
						$class = $t[0];
						$method = $t[1];

						if (!isset($out[$class]))
						{
							$out[$class] = array();
						}

						if (!isset($out[$class][$method]))
						{
							$out[$class][$method] = array();
						}

						$out[$class][$method] = array_merge($out[$class][$method], $group);
						$a = array();

						foreach ($out[$class][$method] as $m)
						{
							$x = str_replace($class . '::', '', $m);

							if (strpos($m, '()') === false)
							{
								$x .= '()';
							}

							$a[] = $x;
						}

						$out[$class][$method] = $a;

						$out[$class][$method] = array_unique($out[$class][$method]);
						sort($out[$class][$method]);
					}
					else
					{
						$class = $k;
						$methods = str_replace('()', '', $v);

						foreach ($methods as $method)
						{
							if (!isset($out[$class]))
							{
								$out[$class] = array();
							}

							if (!isset($out[$class][$method]))
							{
								$out[$class][$method] = array();
							}

							$out[$class][$method] = array_merge($out[$class][$method], $methods);

							$out[$class][$method] = array_map(function($method)
							{
								if (strpos($method, '()') === false)
								{
									$method .= '()';
								}

								return $method;

							}, $out[$class][$method]);

							$out[$class][$method] = array_unique($out[$class][$method]);
							sort($out[$class][$method]);
						}
					}
				}
			}

			return $out;
		}

		return array();
	}

	/**
	 *
	 */
	public static function strip_whitespace($buffer)
	{
		// return HTMLCompressor::compress($buffer);
		return $buffer;
	}
}
