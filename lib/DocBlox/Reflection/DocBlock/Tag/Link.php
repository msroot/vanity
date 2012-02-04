<?php
/**
 * DocBlox
 *
 * PHP Version 5
 *
 * @category   DocBlox
 * @package    Reflection
 * @author     Ben Selby <benmatselby@gmail.com>
 * @copyright  2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://docblox-project.org
 */

/**
 * Reflection class for a @link tag in a Docblock.
 *
 * @category   DocBlox
 * @package    Reflection
 * @author     Ben Selby <benmatselby@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://docblox-project.org
 */
class DocBlox_Reflection_DocBlock_Tag_Link extends DocBlox_Reflection_DocBlock_Tag implements DocBlox_Reflection_DocBlock_Tag_Interface
{
    /** @var string */
    protected $link = '';

    /**
     * Parses a tag and populates the member variables.
     *
     * @throws DocBlox_Reflection_Exception if an invalid tag line was presented
     *
     * @param string $type    Tag type
     * @param string $content Content of the tag
     */
    public function __construct($type, $content)
    {
        $this->tag = $type;
        $pieces = explode(' ', $content);

        if (count($pieces) > 1) {
            $this->link = array_shift($pieces);
            $this->description = implode(' ', $pieces);
        } else {
            $this->link = $content;
            $this->description = $content;
        }

        $this->content = $content;
    }

    /**
    * Returns the link
    *
    * @return string
    */
    public function getLink()
    {
        return $this->link;
    }

    /**
    * Sets the link
    *
    * @param string $link The link
    *
    * @return void
    */
    public function setLink($link)
    {
        $this->link = $link;
    }

   /**
    * Implements DocBlox_Reflection_DocBlock_Tag_Interface
    *
    * @param SimpleXMLElement $xml Relative root of xml document
    */
    public function __toXml(SimpleXMLElement $xml)
    {
        parent::__toXml($xml);

        $xml['link'] = $this->getLink();
    }
}
