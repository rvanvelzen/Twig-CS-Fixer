<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Sniff;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SplFileInfo;
use TwigCsFixer\Environment\StubbedEnvironment;
use TwigCsFixer\Report\SniffViolation;
use TwigCsFixer\Ruleset\Ruleset;
use TwigCsFixer\Runner\Fixer;
use TwigCsFixer\Runner\Linter;
use TwigCsFixer\Sniff\SniffInterface;
use TwigCsFixer\Tests\TestHelper;
use TwigCsFixer\Token\Tokenizer;

abstract class AbstractSniffTestCase extends TestCase
{
    /**
     * @param array<array<int, int>> $expects
     */
    protected function checkSniff(
        SniffInterface $sniff,
        array $expects,
        ?string $filePath = null,
        ?string $fixedFilePath = null
    ): void {
        $env = new StubbedEnvironment();
        $tokenizer = new Tokenizer($env);
        $linter = new Linter($env, $tokenizer);
        $ruleset = new Ruleset();

        $filePath ??= $this->generateFilePath();

        $ruleset->addSniff($sniff);
        $report = $linter->run([new SplFileInfo($filePath)], $ruleset);

        $fixedFilePath ??= substr($filePath, 0, -5).'.fixed.twig';
        if (file_exists($fixedFilePath)) {
            $content = file_get_contents($filePath);
            static::assertNotFalse($content);
            $fixer = new Fixer($tokenizer);

            $diff = TestHelper::generateDiff($fixer->fixFile($content, $ruleset), $fixedFilePath);
            if ('' !== $diff) {
                static::fail($diff);
            }
        }

        $messages = $report->getMessages($filePath);

        /** @var array<array<int, int>> $messagePositions */
        $messagePositions = [];
        foreach ($messages as $message) {
            if (SniffViolation::LEVEL_FATAL === $message->getLevel()) {
                $errorMessage = $message->getMessage();
                $line = $message->getLine();

                if (null !== $line) {
                    $errorMessage = sprintf('Line %s: %s', $line, $errorMessage);
                }
                static::fail($errorMessage);
            }

            $messagePositions[] = [$message->getLine() ?? 0 => $message->getLinePosition()];
        }

        static::assertSame($expects, $messagePositions);
    }

    private function generateFilePath(): string
    {
        $class = new ReflectionClass(static::class);
        $className = $class->getShortName();
        $filename = $class->getFileName();
        static::assertNotFalse($filename);

        $directory = \dirname($filename);

        return "{$directory}/{$className}.twig";
    }
}
