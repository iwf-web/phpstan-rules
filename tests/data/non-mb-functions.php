<?php /** @noinspection _ALL */ declare(strict_types=1);

namespace IWFWeb\PhpstanRules\Tests\data;

class NonMbFunctions
{
    public function __invoke(): void
    {
        // mb_check_encoding();
        // mb_chr();
        $a = chr(65); // @error iwfWeb.mbFunctionUsageRule
        // mb_convert_case();
        // mb_convert_encoding();
        // mb_convert_kana();
        // mb_convert_variables();
        // mb_decode_mimeheader();
        // mb_decode_numericentity();
        // mb_detect_encoding();
        // mb_detect_order();
        // mb_encode_mimeheader();
        // mb_encode_numericentity();
        // mb_encoding_aliases();
        // mb_ereg_match();
        // mb_ereg_replace();
        // mb_ereg_replace_callback();
        // mb_ereg_search();
        // mb_ereg_search_getpos();
        // mb_ereg_search_getregs();
        // mb_ereg_search_init();
        // mb_ereg_search_pos();
        // mb_ereg_search_regs();
        // mb_ereg_search_setpos();
        // mb_eregi();
        // mb_eregi_replace();
        // mb_get_info();
        // mb_http_input();
        // mb_http_output();
        // mb_internal_encoding();
        // mb_language();
        // mb_list_encodings();
        // mb_ord();
        $_a = ord($a); // @error iwfWeb.mbFunctionUsageRule
        // mb_output_handler();
        // mb_parse_str();
        parse_str('foo=bar', $b); // @error iwfWeb.mbFunctionUsageRule
        // mb_preferred_mime_name();
        // mb_regex_encoding();
        // mb_regex_set_options();
        // mb_scrub();
        // mb_send_mail();
        // mb_split();
        // mb_str_pad();
        $c = str_pad('abc', 10); // @error iwfWeb.mbFunctionUsageRule
        // mb_str_split();
        $d = str_split('abc'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strcut();
        // mb_strimwidth();
        // mb_stripos();
        $e = stripos('abc', 'b'); // @error iwfWeb.mbFunctionUsageRule
        // mb_stristr();
        $f = stristr('abc', 'b'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strlen();
        $g = strlen('abc'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strpos();
        $h = strpos('abc', 'b'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strrchr();
        $i = strrchr('a_b_c', '_'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strrichr();
        // mb_strripos();
        $j = strripos('aA_bB_cC', 'b'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strrpos();
        $k = strrpos('aA_bB_cC', 'b'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strstr();
        $l = strstr('a_b_c', '_'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strtolower();
        $m = strtolower('ABC'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strtoupper();
        $n = strtoupper('abc'); // @error iwfWeb.mbFunctionUsageRule
        // mb_strwidth();
        // mb_substr();
        $o = substr('abc', 1); // @error iwfWeb.mbFunctionUsageRule
        // mb_substr_count();
        $p = substr_count('abcabc', 'b'); // @error iwfWeb.mbFunctionUsageRule
    }

    public function edgeCases(): void
    {
        $fn = 'strlen';
        $fn('abc'); // variable call — not a Node\Name, no error
        array_map(fn ($x) => $x, []); // not in banned list, no error
    }
}
