<?php

namespace Illuminate\Html;

use Symfony\Component\Translation\TranslatorInterface;

abstract class BaseWidget
{
    /**
     * @var TranslatorInterface
     */
    protected $lang;

    /**
     * @param TranslatorInterface $lang
     */
    public function setTranslator($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function attributes(array $options)
    {
        $html = '';

        foreach ($options as $key => $value) {
            $html .= ' '.$key.'="'.e($value).'"';
        }

        return $html;
    }

    /**
     * @param array $attributes
     */
    protected function appendClass(array &$attributes, $className)
    {
        $attributes['class'] = isset($attributes['class'])
            ? $attributes['class'] .= ' '.$className
            : $className;
    }

    /**
     * @param array $attributes
     * @param array $extra
     *
     * @return array
     */
    protected function mergeAttributes(array $attributes, array $extra)
    {
        if (isset($extra['class'])) {
            $this->appendClass($attributes, $extra['class']);

            unset($extra['class']);
        }

        return array_merge($attributes, $extra);
    }

}