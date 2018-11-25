<?php

namespace Grav\Plugin\Newsletter\Test\Integration\FileProcessor;

use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\FileProcessor\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    /** @var Csv */
    private $csvProcessor;

    /** @var string */
    private $filePath;

    public function setUp()
    {
        $this->csvProcessor = new Csv(new Container());
        $this->filePath = dirname(dirname(dirname(dirname(dirname(__DIR__)))))
            . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'file.csv';
    }

    public function testUpsertCreatesFileIfItDoesntExist()
    {
        $this->csvProcessor->upsert($this->filePath, ['foo' => 'bar', 'boo' => 'far'],'foo');

        $result = $this->getTmpFileContentAndUnlinkFile();
        $expected = <<<EOD
foo,boo
bar,far

EOD;
        $this->assertSame($expected, $result);
    }

    public function testUpsertAddsLineIfFileExists()
    {
        $content = <<<EOD
foo,boo
bar,far

EOD;
        $this->createTmpFile($content);
        $this->csvProcessor->upsert($this->filePath, ['foo' => 'bar2', 'boo' => 'far2'],'foo');

        $result = $this->getTmpFileContentAndUnlinkFile();
        $expected = <<<EOD
foo,boo
bar,far
bar2,far2

EOD;
        $this->assertSame($expected, $result);
    }

    public function testUpsertEditsLineIfIdentifierExistsInFirstLine()
    {
        $content = <<<EOD
foo,boo
bar,far
bar2,far2

EOD;
        $this->createTmpFile($content);
        $this->csvProcessor->upsert($this->filePath, ['foo' => 'bar_new', 'boo' => 'far'],'boo');

        $result = $this->getTmpFileContentAndUnlinkFile();
        $expected = <<<EOD
foo,boo
bar_new,far
bar2,far2

EOD;
        $this->assertSame($expected, $result);
    }

    public function testUpsertEditsLineIfIdentifierExistsInLastLine()
    {
        $content = <<<EOD
foo,boo
bar,far
bar2,far2

EOD;
        $this->createTmpFile($content);
        $this->csvProcessor->upsert($this->filePath, ['foo' => 'bar2_new', 'boo' => 'far2'],'boo');

        $result = $this->getTmpFileContentAndUnlinkFile();
        $expected = <<<EOD
foo,boo
bar,far
bar2_new,far2

EOD;
        $this->assertSame($expected, $result);
    }

    public function testUpsertEditsLineIfIdentifierExistsInMiddleLine()
    {
        $content = <<<EOD
foo,boo
bar,far
bar2,far2
bar3,far3

EOD;
        $this->createTmpFile($content);
        $this->csvProcessor->upsert($this->filePath, ['foo' => 'bar2_new', 'boo' => 'far2'],'boo');

        $result = $this->getTmpFileContentAndUnlinkFile();
        $expected = <<<EOD
foo,boo
bar,far
bar2_new,far2
bar3,far3

EOD;
        $this->assertSame($expected, $result);
    }

    /**
     * @return string
     */
    private function getTmpFileContentAndUnlinkFile(): string
    {
        $file = fopen($this->filePath, 'r');
        $result = fread($file, filesize($this->filePath));
        fclose($file);
        unlink($this->filePath);
        return $result;
    }

    /**
     * @param string $content
     */
    private function createTmpFile(string $content): void
    {
        $file = fopen($this->filePath, 'w');
        fwrite($file, $content);
        fclose($file);
    }
}