<?php

declare(strict_types=1);

namespace Coala\TestingBundle\PHPStan\Rules;

use PhpParser\Node\UseItem;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

trait RequiredUseAliasMatcherTrait
{
    /** @var array<string, string> namespace => alias */
    private array $aliasByNamespace;

    /**
     * @param list<array{namespace: string, alias: string}> $requiredUseAliases
     *
     * @return array<string, string>
     */
    private function buildAliasByNamespace(array $requiredUseAliases): array
    {
        $map = [];

        foreach ($requiredUseAliases as $entry) {
            $map[$entry['namespace']] = $entry['alias'];
        }

        return $map;
    }

    private function findRequiredAlias(string $fqn): ?string
    {
        return $this->aliasByNamespace[$fqn] ?? null;
    }

    /**
     * @return ?RuleError Error if the use item has a wrong alias, null otherwise
     *
     * @throws ShouldNotHappenException
     */
    private function checkUseItem(UseItem $use, string $fqn): ?RuleError
    {
        $requiredAlias = $this->findRequiredAlias($fqn);

        if ($requiredAlias === null) {
            return null;
        }

        $actualAlias = $use->getAlias()->toString();

        if ($actualAlias === $requiredAlias) {
            return null;
        }

        $message = sprintf(
            'Use statement for "%s" must use alias "%s", found "%s".',
            $fqn,
            $requiredAlias,
            $actualAlias,
        );

        return RuleErrorBuilder::message($message)
            ->identifier(self::IDENTIFIER)
            ->line($use->getStartLine())
            ->tip(sprintf('Use: use %s as %s;', $fqn, $requiredAlias))
            ->build();
    }
}
