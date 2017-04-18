<?php

/**
 * Class for a sniff to find keywords in comments.
 *
 * For example you could use this sniff to find "hack", "fixme", "todo" comments in the code
 */
class Degordian_Sniffs_Commenting_FindKeywordSniff implements PHP_CodeSniffer_Sniff
{

    public $keywords = [
        'hack',
        'todo',
        'fixme'
    ];

    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * An example return value for a sniff that wants to listen for whitespace
     * and any comments would be:
     *
     * <code>
     *    return array(
     *            T_WHITESPACE,
     *            T_DOC_COMMENT,
     *            T_COMMENT,
     *           );
     * </code>
     *
     * @return int[]
     * @see    Tokens.php
     */
    public function register()
    {
        return PHP_CodeSniffer_Tokens::$commentTokens;
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * The stackPtr variable indicates where in the stack the token was found.
     * A sniff can acquire information this token, along with all the other
     * tokens within the stack by first acquiring the token stack:
     *
     * <code>
     *    $tokens = $phpcsFile->getTokens();
     *    echo 'Encountered a '.$tokens[$stackPtr]['type'].' token';
     *    echo 'token information: ';
     *    print_r($tokens[$stackPtr]);
     * </code>
     *
     * If the sniff discovers an anomaly in the code, they can raise an error
     * by calling addError() on the PHP_CodeSniffer_File object, specifying an error
     * message and the position of the offending token:
     *
     * <code>
     *    $phpcsFile->addError('Encountered an error', $stackPtr);
     * </code>
     *
     * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where the
     *                                        token was found.
     * @param int                  $stackPtr The position in the PHP_CodeSniffer
     *                                        file's token stack where the token
     *                                        was found.
     *
     * @return void|int Optionally returns a stack pointer. The sniff will not be
     *                  called again on the current file until the returned stack
     *                  pointer is reached. Return (count($tokens) + 1) to skip
     *                  the rest of the file.
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $content = $tokens[$stackPtr]['content'];
        $matches = [];
        $search = implode('|', $this->keywords);
        $pattern = sprintf('/(?:\A|[^\p{L}]+)(%s)([^\p{L}]+(.*)|\Z)/ui', $search);
        preg_match($pattern, $content, $matches);
        if (empty($matches) === false) {
            $keyword = $matches[1];
            $comment = $matches[1] . $matches[2];
            $type = 'Found';
            $message = trim($comment);
            $message = trim($message, '-:[](). ');
            $warning = 'Comment contains a discouraged keyword';
            $data = [$message];
            if ($message !== '') {
                $type = 'Found';
                $warning .= ' "%s"';
            }

            $phpcsFile->addWarning($warning, $stackPtr, $type, $data);
        }
    }
}