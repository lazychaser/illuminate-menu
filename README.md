Laravel Menu Builder
--------------------

**A menu builder for Laravel using Bootstrap's markup.**

[![Build Status](https://travis-ci.org/lazychaser/illuminate-menu.png?branch=master)](https://travis-ci.org/lazychaser/illuminate-menu)
[![Latest Stable Version](https://poser.pugx.org/kalnoy/illuminate-menu/version.png)](https://packagist.org/packages/kalnoy/illuminate-menu)
[![Total Downloads](https://poser.pugx.org/kalnoy/illuminate-menu/d/total.png)](https://packagist.org/packages/kalnoy/illuminate-menu)

Installation
============

Install using Composer:

```
composer require kalnoy/illuminate-menu:~1.0
```

Add a service provider:

```php
'providers' => [
    'Illuminate\Html\MenuServiceProvider',
],
```

And a facade:

```php
'aliases' => [
    'Menu' => 'Illuminate\Html\MenuFacade',
],
```

Documentation
=============

Base methods:

* `Menu::render($items, $attributes);` -- render a menu
* `Menu::item($options);` -- render a menu item

Rendering basic items:

```php
Menu::render([
    'Link to url' => 'bar',
    'Link to external url' => 'http://bar',
    [ 'label' => 'Link to url', 'url' => 'bar' ],
    [ 'label' => 'Link to route', 'route' => [ 'route.name', 'foo' => 'bar' ] ],
]);
```

### Item options

*   `label` -- a label of the item, automatically translated, so you can specify lang string id
*   `url` -- the url which can be a full URI or local path
*   `route` -- specify a route, possibly with parameters
*   `secure` -- specify `true` to make `url` be secure (doesn't affect `route` option)
*   `items` -- an array of items for drop down menu

*   `visible` -- boolean value or closure to specify whether the item is visible
*   `active` -- boolean value or closure to specify whether to add `active` class to item; if not specified, determined
    automatically based on current url
*   `disabled` -- boolean value or closure to specify whether the menu item is disabled
    
*   `icon` -- a glyphicon id, i.e. `pencil`
*   `badge` -- a value of badge (scalar or closure)
*   any other parameter that will be put as attribute of `<li>` element.