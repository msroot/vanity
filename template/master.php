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
	public $yield;
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
	}

	public function index()
	{
		$template = array(
			'doctype' => 'index',
			'title' => (string) $this->xml->class->name
		);

		$this->start();
		include $this->template_dir . '/partials/index.phtml';
		$this->yield = $this->end();

		$this->start();
		include 'layout.phtml';
		$this->end($this->output_dir . '/generated/class/' . strtolower($this->class) . '/index.html');
	}

	// public function class()
	// {
	// 	$template = array(
	// 		'title' => (string) $this->xml->class->name
	// 	);
	//
	// 	$this->start();
	//
	// 	$this->yield = print_r($this->xml, true);
	//
	// 	include 'layout.phtml';
	//
	// 	$this->end($this->output_dir . '/generated/class/' . strtolower($this->class) . '.html');
	// }
}
