<?php
/**
 * File: Example
 * 	Processes examples.
 *
 * Version:
 * 	2010.04.24
 */

class Example
{
	private $output;

	public function __construct($path)
	{
		$this->output = file_get_contents($path);
	}

	public function sections()
	{
		$cleaned = htmlentities($this->output, ENT_NOQUOTES, 'UTF-8');
		$phpt = new PHPT_Parser($cleaned);
		return $phpt->get_section();
	}

	public function display($code)
	{
		$code = preg_replace('/&lt;\?(php?)|\?&gt;/', '', $code); // Remove <?php blocks
		$code = str_replace("\n\t", "\n", $code); // Remove all first tabs.
		$code = str_replace("\r\n", "\n", $code); // Change all Windows linebreaks to Unix ones.
		$code = preg_replace('/(require|include)(_once?)([^\n]*)\n/', '', $code); // Remove any require/include lines.
		$code = trim($code); // Clean extra whitespace.

		return $code;
	}
}
