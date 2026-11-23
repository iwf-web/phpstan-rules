<?php declare(strict_types=1);

/**
 * PHPStan Rules
 *
 * @package   PHPStan Rules
 * @author    IWF Web Solutions <web-solutions@iwf.ch>
 * @copyright Copyright (c) 2025-2026 IWF Web Solutions <web-solutions@iwf.ch>
 * @license   https://github.com/iwf-web/phpstan-rules/blob/main/LICENSE.txt MIT License
 * @link      https://github.com/iwf-web/phpstan-rules
 */

namespace IWFWeb\PhpstanRules\Common;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Requires declare(strict_types=1) as the first statement of every file.
 *
 * @implements Rule<FileNode>
 */
final readonly class DeclareStrictTypesRule implements Rule
{
    public const string IDENTIFIER = 'iwfWeb.declareStrictTypes';

    #[\Override]
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @param FileNode $node
     *
     * @return list<IdentifierRuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $statements = $node->getNodes();
        $first = $statements[0] ?? null;

        // A shebang line is parsed as inline HTML, PHP allows the declare right after it
        if ($first instanceof Node\Stmt\InlineHTML && str_starts_with($first->value, '#!')) {
            $first = $statements[1] ?? null;
        }

        if ($first instanceof Node\Stmt\Declare_) {
            foreach ($first->declares as $declare) {
                if ($declare->key->toLowerString() === 'strict_types'
                    && $declare->value instanceof Node\Scalar\Int_
                    && $declare->value->value === 1
                ) {
                    return [];
                }
            }
        }

        return [
            RuleErrorBuilder::message('File must start with declare(strict_types=1).')
                ->identifier(self::IDENTIFIER)
                ->line(1)
                ->build(),
        ];
    }
}
