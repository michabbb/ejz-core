<?php

function normLatin($string) {
    $oneLetter = <<<END
        `İ¡¿ÀàÁáÂâÃãÄäÅåÆæçÇÈèÉéÊêËëÌìÍíÎîÏïÐððÑñÒòÓóÔôÕõöÖØøÙùÚúÛûÜüÝýÞþÿŸāĀĂ
        'I!?AaAaAaAaAaAaAacCEeEeEeEeIiIiIiIiDdoNnOoOoOoOooOOoUuUuUuUuYyBbyYaAA

        ăąĄćĆĈĉĊċčČďĎĐđēĒĔĕėĖęĘĘěĚĜĝğĞĠġģĢĤĥĦħĨĩīĪĪĬĭįĮıĴĵķĶĶĸĹĺļĻĽľĿŀłŁńŃņŅňŇ
        aaAcCCcCccCdDDdeEEeeEeeEeEGggGGggGHhHhIiiiIIiiIiJjkkKkLllLLlLllLnNnNnN

        ŉŊŋŌōŎŏŐőŒœŔŕŗřŘśŚŜŝşŞšŠŢţťŤŦŧŨũūŪŪŬŭůŮŰűųŲŴŵŶŷźŹżŻžŽƠơƯưǼǽȘșȚțəƏΐάΆέΈ
        nNnOoOoOoOoRrrrRsSSssSsSTttTTtUuuuUUuuUUuuUWwYyzZzZzZOoUuAaSsTteEiaAeE

        ήΉίΊΰαΑβΒγΓδΔεΕζΖηΗθΘιΙκΚλΛμΜνΝξΞοΟπΠρΡςσΣτΤυΥφΦχΧωΩϊΪϋΫόΌύΎώΏјЈћЋ
        hHiIyaAbBgGdDeEzZhH88iIkKlLmMnN33oOpPrRssStTyYfFxXwWiIyYoOyYwWjjcC

        أبتجحدرزسصضطفقكلمنهوي
        abtghdrzssdtfkklmnhoy

        ẀẁẂẃẄẅẠạẢảẤấẦầẨẩẪẫẬậẮắẰằẲẳẴẵẶặẸẹẺẻẼẽẾếỀềỂểỄễỆệỈỉỊịỌọỎ
        WwWwWwAaAaAaAaAaAaAaAaAaAaAaAaEeEeEeEeEeEeEeEeIiIiOoO

        ỏỐốỒồỔổỖỗỘộỚớỜờỞởỠỡỢợỤụỦủỨứỪừỬửỮữỰựỲỳỴỵỶỷỸỹ–—‘’“”•
        oOoOoOoOoOoOoOoOoOoOoUuUuUuUuUuUuUuYyYyYyYy--''""-
END;
    $oneLetter = nsplit($oneLetter);
    $split = function($_) { return preg_split('/(?<!^)(?!$)/u', $_); };
    $n = count($oneLetter) / 2;
    for ($i = 0; $i < $n; $i++)
        $string = strtr($string, array_combine($split($oneLetter[$i * 2]), $split($oneLetter[$i * 2 + 1])));
    $twoLetter = array(
        'خ' => 'kh', 'ذ' => 'th', 'ش' => 'sh', 'ظ' => 'th',
        'ع' => 'aa', 'غ' => 'gh', 'ψ' => 'ps', 'Ψ' => 'PS',
        'đ' => 'dj', 'Đ' => 'Dj', 'ß' => 'ss', 'ẞ' => 'SS', 
        'Ä' => 'Ae', 'ä' => 'ae', 'Æ' => 'AE', 'æ' => 'ae',
        'Ö' => 'Oe', 'ö' => 'oe', 'Ü' => 'Ue', 'ü' => 'ue',
        'Þ' => 'TH', 'þ' => 'th', 'ђ' => 'dj', 'Ђ' => 'Dj',
        'љ' => 'lj', 'Љ' => 'Lj', 'њ' => 'nj', 'Њ' => 'Nj',
        'џ' => 'dz', 'Џ' => 'Dz', 'ث' => 'th', '…' => '...',
    );
    return strtr($string, $twoLetter);
}

function normLatinRu($string) {
    static $table = array(
        'А' => 'A', 'а' => 'a',
        'Б' => 'B', 'б' => 'b',
        'В' => 'V', 'в' => 'v',
        'Г' => 'G', 'г' => 'g',
        'Д' => 'D', 'д' => 'd',
        'Е' => 'E', 'е' => 'e',
        'Ё' => 'E', 'ё' => 'e',
        'Ж' => 'Zh', 'ж' => 'zh',
        'З' => 'Z', 'з' => 'z',
        'И' => 'I', 'и' => 'i',
        'Й' => 'I', 'й' => 'i',
        'К' => 'K', 'к' => 'k',
        'Л' => 'L', 'л' => 'l',
        'М' => 'M', 'м' => 'm',
        'Н' => 'N', 'н' => 'n',
        'О' => 'O', 'о' => 'o',
        'П' => 'P', 'п' => 'p',
        'Р' => 'R', 'р' => 'r',
        'С' => 'S', 'с' => 's',
        'Т' => 'T', 'т' => 't',
        'У' => 'U', 'у' => 'u',
        'Ф' => 'F', 'ф' => 'f',
        'Х' => 'Kh', 'х' => 'kh',
        'Ц' => 'Tc', 'ц' => 'tc',
        'Ч' => 'Ch', 'ч' => 'ch',
        'Ш' => 'Sh', 'ш' => 'sh',
        'Щ' => 'Shch', 'щ' => 'shch',
        'Ъ' => '', 'ъ' => '',
        'Ы' => 'Y', 'ы' => 'y',
        'Ь' => '', 'ь' => '',
        'Э' => 'E', 'э' => 'e',
        'Ю' => 'Iu', 'ю' => 'iu',
        'Я' => 'Ia', 'я' => 'ia',
        // украинский
        'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
        'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
    );
    return strtr($string, $table);
}

function normRu($string) {
    $string = mb_strtolower($string, "utf-8");
    $string = strtr($string, array("ё" => "е"));
    $string = preg_replace("|[^a-z0-9а-я]|u", ' ', $string);
    $string = preg_replace('|\s+|', ' ', $string);
    $string = trim($string);
    return $string;
}

function normEn($string) {
    $string = strtolower($string);
    $string = preg_replace("|[^a-z0-9]|", ' ', $string);
    $string = preg_replace('|\s+|', ' ', $string);
    $string = trim($string);
    return $string;
}

function normTrim($string) {
    $bom = pack('H*','EFBBBF');
    $string = preg_replace("~^\s*\x{00a0}~siu", ' ', $string);
    $string = preg_replace("~\x{00a0}\s*$~siu", ' ', $string);
    $string = preg_replace("/^({$bom})+/", '', $string);
    $string = trim($string);
    return $string;
}

function normSpace($string) {
    $bom = pack('H*','EFBBBF');
    $string = preg_replace("~\x{00a0}~siu", ' ', $string);
    $string = preg_replace("/^({$bom})+/", '', $string);
    $string = preg_replace('|\s+|', ' ', $string);
    $string = trim($string);
    return $string;
}
