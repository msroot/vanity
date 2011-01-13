<?php
class LinkMap
{
	private $classes;
	private $map;
	private $altmap;

	public function __construct()
	{
		$this->classes = array();
		$this->map = array();
		$this->altmap = array();
		return $this;
	}

	public function add_class($class)
	{
		$this->classes[] = $class;
	}

	public function generate_map()
	{
		$this->process();

		return array(
			'map' => $this->map,
			'altmap' => $this->altmap
		);
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
				$this->altmap[$class]['index'] = '#i=' . strtolower($class);

				if (sizeof($rclass_properties))
				{
					foreach ($rclass_properties as $property => $v)
					{
						$this->map[$class][$property] = strtolower($class) . '/properties.html#' . $property;
						$this->altmap[$class][$property] = '#p=' . strtolower($class) . '/' . $property;
					}
				}

				if (sizeof($rclass_constants))
				{
					foreach ($rclass_constants as $constant => $v)
					{
						$this->map[$class][$constant] = strtolower($class) . '/constants.html#' . $constant;
						$this->altmap[$class][$constant] = '#c=' . strtolower($class) . '/' . $constant;
					}
				}

				if (sizeof($rclass_methods))
				{
					foreach ($rclass_methods as $method)
					{
						$this->map[$class][(string) $method->getName() . '()'] = strtolower($class) . '/' . (string) $method->getName() . '.html';
						$this->altmap[$class][(string) $method->getName() . '()'] = '#m=' . strtolower($class) . '/' . (string) $method->getName();
					}
				}
			}
		}
	}
}
