<?php
/**
 * File: NDocs
 * 	A NaturalDocs comment parser for PHP, without the documentation generator.
 *
 * Version:
 * 	2010.02.18
 *
 * Copyright:
 * 	2010 Ryan Parman
 *
 * License:
 * 	MIT License - http://opensource.org/licenses/mit-license.php
 *
 * See Also:
 * 	NaturalDocs - http://naturaldocs.org
 */


/*%******************************************************************************************%*/
// CLASS

class NDocs
{
	/**
	 * Get Comment Sections
	 * 	Determines which sections of the file are source code comment blocks.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	$content - _string_ (Required) The contents of the entire source code file.
	 *
	 * Returns:
	 * 	_array_ A collection of all of the comment blocks in the file.
	 */
	public static function get_comment_sections($content)
	{
		preg_match_all("/\/\*\*(\s*|.*)\*\//simU", $content, $matches);
		return $matches[1];
	}

	/**
	 * Get Headlines
	 * 	Gets a list of all of the section headers in a given comment block.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	$content - _string_ (Required) The contents of the comment block.
	 *
	 * Returns:
	 * 	_array_ A collection of all of the headlines in the comment block.
	 */
	public static function get_headlines($content)
	{
		preg_match_all('/\n\s*\* ([^\s](\w|\s|\$)*):[^:]/simU', $content, $method);
		return $method[1];
	}

	/**
	 * Parse Headline
	 * 	Determines what content is part of that particular section of the comment block, starting with the header.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	$headline - _string_ (Required) The headline (i.e. section header) to determine the content for.
	 * 	$content - _string_ (Required) The contents of the comment block.
	 *
	 * Returns:
	 * 	_array_ An array of (a) the content that comes after the headline's colon (used for Method, Property and Constant), and (b) the content that comes below the headline, before the next headline.
	 */
	public static function parse_headline($headline, $content)
	{
		$data = array();
		$data['headline'] = $headline;
		preg_match('/' . preg_quote($headline, '/') . ':([^\n]*)(.*)(\*(.*)\*\n)/simU', $content, $method); // Most of the headers in the comment block
		preg_match('/' . preg_quote($headline, '/') . ':\n(.*)/sim', $content, $last); // The last header section in the comment block
		preg_match('/' . preg_quote($headline, '/') . ':([^\n]*)(.*)\n(.*)\n/simU', $content, $const); // Constants and properties

		if (sizeof($method))
		{
			$data['after'] = trim($method[2]);
			$data['content'] = NDocs::parse_content($method[4]);
		}
		elseif (sizeof($last))
		{
			$data['after'] = '';
			$data['content'] = NDocs::parse_content($last[1]);
		}
		elseif (sizeof($const))
		{
			$data['after'] = trim($const[2]);
			$data['content'] = NDocs::parse_content($const[3]);
		}

		return $data;
	}

	/**
	 * Parse Content
	 * 	Once you've parsed the headline to get the headline's content, you can then parse that content into an array of lines.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	$content - _string_ (Required) The contents of the comment block.
	 *
	 * Returns:
	 * 	_array_ The lines of content for that section. Parameter lists become a sub-array.
	 */
	public static function parse_content($content)
	{
		$cleaned = array();
		$contents = explode("\n", $content);
		foreach ($contents as $line)
		{
			$line = trim(preg_replace('/\s*\*\s*/', '', $line));
			if ($line !== '')
			{
				$cleaned[] = $line;
			}
		}

		return NDocs::parse_parameter_list($cleaned);
	}

	/**
	 * Parse Parameter List
	 * 	This will go through all of the lines of content, and if a line matches a parameter regex pattern, it will further parse the line into smaller sections.
	 * 	This is used internally by NDocs::parse_content() and it is unlikely that you would need to call it manually.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	$content - _string_ (Required) The cleaned contents determined by NDocs::parse_content().
	 *
	 * Returns:
	 * 	_mixed_ Will return an array of data chunks if the content matches a parameter list pattern. Otherwise, returns a string of non-processed content.
	 */
	public static function parse_parameter_list($content)
	{
		if (is_array($content) && sizeof($content) > 0)
		{
			$temp = array();
			foreach ($content as $line)
			{
				preg_match('/(.*) - _(.*)_ \((\w*)\) (.*)/', $line, $param);
				preg_match('/(.*) - (.*)/', $line, $options);

				if (sizeof($param))
				{
					$type = str_replace('_', '', $param[2]);
					if (strpos($type, '|') !== false)
					{
						$type = explode('|', $type);
					}

					$temp[] = array(
						'param' => $param[1],
						'type' => $type,
						'required' => $param[3],
						'description' => $param[4]
					);
				}
				elseif (sizeof($options))
				{
					$temp[] = array(
						'param' => $options[1],
						'description' => $options[2]
					);
				}
				else
				{
					$temp[] = $line;
				}
			}

			return $temp;
		}

		return $content;
	}
}
