<?php
/**
 * DocblockParser Class Definition File
 *
 * This file contains the definition for the documenting reflection method class
 *
 * @copyright Copyright 2009, Ian Selby
 * @copyright Copyright 2010, Ryan Parman
 * @author Ian Selby <ian@gen-x-design.com>
 * @author Ryan Parman <http://ryanparman.com>
 * @license MIT License
 * @version 1.1
 */

/**
 * DocblockParser Class
 *
 * This class extends the ReflectionMethod class and adds the ability
 * to parse a class method's doc block comments.
 *
 * @version 1.0
 */
class DocblockParser
{
	/**
	 * Newline Token Number
	 *
	 * @var int
	 */
	const T_DOCBLOCK_NEWLINE 	= 1;
	/**
	 * Whitespace Token Number
	 *
	 * @var int
	 */
	const T_DOCBLOCK_WHITESPACE	= 2;
	/**
	 * Text Token Number
	 *
	 * @var int
	 */
	const T_DOCBLOCK_TEXT		= 36;
	/**
	 * Tag Token Number
	 *
	 * @var int
	 */
	const T_DOCBLOCK_TAG		= 5;

	/**
	 * Map of Token Numbers to Token Names
	 *
	 * @var array
	 */
	protected static $tokenNames 		= array(1 => 'DOCBLOCK_NEWLINE', 2 => 'DOCBLOCK_WHITESPACE', 5 => 'DOCBLOCK_TAG', 36 => 'DOCBLOCK_TEXT');
	/**
	 * Array of Newline Characters
	 *
	 * These characters count as newlines by themselves when being tokenized.
	 *
	 * @var array
	 */
	protected static $newLineChars 		= array('/**', '*/', '*');
	/**
	 * Regex to Match Whitespace
	 *
	 * @var string
	 */
	protected static $whitespaceRegex 	= '/\*[\s\t]+/';
	/**
	 * Regex to Match Tags
	 */
	protected static $tagRegex			= '/@[a-zA-Z0-9]*\s/';

	/**
	 * All Parsed Comments
	 *
	 * @var array
	 */
	protected $comments;
	/**
	 * All Parsed Tags
	 *
	 * This is an associative array of tags with the keys as tag names
	 * and the values as the tag value
	 *
	 * @var array
	 */
	protected $tags;
	/**
	 * The Raw Tokens
	 *
	 * This is an array of token groups.  A token group is an array of individual
	 * tokens
	 *
	 * @var array
	 */
	protected $tokens;
	/**
	 * The Declaring Class for the Method We're Tokenizing the DocBlock of
	 *
	 * @var Object
	 */
	protected $declaringClass;

	/**
	 *
	 */
	protected $docComment;

	/**
	 * Class Constructor
	 *
	 * @param Object $object Instance of the object that contains the method we're going to parse comments for
	 * @param string $method The name of the method to parse comments for
	 *
	 * @return void
	 */
	// public function __construct ($object, $method)
	public function __construct ($docComment)
	{
		// parent::__construct($object, $method);
		//
		// $docComment				= $this->getDocComment();
		// $this->declaringClass 	= $object;

		$this->docComment = $docComment;

		$this->cleanLists();
		$this->tokenizeDocComment($this->docComment);
		$this->parseTokens();
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
	 * Outputs the Doc Tokens
	 *
	 * @return void
	 */
	public function printDocTokens ()
	{
		$return = '';

		foreach ($this->tokens as $tokens)
		{
			foreach ($tokens as $token)
			{
				$return .= $token[0] . '=' . self::$tokenNames[$token[0]] . '=' . $token[1] . "<br />";
			}

			$return .= '<br />';
		}

		echo $return;
	}

	/**
	 * Returns DocblockParser::$tags
	 *
	 * @return array
	 */
	public function getTags ()
	{
		return $this->tags;
	}

	/**
	 * Returns DocblockParser::$comments
	 *
	 * @return array
	 */
	public function getComments ()
	{
		return implode("\n", $this->comments);
	}

	/**
	 * Parses the Tokens
	 *
	 * This function loops over all the token groups and splits the tags and comments
	 * into the appropriate arrays on this class
	 *
	 * @return void
	 */
	protected function parseTokens ()
	{
		foreach ($this->tokens as $tokens)
		{
			$tagName = null;

			foreach ($tokens as $token)
			{
				if ($token[0] == DocblockParser::T_DOCBLOCK_NEWLINE || $token[0] == DocblockParser::T_DOCBLOCK_WHITESPACE)
				{
					continue;
				}

				if ($token[0] == DocblockParser::T_DOCBLOCK_TAG)
				{
					$tagName = $token[1];
				}

				if ($token[0] == DocblockParser::T_DOCBLOCK_TEXT)
				{
					if ($tagName !== null)
					{
						if (isset($this->tags[str_replace('@', '', $tagName)]) &&
						    is_string($this->tags[str_replace('@', '', $tagName)]))
						{
							$this->tags[str_replace('@', '', $tagName)] = array(
								$this->tags[str_replace('@', '', $tagName)],
								$token[1]
							);
						}
						elseif (!isset($this->tags[str_replace('@', '', $tagName)]))
						{
							$this->tags[str_replace('@', '', $tagName)] = $token[1];
						}
						else
						{
							$this->tags[str_replace('@', '', $tagName)][] = $token[1];
						}

						$tagName = null;
					}
					else
					{
						$this->comments[] = $token[1];
					}
				}
			}
		}
	}

	/**
	 * Runs the Tokenizing Routine on a Doc Comment
	 *
	 * @param string $docComment The method's doc comment
	 *
	 * @return void
	 */
	protected function tokenizeDocComment ($docComment)
	{
		$docComment = str_replace("\r\n", "\n", $docComment);
		$lines = explode("\n", $docComment);

		foreach ($lines as $line)
		{
			$this->tokens[] = $this->tokenizeLine($line);
		}
	}

	/**
	 * Tokenizes a Line in the Doc Comment
	 *
	 * @param string $line A doc comment line
	 *
	 * @return array
	 */
	protected function tokenizeLine ($line)
	{
		$lineTokens = array();
		$line 		= trim($line);

		// check for newline
		if (in_array($line, self::$newLineChars))
		{
			$lineTokens[] = array(DocblockParser::T_DOCBLOCK_NEWLINE, "\n");
			// return $lineTokens;

			foreach (self::$newLineChars as $token)
			{
				$regex = '/^(\s|\t)*' . Util::regex_token($token) . '\s?/';
				$line = preg_replace($regex, '', $line);
			}
		}

		if (preg_match(self::$whitespaceRegex, $line, $matches))
		{
			$lineTokens[] = array(DocblockParser::T_DOCBLOCK_WHITESPACE, $matches[0]);
			$line = str_replace($matches[0], '', $line);
		}

		if (preg_match(self::$tagRegex, $line, $matches))
		{
			$lineTokens[] = array(DocblockParser::T_DOCBLOCK_TAG, trim($matches[0]));
			$line = str_replace($matches[0], '', $line);
		}

		$lineTokens[] = array(DocblockParser::T_DOCBLOCK_TEXT, trim($line));
		$lineTokens[] = array(DocblockParser::T_DOCBLOCK_NEWLINE, "\n");

		return $lineTokens;
	}

	/**
	 *
	 */
	public static function parse_param($param)
	{
		$param = explode(' ', $param);

		$type = array_shift($param);
		$name = str_replace(array('$', '&$'), '', array_shift($param));
		$description = preg_replace('/^\((required|optional)\)\s?/i', '', implode(' ', $param));

		return array(
			'type' => $type,
			'name' => $name,
			'description' => $description,
		);
	}

	/**
	 *
	 */
	public static function parse_return($return)
	{
		if (is_array($return))
		{
			$return = array_shift($return);
		}

		$return = explode(' ', $return);

		return array(
			'type' => array_shift($return),
			'description' => implode(' ', $return),
		);
	}

	/**
	 *
	 */
	public static function parse_author($s)
	{
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
		$pieces = explode(' ', $s);
		$url = array_shift($pieces);
		$label = implode(' ', $pieces);

		return sprintf('<a href="%s">%s</a>', $url, $label);
	}
}
