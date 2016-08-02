<?php namespace Luminaire\Premailer\Stub;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     02/08/2016
 * @time     15:39
 */

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Luminaire\Premailer\BasePremailer;

/**
 * The "Base Premailer" specification class
 *
 * @package  \Luminaire\Premailer\Stub
 */
class PremailerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(BasePremailer::class);
        $this->shouldHaveType('Luminaire\Premailer\Stub\Premailer');
    }

    function it_can_set_and_get_charset()
    {
        $this->getCharset()->shouldBe('UTF-8');
        $this->setCharset('UTF-16');
        $this->getCharset()->shouldBe('UTF-16');
    }

    function it_has_a_default_option()
    {
        $this->getOption()->shouldBeArray();
        $this->getOption('styleTag')->shouldBeInteger();
        $this->getOption('htmlComments')->shouldBeInteger();
        $this->getOption('htmlClasses')->shouldBeInteger();
        $this->getOption('textLineWidth')->shouldBeInteger();
        $this->getOption('cssWriterClass')->shouldBeString();
    }

    function it_throws_an_exception_for_invalid_option_name()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption(2, 1);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('invalid_option', 1);

        $this->shouldThrow('\InvalidArgumentException')->duringGetOption(2);
        $this->shouldThrow('\InvalidArgumentException')->duringGetOption('invalid_option');
    }

    function it_validates_style_tag_option()
    {
        $this->setOption('styleTag', BasePremailer::OPTION_STYLE_TAG_BODY);
        $this->setOption('styleTag', BasePremailer::OPTION_STYLE_TAG_HEAD);
        $this->setOption('styleTag', BasePremailer::OPTION_STYLE_TAG_REMOVE);

        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('styleTag', null);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('styleTag', 'invalid value');
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('styleTag', 7);
    }

    function it_validates_html_comments_option()
    {
        $this->setOption('htmlComments', BasePremailer::OPTION_HTML_COMMENTS_KEEP);
        $this->setOption('htmlComments', BasePremailer::OPTION_HTML_COMMENTS_REMOVE);

        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('htmlComments', null);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('htmlComments', 'invalid value');
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('htmlComments', 7);
    }

    function it_validates_html_classes_option()
    {
        $this->setOption('htmlClasses', BasePremailer::OPTION_HTML_CLASSES_KEEP);
        $this->setOption('htmlClasses', BasePremailer::OPTION_HTML_CLASSES_REMOVE);

        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('htmlClasses', null);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('htmlClasses', 'invalid value');
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('htmlClasses', 7);
    }

    function it_validates_text_line_width()
    {
        $this->setOption('textLineWidth', 100);

        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('textLineWidth', null);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('textLineWidth', 'invalid value');
        $this->shouldThrow('\LengthException')->duringSetOption('textLineWidth', -5);
    }

    function it_validates_css_writer_class()
    {
        $this->setOption('cssWriterClass', BasePremailer::OPTION_CSS_WRITER_CLASS_COMPACT);
        $this->setOption('cssWriterClass', BasePremailer::OPTION_CSS_WRITER_CLASS_PRETTY);

        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('cssWriterClass', null);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('cssWriterClass', -5);
        $this->shouldThrow('\InvalidArgumentException')->duringSetOption('cssWriterClass', 'invalid value');
    }

}
