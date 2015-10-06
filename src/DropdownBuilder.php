<?php

namespace Illuminate\Html;

class DropdownBuilder extends BaseMenuBuilder
{

    /**
     * @var string
     */
    public $linkClass = 'dropdown-item';

    /**
     * @param $items
     * @param array $attributes
     *
     * @return string
     */
    public function render($items, array $attributes = [ 'class' => 'dropdown-menu' ])
    {
        $items = $this->items($items);

        if (empty($items)) return '';

        return '<div'.$this->attributes($attributes).'>'.$items.PHP_EOL.'</div>';
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function convertItem($key, $value)
    {
        if (is_string($value) && ! is_string($key)) return $value;

        return parent::convertItem($key, $value);
    }

    /**
     * Remove non-permitted items and repeated dividers.
     *
     * @param array $items
     *
     * @return array
     */
    protected function cleanItems(array $items)
    {
        $items = parent::cleanItems($items);

        $data = [ ];

        for ($i = 0, $end = count($items) - 1; $i <= $end; $i++) {
            if (is_string($items[$i])) {
                while ($i < $end && is_string($items[$i + 1])) $i++;
            }

            // Don't add divider as a first item
            if (($data || $items[$i] !== '-') &&
                // Don't add divider or header as last item
                ($i < $end || ! is_string($items[$i]))
            ) {
                $data[] = $items[$i];
            }
        }

        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function renderItem($data)
    {
        if ($data === '-') return $this->divider();

        if (is_string($data)) return $this->header($data);

        $href = $this->getHref($data);

        return $this->renderLink($href, $data);
    }

    /**
     * @param string $href
     * @param array $data
     *
     * @return array
     */
    protected function linkAttributes($href, array $data)
    {
        $attributes = parent::linkAttributes($href, $data);

        $this->appendStateClasses($attributes, $href, $data);

        return $attributes;
    }

    /**
     * @return string
     */
    public function divider()
    {
        return '<div class="dropdown-divider"></div>';
    }

    /**
     * @param string $title
     *
     * @return string
     */
    public function header($title)
    {
        return '<h6 class="dropdown-header">'.e($title).'</h6>';
    }
}