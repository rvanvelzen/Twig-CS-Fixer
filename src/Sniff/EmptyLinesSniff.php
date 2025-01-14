<?php

declare(strict_types=1);

namespace TwigCsFixer\Sniff;

use TwigCsFixer\Token\Token;
use Webmozart\Assert\Assert;

/**
 * Checks that there are not 2 empty lines following each other.
 */
final class EmptyLinesSniff extends AbstractSniff
{
    protected function process(int $tokenPosition, array $tokens): void
    {
        $token = $tokens[$tokenPosition];

        if (!$this->isTokenMatching($token, Token::EOL_TYPE)) {
            return;
        }

        Assert::keyExists($tokens, $tokenPosition + 1, 'An EOL_TYPE cannot be the last token');
        if ($this->isTokenMatching($tokens[$tokenPosition + 1], Token::EOL_TYPE)) {
            // Rely on the next token check instead to avoid duplicate errors
            return;
        }

        $previous = $this->findPrevious(Token::EOL_TYPE, $tokens, $tokenPosition, true);
        if (false === $previous) {
            // If all previous tokens are EOL_TYPE, we have to count one more
            // since $tokenPosition start at 0
            $i = $tokenPosition + 1;
        } else {
            $i = $tokenPosition - $previous - 1;
        }

        if ($i < 2) {
            return;
        }

        $fixer = $this->addFixableError(
            sprintf('More than 1 empty line is not allowed, found %d', $i),
            $token
        );

        if (null === $fixer) {
            return;
        }

        // Because we added manually extra empty lines to the count
        $i = min($i, $tokenPosition);

        $fixer->beginChangeSet();
        while ($i >= 2 || $i === $tokenPosition) {
            $fixer->replaceToken($tokenPosition - $i, '');
            $i--;
        }
        $fixer->endChangeSet();
    }
}
