<?php
/**
 * Handles all template-related methods.
 *
 * The `Template` class should always extend the `Generator` class.
 */
class Template extends Generator
{
	/**
	 * The `Template::__construct()` method should call the `parent::__construct()` method before
	 * it does anything else. You can always add additional functionality that you want to execute
	 * when `Template` is instantiated.
	 *
	 * @param string $datafile (Required) The file path to the XML definition of the class.
	 * @return void
	 */
	public function __construct($datafile)
	{
		parent::__construct($datafile);

		/**
		 * Custom code needed by this template. Used for building class browser
		 * and search functionality. Set up the base structure.
		 */

		// Add groups
		if (isset($this->options['add-group']) && is_array($this->options['add-group']))
		{
			foreach ($this->options['add-group'] as $group => $patterns)
			{
				if (!isset($this->storage['tree-nodes']))
				{
					$this->storage['tree-nodes'] = array();
				}

				if (!isset($this->storage['tree-nodes'][$group]))
				{
					$this->storage['tree-nodes'][$group] = array('', '#', $group, array());
				}
			}
		}

		// Build tree
		if (!isset($this->storage['tree']))
		{
			$this->storage['tree'] = array();
		}
		$this->build_tree();

		// Build search index
		if (!isset($this->storage['search_index']))
		{
			$this->storage['search_index'] = new stdClass();
			$this->storage['search_index']->index = new stdClass();
			$this->storage['search_index']->index->searchIndex = array();
			$this->storage['search_index']->index->longSearchIndex = array();
			$this->storage['search_index']->index->info = array();
		}
		$this->build_search_index();
	}

	/**
	 * This method is called when the class is going to be processed. It will, in turn, call the
	 * various other methods for each part of the class: constants, properties, methods, and the
	 * class index page.
	 *
	 * If you want to customize this template with additional functionality, make sure that
	 * functionality is triggered here.
	 *
	 * @return void
	 */
	public function class_init()
	{
		parent::class_init();
	}

	/**
	 * This method is called when attempting to build a page for the class index. Unless you choose
	 * to customize the implementation with your own logic here, Vanity will use the implementation
	 * defined in the Generator class which simply munges your templates with the XML data.
	 *
	 * @return void
	 */
	// public function index()
	// {
	// 	/*
	// 	 * 1. Start buffering the output.
	// 	 * 2. Include the template, which will execute its code.
	// 	 * 3. Stop buffering the output, and save it to the $this->body variable.
	// 	 */
	// 	self::start();
	// 	include TEMPLATE_DIR . 'partials/index.phtml';
	// 	$this->body = self::end();
	//
	// 	/*
	// 	 * 1. Start buffering the output.
	// 	 * 2. Include the template, which will execute its code. This template calls $this->body,
	// 	 *    so all of that data gets merged in.
	// 	 * 3. Stop buffering the output. Instead of saving it to a variable, write it to disk.
	// 	 */
	// 	self::start();
	// 	include TEMPLATE_DIR . 'layout.phtml';
	// 	self::end(HTML_DIR . 'class/' . strtolower($this->classname) . '/index.html');
	//
	// 	/*
	// 	 * If the file was successfully created, output the message to the console.
	// 	 */
	// 	$this->output_success(HTML_DIR . 'class/' . strtolower($this->classname) . '/index.html');
	// }

	/**
	 * This method is called when attempting to build a page for a class method. Unless you choose
	 * to customize the implementation with your own logic here, Vanity will use the implementation
	 * defined in the Generator class which simply munges your templates with the XML data.
	 *
	 * @return void
	 */
	// public function methods() {}

	/**
	 * This method is called when attempting to build a page for a class constant. Unless you choose
	 * to customize the implementation with your own logic here, Vanity will use the implementation
	 * defined in the Generator class which simply munges your templates with the XML data.
	 *
	 * @return void
	 */
	// public function constants() {}

	/**
	 * This method is called when attempting to build a page for a class property. Unless you choose
	 * to customize the implementation with your own logic here, Vanity will use the implementation
	 * defined in the Generator class which simply munges your templates with the XML data.
	 *
	 * @return void
	 */
	// public function properties() {}

	/**
	 *
	 */
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
			(string) $this->data->class->name,
			// 'class/' . strtolower((string) $this->data->class->name) . '/index.html',
			'#i=' . (string) $this->data->class->name,
			'',
			array()
		);

		// $tree_node[3][] = array('Constants', 'class/' . strtolower((string) $this->data->class->name) . '/constants.html', '', array());
		// $tree_node[3][] = array('Properties', 'class/' . strtolower((string) $this->data->class->name) . '/properties.html', '', array());
		$tree_node[3][] = array('Constants', '#c=' . (string) $this->data->class->name, '', array());
		$tree_node[3][] = array('Properties', '#p=' . (string) $this->data->class->name, '', array());

		foreach ($this->data->class->methods->method as $method)
		{
			$tree_node[3][] = array(
				(string) $method->name,
				// 'class/' . strtolower((string) $this->data->class->name) . '/' . (string) $method->name . '.html',
				'#m=' . (string) $this->data->class->name . '/' . (string) $method->name,
				'',
				array()
			);
		}

		$add = false;
		$added = false;

		if (isset($this->options['add-group']) && is_array($this->options['add-group']))
		{
			foreach ($this->options['add-group'] as $group => $patterns)
			{
				$add = false;

				foreach ($patterns as $pattern)
				{
					if (preg_match('/' . $pattern . '/i', (string) $this->data->class->name))
					{
						$add = true;
					}
				}

				if ($add)
				{
					$this->storage['tree-nodes'][$group][3][] = $tree_node;
					$added = true;
				}
			}
		}

		if (!$added)
		{
			$this->storage['tree'][] = $tree_node;
		}

		file_put_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.storage', serialize($this->storage));
	}

	/**
	 *
	 */
	public function build_search_index()
	{
		// Collect class name
		$this->storage['search_index']->index->searchIndex[] = strtolower((string) $this->data->class->name);
		$this->storage['search_index']->index->longSearchIndex[] = '';
		$this->storage['search_index']->index->info[] = array(
			(string) $this->data->class->name, // Class name
			'',
			// 'class/' . strtolower((string) $this->data->class->name) . '/index.html', // File location
			'#i=' . (string) $this->data->class->name, // File location
			'', // After the name
			strip_tags((string) $this->data->class->description), // Description
			1 // ?
		);

		// Collect constants
		foreach ($this->data->class->constants->constant as $constant)
		{
			$this->storage['search_index']->index->searchIndex[] = strtolower((string) $constant->name);
			$this->storage['search_index']->index->longSearchIndex[] = strtolower((string) $this->data->class->name);
			$this->storage['search_index']->index->info[] = array(
				(string) $constant->name, // Constant name
				(string) $this->data->class->name, // Class name
				// 'class/' . strtolower((string) $this->data->class->name) . '/constants.html#' . (string) $constant->name, // File location
				'#c=' . (string) $this->data->class->name . '/' . (string) $constant->name, // File location
				'', // After the name
				'', //(string) $property->description->line, // Description
				1 // ?
			);
		}

		// Collect properties
		foreach ($this->data->class->properties->property as $property)
		{
			$this->storage['search_index']->index->searchIndex[] = strtolower((string) $property->name);
			$this->storage['search_index']->index->longSearchIndex[] = strtolower((string) $this->data->class->name);
			$this->storage['search_index']->index->info[] = array(
				(string) $property->name, // Property name
				(string) $this->data->class->name, // Class name
				// 'class/' . strtolower((string) $this->data->class->name) . '/properties.html#' . (string) $property->name, // File location
				'#p=' . (string) $this->data->class->name . '/' . (string) $property->name, // File location
				'', // After the name
				strip_tags((string) $property->description), // Description
				1 // ?
			);
		}

		// Collect methods
		foreach ($this->data->class->methods->method as $method)
		{
			$this->storage['search_index']->index->searchIndex[] = strtolower((string) $method->name);
			$this->storage['search_index']->index->longSearchIndex[] = strtolower((string) $this->data->class->name);
			$this->storage['search_index']->index->info[] = array(
				(string) $method->name . '()', // Property name
				(string) $this->data->class->name, // Class name
				// 'class/' . strtolower((string) $this->data->class->name) . '/' . (string) $method->name . '.html', // File location
				'#m=' . (string) $this->data->class->name . '/' . (string) $method->name, // File location
				'', // After the name
				strip_tags((string) $method->description), // Description
				1 // ?
			);
		}

		file_put_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.storage', serialize($this->storage));
	}

	/**
	 * Extend this method for anything you want Vanity to run once as a
	 * final pass after the other pages are generated.
	 *
	 * @return void
	 */
	public static function fire_last()
	{
		$OPTIONS = unserialize(file_get_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.options'));
		$STORAGE = unserialize(file_get_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.storage'));

		// Generate frame
		echo 'GENERATING FRAMESET' . PHP_EOL;
		self::start();
		include TEMPLATE_DIR . 'index.phtml';
		self::end(HTML_DIR . 'index.html');

		$path = HTML_DIR . 'index.html';
		if (file_exists($path)) echo TAB . 'Created ' . $path . PHP_EOL;
		else echo TAB . '!!!!!!! ' . $path . PHP_EOL;
		echo PHP_EOL;

		// Add groups to the tree
		if (isset($STORAGE['tree-nodes']) && is_array($STORAGE['tree-nodes']))
		{
			foreach ($STORAGE['tree-nodes'] as $node)
			{
				$STORAGE['tree'][] = $node;
			}
		}

		// Add files to the tree
		if (isset($OPTIONS['add-files']) && is_array($OPTIONS['add-files']))
		{
			foreach ($OPTIONS['add-files'] as $key => $file)
			{
				$filename = pathinfo($file, PATHINFO_FILENAME);
				$STORAGE['tree'][] = array($key, 'd=' . $filename . '', '', array());
			}
		}

		// Add the README
		$STORAGE['tree'][] = array('README', 'd=README', '', array());

		// Generate search and browse tree indexes
		self::start();
		echo 'var tree = ' . json_encode($STORAGE['tree']);
		self::end(HTML_DIR . 'panel' . DIRECTORY_SEPARATOR . 'tree.js');

		self::start();
		echo 'var search_data = ' . json_encode($STORAGE['search_index']);
		self::end(HTML_DIR . 'panel' . DIRECTORY_SEPARATOR . 'search_index.js');

		echo 'GENERATING SEARCH AND BROWSE TREE INDEXES' . PHP_EOL;

		$path = HTML_DIR . 'panel' . DIRECTORY_SEPARATOR . 'tree.js';
		if (file_exists($path)) echo TAB . 'Created ' . $path . PHP_EOL;
		else echo TAB . '!!!!!!! ' . $path . PHP_EOL;

		$path = HTML_DIR . 'panel' . DIRECTORY_SEPARATOR . 'search_index.js';
		if (file_exists($path)) echo TAB . 'Created ' . $path . PHP_EOL;
		else echo TAB . '!!!!!!! ' . $path . PHP_EOL;

		echo PHP_EOL;
	}
}
