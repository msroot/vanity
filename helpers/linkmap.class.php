<?php
class LinkMap
{
	private $classes;
	private $map;

	public function __construct()
	{
		$this->classes = array();
		$this->map = array();
		return $this;
	}

	public function add_class($class)
	{
		$this->classes[] = $class;
	}

	public function generate_map()
	{
		$this->process();
		return $this->map;
	}

	private function process()
	{
		if (sizeof($this->classes))
		{
			foreach ($this->classes as $class)
			{
				$rclass = new ReflectionClass($class);
				$rclass_properties = $rclass->getDefaultProperties();
				$rclass_constants = $rclass->getConstants();
				$rclass_methods = $rclass->getMethods();

				$this->map[$class]['index'] = strtolower($class) . '/index.html';

				if (sizeof($rclass_properties))
				{
					foreach ($rclass_properties as $property => $v)
					{
						$this->map[$class][$property] = strtolower($class) . '/properties.html#' . $property;
					}
				}

				if (sizeof($rclass_constants))
				{
					foreach ($rclass_constants as $constant => $v)
					{
						$this->map[$class][$constant] = strtolower($class) . '/constants.html#' . $constant;
					}
				}

				if (sizeof($rclass_methods))
				{
					foreach ($rclass_methods as $method)
					{
						$this->map[$class][(string) $method->getName() . '()'] = strtolower($class) . '/' . (string) $method->getName() . '.html';
					}
				}
			}
		}
	}
}
