<?php

namespace App\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * DATE_FORMAT DQL function for MySQL/MariaDB.
 *
 * Usage: DATE_FORMAT(date_field, 'format_string')
 * Example: DATE_FORMAT(o.createdAt, '%Y-%m')
 */
class DateFormatFunction extends FunctionNode
{
    public $dateExpression = null;
    public $formatString = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->dateExpression = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_COMMA);

        $this->formatString = $parser->ArithmeticPrimary();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'DATE_FORMAT(' .
            $this->dateExpression->dispatch($sqlWalker) . ', ' .
            $this->formatString->dispatch($sqlWalker) .
        ')';
    }
}
