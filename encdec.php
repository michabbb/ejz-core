<?php

function url_base64_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function url_base64_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function xencrypt($string, $key) {
    $string = mt_rand() . ':' . $string . ':' . mt_rand();
    for ($i = 0; $i < strlen($string); $i++) {
        $k = md5($key . (string)(substr($string, $i + 1)) . $i);
        for ($j = 0; $j < strlen($k); $j++)
            $string[$i] = $string[$i] ^ $k[$j];
    }
    return url_base64_encode($string);
}

function xdecrypt($string, $key) {
    $string = url_base64_decode($string);
    for ($i = strlen($string) - 1; $i >= 0; $i--) {
        @ $k = md5($key . (string)(substr($string, $i + 1)) . $i);
        for ($j = 0; $j < strlen($k); $j++)
            $string[$i] = $string[$i] ^ $k[$j];
    }
    $string = explode(':', $string, 2);
    if (count($string) != 2 or !is_numeric($string[0])) return null;
    $string = $string[1];
    $pos = strrpos($string, ':');
    if (!$pos) return null;
    return substr($string, 0, $pos);
}

function oencrypt($string, $key) {
    $method = 'aes-256-ofb';
    $iv = substr(md5($key), 0, 16);
    $string = mt_rand() . ':' . $string . ':' . mt_rand();
    $string = openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv);
    return url_base64_encode($string);
}

function odecrypt($string, $key) {
    $method = 'aes-256-ofb';
    $iv = substr(md5($key), 0, 16);
    $string = url_base64_decode($string);
    $string = openssl_decrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv);
    $string = explode(':', $string, 2);
    if (count($string) != 2 or !is_numeric($string[0]))
        return null;
    $string = $string[1];
    $pos = strrpos($string, ':');
    if (!$pos) return null;
    return substr($string, 0, $pos);
}

function base32_decode($s) {
    static $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $tmp = array();
    foreach (str_split($s) as $c)
        $tmp[] = sprintf('%05b', intval(strpos($map, $c)));
    $tmp = implode('', $tmp);
    $args = array_map('bindec', str_split($tmp, 8));
    array_unshift($args, 'C*');
    return rtrim(call_user_func_array('pack', $args), "\0");
}

function base32_encode($string) {
    static $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = array();
    $collect = array();
    for ($i = 0; $i < strlen($string); $i++)
        $collect[] = str_pad(decbin(ord($string[$i])), 8, '0', STR_PAD_LEFT);
    $neededPad = 5 - (count($collect) % 5);
    if ($neededPad > 0 and $neededPad < 5)
        $collect[] = str_repeat('0', 5 - $neededPad);
    $collect = implode('', $collect);
    foreach (str_split($collect, 5) as $binaryChunk)
        $output[] = $map[bindec($binaryChunk)];
    return implode('', $output);
}
