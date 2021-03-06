<?php namespace Luminaire\Premailer;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     02/08/2016
 * @time     11:05
 */

use Crossjoin\Css\Format\Rule\AtMedia\MediaQuery;
use Crossjoin\Css\Format\Rule\AtMedia\MediaRule;
use Crossjoin\Css\Format\Rule\RuleAbstract;
use Crossjoin\Css\Format\Rule\Style\StyleDeclaration;
use Crossjoin\Css\Format\Rule\Style\StyleRuleSet;
use Crossjoin\Css\Format\Rule\Style\StyleSelector;
use Crossjoin\Css\Reader\CssString;
use Crossjoin\Css\Writer\WriterAbstract;
use Symfony\Component\CssSelector\CssSelectorConverter as CssSelector;
use DOMDocument, DOMElement, DOMXPath;
use LengthException, RuntimeException, InvalidArgumentException;
use Illuminate\Support\Arr;

/**
 * The base Premailer class
 *
 * @package  \Luminaire\Premailer
 */
abstract class BasePremailer
{

    const OPTION_STYLE_TAG                  = 'styleTag';
    const OPTION_STYLE_TAG_BODY             = 1;
    const OPTION_STYLE_TAG_HEAD             = 2;
    const OPTION_STYLE_TAG_REMOVE           = 3;

    const OPTION_HTML_COMMENTS              = 'htmlComments';
    const OPTION_HTML_COMMENTS_KEEP         = 1;
    const OPTION_HTML_COMMENTS_REMOVE       = 2;

    const OPTION_HTML_CLASSES               = 'htmlClasses';
    const OPTION_HTML_CLASSES_KEEP          = 1;
    const OPTION_HTML_CLASSES_REMOVE        = 2;

    const OPTION_TEXT_LINE_WIDTH            = 'textLineWidth';

    const OPTION_CSS_WRITER_CLASS           = 'cssWriterClass';
    const OPTION_CSS_WRITER_CLASS_COMPACT   = '\Crossjoin\Css\Writer\Compact';
    const OPTION_CSS_WRITER_CLASS_PRETTY    = '\Crossjoin\Css\Writer\Pretty';

    /**
     * The options for HTML/text generation
     *
     * @var array
     */
    protected $options = [
        self::OPTION_STYLE_TAG        => self::OPTION_STYLE_TAG_BODY,
        self::OPTION_HTML_CLASSES     => self::OPTION_HTML_CLASSES_KEEP,
        self::OPTION_HTML_COMMENTS    => self::OPTION_HTML_COMMENTS_REMOVE,
        self::OPTION_CSS_WRITER_CLASS => self::OPTION_CSS_WRITER_CLASS_COMPACT,
        self::OPTION_TEXT_LINE_WIDTH  => 75,
    ];

    /**
     * The charset for HTML/text output
     *
     * @var string
     */
    protected $charset = "UTF-8";

    /**
     * The prepared HTML content
     *
     * @var string
     */
    protected $html;

    /**
     * The prepared text content
     *
     * @var string
     */
    protected $text;

    /**
     * The loaded DOM Document
     *
     * @var \DOMDocument
     */
    protected $doc;

    /**
     * Sets the charset used in the HTML document and used for the output.
     *
     * @param  string  $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Gets the charset used in the HTML document and used for the output.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Sets an option for the generation of the mail.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setOption($name, $value)
    {
        if ( ! is_string($name))
        {
            throw new InvalidArgumentException('The argument 0 of [setOption] method is expected to be a [string], but [' . gettype($name) . '] is given.');
        }

        if ( ! isset($this->options[$name]))
        {
            throw new InvalidArgumentException("An option with the name [{$name}] doesn't exist.");
        }

        $this->validateScalarOptionValue($name, $value);
        $this->validatePossibleOptionValue($name, $value);

        $this->options[$name] = $value;
    }

    /**
     * Gets an option for the generation of the mail.
     *
     * @param  string|null  $name
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function getOption($name = null)
    {
        if (is_null($name))
        {
            return $this->options;
        }

        if ( ! is_string($name))
        {
            throw new InvalidArgumentException('The argument 0 of [setOption] method is expected to be a [string], but [' . gettype($name) . '] is given.');
        }

        if ( ! isset($this->options[$name]))
        {
            throw new InvalidArgumentException("An option with the name [{$name}] doesn't exist.");
        }

        return $this->options[$name];
    }

    /**
     * Gets the prepared HTML version of the mail.
     *
     * @return string
     */
    public function getHtml()
    {
        if ($this->html === null)
        {
            $this->prepareContent();
        }

        return $this->html;
    }

    /**
     * Gets the prepared text version of the mail.
     *
     * @return string
     */
    public function getText()
    {
        if ($this->text === null)
        {
            $this->prepareContent();
        }

        return $this->text;
    }

    /**
     * Gets the HTML content from the preferred source.
     *
     * @return string
     */
    abstract protected function getHtmlContent();

    /**
     * Load the DOM Document
     *
     * @return \DOMDocument
     */
    protected function loadDocument()
    {
        if ($this->doc)
        {
            return $this->doc;
        }

        $this->doc = new DOMDocument();
        $this->doc->loadHTML($this->getHtmlContent());

        return $this->doc;
    }

    /**
     * Prepares the mail HTML/text content.
     *
     * @return void
     */
    protected function prepareContent()
    {
        if ( ! class_exists('\DOMDocument'))
        {
            throw new RuntimeException("Required extension 'dom' seems to be missing.");
        }

        $this->loadDocument();

        $xpath      = new DOMXPath($this->doc);
        $stylesheet = (new Parser\StylesheetParser($this->doc))->extract();

        $parser     = new Parser\RelevantSelectorParser($stylesheet);
        $selectors  = $parser->extract();

        foreach ($xpath->query("descendant-or-self::*[@style]") as $element)
        {
            if ($element->attributes !== null)
            {
                $styleAttribute = $element->attributes->getNamedItem("style");

                $styleValue = "";

                if ($styleAttribute !== null)
                {
                    $styleValue = (string) $styleAttribute->nodeValue;
                }

                if ($styleValue !== "")
                {
                    $element->setAttribute('data-premailer-original-style', $styleValue);
                    $element->removeAttribute('style');
                }
            }
        }

        // Get all specificity values (to process the declarations in the correct order,
        // without sorting the array by key, which perhaps could result in a changed
        // order of selectors within the specificity).
        $specificities = array_keys($selectors);
        sort($specificities);

        // Process all style declarations in the correct order
        foreach ($specificities as $specificity)
        {
            /** @var StyleDeclaration[] $declarations */
            foreach ($selectors[$specificity] as $selector => $declarations)
            {
                $xpathQuery = (new CssSelector())->toXPath($selector);
                $elements   = $xpath->query($xpathQuery);

                foreach ($elements as $element)
                {
                    if ($element->attributes !== null)
                    {
                        $styleAttribute = $element->attributes->getNamedItem("style");

                        $styleValue = "";

                        if ($styleAttribute !== null)
                        {
                            $styleValue = (string) $styleAttribute->nodeValue;
                        }

                        $concat = ($styleValue === "") ? "" : ";";

                        foreach ($declarations as $declaration)
                        {
                            $styleValue .= $concat . $declaration->getProperty() . ":" . $declaration->getValue();
                            $concat = ";";
                        }

                        $element->setAttribute('style', $styleValue);
                    }
                }
            }
        }

        // Add temporarily removed style attributes again, after all styles have been applied to the elements
        $elements = $xpath->query("descendant-or-self::*[@data-pre-mailer-original-style]");
        /** @var \DOMElement $element */
        foreach ($elements as $element) {
            if ($element->attributes !== null) {
                $styleAttribute = $element->attributes->getNamedItem("style");
                $styleValue = "";
                if ($styleAttribute !== null) {
                    $styleValue = (string)$styleAttribute->nodeValue;
                }

                $originalStyleAttribute = $element->attributes->getNamedItem("data-pre-mailer-original-style");
                $originalStyleValue = "";
                if ($originalStyleAttribute !== null) {
                    $originalStyleValue = (string)$originalStyleAttribute->nodeValue;
                }

                if ($styleValue !== "" || $originalStyleValue !== "") {
                    $styleValue = ($styleValue !== "" ? $styleValue . ";" : "") . $originalStyleValue;
                    $element->setAttribute('style', $styleValue);
                    $element->removeAttribute('data-pre-mailer-original-style');
                }
            }
        }

        // Optionally remove class attributes in HTML tags
        $optionHtmlClasses = $this->getOption(self::OPTION_HTML_CLASSES);
        if ($optionHtmlClasses === self::OPTION_HTML_CLASSES_REMOVE) {
            $nodesWithClass = [];
            foreach ($xpath->query('descendant-or-self::*[@class]') as $nodeWithClass) {
                $nodesWithClass[] = $nodeWithClass;
            }
            /** @var \DOMElement $nodeWithClass */
            foreach ($nodesWithClass as $nodeWithClass) {
                $nodeWithClass->removeAttribute('class');
            }
        }

        // Optionally remove HTML comments
        $optionHtmlComments = $this->getOption(self::OPTION_HTML_COMMENTS);
        if ($optionHtmlComments === self::OPTION_HTML_COMMENTS_REMOVE) {
            $commentNodes = [];
            foreach ($xpath->query('//comment()') as $comment) {
                $commentNodes[] = $comment;
            }
            foreach ($commentNodes as $commentNode) {
                $commentNode->parentNode->removeChild($commentNode);
            }
        }

        // Write XPath document back to DOM document
        $newDoc = $xpath->document;

        // Generate text version (before adding the styles again)
        $this->text = $this->prepareText($newDoc);

        // Optionally add styles tag to the HEAD or the BODY of the document
        $optionStyleTag = $this->getOption(self::OPTION_STYLE_TAG);
        if ($optionStyleTag === self::OPTION_STYLE_TAG_BODY || $optionStyleTag === self::OPTION_STYLE_TAG_HEAD) {
            $cssWriterClass = $this->getOption(self::OPTION_CSS_WRITER_CLASS);
            /** @var WriterAbstract $cssWriter */
            $cssWriter = new $cssWriterClass($parser->getStylesheetReader()->getStyleSheet());
            $styleNode = $newDoc->createElement("style");
            $styleNode->nodeValue = $cssWriter->getContent();

            if ($optionStyleTag === self::OPTION_STYLE_TAG_BODY) {
                /** @var \DOMNode $bodyNode */
                foreach($newDoc->getElementsByTagName('body') as $bodyNode) {
                    $bodyNode->insertBefore($styleNode, $bodyNode->firstChild);
                    break;
                }
            } elseif ($optionStyleTag === self::OPTION_STYLE_TAG_HEAD) {
                /** @var \DOMNode $headNode */
                foreach($newDoc->getElementsByTagName('head') as $headNode) {
                    $headNode->appendChild($styleNode);
                    break;
                }
            }
        }

        // Generate HTML version
        $this->html = $newDoc->saveHTML();
    }

    /**
     * Prepares the mail text content.
     *
     * @param  \DOMDocument  $doc
     * @return string
     */
    protected function prepareText(DOMDocument $doc)
    {
        $text = $this->convertHtmlToText($doc->childNodes);
        $charset = $this->getCharset();
        $textLineMaxLength = $this->getOption(self::OPTION_TEXT_LINE_WIDTH);

        $text = preg_replace_callback('/^([^\n]+)$/m', function($match) use ($charset, $textLineMaxLength) {
                $break = "\n";
                $parts = preg_split('/((?:\(\t[^\t]+\t\))|[^\p{L}\p{N}])/', $match[0], -1, PREG_SPLIT_DELIM_CAPTURE);

                $return = "";
                $brLength = mb_strlen(trim($break, "\r\n"), $charset);

                $lineLength = $brLength;
                foreach ($parts as $part) {
                    // Replace character before/after links with a zero width space,
                    // and mark links as non-breakable
                    $breakLongLines = true;
                    if (strpos($part, "\t")) {
                        $part = str_replace("\t", mb_convert_encoding("\xE2\x80\x8C", $charset, "UTF-8"), $part);
                        $breakLongLines = false;
                    }

                    // Get part length
                    $partLength = mb_strlen($part, $charset);

                    // Ignore trailing space characters if this would cause the line break
                    if (($lineLength + $partLength) === ($textLineMaxLength + 1)) {
                        $lastChar = mb_substr($part, -1, 1, $charset);
                        if ($lastChar === " ") {
                            $part = mb_substr($part, 0, -1, $charset);
                            $partLength--;
                        }
                    }

                    // Check if enough chars left to add the part
                    if (($lineLength + $partLength) <= $textLineMaxLength) {
                        $return .= $part;
                        $lineLength += $partLength;
                    // Check if the part is longer than the line (so that we need to break it)
                    } elseif ($partLength > ($textLineMaxLength - $brLength)) {
                        if ($breakLongLines === true) {
                            $addPart = mb_substr($part, 0, ($textLineMaxLength - $lineLength), $charset);
                            $return .= $addPart;
                            $lineLength = $brLength;

                            for ($i = mb_strlen($addPart, $charset), $j = $partLength; $i < $j; $i+=($textLineMaxLength - $brLength)) {
                                $addPart = $break . mb_substr($part, $i, ($textLineMaxLength - $brLength), $charset);
                                $return .= $addPart;
                                $lineLength = mb_strlen($addPart, $charset) - 1;
                            }
                        } else {
                            $return .= $break . trim($part) . $break;
                            $lineLength = $brLength;
                        }
                    // Add a break to add the part in the next line
                    } else {
                        $return .= $break . rtrim($part);
                        $lineLength = $brLength + $partLength;
                    }
                }
                return $return;
            }, $text);

        $text = preg_replace('/^\s+|\s+$/', '', $text);

        return $text;
    }

    /**
     * Converts HTML tags to text, to create a text version of an HTML document.
     *
     * @param \DOMNodeList $nodes
     * @return string
     */
    protected function convertHtmlToText(\DOMNodeList $nodes)
    {
        $text = "";

        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            $lineBreaksBefore = 0;
            $lineBreaksAfter = 0;
            $lineCharBefore = "";
            $lineCharAfter = "";
            $prefix = "";
            $suffix = "";

            if (in_array($node->nodeName, ["h1", "h2", "h3", "h4", "h5", "h6", "h"])) {
                $lineCharAfter = "=";
                $lineBreaksAfter = 2;
            } elseif (in_array($node->nodeName, ["p", "td"])) {
                $lineBreaksAfter = 2;
            } elseif (in_array($node->nodeName, ["div"])) {
                $lineBreaksAfter = 1;
            }

            if ($node->nodeName === "h1") {
                $lineCharBefore = "*";
                $lineCharAfter = "*";
            } elseif ($node->nodeName === "h2") {
                $lineCharBefore = "=";
            }

            if ($node->nodeName === '#text') {
                $textContent = html_entity_decode($node->textContent, ENT_COMPAT | ENT_HTML401, $this->getCharset());

                // Replace tabs (used to mark links below) and other control characters
                $textContent = preg_replace("/[\r\n\f\v\t]+/", "", $textContent);

                if ($textContent !== "") {
                    $text .= $textContent;
                }
            } elseif ($node->nodeName === 'a') {
                $href = "";
                if ($node->attributes !== null) {
                    $hrefAttribute = $node->attributes->getNamedItem("href");
                    if ($hrefAttribute !== null) {
                        $href = (string)$hrefAttribute->nodeValue;
                    }
                }
                if ($href !== "") {
                    $suffix = " (\t" . $href . "\t)";
                }
            } elseif ($node->nodeName === 'b' || $node->nodeName === 'strong') {
                $prefix = "*";
                $suffix = "*";
            } elseif ($node->nodeName === 'hr') {
                $text .= str_repeat('-', 75) . "\n\n";
            }

            if ($node->hasChildNodes()) {
                $text .= str_repeat("\n", $lineBreaksBefore);

                $addText = $this->convertHtmlToText($node->childNodes);

                $text .= $prefix;

                $text .= $lineCharBefore ? str_repeat($lineCharBefore, 75) . "\n" : "";
                $text .= $addText;
                $text .= $lineCharAfter ? "\n" . str_repeat($lineCharAfter, 75) . "\n" : "";

                $text .= $suffix;

                $text .= str_repeat("\n", $lineBreaksAfter);
            }
        }

        // Remove unnecessary white spaces at he beginning/end of lines
        $text = preg_replace("/(?:^[ \t\f]+([^ \t\f])|([^ \t\f])[ \t\f]+$)/m", "\\1\\2", $text);

        // Replace multiple line-breaks
        $text = preg_replace("/[\r\n]{2,}/", "\n\n", $text);

        return $text;
    }

    /**
     * Validate the scalar value of the passed option
     *
     * @param  string      $name
     * @param  string|int  $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function validateScalarOptionValue($name, $value)
    {
        switch ($name)
        {
            case self::OPTION_STYLE_TAG:
            case self::OPTION_HTML_CLASSES:
            case self::OPTION_HTML_COMMENTS:
            case self::OPTION_TEXT_LINE_WIDTH:
                if ( ! is_int($value))
                {
                    throw new InvalidArgumentException("The argument 1 of [setOption] method is expected to be a [integer] for option [{name}], but [" . gettype($value) . '] is given.');
                }

                break;

            case self::OPTION_CSS_WRITER_CLASS:
                if ( ! is_string($value))
                {
                    throw new InvalidArgumentException("The argument 1 of [setOption] method is expected to be a [string] for option [{name}], but [" . gettype($value) . '] is given.');
                }

                break;
        }
    }

    /**
     * Validate the possible value of the passed option
     *
     * @param  string      $name
     * @param  string|int  $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function validatePossibleOptionValue($name, $value)
    {
        switch ($name)
        {
            case self::OPTION_STYLE_TAG:
                if ( ! in_array($value, [self::OPTION_STYLE_TAG_BODY, self::OPTION_STYLE_TAG_HEAD, self::OPTION_STYLE_TAG_REMOVE]))
                {
                    throw new InvalidArgumentException("Invalid value [$value] for option [$name].");
                }
                break;

            case self::OPTION_HTML_CLASSES:
                if ( ! in_array($value, [self::OPTION_HTML_CLASSES_REMOVE, self::OPTION_HTML_CLASSES_KEEP]))
                {
                    throw new InvalidArgumentException("Invalid value [$value] for option [$name].");
                }
                break;

            case self::OPTION_HTML_COMMENTS:
                if ( ! in_array($value, [self::OPTION_HTML_COMMENTS_REMOVE, self::OPTION_HTML_COMMENTS_KEEP]))
                {
                    throw new InvalidArgumentException("Invalid value [$value] for option [$name].");
                }
                break;

            case self::OPTION_TEXT_LINE_WIDTH:
                if ($value <= 0)
                {
                    throw new LengthException("Value '" . gettype($value) . "' for option '$name' is to small.");
                }
                break;

            case self::OPTION_CSS_WRITER_CLASS:
                if (is_subclass_of($value, '\Crossjoin\Css\Writer\WriterAbstract', true) === false) {
                    throw new \InvalidArgumentException(
                        "Invalid value '$value' for option '$name'. " .
                        "The given class has to be a subclass of \\Crossjoin\\Css\\Writer\\WriterAbstract."
                    );
                }
        }
    }

}
