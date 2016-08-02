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

use InvalidArgumentException;

/**
 * The "HTML File based Premailer" class
 *
 * @package  \Luminaire\Premailer
 */
class HtmlFile extends BasePremailer
{

    /**
     * File path of the HTML source file
     *
     * @var string
     */
    protected $filename;

    /**
     * Create a new instance of "HTML File Premailer"
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->setFilename($filename);
    }

    /**
     * Sets the file path of the HTML source file.
     *
     * @param  string  $filename
     * @return $this
     */
    protected function setFilename($filename)
    {
        if ( ! is_string($filename))
        {
            throw new InvalidArgumentException("The argument 0 of the [setFilename] method expects to be a string but [" . gettype($filename). "] given.");
        }

        if (is_readable($filename))
        {
            $this->filename = $filename;
        }
        elseif (file_exists($filename))
        {
            throw new InvalidArgumentException("File [{$filename}] isn't readable.");
        }
        else
        {
            throw new InvalidArgumentException("File [{$filename}] doesn't exist.");
        }

        return $this;
    }

    /**
     * Gets the file path of the HTML source file.
     *
     * @return string
     */
    protected function getFilename()
    {
        return $this->filename;
    }

    /**
     * Gets the HTML content from the preferred source.
     *
     * @return string
     */
    protected function getHtmlContent()
    {
        return file_get_contents($this->getFilename());
    }

}
