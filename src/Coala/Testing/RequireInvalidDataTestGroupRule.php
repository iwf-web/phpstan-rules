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

namespace IWFWeb\PhpstanRules\Coala\Testing;

use Coala\TestingBundle\Tests\Helpers\AssertionHelpersTrait;
use IWFWeb\PhpstanRules\Concern\AttributeFinderTrait;
use IWFWeb\PhpstanRules\Concern\NamespaceMatcherTrait;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test methods calling assertFailingValidation must carry #[Group('invalid-data-test')].
 *
 * @implements Rule<ClassMethod>
 */
final readonly class RequireInvalidDataTestGroupRule implements Rule
{
    use AttributeFinderTrait;
    use NamespaceMatcherTrait;

    public const string IDENTIFIER = 'iwfWeb.requireInvalidDataTestGroup';
    private const string SENTINEL_CLASS = AssertionHelpersTrait::class;
    private const string GROUP_ATTRIBUTE = Group::class;
    private const string TEST_ATTRIBUTE = Test::class;
    private const string REQUIRED_GROUP = 'invalid-data-test';

    /**
     * @param list<string> $requireInvalidDataTestGroupNamespaces
     */
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private array $requireInvalidDataTestGroupNamespaces = ['App\Tests'],
    ) {}

    #[\Override]
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     *
     * @return list<IdentifierRuleError>
     *
     * @throws ShouldNotHappenException
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->reflectionProvider->hasClass(self::SENTINEL_CLASS)) {
            return [];
        }

        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return [];
        }

        if (!$this->matchesNamespace($namespace, $this->requireInvalidDataTestGroupNamespaces)) {
            return [];
        }

        if (!$this->isTestMethod($node)) {
            return [];
        }

        if (!$this->callsAssertFailingValidation($node)) {
            return [];
        }

        if ($this->hasInvalidDataTestGroup($node)) {
            return [];
        }

        $message = \sprintf(
            'Test method %s() calls assertFailingValidation() but is missing #[Group(\'%s\')].',
            $node->name->toString(),
            self::REQUIRED_GROUP,
        );

        return [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->line($node->getStartLine())
                ->tip(\sprintf('Add #[Group(\'%s\')] to the method.', self::REQUIRED_GROUP))
                ->build(),
        ];
    }

    private function isTestMethod(ClassMethod $node): bool
    {
        if (str_starts_with($node->name->toString(), 'test')) {
            return true;
        }

        return $this->methodHasAttribute($node, self::TEST_ATTRIBUTE);
    }

    private function callsAssertFailingValidation(ClassMethod $node): bool
    {
        $nodeFinder = new NodeFinder();
        $stmts = $node->stmts ?? [];

        $found = $nodeFinder->findFirst(
            $stmts,
            static function (Node $n): bool {
                if (!($n instanceof MethodCall) && !($n instanceof StaticCall)) {
                    return false;
                }

                return $n->name instanceof Identifier
                    && $n->name->toString() === 'assertFailingValidation';
            },
        );

        return $found !== null;
    }

    private function hasInvalidDataTestGroup(ClassMethod $node): bool
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() !== self::GROUP_ATTRIBUTE) {
                    continue;
                }

                if ($attr->args === []) {
                    continue;
                }

                $firstArg = $attr->args[0];

                if (
                    $firstArg->value instanceof Node\Scalar\String_
                    && $firstArg->value->value === self::REQUIRED_GROUP
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
