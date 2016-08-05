<?php namespace Luminaire\Premailer\Parser;

/**
 * Created by Sublime Text 3
 *
 * @user     Kevin Tanjung
 * @website  http://kevintanjung.github.io
 * @email    kevin@custombagus.com
 * @date     04/08/2016
 * @time     09:09
 */

use Crossjoin\Css\Reader\CssString as StylesheetReader;
use Crossjoin\Css\Format\Rule\AtMedia\MediaQuery;
use Crossjoin\Css\Format\Rule\AtMedia\MediaRule;
use Crossjoin\Css\Format\Rule\Style\StyleRuleSet;
use Crossjoin\Css\Format\Rule\Style\StyleSelector;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * Retrieve relevant CSS selector from a given CSS rules
 *
 * @package  \Luminaire\Poseidon\Parser
 */
class RelevantSelectorParser
{

    /**
     * The pseudo classes that can be set in a style attribute and that are
     * supported by the Symfony CssSelector (doesn't support CSS4 yet).
     *
     * @var array
     */
    protected $allowed_pseudo_classes = [
        StyleSelector::PSEUDO_CLASS_FIRST_CHILD,
        StyleSelector::PSEUDO_CLASS_ROOT,
        StyleSelector::PSEUDO_CLASS_NTH_CHILD,
        StyleSelector::PSEUDO_CLASS_NTH_LAST_CHILD,
        StyleSelector::PSEUDO_CLASS_NTH_OF_TYPE,
        StyleSelector::PSEUDO_CLASS_NTH_LAST_OF_TYPE,
        StyleSelector::PSEUDO_CLASS_LAST_CHILD,
        StyleSelector::PSEUDO_CLASS_FIRST_OF_TYPE,
        StyleSelector::PSEUDO_CLASS_LAST_OF_TYPE,
        StyleSelector::PSEUDO_CLASS_ONLY_CHILD,
        StyleSelector::PSEUDO_CLASS_ONLY_OF_TYPE,
        StyleSelector::PSEUDO_CLASS_EMPTY,
        StyleSelector::PSEUDO_CLASS_NOT,
    ];

    /**
     * The stylesheet reader instance
     *
     * @var \Crossjoin\Css\Reader\CssString
     */
    protected $reader;

    /**
     * The charset of the stylesheet
     *
     * @var string
     */
    protected $charset;

    /**
     * Create a new instance of "Relevant Selector Parser"
     *
     * @param  \Crossjoin\Css\Reader\CssString|string|null  $stylesheet
     * @param  string                                       $charset
     */
    public function __construct($stylesheet = null, $charset = 'UTF-8')
    {
        $this->charset = $charset;

        if ( ! is_null($stylesheet))
        {
            $this->setStylesheetReader($stylesheet);
        }
    }

    /**
     * Get the relevant selectors
     *
     * @return array
     */
    public function extract()
    {
        $selectors = [];
        $rules     = $this->reader->getStyleSheet()->getRules();
        $relevant  = $this->getRelevantStyleRules($rules);

        foreach ($relevant as $rule)
        {
            $this->populateSelectors($selectors, $rule);
        }

        return $selectors;
    }

    /**
     * Store the selectors from the rule to the tank
     *
     * @param  array   &$tank
     * @param  array   $rule
     * @return void
     */
    protected function populateSelectors(array &$tank, $rule)
    {
        foreach ($rule->getSelectors() as $selector)
        {
            if ( ! $this->isPseudoClassAllowed($selector))
            {
                continue;
            }

            $this->prepareSelectorArray(
                $tank,
                $selector->getSpecificity(),
                $selector->getValue()
            );

            foreach ($rule->getDeclarations() as $declaration)
            {
                $this->storeDeclaration(
                    $tank,
                    $declaration,
                    $selector->getSpecificity(),
                    $selector->getValue()
                );
            }
        }
    }

    /**
     * Store the declaration in the selector tank
     *
     * @param  array                                              &$tank
     * @param  \Crossjoin\Css\Format\Rule\Style\StyleDeclaration  $declaration
     * @param  string                                             $specifity
     * @param  string                                             $name
     * @return void
     */
    protected function storeDeclaration(array &$tank, $declaration, $specifity, $name)
    {
        $tank[$specifity][$name][] = $declaration;
    }

    /**
     * Before we build the dictionary of style declaration, we will need to make
     * sure, there is an array to be inserted.
     *
     * @param  array   &$selectors
     * @param  string  $specifity
     * @param  string  $name
     * @return void
     */
    protected function prepareSelectorArray(array &$selectors, $specifity, $name)
    {
        if ( ! isset($selectors[$specifity]))
        {
            $selectors[$specifity] = [];
        }

        if ( ! isset($selectors[$specifity][$name]))
        {
            $selectors[$specifity][$name] = [];
        }
    }

    /**
     * Set the stylesheet reader instance
     *
     * @param  \Crossjoin\Css\Reader\CssString|string  $stylesheet
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setStylesheetReader($stylesheet)
    {
        if (is_string($stylesheet))
        {
            $stylesheet = new StylesheetReader($stylesheet);
        }

        if ( ! $stylesheet instanceof StylesheetReader)
        {
            throw new InvalidArgumentException('The argument 0 of the [setStylesheetReader] method expects to be a string of CSS or a [Crossjoin\Css\Reader\CssString]');
        }

        $this->reader = $stylesheet;
        $this->reader->setEnvironmentEncoding($this->getCharset());

        return $this;
    }

    /**
     * Get the stylesheet reader instance
     *
     * @return \Crossjoin\Css\Reader\CssString|null
     */
    public function getStylesheetReader()
    {
        return $this->reader;
    }

    /**
     * Set the charset of the stylesheet
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
     * Get the charset of the stylesheet
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Check if a Selector has a valid Pseudo Class
     *
     * @param  \Crossjoin\Css\Format\Rule\Style\StyleSelector  $selector
     * @return bool
     */
    public function isPseudoClassAllowed(StyleSelector $selector)
    {
        foreach ($selector->getPseudoClasses() as $pseudo_class)
        {
            if ( ! in_array($pseudo_class, $this->allowed_pseudo_classes))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets all generally relevant style rules
     *
     * @param  \Crossjoin\Css\Format\Rule\RuleAbstract[]  $rules
     * @return \Crossjoin\Css\Format\Rule\Style\StyleRuleSet[]
     */
    protected function getRelevantStyleRules(array $rules)
    {
        $relevants = [];

        foreach ($rules as $rule)
        {
            if ($rule instanceof StyleRuleSet)
            {
                $relevants[] = $rule;
            }

            if ($rule instanceof MediaRule)
            {
                $this->getRelevantMediaRule($rule, $relevants);
            }
        }

        return $relevants;
    }

    /**
     * Gets the relevant style rules from a media rule
     *
     * @param  \Crossjoin\Css\Format\Rule\AtMedia\MediaRule  $rule
     * @param  array                                         &$collection
     * @return void
     */
    protected function getRelevantMediaRule(MediaRule $rule, &$collection)
    {
        foreach ($rule->getQueries() as $media_query)
        {
            if ( ! $this->isAllowedMediaRule($media_query))
            {
                continue;
            }

            foreach ($this->getRelevantStyleRules($rule->getRules()) as $style_rule)
            {
                $collection[] = $style_rule;
            }

            break;
        }
    }

    /**
     * Check if the media rule should be included
     *
     * @param  \Crossjoin\Css\Format\Rule\AtMedia\MediaQuery  $media_query
     * @return bool
     */
    protected function isAllowedMediaRule(MediaQuery $media_query)
    {
        $type      = $media_query->getType();
        $condition = count($media_query->getConditions());

        return ($type === MediaQuery::TYPE_ALL || $type === MediaQuery::TYPE_SCREEN) && $condition === 0;
    }

}
