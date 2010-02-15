<?php
class NDocs
{
	public static function get_comment_sections($content)
	{
		preg_match_all("/\/\*\*(\s*|.*)\*\//simU", $content, $matches);
		return $matches[1];
	}

	public static function get_headlines($content)
	{
		preg_match_all('/\n\s*\* ((\w|\s|\$)*):[^:]/simU', $content, $method);
		return $method[1];
	}

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
		return $cleaned;
	}

	public static function parse_headline($headline, $content)
	{
		$data = array();
		$data['headline'] = $headline;
		preg_match('/' . preg_quote($headline, '/') . ':([^\n]*)(.*)(\*(.*)\*\n)/simU', $content, $method);
		preg_match('/' . preg_quote($headline, '/') . ':\n(.*)/sim', $content, $last);

		// If $method matches
		if (sizeof($method))
		{
			$data['after'] = trim($method[2]);
			$data['content'] = NDocs::parse_content($method[4]);
		}
		else
		{
			$data['after'] = '';
			$data['content'] = NDocs::parse_content($last[1]);
		}

		return $data;
	}
}