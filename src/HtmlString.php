<?php namespace Luminaire\Premailer;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     02/08/2016
 * @time     11:09
 */

use InvalidArgumentException;

/**
 * The "HTML String based Premailer" class
 *
 * @package  \Luminaire\Premailer
 */
class HtmlString extends PreMailerAbstract
{

    /**
     * The HTML source file content
     *
     * @var string
     */
    protected $content;

    /**
     * Create a new instance of "HTML String Premailer"
     *
     * @param  string  $htmlContent
     */
    public function __construct($htmlContent)
    {
        $this->setHtmlContent($htmlContent);
    }

    /**
     * Sets the HTML content
     *
     * @param  string  $htmlContent
     * @return $this
     */
    protected function setHtmlContent($htmlContent)
    {
        if (is_string($htmlContent))
        {
            $this->content = $htmlContent;
        }
        else
        {
            throw new InvalidArgumentException("Invalid type '" . gettype($htmlContent). "' for argument 'htmlContent' given.");
        }

        return $this;
    }

    /**
     * Gets the HTML content from the preferred source.
     *
     * @return string
     */
    protected function getHtmlContent()
    {
        return $this->content;
    }

}
