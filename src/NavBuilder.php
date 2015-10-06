<?php

namespace Illuminate\Html;

use Illuminate\Http\Request;

class NavBuilder extends BaseMenuBuilder
{
    /**
     * @var DropdownBuilder
     */
    protected $dropdownBuilder;

    /**
     * @var array
     */
    public $itemClass = 'nav-item';

    /**
     * @var string
     */
    public $linkClass = 'nav-link';

    /**
     * @var array
     */
    public $dropdownLinkAttributes = [
        'class' => 'dropdown-toggle',
        'data-toggle' => 'dropdown',
    ];

    /**
     * NavBuilder constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->addReserved([ 'dropdown' ]);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return array
     */
    protected function convertItem($key, $value)
    {
        $data = parent::convertItem($key, $value);

        if (isset($data['dropdown'])) {
            $dropdown = $this->getDropdownBuilder();

            if ( ! $data['dropdown'] = $dropdown->render($data['dropdown'])) {
                unset($data['dropdown']);
            }
        }

        return $data;
    }

    /**
     * Render the menu.
     *
     * @param mixed $items
     * @param array $attributes
     *
     * @return string
     */
    public function render($items, array $attributes = [ 'class' => 'nav' ])
    {
        $items = $this->items($items);

        if (empty($items)) return '';

        return '<ul'.$this->attributes($attributes).'>'.$items.PHP_EOL.'</ul>';
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function renderItem($data)
    {
        $href = $this->getHref($data);
        $link = $this->renderLink($href, $data);

        $attributes = [ 'class' => $this->itemClass ];

        $this->appendStateClasses($attributes, $href, $data);

        $html = '<li'.$this->attributes($attributes).'>'.$link;

        if (isset($data['dropdown'])) {
            $html .= PHP_EOL.$data['dropdown'];
        }

        return $html.'</li>';
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

        if (isset($data['dropdown'])) {
            $attributes = $this->mergeAttributes(
                $attributes,
                $this->dropdownLinkAttributes
            );
        }

        return $attributes;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function getHref(array $options)
    {
        if (isset($options['dropdown']) &&
            ! isset($options['href']) &&
            ! isset($options['url']) &&
            ! isset($options['route'])
        ) {
            return '#';
        }

        return parent::getHref($options);
    }

    /**
     * @param DropdownBuilder $dropdownBuilder
     */
    public function setDropdownBuilder($dropdownBuilder)
    {
        $this->dropdownBuilder = $dropdownBuilder;
    }

    /**
     * @return DropdownBuilder
     */
    public function getDropdownBuilder()
    {
        return $this->dropdownBuilder ?: app(DropdownBuilder::class);
    }
}