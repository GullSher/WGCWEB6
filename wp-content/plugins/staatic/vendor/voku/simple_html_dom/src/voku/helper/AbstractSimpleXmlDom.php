<?php

declare (strict_types=1);
namespace Staatic\Vendor\voku\helper;

use BadMethodCallException;
abstract class AbstractSimpleXmlDom
{
    protected static $functionAliases = ['children' => 'childNodes', 'first_child' => 'firstChild', 'last_child' => 'lastChild', 'next_sibling' => 'nextSibling', 'prev_sibling' => 'previousSibling', 'parent' => 'parentNode'];
    protected $node;
    public function __call($name, $arguments)
    {
        $name = \strtolower($name);
        if (isset(self::$functionAliases[$name])) {
            return \call_user_func_array([$this, self::$functionAliases[$name]], $arguments);
        }
        throw new BadMethodCallException('Method does not exist');
    }
    public function __get($name)
    {
        $nameOrig = $name;
        $name = \strtolower($name);
        switch ($name) {
            case 'xml':
                return $this->xml();
            case 'plaintext':
                return $this->text();
            case 'tag':
                return $this->node->nodeName ?? '';
            case 'attr':
                return $this->getAllAttributes();
            default:
                if ($this->node && \property_exists($this->node, $nameOrig)) {
                    return $this->node->{$nameOrig};
                }
                return $this->getAttribute($name);
        }
    }
    public function __invoke($selector, $idx = null)
    {
        return $this->find($selector, $idx);
    }
    public function __isset($name)
    {
        $nameOrig = $name;
        $name = \strtolower($name);
        switch ($name) {
            case 'outertext':
            case 'outerhtml':
            case 'innertext':
            case 'innerhtml':
            case 'innerhtmlkeep':
            case 'plaintext':
            case 'text':
            case 'tag':
                return \true;
            default:
                if ($this->node && \property_exists($this->node, $nameOrig)) {
                    return isset($this->node->{$nameOrig});
                }
                return $this->hasAttribute($name);
        }
    }
    public function __set($name, $value)
    {
        $nameOrig = $name;
        $name = \strtolower($name);
        switch ($name) {
            case 'outerhtml':
            case 'outertext':
                return $this->replaceNodeWithString($value);
            case 'innertext':
            case 'innerhtml':
                return $this->replaceChildWithString($value);
            case 'innerhtmlkeep':
                return $this->replaceChildWithString($value, \false);
            case 'plaintext':
                return $this->replaceTextWithString($value);
            default:
                if ($this->node && \property_exists($this->node, $nameOrig)) {
                    return $this->node->{$nameOrig} = $value;
                }
                return $this->setAttribute($name, $value);
        }
    }
    public function __toString()
    {
        return $this->xml();
    }
    public function __unset($name)
    {
        $this->removeAttribute($name);
    }
    /**
     * @param string $selector
     */
    public abstract function find($selector, $idx = null);
    public abstract function getAllAttributes();
    /**
     * @param string $name
     */
    public abstract function getAttribute($name) : string;
    /**
     * @param string $name
     */
    public abstract function hasAttribute($name) : bool;
    /**
     * @param bool $multiDecodeNewHtmlEntity
     */
    public abstract function innerXml($multiDecodeNewHtmlEntity = \false) : string;
    /**
     * @param string $name
     */
    public abstract function removeAttribute($name) : SimpleXmlDomInterface;
    /**
     * @param string $string
     * @param bool $putBrokenReplacedBack
     */
    protected abstract function replaceChildWithString($string, $putBrokenReplacedBack = \true) : SimpleXmlDomInterface;
    /**
     * @param string $string
     */
    protected abstract function replaceNodeWithString($string) : SimpleXmlDomInterface;
    protected abstract function replaceTextWithString($string) : SimpleXmlDomInterface;
    /**
     * @param string $name
     * @param bool $strictEmptyValueCheck
     */
    public abstract function setAttribute($name, $value = null, $strictEmptyValueCheck = \false) : SimpleXmlDomInterface;
    public abstract function text() : string;
    /**
     * @param bool $multiDecodeNewHtmlEntity
     */
    public abstract function xml($multiDecodeNewHtmlEntity = \false) : string;
}
