<?php

namespace LifeSpikes\ScrambleMatchStmt\Traits;

use PhpParser\Node;
use RuntimeException;
use Dedoc\Scramble\Infer\Services\FileParser;
use Dedoc\Scramble\Infer\Services\FileParserResult;

/**
 * This is where we perform the initializing parsing of our class file, and where
 * we find the method that we're going to later on traverse through to find our match
 * statement.
 */
trait ParsesResourceFile
{
    use ReflectsResourceFile;

    public function getFileParser(): FileParserResult
    {
        if (!file_exists($path = $this->reflection()->getFileName())) {
            throw new RuntimeException("Unable to find class at: $path");
        }

        return app(FileParser::class)->parseContent(file_get_contents($path));
    }

    public function getMethod(string $name): ?Node
    {
        $chunks = explode('\\', $this->reflection()->name);
        return $this->getFileParser()->findMethod(array_pop($chunks).'@'.$name);
    }
}
