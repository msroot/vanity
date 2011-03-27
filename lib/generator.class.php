<?php
class Generator
{
	public $classname;
	public $data;
	public $template;
	public $partials;
	public $linkmap;
	public $options;
	public $storage;

	/**
	 * Constructs a new instance of the class.
	 *
	 * @return void
	 */
	public function __construct($datafile)
	{
		global $vanitydir;
		$this->data = simplexml_load_file($datafile, 'Vanity_Template_XML', LIBXML_NOCDATA);
		$this->classname = (string) $this->data->class->name;
		$this->template = new stdClass();
		$this->partials = self::fetch_all_partials();
		$this->linkmap = unserialize($vanitydir->get_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.linkmap'));
		$this->options = unserialize($vanitydir->get_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.options'));
		$this->storage = unserialize($vanitydir->get_contents(VANITY_CACHE_DIR . sha1(CONFIG_DIR) . '.storage'));

		// Default values
		$this->template->type = 'Class';
		$this->template->title = (string) $this->data->class->name;
		$this->template->subtext = (string) $this->data->class->file;

		$this->class_init();
	}

/**************************************************************************************************************************/

	/**
	 * Start buffering the output.
	 *
	 * @return void
	 */
	public static function start()
	{
		ob_start();
	}

	/**
	 * Stop buffering the output.
	 *
	 * @param string $fname (Optional) The file path to write the contents of the buffer to.
	 * @return void
	 */
	public static function end($fname = null)
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
			file_put_contents($fname, Util::strip_whitespace($contents));
		}

		return $contents;
	}

/**************************************************************************************************************************/

	/**
	 * The method that kicks off all of the other processing.
	 *
	 * @return void
	 */
	public function class_init()
	{
		$this->index();
		$this->constants();
		$this->properties();
		$this->methods();
	}

	/**
	 * The method to execute while generating the class index page.
	 *
	 * @return void
	 */
	public function index()
	{
		$this->template->type = 'Class';
		$this->template->titlebar = $this->classname;

		self::start();
		include TEMPLATE_DIR . 'partials/index.phtml';
		$this->body = self::end();

		self::start();
		include TEMPLATE_DIR . 'layout.phtml';
		self::end(HTML_DIR . 'class/' . strtolower($this->classname) . '/index.html');

		$this->output_success(HTML_DIR . 'class/' . strtolower($this->classname) . '/index.html');
	}

	/**
	 * The method to execute while generating the pages for class constants.
	 *
	 * @return void
	 */
	public function constants()
	{
		$this->template->type = 'Constants';
		$this->template->titlebar = $this->classname . ' - Constants';

		self::start();
		include TEMPLATE_DIR . 'partials/constants.phtml';
		$this->body = self::end();

		self::start();
		include TEMPLATE_DIR . 'layout.phtml';
		self::end(HTML_DIR . 'class/' . strtolower($this->classname) . '/constants.html');

		$this->output_success(HTML_DIR . 'class/' . strtolower($this->classname) . '/constants.html');
	}

	/**
	 * The method to execute while generating the pages for class properties.
	 *
	 * @return void
	 */
	public function properties()
	{
		$this->template->type = 'Properties';
		$this->template->titlebar = $this->classname . ' - Properties';

		self::start();
		include TEMPLATE_DIR . 'partials/properties.phtml';
		$this->body = self::end();

		self::start();
		include TEMPLATE_DIR . 'layout.phtml';
		self::end(HTML_DIR . 'class/' . strtolower($this->classname) . '/properties.html');

		$this->output_success(HTML_DIR . 'class/' . strtolower($this->classname) . '/properties.html');
	}

	/**
	 * The method to execute while generating the pages for class methods.
	 *
	 * @return void
	 */
	public function methods()
	{
		$this->template->type = 'Method';

		foreach ($this->data->class->methods->method as $method)
		{
			$this->template->titlebar = $this->classname . '::' . (string) $method->name . '()';

			self::start();
			include TEMPLATE_DIR . 'partials/methods.phtml';
			$this->body = self::end();

			self::start();
			include TEMPLATE_DIR . 'layout.phtml';
			self::end(HTML_DIR . 'class/' . strtolower($this->classname) . '/' . strtolower((string) $method->name) . '.html');

			$this->output_success(HTML_DIR . 'class/' . strtolower($this->classname) . '/' . strtolower((string) $method->name) . '.html');
		}
	}

/**************************************************************************************************************************/

	/**
	 * Reads the `copy.yml` file and determines which files to copy from the TEMPLATE_DIR to
	 * the HTML_DIR.
	 *
	 * @return void
	 */
	public static function copy()
	{
		global $configdir, $vanitydir;
		if ($vanitydir->exists(TEMPLATE_DIR . 'copy.yml'))
		{
			$files = spyc_load($vanitydir->get_contents(TEMPLATE_DIR . 'copy.yml'));
			foreach ($files as $file)
			{
				$subsequent_path = '';
				if (strpos($file, DIRECTORY_SEPARATOR) !== false)
				{
					$subsequent_path = explode(DIRECTORY_SEPARATOR, $file);
					array_pop($subsequent_path);
					$subsequent_path = implode(DIRECTORY_SEPARATOR, $subsequent_path) . DIRECTORY_SEPARATOR;
					if (!$outputdir->exists(HTML_DIR . $subsequent_path))
					{
						$outputdir->mkdir(HTML_DIR . $subsequent_path, true);
					}
				}

				$cmd = 'cp -Rf ' . TEMPLATE_DIR . $file . ' ' . HTML_DIR . $subsequent_path;
				echo TAB . $cmd . PHP_EOL;
				//shell_exec($cmd);
			}
		}

		foreach (array('xml', 'json', 'php') as $type)
		{
			$cmd = 'cp -Rf ' . OUTPUT_DIR . $type . ' ' . HTML_DIR;
			echo TAB . $cmd . PHP_EOL;
			//shell_exec($cmd);
		}
	}

	/**
	 *
	 */
	public function files()
	{
		unset($this->data);
		unset($this->classname);

		$fileset = array();
		$map = array('README' => '');

		if (isset($this->options['readme']))
		{
			$file = realpath(WORKING_DIR . $this->options['readme']);
			if (file_exists($file))
			{
				$fileset[] = $file;
			}
		}
		else
		{
			$fileset[] = TEMPLATE_DIR . 'partials' . DIRECTORY_SEPARATOR . 'README.phtml';
		}

		if (isset($this->options['add-files']) &&
		    is_array($this->options['add-files']))
		{
			foreach ($this->options['add-files'] as $key => $file)
			{
				$filepath = realpath(WORKING_DIR . $file);
				if (file_exists($filepath))
				{
					$fileset[] = $filepath;
					$map[$filepath] = $key;
				}
			}
		}

		foreach ($fileset as $file)
		{
			$filename = pathinfo(WORKING_DIR . $file, PATHINFO_FILENAME);
			$this->body = Util::convert_to_html($file);

			$doc = new DOMDocument();
			$doc->loadHTML($this->body);

			$this->template->type = isset($map[$file]) ? $map[$file] : 'README';
			if (strtolower($this->template->type) === 'readme')
			{
				$title = array();
				if (isset($this->options['product-name'])) $title[] = SmartyPants($this->options['product-name']);
				if (isset($this->options['product-version'])) $title[] = SmartyPants($this->options['product-version']);

				$this->template->title = implode(' ', $title);
			}
			else
			{
				$this->template->title = (string) $doc->getElementsByTagName('h1')->item(0)->textContent;
			}
			$this->template->subtext = str_replace(WORKING_DIR, '', $file);
			$this->template->titlebar = $this->template->type;

			self::start();
			include TEMPLATE_DIR . 'layout.phtml';
			self::end(HTML_DIR . 'files' . DIRECTORY_SEPARATOR . 'included' . DIRECTORY_SEPARATOR . strtolower($filename) . '.html');

			$this->output_success(HTML_DIR . 'files' . DIRECTORY_SEPARATOR . 'included' . DIRECTORY_SEPARATOR . strtolower($filename) . '.html');
		}
	}

	/**
	 * Checks to determine whether or not the requested file path was successfully written to,
	 * then outputs a message indicating success or failure.
	 *
	 * @param string $path (Required) The file system path to verify.
	 * @return void
	 */
	public function output_success($path)
	{
		if (file_exists($path))
		{
			echo TAB . 'Created ' . $path . PHP_EOL;
		}
		else
		{
			echo TAB . '!!!!!!! ' . $path . PHP_EOL;
		}
	}

	/**
	 * Gets a list of all same-named properties for a given iterable object.
	 *
	 * @param SimpleXMLElement $obj (Required) An iterable `SimpleXMLElement` object.
	 * @param string $property (Required) The name of the property to return.
	 * @return array An indexed array of matching property values.
	 */
	public static function group_by_letter($obj, $property)
	{
		$arr = array();

		foreach ($obj as $o)
		{
			$letter = strtolower((string) $o->$property);
			$letter = $letter[0];

			if (!preg_match('/[a-z]/i', $letter))
			{
				$letter = '#';
			}

			if (!isset($arr[$letter]))
			{
				$arr[$letter] = array();
			}

			$arr[$letter][] = (string) $o->$property;
		}

		return $arr;
	}

	/**
	 *
	 */
	public static function fetch_all_partials()
	{
		$files = Util::rglob(PARTIALS_DIR . '**.*');
		$PARTIALS = Util::content_partials($files);
		return (isset($PARTIALS['__all__']) && isset($PARTIALS['__all__'][''])) ? $PARTIALS['__all__'][''] : array();
	}

	/**
	 * Extend this method for anything you want Vanity to run once as a
	 * final pass after the other pages are generated.
	 */
	public static function fire_last() {}
}
