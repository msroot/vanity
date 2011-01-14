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
		$out = 'array( ';
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
		$out .= implode(', ', $collect);
		$out .= ' )';

		return $out;
	}

	/**
	 *
	 */
	public static function read_examples($yml = 'examples.yml', $class, $method)
	{
		return Vanity_CacheFile::init($class . '_' . $method, CACHE_DIR, 31557600)->response_manager(function($yml)
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

		}, array($yml));
	}
}
