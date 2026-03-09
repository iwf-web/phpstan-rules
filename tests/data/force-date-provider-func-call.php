<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\DateProvider;

class FuncCallExamples
{
    public function bannedFunctions(): void
    {
        $a = time(); // @error iwfWeb.forceDateProviderFuncCall
        $b = date('Y-m-d'); // @error iwfWeb.forceDateProviderFuncCall
        $c = mktime(); // @error iwfWeb.forceDateProviderFuncCall
        $d = strtotime('now'); // @error iwfWeb.forceDateProviderFuncCall
        $e = gmdate('Y-m-d'); // @error iwfWeb.forceDateProviderFuncCall
    }

    public function allowedFunctions(): void
    {
        $a = date('Y-m-d', 0);
        $b = gmdate('Y-m-d', 0);
        $c = mktime(0, 0, 0, 1, 1, 2025);
        $d = strtotime('2025-01-01', 0);
    }

    public function edgeCases(): void
    {
        $fn = 'time';
        $fn(); // variable call — not a Node\Name, no error
        microtime(); // not in banned list, no error
    }
}
