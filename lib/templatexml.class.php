<?php
class Vanity_Template_XML extends SimpleXMLElement
{
	public function methods()
	{
		return $this->class->methods;
	}

	public function properties()
	{
		return $this->class->properties;
	}

	public function constants()
	{
		return $this->class->constants;
	}
}
