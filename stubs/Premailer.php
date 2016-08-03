<?php namespace Luminaire\Premailer\Stub;

use Luminaire\Premailer\BasePremailer;

class Premailer extends BasePremailer
{

    /**
     * Gets the HTML content from the preferred source.
     *
     * @return string
     */
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
