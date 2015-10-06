<?php

namespace Illuminate\Html;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Http\Request;

abstract class BaseMenuBuilder extends BaseWidget
{
    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var Request
     */
    protected $request;

    /**
     * The list of reserved attributes of the item.
     *
     * @var array
     */
    protected $reserved = [
        'route', 'url', 'secure', 'label',
        'icon', 'badge',
        'visible', 'active', 'disabled'
    ];

    /**
     * A class that is appended to the menu item when it is active.
     *
     * @var string
     */
    public $activeClass = 'active';

    /**
     * A class that is appended to the menu item when it is disabled.
     *
     * @var string
     */
    public $disabledClass = 'disabled';

    /**
     * @var array
     */
    public $linkClass;

    /**
     * Initialize the builder.
     *
     * @param Request $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Render a list of menu items.
     *
     * @param mixed $items
     *
     * @return string
     */
    public function items($items)
    {
        $items = $this->convertItemList($items);

        return array_reduce($items, function ($carry, $item) {
            return $carry.PHP_EOL.$this->renderItem($item);
        }, '');
    }

    /**
     * Normalize items.
     *
     * @param mixed $items
     *
     * @return array
     */
    protected function convertItemList($items)
    {
        $data = [ ];

        foreach ($this->getArrayable($items) as $key => $value) {
            if ($item = $this->convertItem($key, $value)) $data[] = $item;
        }

        return $this->cleanItems($data);
    }

    /**
     * Normalize an item to be consumable by `item` method.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function convertItem($key, $value)
    {
        if ($value instanceof MenuItem) $value = $value->getMenuItemOptions();

        if (is_array($value)) {
            if (is_string($key)) $value['label'] = $key;

            return $value;
        } elseif (is_string($value)) {
            return [ 'label' => $key, 'url' => $value ];
        }

        throw new \InvalidArgumentException('Unknown menu item type.');
    }

    /**
     * @param array $items
     *
     * @return array
     */
    protected function cleanItems(array $items)
    {
        return array_values(array_filter($items, [ $this, 'isVisible' ]));
    }

    /**
     * Render an item.
     *
     * @param string|array|MenuItem $label
     * @param string|array|null $url
     *
     * @return string
     */
    public function item($label, $url = null)
    {
        if (is_null($url)) {
            $url = $label;
            $label = null;
        }

        $data = $this->convertItem($label, $url);

        if ( ! $this->isVisible($data)) return '';

        return $this->renderItem($data);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    abstract protected function renderItem($data);

    /**
     * Get menu item link.
     *
     * @param $href
     * @param array $data
     *
     * @return string
     */
    protected function renderLink($href, array $data)
    {
        $label = e($this->getLabel($data));

        if (isset($data['badge'])) {
            $label .= Helpers::badge(value($data['badge']));
        }

        if (isset($data['icon'])) {
            $label = Helpers::icon($data['icon']).$label;
        }

        $attributes = $this->linkAttributes($href, $data);

        return '<a'.$this->attributes($attributes).'>'.$label.'</a>';
    }

    /**
     * @param string $href
     * @param array $data
     *
     * @return array
     */
    protected function linkAttributes($href, array $data)
    {
        $class = $this->linkClass;

        return $this->mergeAttributes(compact('href', 'class'),
                                      array_except($data, $this->reserved));
    }

    /**
     * @param array $attributes
     * @param string $href
     * @param array $data
     */
    protected function appendStateClasses(&$attributes, $href, $data)
    {
        if ($this->isActive($href, $data)) {
            $this->appendClass($attributes, $this->activeClass);
        }

        if ($this->isDisabled($data)) {
            $this->appendClass($attributes, $this->disabledClass);
        }
    }

    /**
     * Get whether a href is active.
     *
     * @param string $href
     *
     * @return bool
     */
    public function isActive($href, array $options)
    {
        if (isset($options['active'])) return value($options['active']);

        if ( ! $href || $href === '#') return false;

        // Check if url leads to the main page
        if ($href === $this->request->root()) {
            return $this->request->path() === '/';
        }

        // Check query string parameters
        if (false !== $pos = strpos($href, '?')) {
            $params = [ ];

            parse_str(substr($href, $pos + 1), $params);

            if ( ! $this->requestHasParameters($params)) return false;

            $href = substr($href, 0, $pos);
        }

        return rtrim($href, '/') === $this->request->url();
    }

    /**
     * Check if the request has all needed parameters.
     *
     * @param array $params
     *
     * @return bool
     */
    protected function requestHasParameters($params)
    {
        if (empty($params)) return true;

        $parameters = $this->request->query;

        foreach ($params as $key => $value) {
            if ( ! $parameters->has($key) || $parameters->get($key) != $value) return false;
        }

        return true;
    }

    /**
     * Get whether the menu item is visible.
     *
     * @param array|string $options
     *
     * @return bool
     */
    protected function isVisible($options)
    {
        if ( ! is_array($options)) return true;

        if (isset($options['visible'])) {
            return value($options['visible']);
        }

        return true;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    protected function isDisabled($options)
    {
        if ( ! is_array($options)) return false;

        if (isset($options['disabled'])) {
            return value($options['disabled']);
        }

        return false;
    }

    /**
     * Get link href from options.
     *
     * @param array $options
     *
     * @return string
     */
    protected function getHref(array $options)
    {
        if (isset($options['href'])) return $options['href'];

        if (isset($options['url'])) {
            $secure = array_get($options, 'secure', false);

            return $this->hrefFromUrl($options['url'], $secure);
        }

        if (isset($options['route'])) {
            return $this->hrefFromRoute($options['route']);
        }

        return $this->url ? $this->url->current() : '#';
    }

    /**
     * Get href from url.
     *
     * @param string|array $url
     * @param bool $secure
     *
     * @return string
     */
    protected function hrefFromUrl($url, $secure)
    {
        if ( ! $this->url) return $url;

        if ( ! is_array($url)) return $this->url->to($url, [ ], $secure);

        return $this->url->to($url[0], array_splice($url, 1), $secure);
    }

    /**
     * Get a href from route.
     *
     * @param string|array $route
     *
     * @return string
     */
    protected function hrefFromRoute($route)
    {
        if ( ! is_array($route)) return $this->url->route($route);

        return $this->url->route($route[0], array_splice($route, 1));
    }

    /**
     * Get a label from options.
     *
     * @param array $options
     *
     * @return string
     */
    protected function getLabel(array $options)
    {
        if (isset($options['label'])) {
            $label = $options['label'];

            return is_null($this->lang) ? $label : $this->lang->trans($label);
        }

        return '';
    }

    /**
     * @param \Illuminate\Routing\UrlGenerator $url
     */
    public function setUrlGenerator($url)
    {
        $this->url = $url;
    }

    /**
     * @param array $reserved
     */
    protected function addReserved(array $reserved)
    {
        $this->reserved = array_merge($this->reserved, $reserved);
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    protected function getArrayable($items)
    {
        if (is_array($items)) return $items;

        if ($items instanceof \IteratorAggregate) return $items;

        throw new \InvalidArgumentException("Unknown menu items type.");
    }

}