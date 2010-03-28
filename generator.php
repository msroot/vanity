<?php
class Generator
{
	public $write_to;

	public function __construct($class, $output)
	{
		return $this;
	}

	public function start()
	{
		ob_start();
	}

	public function end($fname = null)
	{
		if (!is_writable(dirname($fname)))
		{
			mkdir(dirname($fname), 0777, true);
			chmod(dirname($fname), 0777);
		}

		$contents = ob_get_contents();
		ob_end_clean();

		if ($fname)
		{
			file_put_contents($fname, $contents);
			$this->write_to = $fname;
		}

		return $contents;
	}
}
