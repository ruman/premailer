<?php namespace Luminaire\Premailer;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     03/08/2016
 * @time     14:02
 */

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DOMDocument;

/**
 * The "Style Sheet Extractor" specification test
 *
 * @package  \Luminaire\Premailer
 */
class StyleSheetExtractorSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('Luminaire\Premailer\StyleSheetExtractor');
    }

    function it_get_and_set_the_document_instance(DOMDocument $doc)
    {
        $this->setDocument($doc)->getDocument()->shouldBeAnInstanceOf('\DOMDocument');
    }

    function it_throws_an_exception_if_there_are_no_document_instance()
    {
        $this->shouldThrow('\RuntimeException')->duringExtract();
    }

    function it_extracts_the_css_from_a_html_document()
    {
        $doc = new DOMDocument();
        $doc->loadHTML($this->getHtmlContent());

        $lines = [
            '           body {',
            '               background-color: black;',
            '               color: white;',
            '           }',
        ];

        $this->beConstructedWith($doc);

        $this->extract()->shouldMatch('/' . join("\r\n", $lines) . '/');
    }

    protected function getHtmlContent()
    {
        $lines = [
            '<html>',
            '   <head>',
            '       <style type="text/css">',
            '           body {',
            '               background-color: black;',
            '               color: white;',
            '           }',
            '       </style>',
            '   </head>',
            '   <body>',
            '   </body>',
            "</html>",
        ];

        return join("\r\n", $lines);
    }

}
