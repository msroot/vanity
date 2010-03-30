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
			$arr[(string) $o->param] = array(
				'type' => (string) $o->type,
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
}
