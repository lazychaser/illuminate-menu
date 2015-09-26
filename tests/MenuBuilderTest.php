<?php

use Illuminate\Html\HtmlBuilder;
use Illuminate\Html\MenuBuilder as Builder;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\RouteCollection;
use Illuminate\Http\Request;
use Mockery as m;

class MenuBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Builder
     */
    protected $builder;

    public function setUp()
    {
        $this->request = Request::create('/foo', 'GET', [ 'bar' => 'baz' ]);

        $this->builder = new Builder($this->request);
    }

    public function testWithUrl()
    {
        $url = $this->mockUrlGenerator();

        $url->shouldReceive('to')->with('bar', [], true)->andReturn('https://bar');
        $url->shouldReceive('to')->with('bar', [], false)->times(11)->andReturn('bar');
        $url->shouldReceive('route')->with('bar')->andReturn('bar');
        $url->shouldReceive('current')->andReturn('current');

        $v1 = $this->builder->item('foo', [ 'url' => 'bar', 'secure' => true ]);
        $v2 = $this->builder->item('foo', 'bar');
        $v3 = $this->builder->item([ 'label' => 'foo', 'url' => 'bar', 'icon' => 'baz' ]);
        $v4 = $this->builder->item([ 'label' => 'foo', 'url' => 'bar', 'badge' => 1 ]);
        $v5 = $this->builder->item([ 'label' => 'foo', 'url' => 'bar', 'badge' => function () { return 1; } ]);
        $v6 = $this->builder->item([ 'label' => 'foo', 'route' => 'bar' ]);
        $v7 = $this->builder->item([ 'visible' => false ]);
        $v8 = $this->builder->item([ 'visible' => true, 'url' => 'bar' ]);
        $v9 = $this->builder->item([ 'visible' => function () { return false; } ]);
        $v10 = $this->builder->item([ 'active' => true, 'class' => 'test', 'url' => 'bar' ]);
        $v11 = $this->builder->item([ 'active' => function () { return true; }, 'url' => 'bar' ]);
        $v12 = $this->builder->item([ 'url' => 'bar', 'rel' => 'baz' ]);
        $v13 = $this->builder->item([ 'label' => 'foo' ]);
        $v14 = $this->builder->item([ 'disabled' => true, 'url' => 'bar' ]);
        $v15 = $this->builder->item([ 'disabled' => function () { return true; }, 'url' => 'bar' ]);
        $v16 = $this->builder->item('-');
        $v17 = $this->builder->item([ 'url' => 'bar', 'linkOptions' => [ 'foo' => 'baz' ] ]);

        $this->assertEquals('<li><a href="https://bar">foo</a></li>', $v1);
        $this->assertEquals('<li><a href="bar">foo</a></li>', $v2);
        $this->assertEquals('<li><a href="bar"><span class="glyphicon glyphicon-baz"></span> foo</a></li>', $v3);
        $this->assertEquals('<li><a href="bar">foo <span class="badge">1</span></a></li>', $v4);
        $this->assertEquals('<li><a href="bar">foo <span class="badge">1</span></a></li>', $v5);
        $this->assertEquals('<li><a href="bar">foo</a></li>', $v6);
        $this->assertEquals('', $v7);
        $this->assertEquals('<li><a href="bar"></a></li>', $v8);
        $this->assertEquals('', $v9);
        $this->assertEquals('<li class="test active"><a href="bar"></a></li>', $v10);
        $this->assertEquals('<li class="active"><a href="bar"></a></li>', $v11);
        $this->assertEquals('<li rel="baz"><a href="bar"></a></li>', $v12);
        $this->assertEquals('<li><a href="current">foo</a></li>', $v13);
        $this->assertEquals('<li class="disabled"><a href="bar"></a></li>', $v14);
        $this->assertEquals('<li class="disabled"><a href="bar"></a></li>', $v15);
        $this->assertEquals('<li class="divider"></li>', $v16);
        $this->assertEquals('<li><a href="bar" foo="baz"></a></li>', $v17);
    }

    public function testRender()
    {
        $url = $this->mockUrlGenerator();

        $url->shouldReceive('to')->with('bar', [], false)->times(4)->andReturn('bar');

        $def = '<ul class="nav">'.PHP_EOL.'<li><a href="bar">foo</a></li>'.PHP_EOL.'</ul>';

        $v1 = $this->builder->render([ 'foo' => 'bar' ]);
        $v3 = $this->builder->render([ [ 'url' => 'bar', 'label' => 'foo' ] ]);
        $v4 = $this->builder->render([ '-', 'foo' => 'bar', '-', [ 'visible' => false ], '-', '-', 'baz' => 'bar' ],
            []);
        $v5 = $this->builder->render([]);

        $this->assertEquals($def, $v1);
        $this->assertEquals($def, $v3);

        $this->assertEquals('<ul>'.PHP_EOL.
            '<li><a href="bar">foo</a></li>'.PHP_EOL.
            '<li class="divider"></li>'.PHP_EOL.
            '<li><a href="bar">baz</a></li>'.PHP_EOL.
            '</ul>', $v4);

        $this->assertEquals('', $v5);
    }

    public function testDropdown()
    {
        $url = $this->mockUrlGenerator();

        $url->shouldReceive('to')->with('baz', [], false)->andReturn('baz');

        $value = $this->builder->item([ 'label' => 'foo', 'items' => [ 'bar' => 'baz' ] ]);

        $this->assertEquals('<li>'.
            '<a href="#" class="dropdown-toggle" data-toggle="dropdown">foo <span class="caret"></span></a>'.PHP_EOL.
            '<ul class="dropdown-menu">'.PHP_EOL.
                '<li><a href="baz">bar</a></li>'.PHP_EOL.
            '</ul></li>', $value);
    }

    public function testUrlWithParameters()
    {
        $url = $this->mockUrlGenerator();

        $url->shouldReceive('to')->with('foo', [ 'bar' => 'baz' ], false)
            ->andReturn('http://localhost/foo?bar=baz');

        $url->shouldReceive('to')->with('foo', [ 'baz' => 'bar' ], false)->andReturn('http://localhost/foo?baz=bar');

        $url->shouldReceive('route')->with('foo', [ 'bar' => 'baz' ])->andReturn('http://localhost/foo?bar=baz');

        $v1 = $this->builder->item([ 'url' => [ 'foo', 'bar' => 'baz' ]]);
        $v2 = $this->builder->item([ 'url' => [ 'foo', 'baz' => 'bar' ]]);
        $v3 = $this->builder->item([ 'route' => [ 'foo', 'bar' => 'baz' ]]);

        $this->assertEquals('<li class="active"><a href="http://localhost/foo?bar=baz"></a></li>', $v1);
        $this->assertEquals('<li><a href="http://localhost/foo?baz=bar"></a></li>', $v2);
        $this->assertEquals('<li class="active"><a href="http://localhost/foo?bar=baz"></a></li>', $v3);
    }

    public function testTrans()
    {
        $this->mockUrlGenerator()->shouldReceive('current')->andReturn('bar');

        $lang = m::mock();
        $lang->shouldReceive('trans')->with('bar')->andReturn('translated');

        $this->builder->setTranslator($lang);

        $value = $this->builder->item([ 'label' => 'bar' ]);

        $this->assertEquals('<li><a href="bar">translated</a></li>', $value);
    }

    /**
     * @return m\MockInterface
     */
    protected function mockUrlGenerator()
    {
        $this->builder->setUrlGenerator($url = m::mock());

        return $url;
    }

}