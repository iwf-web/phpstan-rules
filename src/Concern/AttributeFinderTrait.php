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

namespace IWFWeb\PhpstanRules\Concern;

use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\ClassMethod;

trait AttributeFinderTrait
{
    /**
     * @param array<AttributeGroup> $attrGroups
     */
    private function hasAttribute(array $attrGroups, string $fqcn): bool
    {
        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === $fqcn) {
                    return true;
                }
            }
        }

        return false;
    }

    private function methodHasAttribute(ClassMethod $method, string $fqcn): bool
    {
        return $this->hasAttribute($method->attrGroups, $fqcn);
    }
}
