<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;

/**
 * Ensure that files has no trailing space.
 */
final class TrailingSpaceSniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];
        if (!$this->isTokenMatching($token, Token::EOL_TOKENS)) {
            return;
        }

        if (
            !isset($tokens[$tokenPosition - 1])
            || !$this->isTokenMatching($tokens[$tokenPosition - 1], Token::INDENT_TOKENS)
        ) {
            return;
        }

        $fixer = $this->addFixableError(
            'A line should not end with blank space(s).',
            $token
        );

        if (null === $fixer) {
            return;
        }

        $fixer->replaceToken($tokenPosition - 1, '');
    }
}
