<?php

declare(strict_types=1);

namespace TwigCsFixer\Tests\Runner\Fixtures\Linter;

use TwigCsFixer\Sniff\AbstractSpacingSniff;
use TwigCsFixer\Token\Token;

/**
 * This Sniff is buggy because it can't decide how to solve `,]`.
 */
final class BuggySniff extends AbstractSpacingSniff
{
    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceBefore(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [']'])) {
            return 0;
        }

        return null;
    }

    /**
     * @param array<int, Token> $tokens
     */
    protected function getSpaceAfter(int $tokenPosition, array $tokens): ?int
    {
        $token = $tokens[$tokenPosition];
        if ($this->isTokenMatching($token, Token::PUNCTUATION_TYPE, [','])) {
            return 1;
        }

        return null;
    }
}