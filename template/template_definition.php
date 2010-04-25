<?php
/**
 * Class: CopyFiles
 * 	Used for handling copy operations.
 */
class CopyFiles
{
	/**
	 * Method: get_files()
	 * 	Tells Vanity which files/directories to copy directly from the template directory over to the output directory.
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
	 * Property: $title
	 * 	Holds the title that was passed in from the CLI.
	 */
	public $title;

	/**
	 * Property: $readme
	 * 	Holds the README location that was passed in from the CLI.
	 */
	public $readme;

	/**
	 * Method: __construct
	 */
	public function __construct($class, $output_dir, $template_dir, $title, $readme = null)
	{
		$this->template_dir = $template_dir;
		$this->title = $title;
		$this->readme = $readme;
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
			'class/' . strtolower((string) $this->xml->class->name) . '/index.html',
			(isset($this->xml->class->summary->parentClasses->class) ? ' < ' . (string) $this->xml->class->summary->parentClasses->class : ''),
			array()
		);

		$tree_node[3][] = array('Constants', 'class/' . strtolower((string) $this->xml->class->name) . '/constants.html', '', array());
		$tree_node[3][] = array('Properties', 'class/' . strtolower((string) $this->xml->class->name) . '/properties.html', '', array());

		foreach ($this->xml->class->methods->method as $method)
		{
			$tree_node[3][] = array(
				(string) $method->name,
				'class/' . strtolower((string) $this->xml->class->name) . '/' . (string) $method->name . '.html',
				(isset($method->inherited) ? ' < ' . (string) $method->inherited->attributes()->from : ''),
				array()
			);
		}

		return $tree_node;
	}

	public function build_search_index($search_index)
	{
		// Collect class name
		$search_index->index->searchIndex[] = strtolower((string) $this->xml->class->name);
		$search_index->index->longSearchIndex[] = '';
		$search_index->index->info[] = array(
			(string) $this->xml->class->name, // Class name
			'',
			'class/' . strtolower((string) $this->xml->class->name) . '/index.html', // File location
			'', // After the name
			(string) $this->xml->class->fileData->docBlock->section->contents, // Description
			1 // ?
		);

		// Collect constants
		foreach ($this->xml->class->constants->constant as $constant)
		{
			$search_index->index->searchIndex[] = strtolower((string) $constant->name);
			$search_index->index->longSearchIndex[] = strtolower((string) $this->xml->class->name);
			$search_index->index->info[] = array(
				(string) $constant->name, // Constant name
				(string) $this->xml->class->name, // Class name
				'class/' . strtolower((string) $this->xml->class->name) . '/constants.html#' . (string) $constant->name, // File location
				'', // After the name
				'', //(string) $property->description->line, // Description
				1 // ?
			);
		}

		// Collect properties
		foreach ($this->xml->class->properties->property as $property)
		{
			$search_index->index->searchIndex[] = strtolower((string) $property->name);
			$search_index->index->longSearchIndex[] = strtolower((string) $this->xml->class->name);
			$search_index->index->info[] = array(
				(string) $property->name, // Property name
				(string) $this->xml->class->name, // Class name
				'class/' . strtolower((string) $this->xml->class->name) . '/properties.html#' . (string) $property->name, // File location
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
				'class/' . strtolower((string) $this->xml->class->name) . '/' . (string) $method->name . '.html', // File location
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
		$this->constants();
		$this->properties();
		$this->methods();
	}

	public function frame()
	{
		$this->start();
		include $this->template_dir . '/index.phtml';
		$this->end($this->output_dir . '/html/index.html');

		echo '    ' . $this->output_dir . '/html/index.html' . PHP_EOL;
	}

	public function readme($classes)
	{
		$this->xml = null;
		$this->file_depth = '';

		$template = array(
			'doctype' => 'README',
			'title' => $this->title,
			'classes' => $classes,
			'subtext' => 'Download the <a href="latest_docs.tar.gz">offline HTML docs</a>.'
		);

		$this->start();
		if ($this->readme)
		{
			echo '<div class="method readme">' . Markdown(file_get_contents(realpath($this->readme))) . '</div>';
		}
		else
		{
			include $this->template_dir . '/partials/README.phtml';
		}
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
			'title' => (string) $this->xml->class->name,
			'subtext' => (string) $this->xml->class->summary->docBlock->section[0]->contents->line
		);

		$this->start();
		include $this->template_dir . '/partials/index.phtml';
		$this->body = $this->end();

		$this->start();
		include 'layout.phtml';
		$this->end($this->output_dir . '/html/class/' . strtolower($this->class) . '/index.html');
	}

	public function constants()
	{
		$template = array(
			'doctype' => 'Constants',
			'title' => (string) $this->xml->class->name,
			'subtext' => (string) $this->xml->class->summary->docBlock->section[0]->contents->line
		);

		$this->start();
		include $this->template_dir . '/partials/constants.phtml';
		$this->body = $this->end();

		$this->start();
		include 'layout.phtml';
		$this->end($this->output_dir . '/html/class/' . strtolower($this->class) . '/constants.html');
	}

	public function properties()
	{
		$template = array(
			'doctype' => 'Properties',
			'title' => (string) $this->xml->class->name,
			'subtext' => (string) $this->xml->class->summary->docBlock->section[0]->contents->line
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
				'header' => (string) $this->xml->class->name,
				'subtext' => (string) $this->xml->class->summary->docBlock->section[0]->contents->line
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
