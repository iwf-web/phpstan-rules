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

namespace IWF\RectorRules\Controller;

use PhpParser\Node\Stmt\ClassMethod;

trait ControllerRuleHelperTrait
{
    private const string ROUTE_ATTRIBUTE = 'Symfony\Component\Routing\Attribute\Route';

    private function hasRouteAttribute(ClassMethod $method): bool
    {
        foreach ($method->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === self::ROUTE_ATTRIBUTE) {
                    return true;
                }
            }
        }

        return false;
    }
}
