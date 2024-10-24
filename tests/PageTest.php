<?php

namespace Rougin\Staticka;

/**
 * @package Staticka
 *
 * @author Rougin Gutib <rougingutib@gmail.com>
 */
class PageTest extends Testcase
{
    /**
     * @var \Rougin\Staticka\Parser
     */
    protected $parser;

    /**
     * @return void
     */
    public function doSetUp()
    {
        $this->parser = new Parser;
    }

    /**
     * @return void
     */
    public function test_from_string()
    {
        $expected = $this->getHtml('SimplePlate');

        $page = new Page;

        $page->setName('Hello world!');

        $page->setBody("# {NAME}\nThis is a sample page.");

        $actual = $this->getActual($page);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function test_from_constructor()
    {
        $expected = $this->getHtml('SimplePlate');

        $page = new Page("# {NAME}\nThis is a sample page.", Page::TYPE_BODY);

        $page->setName('Hello world!');

        $actual = $this->getActual($page);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function test_from_markdown_file()
    {
        $expected = $this->getHtml('FromMdFile');

        $file = __DIR__ . '/Fixture/Plates/HelloWorld.md';

        $page = new Page($file);

        $actual = $this->getActual($page);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     */
    public function test_with_front_matter()
    {
        $expected = $this->getHtml('FrontMatter');

        $file = __DIR__ . '/Fixture/Plates/FrontMatter.md';

        $page = new Page($file);

        $actual = $this->getActual($page);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param \Rougin\Staticka\Page $page
     *
     * @return string
     */
    protected function getActual(Page $page)
    {
        $actual = $this->parser->parsePage($page);

        return $actual->getHtml();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getHtml($name)
    {
        $file = __DIR__ . '/Fixture/Output/' . $name . '.html';

        /** @var string */
        $result = file_get_contents($file);

        return str_replace("\r\n", "\n", $result);
    }
}
