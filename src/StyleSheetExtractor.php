<?php namespace Luminaire\Premailer;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     03/08/2016
 * @time     13:58
 */

use DOMDocument;
use DOMElement;
use RuntimeException;

/**
 * The Style Sheet Extractor class
 *
 * @package  \Luminaire\Premailer
 */
class StyleSheetExtractor
{

    /**
     * The DOM Document instance
     *
     * @var \DOMDocument
     */
    protected $document;

    /**
     * Create a new instance of Style Sheet Extractor
     *
     * @param  \DOMDocument|null  $document
     */
    public function __construct(DOMDocument $document = null)
    {
        if ($document)
        {
            $this->setDocument($document);
        }
    }

    /**
     * Get the DOM Document instance
     *
     * @return \DOMDocument|null
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the DOM Document instance
     *
     * @param  \DOMDocument  $document
     * @return $this
     */
    public function setDocument(DOMDocument $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get all CSS in the mail template
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function extract()
    {
        if ( ! $this->document)
        {
            throw new RuntimeException('There are no [DOMDocument] instance to work with. Use the [setDocument] method to pass an instance of the [DOMDocument].');
        }

        $stylesheet = "";

        foreach ($this->getStyleTags($this->document) as $node)
        {
            if ($content = $this->getStyleTagContent($node))
            {
                $stylesheet .= $content . "\r\n";
            }

            $node->parentNode->removeChild($node);
        }

        return $stylesheet;
    }

    /**
     * Get all DOM element that has "style" attribute
     *
     * @param  \DOMDocument  $doc
     * @return array
     */
    protected function getStyleTags(DOMDocument $doc)
    {
        $nodes = [];

        foreach ($doc->getElementsByTagName('style') as $element)
        {
            $nodes[] = $element;
        }

        return $nodes;
    }

    /**
     * Get the HTML <style> tag CSS content
     *
     * @param  \DOMElement  $node
     * @return string|null
     */
    protected function getStyleTagContent(DOMElement $node)
    {
        if ( ! $this->isStyleTypeAllowed($node) || ! $this->isStyleMediaAllowed($node))
        {
            return null;
        }

        return (string) $node->nodeValue;
    }

    /**
     * Check if the HTML <style> tag has no [media] attribute or if it has a
     * [media] attribute, then it must either have a value of "all" or "screen".
     *
     * @param  \DOMElement  $style_node
     * @return bool
     */
    private function isStyleMediaAllowed(DOMElement $style_node)
    {
        $media = $style_node->attributes->getNamedItem('media');

        if (is_null($media)) return true;

        $media       = str_replace(' ', '', (string) $media->nodeValue);
        $media_types = explode(',', $media_types);

        return in_array('all', $media_types) || in_array('screen', $media_types);
    }

    /**
     * Check if the HTML <style> tag has the default [type] attribute or the value
     * of the [type] attribute is set to "text/css".
     *
     * @param  \DOMElement  $style_node
     * @return bool
     */
    private function isStyleTypeAllowed(DOMElement $style_node)
    {
        $type = $style_node->attributes->getNamedItem('type');

        return is_null($type) || (string) $type->nodeValue == 'text/css';
    }

}
