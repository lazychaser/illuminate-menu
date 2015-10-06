<?php

namespace Illuminate\Html;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Symfony\Component\Translation\TranslatorInterface;

class MenuBuilder {

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var TranslatorInterface
     */
    protected $lang;

    /**
     * The list of reserved attributes of the item.
     *
     * @var array
     */
    protected $reserved = [ 'route', 'url', 'secure', 'label', 'items', 'linkOptions',
                            'icon', 'badge',
                            'visible', 'active', 'disabled' ];

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
     * A class for child menus.
     *
     * @var string
     */
    public $dropdownClass = 'dropdown-menu';

    /**
     * @var array
     */
    public $dropdownLinkAttributes = [ 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown' ];

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
     * Render the menu.
     *
     * @param mixed $items
     * @param array $options
     *
     * @return string
     */
    public function render($items, array $options = [ 'class' => 'nav' ])
    {
        $items = $this->items($items);

        if (empty($items)) return '';

        $options = $this->attributes($options);

        return '<ul'.$options.'>'.$items.PHP_EOL.'</ul>';
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function attributes(array $options)
    {
        $html = '';

        foreach ($options as $key => $value)
        {
            $html .= ' '.$key.'="'.e($value).'"';
        }

        return $html;
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
        return array_reduce($this->normalizeItems($items), function ($carry, $item)
        {
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
    protected function normalizeItems($items)
    {
        $data = [];

        foreach ($this->getArrayable($items) as $key => $value)
        {
            if ($item = $this->normalizeItem($key, $value)) $data[] = $item;
        }

        return $this->cleanItems($data);
    }

    /**
     * Normalize an item to be consumable by `item` method.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return array|string
     */
    protected function normalizeItem($key, $value)
    {
        if ($value === '-') return '-';

        if ($value instanceof MenuItem) $value = $value->getMenuItemOptions();

        if (is_array($value))
        {
            if (is_string($key))
            {
                $value['label'] = $key;
            }

            if (isset($value['items']))
            {
                $value['items'] = $this->normalizeItems($value['items']);

                if (empty($value['items'])) unset($value['items']);
            }

            return $value;
        }
        elseif (is_string($value))
        {
            return [ 'label' => $key, 'url' => $value ];
        }

        throw new \InvalidArgumentException("Unknown menu item type.");
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
        $items = array_values(array_filter($items, [ $this, 'isVisible' ]));

        $data = [];
        $i = 0;
        $total = count($items);

        while ($i < $total)
        {
            if ($items[$i] === '-')
            {
                if ( ! empty($data)) $data[] = '-';

                // Skip repeated dividers
                while (++$i < $total and $items[$i] === '-');
            }
            else
            {
                $data[] = $items[$i++];
            }
        }

        // Remove last divider
        if (end($data) === '-') array_pop($data);

        return $data;
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
        if (is_null($url))
        {
            $url = $label;
            $label = null;
        }

        $data = $this->normalizeItem($label, $url);

        if ( ! $this->isVisible($data)) return '';

        return $this->renderItem($data);
    }

    /**
     * Render an item.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function renderItem($options)
    {
        if ($options === '-') return $this->divider();

        $href = $this->getHref($options);
        $link = $this->renderLink($href, $options);

        $attributes = array_except($options, $this->reserved);

        if ($this->isActive($href, $options))
        {
            $this->appendClass($attributes, $this->activeClass);
        }

        if ($this->isDisabled($options))
        {
            $this->appendClass($attributes, $this->disabledClass);
        }

        $attributes = $this->attributes($attributes);

        $html = '<li'.$attributes.'>'.$link;

        if (isset($options['items']))
        {
            $html .= PHP_EOL.$this->render($options['items'], [ 'class' => $this->dropdownClass ]);
        }

        return $html . '</li>';
    }

    /**
     * Get menu item link.
     *
     * @param array $options
     *
     * @return string
     */
    protected function renderLink($href, array $options)
    {
        $attributes = compact('href');

        $label = e($this->getLabel($options));

        if (isset($options['badge']))
        {
            $label .= ' '.$this->badge(value($options['badge']));
        }

        if (isset($options['items']))
        {
            $attributes = $this->mergeAttributes($attributes, $this->dropdownLinkAttributes);

            $label .= ' '.$this->caret();
        }

        if (isset($options['icon']))
        {
            $label = $this->icon($options['icon']).' '.$label;
        }

        if (isset($options['linkOptions']))
        {
            $attributes = $this->mergeAttributes($attributes, $options['linkOptions']);
        }

        return '<a'.$this->attributes($attributes).'>'.$label.'</a>';
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
        if ($href === $this->request->root())
        {
            return $this->request->path() === '/';
        }

        // Check query string parameters
        if (false !== $pos = strpos($href, '?'))
        {
            $params = [];

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

        foreach ($params as $key => $value)
        {
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

        if (isset($options['visible']))
        {
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

        if (isset($options['disabled']))
        {
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
        if (isset($options['url']))
        {
            $secure = array_get($options, 'secure', false);

            return $this->hrefFromUrl($options['url'], $secure);
        }

        if (isset($options['route']))
        {
            return $this->hrefFromRoute($options['route']);
        }

        return isset($options['items']) ? '#' : $this->url->current();
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

        if ( ! is_array($url)) return $this->url->to($url, [], $secure);

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
        if (isset($options['label']))
        {
            $label = $options['label'];

            return is_null($this->lang) ? $label : $this->lang->trans($label);
        }

        return '';
    }

    /**
     * Get a caret element.
     *
     * @return string
     */
    public function caret()
    {
        return '<span class="caret"></span>';
    }

    /**
     * Generate icon.
     *
     * @param string $icon
     *
     * @return string
     */
    protected function icon($icon)
    {
        return '<span class="glyphicon glyphicon-'.$icon.'"></span>';
    }

    /**
     * @param $badge
     *
     * @return string
     */
    protected function badge($badge)
    {
        return '<span class="badge">'.$badge.'</span>';
    }

    /**
     * @param \Illuminate\Routing\UrlGenerator $url
     */
    public function setUrlGenerator($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function divider()
    {
        return '<li class="divider"></li>';
    }

    /**
     * @param TranslatorInterface $lang
     */
    public function setTranslator($lang)
    {
        $this->lang = $lang;
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
        if (isset($extra['class']))
        {
            $this->appendClass($attributes, $extra['class']);

            unset($extra['class']);
        }

        return array_merge($attributes, $extra);
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