<?php

namespace Grav\Plugin\Newsletter;

interface FileProcessorInterface
{
    /**
     * Adds new line with $values to the file specified by $filePath or updates line if $identifier value is already
     * present in file.
     */
    public function upsert(string $filePath, array $values, string $identifier): void;

    /**
     * Removes line identified by $hash from file specified by $filePath.
     * Returns true if line was cut and false otherwise.
     */
    public function cut(string $filePath, string $hash): bool;
}