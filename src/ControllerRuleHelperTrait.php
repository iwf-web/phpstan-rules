<?php

declare(strict_types=1);

namespace Coala\TestingBundle\PHPStan\Rules;

use PhpParser\Node\Stmt\ClassMethod;

trait ControllerRuleHelperTrait
{
    private function hasRouteAttribute(ClassMethod $method): bool
    {
        foreach ($method->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === 'Symfony\Component\Routing\Attribute\Route') {
                    return true;
                }
            }
        }

        return false;
    }
}
