<?php
/**
 * DocblockParser Class Definition File
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @license MIT License
 * @version 1.1
 */

class DocblockParser
{
	/**
	 *
	 */
	protected $docComment;

	public function __construct ($docComment)
	{
		$this->docComment = $docComment;
	}

	/**
	 *
	 */
	public function cleanLists()
	{
		$this->docComment = preg_replace('/<ul>(\s+\*\s+)<li>/im', "<ul><li>", $this->docComment);
		$this->docComment = preg_replace('/<\/li>(\s+\*\s+)<li>/im', "</li><li>", $this->docComment);
		$this->docComment = preg_replace('/<\/li>(\s+\*\s+)<\/ul>/im', "</li></ul>", $this->docComment);
	}

	/**
	 *
	 */
	public static function parse_author($s)
	{
		if (is_array($s) && isset($s['description'])) $s = $s['description'];

		return preg_replace_callback('/([a-z\s]*)<([^>]*)>/i', function($m)
		{
			if (preg_match('/^\w[-.\w]*@([-a-z0-9]+\.)+[a-z]{2,4}$/i', $m[2]))
			{
				return '<a href="mailto:' . trim($m[2]) . '">' . trim($m[1]) . '</a>';
			}
			else
			{
				return '<a href="' . trim($m[2]) . '">' . trim($m[1]) . '</a>';
			}

		}, trim($s));
	}

	/**
	 *
	 */
	public static function parse_link($s)
	{
		$s = $s['description'];
		$pieces = explode(' ', $s);
		$url = array_shift($pieces);
		$label = implode(' ', $pieces);

		return sprintf('<a href="%s">%s</a>', $url, $label);
	}
}
