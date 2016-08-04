<?php namespace Luminaire\Premailer\Laravel;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     04/08/2016
 * @time     11:34
 */

use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\View\Engines\CompilerEngine;

/**
 * The "Premailer" view compiler class
 *
 * @package  \Luminaire\Premailer\Laravel
 */
class PremailerCompiler extends Compiler implements CompilerInterface
{

    /**
     * Compile the view at the given path.
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path)
    {

    }

}
