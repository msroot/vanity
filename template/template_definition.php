<?php
include 'helpers/markdown.php';

/**
 * Class: CopyFiles
 * 	Used for handling copy operations.
 */
class CopyFiles
{
	/**
	 * Method: get_files()
	 * 	Tells NDocs which files/directories to copy directly from the template directory over to the output directory.
	 *
	 * Access:
	 * 	public static
	 *
	 * Returns:
	 * 	_array_ The list of files and folders to copy.
	 */
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


/**
 * Class: Template
 * 	Handles all template-related methods.
 */
class Template extends Generator
{
	/**
	 * Property: $xml
	 * 	Holds the XML data used in the templates.
	 */
	public $xml;

	/**
	 * Property: $body
	 */
	public $body;

	/**
	 * Property: $template_dir
	 * 	Holds the location of the template (input) directory.
	 */
	public $template_dir;

	/**
	 * Method: __construct
	 */
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
			(isset($this->xml->class->summary->parentClasses->class) ? ' < ' . (string) $this->xml->class->summary->parentClasses->class : ''),
			array()
		);

		foreach ($this->xml->class->methods->method as $method)
		{
			$tree_node[3][] = array(
				(string) $method->name,
				'class/' . (string) $this->xml->class->name . '/' . (string) $method->name . '.html',
				(isset($method->inherited) ? ' < ' . (string) $method->inherited->attributes()->from : ''),
				array()
			);
		}

		return $tree_node;
	}

	public function build_search_index($search_index)
	{
		/*
		var search_data = {
			"index": {
				"searchIndex": ["authenticate"],
				"longSearchIndex": [""],
				"info": [
					["authenticate()", "CloudFusion", "class\/CloudFusion\/authenticate.html", "", "Default, shared method for authenticating a connection to AWS.", 1]
				]
			}
		}
		*/

		// Collect class name
		$search_index->index->searchIndex[] = strtolower((string) $this->xml->class->name);
		$search_index->index->longSearchIndex[] = '';
		$search_index->index->info[] = array(
			(string) $this->xml->class->name, // Class name
			'',
			'class/' . (string) $this->xml->class->name . '/index.html', // File location
			'', // After the name
			(string) $this->xml->class->fileData->docBlock->section->contents, // Description
			1 // ?
		);

		// Collect constants

		// Collect properties
		foreach ($this->xml->class->properties->property as $property)
		{
			$search_index->index->searchIndex[] = strtolower((string) $property->name);
			$search_index->index->longSearchIndex[] = strtolower((string) $this->xml->class->name);
			$search_index->index->info[] = array(
				(string) $property->name, // Property name
				(string) $this->xml->class->name, // Class name
				'class/' . (string) $this->xml->class->name . '/properties.html#' . (string) $property->name, // File location
				'', // After the name
				(string) $property->description->line, // Description
				1 // ?
			);
		}

		// Collect methods
		foreach ($this->xml->class->methods->method as $method)
		{
			$search_index->index->searchIndex[] = strtolower((string) $method->name);
			$search_index->index->longSearchIndex[] = strtolower((string) $this->xml->class->name);
			$search_index->index->info[] = array(
				(string) $method->name . '()', // Property name
				(string) $this->xml->class->name, // Class name
				'class/' . (string) $this->xml->class->name . '/' . (string) $method->name . '.html', // File location
				'', // After the name
				(string) $method->docBlock->section->contents, // Description
				1 // ?
			);
		}

		return $search_index;
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
