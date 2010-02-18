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
		preg_match_all('/\n\s*\* ([^\s](\w|\s|\$)*):[^:]/simU', $content, $method);
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

		return NDocs::parse_parameter_list($cleaned);
	}

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