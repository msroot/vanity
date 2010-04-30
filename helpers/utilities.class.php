<?php
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

	public static function rdelete($dir)
	{
		$files = glob($dir . '*', GLOB_MARK);
		foreach ($files as $file)
		{
			if (is_dir($file))
			{
				Util::rdelete($file);
			}
			else
			{
				unlink($file);
			}
		}

		if (is_dir($dir))
		{
			rmdir($dir);
		}
	}

	public static function group_by_letter($obj, $property)
	{
		$arr = array();

		foreach ($obj as $o)
		{
			$letter = strtolower((string) $o->$property);
			$letter = $letter[0];

			if (!preg_match('/[a-z]/i', $letter))
			{
				$letter = '#';
			}

			if (!isset($arr[$letter]))
			{
				$arr[$letter] = array();
			}

			$arr[$letter][] = (string) $o->$property;
		}

		return $arr;
	}

	public static function process_parameters($obj)
	{
		$arr = array();

		foreach ($obj as $o)
		{
			// Default
			$type = (string) $o->type;

			// Handle multiple types
			if (isset($o->type->line))
			{
				$type = array();
				foreach ($o->type->line as $line)
				{
					$type[] = (string) $line;
				}
			}

			$arr[(string) $o->param] = array(
				'type' => $type,
				'description' => (string) $o->description,
			);
		}

		return $arr;
	}

	public static function clean_source($source)
	{
		$source = substr($source, 1);
		$source = str_replace("\n\t", "\n", $source);
		$source = str_replace("\t", '    ', $source);
		$source = Util::entitize($source);

		return $source;
	}

	public static function entitize($s)
	{
		return htmlspecialchars($s, ENT_COMPAT, 'UTF-8');
	}

	public static function linkify_as_markdown($s)
	{
		return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@', '<$1>', $s);
	}

	/**
	 * Method: size_readable()
	 * 	Return human readable file sizes. Original function by Aidan Lister <mailto:aidan@php.net>, modified by Ryan Parman.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	size - _integer_ (Required) Filesize in bytes.
	 * 	unit - _string_ (Optional) The maximum unit to use. Defaults to the largest appropriate unit.
	 * 	retstring - _string_ (Optional) The format for the return string. Defaults to '%01.2f %s'
	 *
	 * Returns:
	 * 	_string_ The human-readable file size.
	 *
 	 * Examples:
 	 * 	example::utilities/size_readable.phpt:
 	 * 	example::utilities/size_readable2.phpt:
 	 * 	example::utilities/size_readable3.phpt:
 	 *
	 * See Also:
	 * 	Original Function - http://aidanlister.com/repos/v/function.size_readable.php
	 */
	public static function size_readable($size, $unit = null, $retstring = null)
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
		if ($retstring === null)
		{
			$retstring = '%01.2f %s';
		}

		// Loop
		$i = 0;
		while ($unit != $i && $size >= 1024 && $i < $ii)
		{
			$size /= $mod;
			$i++;
		}

		return sprintf($retstring, $size, $sizes[$i]);
	}

	public static function unwrap_array($array)
	{
		$out = 'array( ';
		$collect = array();
		foreach ($array as $item)
		{
			switch (gettype($item))
			{
				case 'integer':
					$collect[] = $item;
					break;

				case 'string':
					$collect[] = '"' . $item . '"';
					break;

				case 'array':
					$collect[] = Util::unwrap_array($item);
					break;

				case 'object':
					$collect[] = get_class($item);
					break;

				default:
					$collect[] = gettype($item);
			}
		}
		$out .= implode(', ', $collect);
		$out .= ' )';

		return $out;
	}
}
