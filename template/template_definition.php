<?php
include 'helpers/markdown.php';

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
	public $template_dir;

	public function __construct($class, $output_dir, $template_dir)
	{
		$this->template_dir = $template_dir;
		$this->file_depth = '../../';

		$this->xml = simplexml_load_file(realpath($output_dir) . '/xml/' . $class . '.xml', "SimpleXMLElement", LIBXML_NOCDATA);
		parent::__construct($class, $output_dir);
	}

	public function build_tree()
	{
		/*
		var tree = [
			["CGI", "", " < Object", [
				["Cookie", "classes\/CGI\/Cookie.html", " < DelegateClass(Array)", []],
				["QueryExtension", "classes\/CGI\/QueryExtension.html", "", []]
			]]
		];
		*/

		$tree_node = array(
			(string) $this->xml->class->name,
			'class/' . (string) $this->xml->class->name . '/index.html',
			(isset($this->xml->class->summary->parentClasses->class) ? ' <' . (string) $this->xml->class->summary->parentClasses->class . '>' : ''),
			array()
		);

		foreach ($this->xml->class->methods->method as $method)
		{
			$tree_node[3][] = array(
				(string) $method->name,
				'class/' . (string) $this->xml->class->name . '/' . (string) $method->name . '.html',
				(isset($method->inherited) ? ' <' . (string) $method->inherited->attributes()->from . ">" : ''),
				array()
			);
		}

		return $tree_node;
	}

	public function class_init()
	{
		$this->index();
		$this->properties();
		$this->methods();
	}

	public function readme($classes)
	{
		$this->xml = null;
		$this->file_depth = '';

		$template = array(
			'doctype' => 'README',
			'title' => 'PHP SDK Reference',
			'classes' => $classes
		);

		$this->start();
		include $this->template_dir . '/partials/README.phtml';
		$this->body = $this->end();

		$this->start();
		include 'layout.phtml';
		$this->end($this->output_dir . '/html/README.html');

		echo '    ' . $this->output_dir . '/html/README.html' . PHP_EOL;
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
		$this->end($this->output_dir . '/html/class/' . strtolower($this->class) . '/index.html');
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
		$this->end($this->output_dir . '/html/class/' . strtolower($this->class) . '/properties.html');
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
			$this->end($this->output_dir . '/html/class/' . strtolower($this->class) . '/' . (string) $method->name . '.html');
		}
	}
}
