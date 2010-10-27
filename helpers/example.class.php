<?php
/**
 * File: Example
 * 	Processes examples for output.
 *
 * Version:
 * 	2010.10.09
 */

class Example
{
	private $output;
	private $mod_path;

	public function __construct($path)
	{
		$this->output = file_get_contents($path);
		$this->mod_path = str_replace('/', '_', $path);
	}

	public function sections()
	{
		return Vanity_CacheFile::init('sections_' . $this->mod_path, CACHE_DIR, 31557600)->response_manager(function($output)
		{
			$cleaned = htmlentities($output, ENT_NOQUOTES, 'UTF-8');
			$phpt = new PHPT_Parser($cleaned);
			return $phpt->get_section();

		}, array($this->output));
	}

	public function display($code, $type)
	{
		return Vanity_CacheFile::init($type . '_' . $this->mod_path, CACHE_DIR, 31557600)->response_manager(function($code)
		{
			$example = new Examplify($code);
			$code = $example->output();

			$code = preg_replace('/&lt;\?(php?)|\?&gt;/', '', $code); // Remove <?php blocks
			$code = str_replace("\n\t", "\n", $code); // Remove all first tabs.
			$code = str_replace("\r\n", "\n", $code); // Change all Windows linebreaks to Unix ones.
			$code = trim($code); // Clean extra whitespace.

			return $code;

		}, array($code));
	}
}
