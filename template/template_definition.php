<?php
class CopyFiles
{
	public static function get_files()
	{
		// Define which files to copy to the generated doc directory.
		return array(
			'index.html',
			'images',
			'panel',
			'scripts',
			'styles',
		);
	}
}

class Template extends Generator
{
	public $xml;
	public $body;
	public $class;
	public $output_dir;
	public $template_dir;

	public function __construct($class, $output_dir, $template_dir)
	{
		$this->class = $class;
		$this->output_dir = $output_dir;
		$this->template_dir = $template_dir;

		$this->xml = simplexml_load_file(realpath($output_dir) . '/xml/' . $class . '.xml', "SimpleXMLElement", LIBXML_NOCDATA);
		parent::__construct($class, $output_dir);

		$this->index();
		$this->properties();
		$this->methods();
	}

	public function index()
	{
		$template = array(
			'doctype' => 'Class',
			'title' => (string) $this->xml->class->name
		);

		$this->start();
		include $this->template_dir . '/partials/index.phtml';
		$this->body = $this->end();

		$this->start();
		include 'layout.phtml';
		$this->end($this->output_dir . '/generated/class/' . strtolower($this->class) . '/index.html');
	}

	public function properties()
	{
		$template = array(
			'doctype' => 'Properties',
			'title' => (string) $this->xml->class->name
		);

		$this->start();
		include $this->template_dir . '/partials/properties.phtml';
		$this->body = $this->end();

		$this->start();
		include 'layout.phtml';
		$this->end($this->output_dir . '/generated/class/' . strtolower($this->class) . '/properties.html');
	}

	public function methods()
	{
		foreach ($this->xml->class->methods->method as $method)
		{
			$template = array(
				'doctype' => 'Methods',
				'title' => (string) $this->xml->class->name . '::' . (string) $method->name,
				'header' => (string) $this->xml->class->name
			);

			$this->start();
			include $this->template_dir . '/partials/methods.phtml';
			$this->body = $this->end();

			$this->start();
			include 'layout.phtml';
			$this->end($this->output_dir . '/generated/class/' . strtolower($this->class) . '/' . (string) $method->name . '.html');
		}
	}
}
