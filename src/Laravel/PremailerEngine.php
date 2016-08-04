<?php namespace Luminaire\Premailer\Laravel;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     04/08/2016
 * @time     13:44
 */

use Illuminate\View\Engines\EngineInterface;
use Luminaire\Premailer\Laravel\PremailerCompiler;
use Luminaire\Premailer\HtmlString;

/**
 * The "Premailer" view engine class
 *
 * @package  \Luminaire\Premailer\Laravel
 */
class PremailerEngine implements EngineInterface
{

    /**
     * The underlying view engine instance
     *
     * @var \Illuminate\View\Engines\EngineInterface
     */
    protected $engine;

    /**
     * The "Premailer" compiler instance.
     *
     * @var \Illuminate\View\Compilers\CompilerInterface
     */
    protected $compiler;

    /**
     * A stack of the last compiled templates
     *
     * @var array
     */
    protected $compiled = [];

    /**
     * Create a new instance of "Premailer" view engine
     *
     * @param  \Illuminate\View\Engines\CompilerEngine         $engine
     * @param  \Luminaire\Premailer\Laravel\PremailerCompiler  $compiler [description]
     */
    public function __construct(EngineInterface $engine, PremailerCompiler $compiler)
    {
        $this->engine = $engine;

        $this->compiler = $compiler;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        // To preserve the capability of using any underlying templating system,
        // e.g. the Blade template or any templating engines that has already
        // been integrated to Illuminate View, Premailer will act as wrapper,
        // that will only inlined the CSS from the template system result.
        $result = $this->engine->get($path, $data);

        $premailer = new HtmlString($result);

        return $premailer->getHtml();
    }

    /**
     * Get the base engine instance
     *
     * @return \Illuminate\View\Engines\EngineInterface
     */
    public function getBaseEngine()
    {
        return $this->engine;
    }

    /**
     * Get the compiler implementation.
     *
     * @return \Illuminate\View\Compilers\CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

}
