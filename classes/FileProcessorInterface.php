<?php

namespace Grav\Plugin\Newsletter;

interface FileProcessorInterface
{
    /**
     * Adds new line with $values to the file specified by $path or updates line if $identifier value is already present
     * in file.
     */
    public function upsert(string $filePath, array $values, string $identifier): void;
}