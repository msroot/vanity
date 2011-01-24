<?php
/**
 * File: Example
 * 	Processes examples for output.
 *
 * Version:
 * 	2011.01.18
 */

class Example
{
	private $output;
	private $mod_path;
	private $path;

	public function __construct($path)
	{
		if ($path instanceof SimpleXMLElement)
		{
			$this->path = false;
			$this->output = $path;
		}
		else
		{
			$this->path = true;
			$this->output = file_get_contents($path);
			$this->mod_path = str_replace('/', '_', $path);
		}
	}

	public function sections()
	{
		if ($this->path)
		{
			$cleaned = htmlentities($this->output, ENT_NOQUOTES, 'UTF-8');
			$phpt = new PHPT_Parser($cleaned);
			return $phpt->get_section();
		}
		else
		{
			$tarray = array();

			if (isset($this->output->title))
			{
				$tarray['TEST'] = (string) $this->output->title;
			}
			if (isset($this->output->simpara))
			{
				$tarray['DESCRIPTION'] = (string) $this->output->simpara;
			}
			if (isset($this->output->programlisting))
			{
				$tarray['FILE'] = trim($this->display((string) $this->output->programlisting));
			}
			if (isset($this->output->screen))
			{
				$tarray['EXPECT'] = trim($this->display((string) $this->output->screen));
			}

			return $tarray;
		}
	}

	public static function display($code)
	{
		$example = new Examplify($code);
		$code = $example->output();

		$code = preg_replace('/(<|&lt;)\?(php?)|\?(&gt;|>)/', '', $code); // Remove <?php blocks
		$code = str_replace("\n\t", "\n", $code); // Remove all first tabs.
		$code = str_replace("\r\n", "\n", $code); // Change all Windows linebreaks to Unix ones.
		$code = trim($code); // Clean extra whitespace.

		return $code;
	}
}
