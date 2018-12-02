<?php

namespace Grav\Plugin\Newsletter\FileProcessor;

use Grav\Common\Filesystem\Folder;
use Grav\Plugin\Newsletter\FileProcessorInterface;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

class Csv implements FileProcessorInterface
{
    public function upsert(string $filePath, array $values, string $identifier): void
    {
        $this->createFolderIfDoesntExist($filePath);
        if (!$this->fileExists($filePath)) {
            $this->createNewFileWithLineOfValues($filePath, $values);
        } else {
            $reader = $this->getFileReader($filePath);
            $index = $this->getIndexOfLineFilteredByIdentifier($reader, $values, $identifier);
            if (!is_null($index)) {
                $this->updateLineOfValuesInExistentFile($filePath, $reader, $values, $index);
            } else {
                $this->addLineOfValuesToExisitngFile($filePath, $values);
            }
        }
    }

    public function cut(string $filePath, string $hash): bool
    {
        if (empty($hash) || !$this->fileExists($filePath)) {
            return false;
        }
        $reader = $this->getFileReader($filePath);
        $index = $this->getIndexOfLineFilteredByIdentifier($reader, ['hash' => $hash], 'hash');
        if (is_null($index)) {
            return false;
        }
        $this->cutLineFromExistingFile($filePath, $reader, $index);
        return true;
    }

    private function createFolderIfDoesntExist(string $filePath): void
    {
        Folder::mkdir(dirname($filePath));
    }

    private function fileExists(string $filePath): bool
    {
        return file_exists($filePath);
    }

    private function createNewFileWithLineOfValues(string $filePath, array $values): void
    {
        $writer = Writer::createFromPath($filePath, 'w');
        $writer->insertOne(array_keys($values));
        $writer->insertOne($values);
    }

    private function getFileReader(string $filePath): Reader
    {
        $reader = Reader::createFromPath($filePath);
        $reader->setHeaderOffset(0);
        return $reader;
    }

    private function getIndexOfLineFilteredByIdentifier(Reader $reader, array $values, string $identifier):? int
    {
        $statement = (new Statement())->where(function (array $record) use ($values, $identifier) {
            return $record[$identifier] === $values[$identifier];
        });
        $filteredRecords = $statement->process($reader);
        return count($filteredRecords) > 0 ? $filteredRecords->getRecords()->key() : null;
    }

    private function getRecordsBeforeIndex(Reader $reader, int $index): array
    {
        return $index > 1 ? (new Statement())->limit($index - 1)->process($reader)->jsonSerialize() : [];
    }

    private function getRecordsAfterIndex(Reader $reader, int $index): array
    {
        return (new Statement())->offset($index)->process($reader)->jsonSerialize();
    }

    private function updateLineOfValuesInExistentFile(string $filePath, Reader $reader, array $values, int $index): void
    {
        $beforeRecords = $this->getRecordsBeforeIndex($reader, $index);
        $afterRecords = $this->getRecordsAfterIndex($reader, $index);
        $writer = Writer::createFromPath($filePath, 'w');
        $writer->insertOne(array_keys($values));
        $writer->insertAll($beforeRecords);
        $writer->insertOne($values);
        $writer->insertAll($afterRecords);
    }

    private function addLineOfValuesToExisitngFile(string $filePath, array $values): void
    {
        $writer = Writer::createFromPath($filePath, 'a+');
        $writer->insertOne($values);
    }

    private function cutLineFromExistingFile(string $filePath, Reader $reader, int $index): void
    {
        $header = $reader->getHeader();
        $beforeRecords = $this->getRecordsBeforeIndex($reader, $index);
        $afterRecords = $this->getRecordsAfterIndex($reader, $index);
        $writer = Writer::createFromPath($filePath, 'w');
        $writer->insertOne($header);
        $writer->insertAll($beforeRecords);
        $writer->insertAll($afterRecords);
    }
}