<?php

// no namespace, add these functions and constants to global scope

define('SQL_FORMAT_DATE', 'Y-m-d');
define('SQL_FORMAT_DATETIME', 'Y-m-d H:i:s');

function esc($s, $decode = false) {
    $_ = ENT_NOQUOTES;
    return $decode ? html_entity_decode($s, $_) : htmlspecialchars($s, $_);
}

function fesc($s, $decode = false) {
    $_ = ENT_QUOTES;
    return $decode ? html_entity_decode($s, $_) : htmlspecialchars($s, $_);
}

function cloakHTML($s) {
    $s = '' . $s;
    $return = array();
    for ($i = 0; $i < strlen($s); $i++)
        if (ctype_alnum($s[$i])) $return[] = '&#' . ord($s[$i]) . ';';
        else $return[] = fesc($s[$i]);
    return implode('', $return);
}

function template() { // $template, $vars
    if (func_num_args() > 1)
        extract(func_get_arg(1));
    ob_start();
    include(func_get_arg(0));
    return ob_get_clean();
}

function validateHost($host) {
    return (
        preg_match('/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i', $host)
            and
        strlen($host) <= 253
            and
        preg_match('/^[^\.]{1,63}(\.[^\.]{1,63})*$/', $host)
    );
}

function host($url) {
    $host = strtolower(parse_url($url, PHP_URL_HOST));
    if (validateHost($host)) return $host;
    return null;
}

function curdate($days = 0) {
    return date(SQL_FORMAT_DATE, time() + $days * 24 * 3600);
}

function now($seconds = 0) {
    return date(SQL_FORMAT_DATETIME, time() + $seconds);
}

function getTagAttr($tag, $attr = null) {
    $tag = trim($tag);
    $tag = preg_replace('~^(<\w+\b([^>]*)>).*$~is', '$2', $tag);
    preg_match_all('~\b(?P<attr>[\w-]+)=([\'"])?(?P<value>.*?)\2~', $tag, $one, PREG_SET_ORDER);
    preg_match_all('~\b(?P<attr>[\w-]+)=(?P<value>\S+)~', $tag, $two, PREG_SET_ORDER);
    $collector = array();
    foreach (array_merge($two, $one) as $elem)
        $collector[strtolower($elem['attr'])] = html_entity_decode($elem['value'], ENT_QUOTES);
    if (!is_null($attr)) return @ $collector[$attr];
    return $collector;
}

function nsplit($value) {
    $value = str_replace(chr(13), chr(10), $value);
    $value = explode(chr(10), $value);
    $value = array_map('trim', $value);
    $value = array_filter($value);
    return array_values($value);
}

function is_closure($obj) {
    return is_callable($obj) and is_object($obj);
}

function is_ip($string) {
    $string = trim($string);
    preg_match("~^\d+\.\d+\.\d+\.\d+$~", $string, $match);
    if (!$match) return false;
    $string = explode('.', $string);
    $string = array_values(array_filter($string, function($part) {
        $part = intval($part);
        return (0 <= $part) and ($part <= 255);
    }));
    return count($string) === 4;
}

function str_replace_once($needle, $replace, $haystack) {
    @ $pos = strpos($haystack, $needle);
    if ($pos === false) return $haystack;
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}

function is_assoc($arr) {
    if (!is_array($arr)) return false;
    $count0 = count($arr);
    $count1 = count(array_filter(array_keys($arr), 'is_string'));
    return $count0 === $count1;
}

function array_transpose($array) {
    $return = array();
    foreach ($array as $key => $row)
        foreach ($row as $k => $v) {
            if (!isset($return[$k])) $return[$k] = array();
            $return[$k][$key] = $v;
        }
    return $return;
}

function str_truncate($string, $len = 40, $center = true, $replacer = '...') {
    $l = mb_strlen($replacer);
    if ($center and $len < (2 + $l)) $len = (2 + $l);
    if (!$center and $len < (1 + $l)) $len = (1 + $l);
    if ($center and mb_strlen($string) > $len) {
        $len -= $l;
        $begin = ceil($len / 2);
        $end = $len - $begin;
        return mb_substr($string, 0, $begin) . $replacer . mb_substr($string, - $end);
    } elseif (!$center and mb_strlen($string) > $len) {
        $len -= $l;
        $begin = $len;
        return mb_substr($string, 0, $begin) . $replacer;
    } else return $string;
}

function mt_shuffle(& $items, $seed = null) {
    $keys = array_keys($items);
    $SEED = '';
    for ($i = count($items) - 1; $i > 0; $i--) {
        if ($seed) {
            $j = rand_from_string($SEED . $seed) % ($i + 1);
            $SEED .= $j;
        } else $j = mt_rand(0, $i);
        list($items[$keys[$i]], $items[$keys[$j]]) = array($items[$keys[$j]], $items[$keys[$i]]);
    }
}

function file_get_ext($file) {
    $info = pathinfo($file);
    if (isset($info['extension']))
        return strtolower($info['extension']);
    return '';
}

function file_get_name($file) {
    if (substr($file, -1) === '/')
        return '';
    $info = pathinfo($file);
    if (isset($info['filename']))
        return $info['filename'];
    return '';
}

function rand_from_string($string) {
    $int = md5($string);
    $int = preg_replace('/[^0-9]/', '', $int);
    $int = substr($int, 0, strlen(mt_getrandmax() . '') - 1);
    return intval($int);
}

function gauss($peak = 0, $stdev = 1, $seed = null) {
    $x = ($seed ? rand_from_string($seed) : mt_rand()) / mt_getrandmax();
    $y = ($seed ? rand_from_string($seed . $x) : mt_rand()) / mt_getrandmax();
    $gauss = sqrt(-2 * log($x)) * cos(2 * pi() * $y);
    return $gauss * $stdev + $peak;
}

function getUserAgent($re = null, $seed = null) {
    $LIST = __DIR__ . '/ua.list.txt';
    if (!is_file($LIST)) _log("INVALID UA LIST!", E_USER_ERROR);
    if (is_array($seed)) $seed = json_encode($seed);
    if ($seed and is_string($seed . '')) $seed = rand_from_string($seed . '');
    else $seed = rand_from_string(microtime(true) . '');
    $list = nsplit(file_get_contents($LIST));
    if ($re and is_string($re))
        $list = array_values(array_filter($list, function($line) use($re) {
            if (preg_match('~^\w+$~', $re)) $re = "~{$re}~i";
            return @ preg_match($re, $line);
        }));
    if (!$list) return '';
    $ua = $list[$seed % count($list)];
    return $ua;
}

function parseProxy($proxy) {
    if (!is_string($proxy)) return null;
    $proxy = parse_url($proxy);
    if (!in_array($proxy['scheme'], array('proxy', 'socks', 'http', 'https')))
        return null;
    if (!$proxy['host'] or !isset($proxy['port'])) return null;
    @ $user = $proxy['user'] ?: '';
    @ $pass = $proxy['pass'] ?: '';
    if ($user and $pass) $user .= ':' . $pass;
    $proxy['host:port'] = "{$proxy['host']}:{$proxy['port']}";
    $proxy['user:pass'] = $user;
    return $proxy;
}

function prepareAttr($attr) {        
    $collector = array();
    if (is_string($attr))
        $attr = getTagAttr($attr);
    foreach ($attr as $k => $v) {
        if (!$k or (!$v and $v !== "0")) continue;
        if (is_assoc($v)) {
            $_collector = array();
            foreach ($v as $_k => $_v) {
                if (!$_k or (!$_v and $_v !== "0")) continue;
                $_collector[] = sprintf('%s:%s', fesc($_k), fesc($_v));
            }
            $v = implode(';', $_collector) . ($collector ? ';' : '');
        } elseif (is_array($v)) {
            $v = implode(' ', $v);
        }
        if (!$v and $v !== "0") continue;
        $collector[] = sprintf('%s="%s"', $k, fesc($v));
    }
    return implode(' ', $collector);
}

function xpath($string, $query = '/*') {
    if (is_string($string)) {
        // SOME FIXES TO BE COMPATIBLE WITH XML
        $tags = 'area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr';
        $string = preg_replace_callback("~<({$tags})\b[^>]*>~", function($m) {
            $last = mb_substr($m[0], -2);
            if ($last != "/>") return rtrim(mb_substr($m[0], 0, -1)) . ' />';
            return $m[0];
        }, $string);
        $string = preg_replace_callback('~<html\b[^>]*>~', function($m) {
            $attrs = getTagAttr($m[0]);
            if (!isset($attrs['xmlns'])) return $m[0];
            unset($attrs['xmlns']);
            $attrs = prepareAttr($attrs);
            $attrs = $attrs ? " {$attrs}" : '';
            return "<html{$attrs}>";
        }, $string);
    }
    if (func_num_args() > 2)
        $callback = func_get_arg(2);
    $query = trim($query);
    if (!$query) return array();
    $predefined = array(
        'remove' => function($tag) {
            $tag -> parentNode -> removeChild($tag);
        },
        'unwrap' => function($tag) {
            if ($tag -> hasChildNodes()) {
                $collector = array();
                foreach ($tag -> childNodes as $child)
                    $collector[] = $child;
                for ($i = 0; $i < count($collector); $i++)
                    $tag -> parentNode -> insertBefore($collector[$i], $tag);
            }
            $tag -> parentNode -> removeChild($tag);
        }
    );
    $query = preg_replace(
        '~class\((?P<class>.*?)\)~i',
        'contains(concat(" ",normalize-space(@class)," ")," $1 ")',
        $query
    );
    $query = preg_replace(
        '~lower-case\((?P<lower>.*?)\)~i',
        'translate($1,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")',
        $query
    );
    if ($string instanceof DOMNode) {
        if ($string -> nodeType === XML_TEXT_NODE)
            if (func_num_args() === 1)
                return $string -> nodeValue;
            elseif(func_num_args() === 2)
                return array();
            else return '';
        $doc = $string -> ownerDocument;
        $string = $doc -> saveXML($string);
    }
    $doc = new DOMDocument();
    $doc -> preserveWhiteSpace = false;
    if (isset($callback) and $callback === "preserveWhiteSpace") {
        unset($callback);
        $doc -> preserveWhiteSpace = true;
    }
    $doc -> formatOutput = true;
    libxml_use_internal_errors(true);
    $_ = $doc -> loadXML($string);
    if (!$_) $doc -> loadHTML('<?xml encoding="UTF-8">' . $string);
    $doc -> normalizeDocument();
    libxml_clear_errors();
    $xpath = new DOMXPath($doc);
    $tags = $xpath -> query($query);
    if (isset($callback) and !is_numeric($callback)) {
        if (!($tags instanceof DOMNodeList)) {
            _log("XPATH: INVALID QUERY: {$query}", E_USER_WARNING);
            return "";
        }
        if (is_string($callback) and isset($predefined[$callback]))
            $callback = $predefined[$callback];
        $callback = function ($tag) use ($callback) {
            $_ = $tag;
            while (isset($_ -> parentNode))
                $_ = $_ -> parentNode;
            if ($_ instanceof DOMDocument)
                $callback($tag);
        };
        $collector = array();
        foreach ($tags as $tag) $collector[] = $tag;
        $tags = $collector;
        if ($callback === 'attr') {
            list($tag) = $tags;
            $tag = $doc->saveXML($tag);
            $qs = explode('@', $query);
            return getTagAttr($tag, $qs[count($qs) - 1]);
        }
        for ($i = 0; $i < count($tags); $i++)
            $callback($tags[$i]);
        return $doc -> saveXML($doc -> documentElement);
    }
    $return = array();
    if (!($tags instanceof DOMNodeList)) {
        _log("XPATH: INVALID QUERY: {$query}", E_USER_WARNING);
        return array();
    }
    foreach ($tags as $tag)
        $return[] = $doc->saveXML($tag);
    if (isset($callback) and is_numeric($callback)) {
        $int = intval($callback);
        if ($int < 0) $int = count($return) + $int;
        return isset($return[$int]) ? $return[$int] : '';
    }
    if (func_num_args() === 1)
        return implode('', $return);
    return $return;
}

function curl($urls, $settings = array()) {
    $return = array();
    if (is_string($urls))
        $urls = array($urls);
    $urls = array_values(array_unique($urls));
    $modifyContent = isset($settings['modifyContent']) ? $settings['modifyContent'] : null;
    $retry = isset($settings['retry']) ? $settings['retry'] : 1;
    $verbose = isset($settings['verbose']) ? intval($settings['verbose']) : false;
    $threads = isset($settings['threads']) ? intval($settings['threads']) : 5;
    if ($threads <= 0) $threads = 1;
    if ($threads >= 100) $threads = 100;
    $sleep = isset($settings['sleep']) ? $settings['sleep'] : 5;
    $delay = isset($settings['delay']) ? $settings['delay'] : 0;
    $format = isset($settings['format']) ? $settings['format'] : (count($urls) > 1 ? 'array' : 'simple');
    $checker = isset($settings['checker']) ? $settings['checker'] : false;
    $ignoreErrors = isset($settings['ignoreErrors']) ? $settings['ignoreErrors'] : array();
    if (is_numeric($checker)) $checker = array(intval($checker));
    if (!is_callable($checker) and is_array($_ = $checker))
        $checker = function ($url, $ch) use ($_) {
            $info = curl_getinfo($ch);
            $code = intval($info['http_code']);
            return in_array($code, array_map('intval', $_));
        };
    $handleReturn = function (& $return) use ($checker, $verbose, $modifyContent, $ignoreErrors) {
        $fail = array();
        foreach (array_keys($return) as $key) {
            $value = & $return[$key];
            if (!is_array($value) or !array_key_exists('ch', $value) or !is_resource($value['ch']))
                continue;
            $ch = $value['ch'];
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            if ($error and in_array($errno, $ignoreErrors)) $error = "";
            if ($error or ($checker and !$checker($value['url'], $ch))) {
                unset($return[$key]);
                curl_close($ch);
                $fail[$key] = $value['url'];
                if ($verbose) _log("{$value['url']} .. ERR!" . ($error ? " ({$error})" : ''), E_USER_WARNING);
                continue;
            }
            $info = curl_getinfo($ch);
            $content = curl_multi_getcontent($ch);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            if ($verbose and $value['url'] === $info['url'])
                _log("{$value['url']} .. OK!");
            elseif ($verbose)
                _log("{$value['url']} .. {$info['url']} .. OK!");
            if (intval($headerSize) > 0) {
                $header = substr($content, 0, $headerSize);
                $content = substr($content, $headerSize);
                $headers = array();
                $_ = explode("\r\n\r\n", $header);
                for ($index = 0; $index < count($_) - 1; $index++)
                    foreach (explode("\r\n", $_[$index]) as $i => $line)
                        if ($i === 0) {
                            $line = explode(' ', $line);
                            $headers[$index]['http-code'] = $line[1];
                        } else {
                            $line = explode(': ', $line, 2);
                            if (count($line) != 2) continue;
                            list($k, $v) = $line;
                            $headers[$index][strtolower($k)] = $v;
                        }
            } else $header = '';
            $return[$key]['content'] = $content;
            $return[$key]['header'] = $header;
            if ($modifyContent and is_callable($modifyContent))
                $return[$key]['content'] = $modifyContent($value['url'], $content);
            $return[$key]['info'] = $info;
            if (isset($headers)) $return[$key]['headers'] = $headers;
            unset($value['ch']);
            curl_close($ch);
        }
        return $fail;
    };
    $getCH = function ($url, $settings) {
        $ch = curl_init();
        $opts = array();
        $setopt = function ($arr) use (& $ch, & $opts) {
            $opts = array_replace($opts, $arr);
            curl_setopt_array($ch, $arr);
        };
        $setopt(array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_USERAGENT => getUserAgent(),
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HEADER => true
        ));
        $acceptCallable = array(
            CURLOPT_HEADERFUNCTION,
            CURLOPT_PROGRESSFUNCTION,
            CURLOPT_READFUNCTION,
            CURLOPT_WRITEFUNCTION
        );
        if (defined("CURLOPT_PASSWDFUNCTION"))
            $acceptCallable[] = CURLOPT_PASSWDFUNCTION;
        if (is_string($url) and host($url))
            $setopt(array(CURLOPT_URL => $url));
        $constants = array_keys(get_defined_constants());
        $constantsStrings = array_values(array_filter($constants, function ($constant) {
            return strpos($constant, 'CURLOPT_') === 0;
        }));
        $constantsValues = array_map('constant', $constantsStrings);
        foreach ($settings as $key => $value) {
            if (in_array($key, $constantsStrings))
                $key = constant($key);
            if (!in_array($key, $constantsValues)) continue;
            if (is_callable($value) and !in_array($key, $acceptCallable))
                $value = $value($url);
            $setopt(array($key => $value));
        }
        if (isset($opts[CURLOPT_URL]) and host($opts[CURLOPT_URL]))
            return $ch;
        return null;
    };
    do {
        $fails = array();
        while ($urls) {
            if ($threads == 1 or count($urls) == 1) {
                $single = curl_init();
                $multi = null;
            } else {
                $single = null;
                $multi = curl_multi_init();
            }
            for ($i = 0; $i < $threads and $urls; $i++) {
                $key = key($urls);
                $ch = $getCH($urls[$key], $settings);
                if (is_null($ch)) {
                    unset($urls[$key]);
                    $i--;
                    continue;
                }
                $return[$key] = array(
                    'url' => $urls[$key],
                    'ch' => $ch
                );
                if ($multi)
                    curl_multi_add_handle($multi, $ch);
                else $single = $ch;
                unset($urls[$key]);
            }
            if ($multi) {
                do {
                    curl_multi_exec($multi, $running);
                    usleep(200000);
                } while ($running > 0);
                curl_multi_close($multi);
            } else {
                curl_exec($single);
            }
            $fails[] = $handleReturn($return);
            if ($urls and $delay) sleep($delay);
        }
        foreach ($fails as $fail)
            foreach ($fail as $k => $v)
                $urls[$k] = $v;
    } while ($urls and $retry-- and sleep($sleep) === 0);
    if ($format === "simple") {
        return implode("\n\n", array_values(array_column($return, 'content')));
    } elseif ($format === "array") {
        return array_column($return, 'content', 'url');
    } elseif ($format === "complex") {
        return array_column($return, null, 'url');
    } else return;
}

function get_mimes() {
    static $mimes = array(
        'image' => array(
            'image/jpeg'   => 'jpg|jpeg',
            'image/gif'    => 'gif',
            'image/png'    => 'png',
            'image/bmp'    => 'bmp',
            'image/tiff'   => 'tif|tiff',
            'image/x-icon' => 'ico',
        ),
        'video' => array(
            'video/x-ms-asf'   => 'asf|asx',
            'video/x-ms-wmv'   => 'wmv',
            'video/x-ms-wmx'   => 'wmx',
            'video/x-ms-wm'    => 'wm',
            'video/avi'        => 'avi',
            'video/divx'       => 'divx',
            'video/x-flv'      => 'flv',
            'video/quicktime'  => 'mov|qt',
            'video/mpeg'       => 'mp4|mpeg|mpg|mpe',
            'video/mp4'        => 'mp4|m4v',
            'video/ogg'        => 'ogv',
            'video/webm'       => 'webm',
            'video/x-matroska' => 'mkv',
        ),
        'audio' => array(
            'audio/mpeg'        => 'mp3|m4a|m4b',
            'audio/x-mpeg'      => 'mp3|m4a|m4b',
            'audio/mpeg3'       => 'mp3',
            'audio/x-mpeg-3'    => 'mp3',
            'audio/x-realaudio' => 'ra|ram',
            'audio/wav'         => 'wav',
            'audio/x-wav'       => 'wav',
            'audio/ogg'         => 'ogg|oga',
            'audio/midi'        => 'mid|midi',
            'audio/x-midi'      => 'mid|midi',
            'audio/mid'         => 'mid|midi',
            'audio/x-mid'       => 'mid|midi',
            'audio/x-ms-wma'    => 'wma',
            'audio/x-ms-wax'    => 'wax',
            'audio/x-matroska'  => 'mka',
            'audio/flac'        => 'flac',
            'audio/aac'         => 'aac',
            'audio/x-aac'       => 'aac',
        ),
        'text' => array(
            'text/plain'                => 'txt|h',
            'text/csv'                  => 'csv',
            'text/tab-separated-values' => 'tsv',
            'text/calendar'             => 'ics',
            'text/richtext'             => 'rtx',
            'text/css'                  => 'css',
            'text/html'                 => 'htm|html',
        ),
        'archive' => array(
            'application/x-tar'           => 'tar',
            'application/zip'             => 'zip',
            'application/x-gzip'          => 'gz|gzip|tgz',
            'application/rar'             => 'rar',
            'application/x-7z-compressed' => '7z',
        ),
    );
    if (func_num_args() > 0) {
        $type = func_get_arg(0);
        return $mimes[$type];
    }
    $return = call_user_func_array('array_merge', array_values($mimes));
    $return = array_map(function ($_) { return explode('|', $_); }, $return);
    return $return;
}

function check_mime($file) {
    if (!is_file($file)) return;
    if (!function_exists('finfo_open'))
        _log("NOT DEFINED | finfo_open", E_USER_ERROR);
    if (!defined('FILEINFO_MIME_TYPE'))
        _log("NOT DEFINED | FILEINFO_MIME_TYPE", E_USER_ERROR);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($finfo, $file);
    finfo_close($finfo);
    $nargs = func_num_args();
    if ($nargs == 1) return $type;
    $mimes = get_mimes(func_get_arg(1));
    if (!isset($mimes[$type])) return false;
    if ($nargs == 2) return true;
    $check = func_get_arg(2);
    if (!$check) return true;
    return in_array(file_get_ext($file), $mimes[$type]);
}

function checkIP($ip, $list) {
    if (!validateHost($ip)) return false;
    if (!is_array($list)) $list = explode(',', $list);
    foreach ($list as $mask) {
        $mask = str_replace('.', '\\.', $mask);
        $mask = str_replace('*', '[0-9]+(\\.[0-9]+)*', $mask);
        if (preg_match("~^{$mask}$~", $ip)) return true;
    }
    return false;
}

function wait_pid($pid) {
    $pid = intval($pid);
    while (file_exists("/proc/{$pid}")) {
        sleep(1);
        clearstatcache("/proc/{$pid}");
    }
}

function getopts($opts) {
    if (func_num_args() > 1) $argv = func_get_arg(1);
    else $argv = $_SERVER['argv'];
    if (!is_array($argv)) return array();
    $collect = array();
    $next = null;
    for ($i = 0; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if ($next) {
            $collect[$next] = $arg;
            $next = null;
        } elseif (preg_match('~^-([a-zA-Z0-9])$~', $arg, $match)) {
            $arg = $match[1];
            if (!array_key_exists($arg, $opts))
                return array();
            if ($opts[$arg]) $next = $arg;
            else $collect[$arg] = true;
        } elseif (preg_match('~^--([a-z0-9][a-z0-9-]+)$~', $arg, $match)) {
            $arg = $match[1];
            if (!array_key_exists($arg, $opts))
                return array();
            if ($opts[$arg]) $next = $arg;
            else $collect[$arg] = true;
        } elseif (preg_match('~^--([a-z0-9])$~', $arg, $match)) {
            return array();
        } elseif(preg_match('~^--([a-z0-9-]+)=(.*)$~', $arg, $match)) {
            $arg = $match[1];
            $value = $match[2];
            if (!array_key_exists($arg, $opts))
                return array();
            if ($opts[$arg]) $collect[$arg] = $value;
            else return array();
        } elseif(preg_match('~^-([a-z])(.*)$~', $arg, $match)) {
            $arg = $match[1];
            $value = $match[2];
            if (!array_key_exists($arg, $opts))
                return array();
            if ($opts[$arg] and $value) $collect[$arg] = $value;
            elseif($opts[$arg]) $next = $arg;
            else {
                $arg = $match[1] . $match[2];
                for ($j = 0; $j < strlen($arg); $j++) {
                    if (!array_key_exists($arg[$j], $opts))
                        return array();
                    if (strlen($arg) > 1 and $opts[$arg[$j]]) return array();
                    elseif($opts[$arg[$j]]) $next = $arg[$j];
                    else $collect[$arg[$j]] = true;
                }
            }
        } else $collect[] = $arg;
    }
    if ($next) return array();
    return $collect;
}

function realurl($url, $base = '') {
    if (!host($base) and !host($url)) return null;
    if (strpos($url, '#') === 0) return null;
    if (strpos($url, 'javascript:') === 0) return null;
    if (strpos($url, 'mailto:') === 0) return null;
    if (strpos($url, 'skype:') === 0) return null;
    if (strpos($url, 'data:image/') === 0) return null;
    if (!parse_url($url, PHP_URL_SCHEME) and host($base) and host($url))
        $url = (parse_url($base, PHP_URL_SCHEME) ?: 'http') . ':' . $url;
    $normalize = function ($url) {
        $parse = parse_url($url);
        if (!$parse) return null;
        $url = substr($url, strlen("{$parse['scheme']}://{$parse['host']}"));
        if (!$url) $url = '/';
        do {
            $old = $url;
            $url = preg_replace('~/+~', '/', $url);
            $url = preg_replace('~/\./~', '/', $url);
            $url = preg_replace('~/[^/]+/\.\./~', '/', $url);
            $url = preg_replace('~^/\.\./~', '/', $url);
            $url = preg_replace('~\?+$~', '', $url);
        } while ($old != $url);
        return "{$parse['scheme']}://{$parse['host']}{$url}";
    };
    if (host($url)) return $normalize($url);
    if (strpos($url, '/') === 0)
        return $normalize(preg_replace('~(?<!/)/(?!/).*$~', '', $base) . $url);
    if (strpos($url, '?') === 0)
        return $normalize(preg_replace('~\?.*$~', '', $base) . $url);
    return $normalize(preg_replace('~/[^/]+$~', '/', $base) . $url);
}

function toStorage($file, $settings = array()) {
    $dir = isset($settings['dir']) ? $settings['dir'] : false;
    clearstatcache();
    $delete = isset($settings['delete']) ? $settings['delete'] : false;
    // $dir - это каталог! $file - это файл!
    if (!is_file($file) or !is_dir($dir)) {
        _log('INVALID FILE OR DIRECTORY!', E_USER_WARNING);
        return null;
    }
    $dir = rtrim($dir, '/');
    $defaultName = file_get_name($file);
    $defaultExt = file_get_ext($file);
    $name = isset($settings['name']) ? $settings['name'] : $defaultName;
    $ext = isset($settings['ext']) ? $settings['ext'] : $defaultExt;
    //
    $name = preg_replace('~[^a-zA-Z0-9\.-]~', ' ', $name);
    $name = preg_replace('~\s+~', '-', $name);
    if (!$name) $name = mt_rand();
    $target = $dir . '/' . $name . ($ext ? ".{$ext}" : "");
    $i = 0;
    $initialTarget = $target;
    while (file_exists($target)) {
        $target = preg_replace($i ? '~-\d+(\.\w+)$~' : '~(\.\w+)$~', '-' . (++$i) . '$1', $target);
    }
    if ($delete) rename($file, $target);
    else copy($file, $target);
    if ($initialTarget != $target and md5_file($initialTarget) === md5_file($target)) {
        unlink($target);
        $target = $initialTarget;
    }
    return $target;
}

function generator($string, $wrap = true) {
    if ($wrap) $string = '[' . implode('|', nsplit($string)) . ']';
    $check = $string;
    $function = __FUNCTION__;
    $regex = '~\[(((?>[^\[\]]+)|(?R))*)\](\((?P<count>\d+)(?P<sep>[,;]?)(?P<params>.*?)\))?~';
    $string = preg_replace_callback($regex, function ($m) use ($function) {
        $string = call_user_func_array($function, array($m[1], $wrap = false));
        $string = explode('|', $string);
        $chooser = array();
        if (isset($m['count'])) {
            $count = intval($m['count']);
            $params = ($m['sep'] and $m['params']) ? explode($m['sep'], $m['params']) : array();
        } else { $count = 1; $params = array(); }
        $pointer = 0;
        foreach ($string as $s)
            if (preg_match('|^(.*)\((\d+(\.\d+)?)\)$|', $s, $match))
                if (floatval($match[2]) > 0)
                    $chooser[(in_array('ignore', $params) ? 1 : floatval($match[2])) . '!' . $pointer++] = $match[1];
                else ;
            else $chooser['1!' . $pointer++] = $s;
        if (count($chooser) === 1)
            return array_pop($chooser);
        $return = array();
        if ($count < 0) $count = count($chooser) - $count;
        elseif ($count > count($chooser)) $count = count($chooser);
        for ($i = 0; $i < $count; $i++) {
            $sum = array_sum(array_map('floatval', array_keys($chooser)));
            $rnd = mt_rand() / mt_getrandmax();
            $keys = array_keys($chooser);
            foreach ($keys as $chance) {
                $init = $chance;
                $value = $chooser[$chance];
                $chance = floatval($chance) / $sum;
                if ($rnd <= $chance and !isset($return[$value])) {
                    $return[$value] = $value;
                    unset($chooser[$init]);
                    break;
                } else $rnd -= $chance;
            }
        }
        $return = array_values($return);
        if ($params) $sep = array_shift($params);
        else $sep = '';
        return implode($sep, $return);
    }, $string);
    if ($check === $string) return $string;
    else return call_user_func_array($function, array($string, $wrap = false));
}

function config() {
    static $config = array();
    $n = func_num_args();
    if ($n === 0)
        return $config;
    if ($n > 2) return;
    $first = func_get_arg(0);
    if ($n === 1 and $first === ".")
        return $config;
    if ($n === 2 and $first === ".") {
        $config = func_get_arg(1);
        return;
    }
    $first = ltrim($first, '.');
    if (!$first) return;
    $first = explode('.', $first);
    if (count($first) > 2) return;
    $fnmatch0 = (strpos($first[0], '*') !== false or strpos($first[0], '?') !== false);
    $fnmatch = ($fnmatch0 or strpos(implode('', $first), '*') !== false or strpos(implode('', $first), '?') !== false);
    if ($fnmatch and $n === 2) return;
    if ($fnmatch0) {
        $return = array();
        foreach (array_keys($config) as $key) {
            if (!fnmatch($first[0], $key)) continue;
            $args = array(count($first) === 2 ? "{$key}.{$first[1]}" : $key);
            $return[$key] = call_user_func_array(__FUNCTION__, $args);
            if (is_null($return[$key])) unset($return[$key]);
            elseif (!is_array($return[$key]))
                $return[$key] = array($first[1] => $return[$key]);
        }
        return $return;
    }
    if ($fnmatch) {
        $return = array();
        if (!isset($config[$first[0]]) or !is_array($config[$first[0]]))
            return $return;
        foreach (array_keys($config[$first[0]]) as $key) {
            if (!fnmatch($first[1], $key)) continue;
            $args = array("{$first[0]}.{$key}");
            $return[$key] = call_user_func_array(__FUNCTION__, $args);
            if (is_null($return[$key])) unset($return[$key]);
        }
        return $return;
    }
    if ($n === 1 and count($_ = $first) === 1) {
        return isset($config[$_[0]]) ? $config[$_[0]] : null;
    }
    if ($n === 2 and count($_ = $first) === 1) {
        $value = func_get_arg(1);
        if (!is_null($value))
            $config[$_[0]] = $value;
        else unset($config[$_[0]]);
        return;
    }
    if ($n === 1 and count($_ = $first) === 2)
        return isset($config[$_[0]][$_[1]]) ? $config[$_[0]][$_[1]] : null;
    if ($n === 2 and count($_ = $first) === 2) {
        $value = func_get_arg(1);
        if (!is_null($value)) {
            if (!isset($config[$_[0]]))
                $config[$_[0]] = array();
            if (substr($_[1], -2) === '[]') {
                $_[1] = substr($_[1], 0, -2);
                if (!isset($config[$_[0]][$_[1]]))
                    $config[$_[0]][$_[1]] = array();
                $config[$_[0]][$_[1]][] = $value;
            } else $config[$_[0]][$_[1]] = $value;
        } else {
            unset($config[$_[0]][$_[1]]);
        }
        return;
    }
    return;
}

function ini_file_set($file, $key, $value) {
    $key = explode('.', $key);
    if (count($key) != 2 or !$key[0] or !$key[1])
        return false;
    if (!is_file($file))
        $ini = array();
    else $ini = parse_ini_file($file, true);
    if (!array_key_exists($key[0], $ini))
        $ini[$key[0]] = array();
    if (is_null($value)) {
        unset($ini[$key[0]][$key[1]]);
    } else {
        $ini[$key[0]][$key[1]] = $value;
    }
    $save = array();
    $echo = function($_) {
        if (is_numeric($_)) return $_;
        if (ctype_alnum($_)) return $_;
        if ($_ === "") return "";
        return "'{$_}'";
    };
    foreach ($ini as $key => $val) {
        $save[] = sprintf("[%s]", $key);
        foreach ($val as $_key => $_val)
            if (is_array($_val)) {
                foreach ($_val as $_)
                    $save[] = sprintf("%s[] = %s", $_key, $echo($_));
            } else {
                $save[] = sprintf("%s = %s", $_key, $echo($_val));
            }
        $save[] = "\n";
    }
    $head = "; <?php exit();\n; /*";
    $tail = "; */";
    $save = sprintf("%s\n\n%s\n\n%s\n", $head, trim(implode("\n", $save)), $tail);
    $save = str_replace("\n\n\n", "\n\n", $save);
    file_put_contents($file, $save);
    return true;
}

function inputToArgument($input, $trim = true) {
    if (!is_string($input)) return null;
    if ($trim) $input = trim($input);
    if (is_null($input)) return null;
    if ($input === "") return "";
    if (strtolower($input) === 'true') return true;
    if (strtolower($input) === 'false') return false;
    if (strtolower($input) === 'null') return null;
    if (defined($input)) return constant($input);
    if (is_float($input)) return floatval($input);
    if (is_numeric($input)) return intval($input);
    if (preg_match('~^\[(.*)\]$~s', $input, $match)) {
        $match[1] = trim($match[1]);
        if ($match[1] === "") return array();
        $array = array();
        $kvs = preg_split('~\s*,\s*~', $match[1]);
        foreach ($kvs as $kv) {
            $kv = explode('=>', $kv, 2);
            if (count($kv) == 2)
                $array[inputToArgument($kv[0], $trim)] = inputToArgument($kv[1], $trim);
            else $array[] = inputToArgument($kv[0], $trim);
        }
        return $array;
    }
    return $input;
}

function argumentToOutput($argument) {
    $return = function ($_) {
        return $_ . ($_ !== "" ? "\n" : '');
    };
    if (is_null($argument)) return $return("null");
    if ($argument === false) return $return("false");
    if ($argument === true) return $return("true");
    if (is_string($argument)) return $return(trim($argument));
    if (is_float($argument) or is_integer($argument)) return $return($argument);
    if (is_object($argument) and method_exists($argument, '__toString'))
        return $return(trim((string)($argument)));
    if (!is_array($argument)) return "";
    if (count(array_filter(array_keys($argument), 'is_numeric')) == count($argument) and count(array_filter($argument, 'is_array')) == 0)
        return $return(implode("\n", array_map(function ($argument) {
            return trim(argumentToOutput($argument));
        }, array_values($argument))));
    if (is_assoc($argument) and count(array_filter($argument, 'is_array')) == 0) {
        $echo = array();
        foreach ($argument as $k => $v) {
            $v = trim(argumentToOutput($v));
            $echo[] = "{$k} => {$v}";
        }
        return $return(implode("\n", $echo));
    }
    return $return(trim(json_encode($argument, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)));
}

function gocron($file, $dir) {
    if (!is_dir($dir)) mkdir($dir);
    /*
        * * * * *
        | | | | |
        | | | | +----- Дни недели (0-6), 0 - воскресенье
        | | | +------- Месяцы (1-12)
        | | +--------- Дни месяца (1-31)
        | +----------- Часы (0-23)
        +------------- Минуты (0-59)
    */
    // * - любое значение
    // 1 - определенное значение
    // 1-2 - интервал значений
    // 1,4 - список значений
    // */2 - четные значения
    // 1,2-3,*/4 - mix
    if (is_file($file)) $crons = nsplit(template($file));
    else _log(__FUNCTION__ . ": INVALID CRON! | {$file}", E_USER_ERROR);
    $time = func_num_args() > 2 ? func_get_arg(2) : time();
    $trigger = function ($cron) use ($time) {
        $format = "iHdnw";
        for ($i = 0; $i < strlen($format); $i++) {
            $t = intval(date($format[$i], $time));
            foreach (explode(',', $cron[$i]) as $elem) {
                if ($elem === '*') continue 2;
                if (is_numeric($elem) and $elem == $t) continue 2;
                if (
                    preg_match('~^(\d+)-(\d+)$~', $elem, $match)
                        and
                    intval($match[1]) <= $t
                        and
                    $t <= intval($match[2])
                ) continue 2;
                if (
                    preg_match('~^\*/(\d+)$~', $elem, $match)
                        and
                    ($t % $match[1] === 0)
                ) continue 2;
            }
            return false;
        }
        return true;
    };
    foreach ($crons as $cron) {
        if (strpos($cron, '#') === 0) continue;
        $cron = preg_split('~\s+~', $cron, 6);
        if (count($cron) != 6) continue;
        $exec = array_pop($cron);
        if (!$trigger($cron)) continue;
        $name = preg_split('~\s+~', $exec);
        array_walk($name, function (& $_) {
            if (is_file($_)) $_ = basename($_);
            if (is_dir(dirname($_))) $_ = basename($_);
        });
        $name = implode(' ', $name);
        $std = sprintf(
            "%s/%s.%s.txt",
            $dir,
            time(),
            substr(str_replace(' ', '-', normEn($name)), 0, 255)
        );
        shell_exec($_ = sprintf("nohup bash -c %s >%s 2>&1 &", escapeshellarg($exec), escapeshellarg($std)));
        _log(__FUNCTION__ . ': ' . $_);
    }
}

function lexer(& $string, $rules = array()) {
    $escape = false;
    $ignore = false;
    $return = array('output' => array());
    $modify = function ($output, $scheme, $map, $debug) use (& $modify) {
        while ($scheme)
            if (
                preg_match('~^[\w0-9]+~', $scheme, $match)
                    or
                preg_match('~^\((((?>[^\(\)]+)|(?R))*)\)~', $scheme, $match)
            ) {
                $scheme = substr($scheme, strlen($match[0]));
                $while = ($scheme[0] === '*');
                $scheme = ltrim($scheme, '*,');
                do {
                    $old = $output;
                    if (isset($match[1])) {
                        if (is_callable($_ = $modify))
                            $output = $_($output, $match[1], $map, $debug);
                    } else {
                        if (is_callable($_ = $map[$match[0]]))
                            $output = $_($output);
                        if (is_callable($debug) and $old != $output)
                            $debug($match[0], $old, $output);
                    }
                } while ($while and $old != $output);
            } else $scheme = '';
        return $output;
    };
    $stringify = function ($output, $map, $extra = array()) use (& $stringify) {
        $parents = isset($extra['parents']) ? $extra['parents'] : array();
        $next = isset($extra['next']) ? $extra['next'] : null;
        $prev = isset($extra['prev']) ? $extra['prev'] : null;
        $collector = array();
        xpath($output, '/*', function ($tag) use (& $collector, $stringify, $map, $parents, $prev, $next) {
            $body = array();
            $parents[] = $tag->nodeName;
            $length = ($tag->childNodes->length ?: 0);
            for ($i = 0; $i < $length; $i++)
                if ($tag->childNodes->item($i)->nodeType === XML_TEXT_NODE)
                    $body[] = $tag->childNodes->item($i)->nodeValue;
                else {
                    for ($j = 0, $p = array(); $j < $i; $j++)
                        $p[] = $tag->childNodes->item($j)->nodeName;
                    $p = array_reverse($p);
                    for ($j = $i + 1, $n = array(); $j < $length; $j++)
                        $n[] = $tag->childNodes->item($j)->nodeName;
                    $body[] = $_ = call_user_func($stringify, xpath($tag->childNodes->item($i)), $map, array(
                        'parents' => $parents,
                        'prev' => $p,
                        'next' => $n,
                    ));
                }
            array_pop($parents);
            $body = implode('', $body);
            $attr = array();
            if ($tag->hasAttributes())
                foreach ($tag->attributes as $_)
                    $attr[$_->nodeName] = $_->nodeValue;
            if (isset($map[$tag->nodeName]) and is_callable($map[$tag->nodeName]))
                $collector[] = call_user_func($map[$tag->nodeName], $body, array(
                    'attr' => $attr,
                    'parents' => $parents,
                    'prev' => $prev,
                    'next' => $next,
                ));
            elseif (!isset($map[$tag->nodeName])) _warn(__FUNCTION__ . ": NO {$tag->nodeName} IN MAP!");
            elseif (!is_callable($map[$tag->nodeName])) _warn(__FUNCTION__ . ": {$tag->nodeName} IN MAP IS NOT CALLABLE!");
        });
        return implode('', $collector);
    };
    $autoFix = function ($output) {
        $reg = '~<(?P<close>/?)(?P<tag>\w+)\b[^>]*>~';
        $order = array();
        $output = preg_replace_callback($reg, function ($match) use (& $order) {
            if (substr($match[0], -2) === '/>') return $match[0];
            $isClose = (isset($match['close']) and $match['close']);
            if (!$isClose) {
                $order[] = $match['tag'];
                return $match[0];
            }
            if (!$order) return '';
            $last = array_pop($order);
            if ($last === $match['tag']) return $match[0];
            $order[] = $last;
            $index = array_search($match['tag'], $order);
            if ($index === false) return '';
            $return = "";
            $count = count($order);
            for ($i = $index + 1; $i < $count; $i++)
                $return .= "</{$order[$i]}>";
            for ($i = $index; $i < $count; $i++) array_pop($order);
            return $return . $match[0];
        }, $output);
        foreach (array_reverse($order) as $tag) $output .= "</{$tag}>";
        return $output;
    };
    $pushChar = function ($char) use (& $return) {
        $char = esc($char);
        $space = '<space/>';
        $begin = '<string>';
        $bl = mb_strlen($begin);
        $end = '</string>';
        $el = mb_strlen($end);
        $count = count($return['output']);
        if (
            $count > 0
                and
            ($part =  & $return['output'][$count - 1])
                and
            mb_substr($part, 0, $bl) === $begin
                and
            mb_substr($part, -$el) === $end
        ) {
            $part = mb_substr($part, 0, - $el) . $char . $end;
        } elseif ($count > 1) {
            $triggerSpace = false;
            for ($i = $count - 1; $i >= 0; $i--) {
                if ($return['output'][$i] === $space)
                    $triggerSpace = true;
                else break;
            }
            if (
                $triggerSpace
                    and
                $i >= 0
                    and
                mb_substr($return['output'][$i], 0, $bl) === $begin
                    and
                mb_substr($return['output'][$i], - $el) === $end
            ) {
                $spaces = str_repeat(' ', $count - $i - 1);
                $return['output'][$i] = mb_substr($return['output'][$i], 0, -$el) . $spaces . $char . $end;
                $return['output'] = array_slice($return['output'], 0, $i + 1);
            } else $return['output'][] = "{$begin}{$char}{$end}";
        } else $return['output'][] = "{$begin}{$char}{$end}";
    };
    $cloakEscape = 0;
    @ $escapeChar = $rules['escapeChar'];
    @ $escapeSequence = $rules['escapeSequence'];
    if (is_string($escapeSequence)) {
        $_ = array();
        for ($i = 0; $i < strlen($escapeSequence); $i++)
            $_[$escapeSequence[$i]] = $escapeSequence[$i];
        $escapeSequence = $_;
    }
    @ $ignoreBegin = $rules['ignoreBegin'];
    @ $ignoreEnd = $rules['ignoreEnd'];
    @ $endDomain = $rules['endDomain'];
    @ $wrapper = $rules['wrapper'];
    @ $tags = (array)($rules['tags']);
    $string = str_replace("\r\n", "\n", $string);
    while ($escape or mb_strlen($string) > 0) {
        if ($ignoreEnd and $ignore and mb_strpos($string, $ignoreEnd) === 0) {
            $ignore = false;
            $string = mb_substr($string, mb_strlen($ignoreEnd));
            continue;
        }
        if ($ignore) {
            $string = mb_substr($string, 1);
            continue;
        }
        if ($escape and !$string) {
            $escape = false;
            $pushChar($escapeChar);
            continue;
        }
        if ($escape) {
            $escape = false;
            $_ = mb_substr($string, 0, 1);
            $string = mb_substr($string, 1);
            if ($escapeSequence and array_key_exists($_, $escapeSequence)) {
                $pushChar($escapeSequence[$_]);
            } elseif ($cloakEscape === 0) {
                $cloakEscape = 1;
                $string = $escapeChar . $_ . $string;
            } else {
                $cloakEscape = 0;
                $pushChar($escapeChar);
                $string = $_ . $string;
            }
            continue;
        }
        if ($escapeChar and mb_strpos($string, $escapeChar) === 0 and in_array($cloakEscape, array(0, 2))) {
            $escape = true;
            $string = mb_substr($string, mb_strlen($escapeChar));
            continue;
        }
        if ($ignoreBegin and mb_strpos($string, $ignoreBegin) === 0) {
            $ignore = true;
            $string = mb_substr($string, mb_strlen($ignoreBegin));
            continue;
        }
        if (
            $endDomain and is_callable($endDomain)
            and ($result = call_user_func_array($endDomain, array(& $string)))
        ) {
            if (is_assoc($result))
                $return = array_merge_recursive($return, $result);
            elseif (is_string($result)) $return['output'][] = $result;
            break;
        }
        if ($endDomain and is_string($endDomain) and mb_strpos($string, $endDomain) === 0) {
            $string = mb_substr($string, mb_strlen($endDomain));
            if (isset($wrapper[1])) $return['output'][] = $wrapper[1];
            break;
        }
        /* SEARCH TAGS - BEGIN */
        foreach ($tags as $tag)
            if (
                is_array($tag) and isset($tag['beginDomain']) and
                (
                    (($callable = is_callable($tag['beginDomain'])) and ($result = call_user_func_array($tag['beginDomain'], array(& $string))))
                        or
                    (is_string($tag['beginDomain']) and mb_strpos($string, $tag['beginDomain']) === 0)
                )
            ) {
                $cloakEscape = 0;
                if (isset($tag['tags']) and $tag['tags'] === 'inherit')
                    $tag['tags'] = $rules['tags'];
                if (!$callable)
                    $string = mb_substr($string, mb_strlen($tag['beginDomain']));
                if (!$callable and isset($tag['wrapper'][0]))
                    $return['output'][] = $tag['wrapper'][0];
                $value = lexer($string, $tag);
                if ($callable) {
                    if (is_assoc($result))
                        $return = array_merge_recursive($return, $result);
                    elseif (is_string($result)) $return['output'][] = $result;
                }
                if (is_assoc($value))
                    $return = array_merge_recursive($return, $value);
                else $return['output'][] = $value;
                continue 2;
            } elseif (is_array($tag) and count($tag) === 2 and isset($tag[0]) and mb_strpos($string, $tag[0]) === 0) {
                $cloakEscape = 0;
                $string = mb_substr($string, mb_strlen($tag[0]));
                $return['output'][] = $tag[1];
                continue 2;
            } elseif (is_callable($tag) and ($result = call_user_func_array($tag, array(& $string)))) {
                $cloakEscape = 0;
                if (is_assoc($result))
                    $return = array_merge_recursive($return, $result);
                else $return['output'][] = $result;
                continue 2;
            }
        /* SEARCH TAGS - END */
        if (in_array($string[0], array("\n", "\r"))) {
            $string = mb_substr($string, 1);
            $return['output'][] = "<nl/>";
            continue;
        }
        if (ctype_space($string[0])) {
            $string = mb_substr($string, 1);
            $return['output'][] = "<space/>";
            continue;
        }
        if ($cloakEscape === 1) {
            $cloakEscape = 2;
            continue;
        }
        if (mb_strlen($string)) {
            $_ = mb_substr($string, 0, 1);
            $string = mb_substr($string, 1);
            $pushChar($_);
            continue;
        }
    }
    $return['output'] = implode('', $return['output']);
    if (isset($rules['autoFix']) and $rules['autoFix'])
        $return['output'] = $autoFix($return['output']);
    if (isset($rules['modify']) and $rules['modify'] and is_assoc($rules['modify']))
        $return['output'] = call_user_func_array(
            $modify, array(
                $return['output'],
                $rules['modify']['scheme'],
                $rules['modify']['map'],
                (isset($rules['modify']['debug']) ? $rules['modify']['debug'] : null),
            )
        );
    if (isset($rules['stringify']) and $rules['stringify'] and is_assoc($rules['stringify']))
        $return['output'] = call_user_func_array(
            $stringify, array(
                $return['output'],
                $rules['stringify']
            )
        );
    return $return;
}

function SQL() {
    static $link;
    if (!isset($link) or !$link) {
        $link = call_user_func_array('mysqli_connect', func_get_args());
        if (mysqli_connect_errno())
            _err("INVALID SQL CONNECTION: " . mysqli_connect_error());
        return $link;
    }
    $n = func_num_args();
    if (!$n) return $link;
    $args = func_get_args();
    $sql = array_shift($args);
    $sql = trim($sql);
    $isSelect = (stripos($sql, 'select') === 0);
    $isInsert = (stripos($sql, 'insert') === 0);
    $isUpdate = (stripos($sql, 'update') === 0);
    $isDelete = (stripos($sql, 'delete') === 0);
    $isUnknown = !($isSelect or $isInsert or $isUpdate or $isDelete);
    if (!$isUnknown and $args) {
        $collect = array();
        foreach ($args as $arg)
            if (is_assoc($arg))
                foreach ($arg as $k => $v) {
                    $collect[] = sprintf("`%s`", $k);
                    $collect[] = sprintf("'%s'", mysqli_real_escape_string($link, $v));
                }
            else $collect[] = sprintf("'%s'", mysqli_real_escape_string($link, $arg));
        array_unshift($collect, $sql);
        $sql = call_user_func_array('sprintf', $collect);
    }
    $result = mysqli_query($link, $sql);
    if (!$result) _err("INVALID SQL: " . mysqli_sqlstate($link));
    if ($isUnknown) {
        mysqli_free_result($result);
        return true;
    }
    if ($isInsert) {
        mysqli_free_result($result);
        return mysqli_insert_id($link);
    }
    if (!$isSelect) {
        $n = mysqli_num_rows($result);
        mysqli_free_result($result);
        return $n;
    }
    $return = mysqli_fetch_all($result, MYSQLI_NUM);
    mysqli_free_result($result);
    return $return;
}

function _log($msg, $level = E_USER_NOTICE) {
    if (defined('STDIN') and defined('STDERR')) {
        $msg = ($msg[strlen($msg) - 1] === "\n" ? $msg : $msg . "\n");
        fwrite(STDERR, $msg);
    } else {
        trigger_error($msg, $level);
    }
    if ($level === E_USER_ERROR) exit(1);
}

function _warn($msg) {
    _log($msg, E_USER_WARNING);
}

function _err($msg) {
    _log($msg, E_USER_ERROR);
}

require_once(__DIR__ . '/norm.php');
require_once(__DIR__ . '/encdec.php');
require_once(__DIR__ . '/image.php');
