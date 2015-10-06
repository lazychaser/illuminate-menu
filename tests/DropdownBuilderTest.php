<?php

use Illuminate\Html\DropdownBuilder as Builder;
use Illuminate\Html\MenuItem;
use Illuminate\Http\Request;
use Mockery as m;

class DropdownBuilderTest extends PHPUnit_Framework_TestCase {

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

        $url->shouldReceive('to')->with('bar', [], false)->andReturn('bar');

        $v2 = $this->builder->item('foo', 'bar');
        $v16 = $this->builder->item('-');
        $v17 = $this->builder->item('Header');

        $this->assertEquals('<a href="bar" class="dropdown-item">foo</a>', $v2);
        $this->assertEquals('<div class="dropdown-divider"></div>', $v16);
        $this->assertEquals('<h6 class="dropdown-header">Header</h6>', $v17);
    }

    public function testRender()
    {
        $url = $this->mockUrlGenerator();

        $url->shouldReceive('to')->with('bar', [], false)->times(4)->andReturn('bar');

        $def = '<div class="dropdown-menu">'.PHP_EOL.'<a href="bar" class="dropdown-item">foo</a>'.PHP_EOL.'</div>';

        $v1 = $this->builder->render([ 'foo' => 'bar' ]);
        $v3 = $this->builder->render([ [ 'url' => 'bar', 'label' => 'foo' ] ]);
        $v4 = $this->builder->render(
            [
                'Header1',
                '-',
                'Header 2',
                'foo' => 'bar',
                '-',
                [ 'visible' => false ],
                '-', '-',
                'baz' => 'bar',
                '-',
            ],

            []
        );
        $v5 = $this->builder->render([]);

        $this->assertEquals($def, $v1);
        $this->assertEquals($def, $v3);

        $this->assertEquals(
            '<div>'.PHP_EOL.
            '<h6 class="dropdown-header">Header 2</h6>'.PHP_EOL.
            '<a href="bar" class="dropdown-item">foo</a>'.PHP_EOL.
            '<div class="dropdown-divider"></div>'.PHP_EOL.
            '<a href="bar" class="dropdown-item">baz</a>'.PHP_EOL.
            '</div>', $v4);

        $this->assertEquals('', $v5);
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