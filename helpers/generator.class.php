<?php
class Generator
{
	public $examples;
	public $class;
	public $output_dir;
	public $write_to = array();

	public function __construct($class, $output)
	{
		$this->class = $class;
		$this->output_dir = $output;
		$this->read_examples();
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
			@mkdir(dirname($fname), 0777, true);
			@chmod(dirname($fname), 0777);
		}

		$contents = ob_get_contents();
		ob_end_clean();

		if ($fname)
		{
			file_put_contents($fname, $contents);
			$this->write_to[] = $fname;
		}

		return $contents;
	}

	public function read_examples()
	{
		$this->examples = array();
		$all_examples = Util::rglob('examples.yml');
		foreach ($all_examples as $example)
		{
			$example = realpath($example);
			$yaml = spyc_load_file($example);
			foreach ($yaml as $class => $methods)
			{
				if ($methods)
				{
					foreach ($methods as $method => $tests)
					{
						if ($tests)
						{
							foreach ($tests as $index => $test)
							{
									$yaml[$class][$method][$index] = dirname($example) . DIRECTORY_SEPARATOR . $test;
							}
						}
					}
				}
			}
			$this->examples = array_merge($this->examples, $yaml);
		}
	}
}
