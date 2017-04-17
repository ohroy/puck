<?php
/**
 * Created by rozbo at 2017/4/17 下午6:37
 */

namespace tests\puck\helpers;

use PHPUnit\Framework\TestCase;

class DomTest extends TestCase {
    /**
     * @var \puck\helpers\Dom;
     */
    private $dom;

    public function setUp() {
        $this->dom = app('dom');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadWithInvalidArgument() {
        $this->dom->load([]);
    }


    public function loadHtmlCharsetTests() {
        return array(
            array('<html><div class="foo">English language</html>', 'English language'),
            array('<html><div class="foo">Русский язык</html>', 'Русский язык'),
            array('<html><div class="foo">اللغة العربية</html>', 'اللغة العربية'),
            array('<html><div class="foo">漢語</html>', '漢語'),
            array('<html><div class="foo">Tiếng Việt</html>', 'Tiếng Việt'),
        );
    }

    /**
     * @dataProvider loadHtmlCharsetTests
     */
    public function testLoadHtmlCharset($html, $text) {
        $document = $this->dom->load($html, false);
        $this->assertEquals($text, $document->first('div')->text());
    }


    public function testCreateElementBySelector() {
        $document = $this->dom;
        $element = $document->createElementBySelector('a.external-link[href=http://example.com]');
        $this->assertEquals('a', $element->tag);
        $this->assertEquals('', $element->text());
        $this->assertEquals(['href' => 'http://example.com', 'class' => 'external-link'], $element->attributes());
        $element = $document->createElementBySelector('#block', 'Foo');
        $this->assertEquals('div', $element->tag);
        $this->assertEquals('Foo', $element->text());
        $this->assertEquals(['id' => 'block'], $element->attributes());
        $element = $document->createElementBySelector('input', null, ['name' => 'name', 'placeholder' => 'Enter your name']);
        $this->assertEquals('input', $element->tag);
        $this->assertEquals('', $element->text());
        $this->assertEquals(['name' => 'name', 'placeholder' => 'Enter your name'], $element->attributes());
    }

    public function testAppendChild() {
        $html = '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Document</title>
        </head>
        <body>
        </body>
        </html>';
        $document = $this->dom;
        $this->assertCount(0, $document->find('span'));
        $node = $document->createElement('span');
        $appendedChild = $document->appendChild($node);
        $this->assertCount(1, $document->find('span'));
        $this->assertTrue($appendedChild->is($document->first('span')));
        $appendedChild->remove();
        $this->assertCount(0, $document->find('span'));
        $nodes = [];
        $nodes[] = $document->createElement('span');
        $nodes[] = $document->createElement('span');
        $appendedChildren = $document->appendChild($nodes);
        $nodes = $document->find('span');
        $this->assertCount(2, $appendedChildren);
        $this->assertCount(2, $nodes);
        foreach ($appendedChildren as $index => $child) {
            $this->assertTrue($child->is($nodes[$index]));
        }
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLoadWithNotExistingFile() {
        $this->dom->load('path/to/file', true);
    }


    public function testFirst() {
        $html = '<ul><li>One</li><li>Two</li><li>Three</li></ul>';
        $document = $this->dom;
        $this->dom->loadHtml($html);
        $items = $document->find('ul > li');
        $this->assertEquals($items[0]->getNode(), $document->first('ul > li')->getNode());
        $this->assertEquals('One', $document->first('ul > li::text'));
        $document = $this->dom->release()->init();
        $this->assertNull($document->first('ul > li'));
    }


    public function testCount() {
        $html = '<ul><li>One</li><li>Two</li><li>Three</li></ul>';
        $this->assertEquals(3, $this->dom->loadHtml($html)->count('li'));
        $this->assertEquals(0, $this->dom->release()->init()->count('li'));
    }


    public function testHtmlWithOptions() {
        $html = '<html><body><span></span></body></html>';
        $document = $this->dom->loadHtml($html);
        $this->assertEquals('<html><body><span></span></body></html>', $document->html());
        $this->assertEquals('<html><body><span/></body></html>', $document->html(0));
    }


    public function testText() {
        $html = '<html>foo</html>';
        $document = $this->dom->loadHtml($html);
        $this->assertEquals('foo', $document->text());
    }
}
