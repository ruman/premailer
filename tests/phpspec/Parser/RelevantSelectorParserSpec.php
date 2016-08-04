<?php namespace Luminaire\Premailer\Parser;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     04/08/2016
 * @time     09:20
 */

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Crossjoin\Css\Reader\CssString;

/**
 * The "Relevant Selector Parser" specification test
 *
 * @package  \Luminaire\Premailer\Parser
 */
class RelevantSelectorParserSpec extends ObjectBehavior
{

    function it_is_initializable(CssString $reader)
    {
        $this->beConstructedWith($reader);
        $this->shouldHaveType('Luminaire\Premailer\Parser\RelevantSelectorParser');
    }

    function it_get_and_set_the_reader_instance()
    {
        $stylesheet = 'body { background-color: white; color: black; }';

        $this->setStylesheetReader($stylesheet)
             ->getStylesheetReader()
             ->shouldBeAnInstanceOf(CssString::class);
    }

}
