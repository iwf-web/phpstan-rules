<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\DateProvider;

class FuncCallExamples
{
    public function bannedFunctions(): void
    {
        $a = time(); // @error iwf.forceDateProviderFuncCall
        $b = date('Y-m-d'); // @error iwf.forceDateProviderFuncCall
        $c = mktime(); // @error iwf.forceDateProviderFuncCall
        $d = strtotime('now'); // @error iwf.forceDateProviderFuncCall
        $e = gmdate('Y-m-d'); // @error iwf.forceDateProviderFuncCall
    }

    public function allowedFunctions(): void
    {
        $a = date('Y-m-d', 0);
        $b = gmdate('Y-m-d', 0);
        $c = mktime(0, 0, 0, 1, 1, 2025);
        $d = strtotime('2025-01-01', 0);
    }
}
