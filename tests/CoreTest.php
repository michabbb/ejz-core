<?php

class CoreTest extends PHPUnit_Framework_TestCase {
    public function testArrayTranspose() {
        $array = array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9)
        );
        $array = array_transpose($array);
        $this->assertEquals($array, array(
            array(1, 4, 7),
            array(2, 5, 8),
            array(3, 6, 9)
        ));
        //
        $array = array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9, 'key' => 'value')
        );
        $array = array_transpose($array);
        $this->assertEquals($array, array(
            array(1, 4, 7),
            array(2, 5, 8),
            array(3, 6, 9),
            'key' => array(2 => 'value')
        ));
    }
    public function testEsc() {
        $this->assertEquals(esc("<&>'\""), "&lt;&amp;&gt;'\"");
        $_ = "<&>'\"";
        $this->assertEquals(esc(esc($_), $decode = true), $_);
    }
    public function testFesc() {
        $this->assertEquals(fesc("<&>'\""), "&lt;&amp;&gt;&#039;&quot;");
        $_ = "<&>'\"";
        $this->assertEquals(fesc(fesc($_), $decode = true), $_);
    }
    public function testHost() {
        $this->assertEquals(host("http://example.com"), "example.com");
        $this->assertEquals(host("http://EXAMPLE.COM"), "example.com");
        $this->assertEquals(host("http://example.com/"), "example.com");
        $this->assertEquals(host("http://example.com/ "), "example.com");
        $this->assertNotEquals(host("http:// example.com/"), "example.com");
        $this->assertNotEquals(host(" http://example.com/"), "example.com");
        $this->assertNotEquals(host("http ://example.com/"), "example.com");
        $this->assertNotEquals(host("http://example . com/"), "example.com");
        $this->assertNotEquals(host("http://example. com/"), "example.com");
        $this->assertNotEquals(host("http://example .com/"), "example.com");
    }
    public function testValidateHost() {
        $this->assertTrue(validateHost("example.com"));
        $this->assertTrue(validateHost("domain"));
        $this->assertTrue(validateHost("EXAMPLE.COM"));
        $this->assertTrue(validateHost("Example.Com"));
        $this->assertTrue(validateHost("10.0.0.1"));
        $this->assertTrue(validateHost("127.0.0.1"));
        $this->assertFalse(validateHost("site . com"));
        $this->assertFalse(validateHost("site. com"));
        $this->assertFalse(validateHost("site .com"));
    }
    public function testCurdate() {
        $this->assertEquals(intval(curdate()) > 2010, true);
    }
    public function testNow() {
        $this->assertEquals(intval(now()) > 2010, true);
        $this->assertEquals(count(explode(' ', now())), 2);
    }
    public function testGetTagAttr() {
        $fesc = fesc($_ = "<&\"'>");
        $tag = "
            <a data-value=1 fesc='{$fesc}' href='/about/' class=\"class\" target=_blank>About</a>
        ";
        $attr = getTagAttr($tag);
        $attrHref = getTagAttr($tag, 'href');
        $attrNone = getTagAttr($tag, 'none');
        $attrFesc = getTagAttr($tag, 'fesc');
        $this->assertEquals($attr['href'], "/about/");
        $this->assertEquals($attr['class'], "class");
        $this->assertEquals($attr['target'], "_blank");
        $this->assertEquals($attr['data-value'], "1");
        $this->assertTrue($attrHref ? true : false);
        $this->assertFalse($attrNone ? true : false);
        $this->assertEquals($attrFesc, $_);
    }
    public function testNsplit() {
        $this->assertEquals(nsplit("one"), array("one"));
        $this->assertEquals(nsplit("
            one
            two
        "), array("one", "two"));
        $this->assertEquals(nsplit("
        "), array());
        $this->assertEquals(nsplit(""), array());
    }
    public function testIsClosure() {
        $closure = function() { ; };
        $this->assertFalse(is_closure('is_closure'));
        $this->assertFalse(is_closure(array($this, 'testIsClosure')));
        $this->assertTrue(is_closure($closure));
    }
    public function testIsIp() {
        $this->assertTrue(is_ip('127.0.0.1'));
        $this->assertTrue(is_ip('192.168.0.1'));
        $this->assertTrue(is_ip('1.1.1.1'));
        $this->assertFalse(is_ip('1.2.3.4.5'));
        $this->assertFalse(is_ip('a.b.c.d'));
        $this->assertFalse(is_ip('1,2.1,3.1,4.1,5'));
        $this->assertFalse(is_ip('1000.1.1.1'));
        $this->assertFalse(is_ip('256.256.256.256'));
        $this->assertFalse(is_ip('256.256.256.'));
        $this->assertFalse(is_ip('0.0.0'));
    }
    public function testStrReplaceOnce() {
        $str = "one one";
        $this->assertEquals(str_replace_once("one", "two", $str), "two one");
        $this->assertEquals(str_replace_once("three", "two", $str), "one one");
        $this->assertEquals(str_replace_once("", "two", $str), "one one");
        $this->assertEquals(str_replace_once("one", "", $str), " one");
    }
    public function testIsAssoc() {
        $this->assertTrue(is_assoc(array()));
        $this->assertTrue(is_assoc(array('key' => 'value')));
        $this->assertFalse(is_assoc(array('value')));
        $this->assertFalse(is_assoc(array('0' => 'value')));
        $this->assertFalse(is_assoc(array('0' => 'value', 'key' => 'value')));
    }
    public function testStrTruncate() {
        $one = "Hello, world!";
        $this->assertEquals(str_truncate($one), $one);
        $this->assertEquals(str_truncate($one, 40), $one);
        $this->assertEquals(str_truncate($one, 6), "He...!");
        $this->assertEquals(str_truncate($one, 6, false), "Hel...");
        $this->assertEquals(str_truncate($one, 6, false, '..'), "Hell..");
        $this->assertEquals(str_truncate($one, 0, false, '..'), "H..");
        $this->assertEquals(str_truncate($one, 0, true, '..'), "H..!");
    }
    public function testMtShuffle() {
        //
        // Typical
        //
        $total = 100;
        $collector = array();
        $one = array("1", "2", "3", "4", "5");
        for($i = 0; $i < $total; $i++) {
            $array = $one;
            mt_shuffle($array);
            $collector[] = implode('', $array);
        }
        $this->assertTrue(count(array_unique($collector)) > 2);
        //
        // Different indices
        //
        $total = 100;
        $collectorV = array();
        $collectorK = array();
        $one = array(10 => "1", 20 => "2", 30 => "3", 40 => "4", 50 => "5");
        for($i = 0; $i < $total; $i++) {
            $array = $one;
            mt_shuffle($array);
            $collectorV[] = implode('', array_values($array));
            $collectorK[] = implode('', array_keys($array));
        }
        $this->assertTrue(count(array_unique($collectorV)) > 2);
        $this->assertTrue(count(array_unique($collectorK)) === 1);
        //
        // Keys
        //
        $total = 100;
        $collectorV = array();
        $collectorK = array();
        $one = array(
            'number-1' => "1",
            'number-2' => "2",
            'number-3' => "3",
            'number-4' => "4",
            'number-5' => "5"
        );
        for($i = 0; $i < $total; $i++) {
            $array = $one;
            mt_shuffle($array);
            $collectorV[] = implode('', array_values($array));
            $collectorK[] = implode('', array_keys($array));
        }
        $this->assertTrue(count(array_unique($collectorV)) > 2);
        $this->assertTrue(count(array_unique($collectorK)) === 1);
    }
    public function testFileGetExt() {
        $this->assertEquals(file_get_ext("/etc/passwd"), "");
        $this->assertEquals(file_get_ext("/var/log/nginx/"), "");
        $this->assertEquals(file_get_ext("/var/log/nginx/access.log"), "log");
        $this->assertEquals(file_get_ext("/var/log/nginx/access.LOG"), "log");
    }
    public function testFileGetName() {
        $this->assertEquals(file_get_name("/etc/passwd"), "passwd");
        $this->assertEquals(file_get_name("/var/log/nginx/"), "");
        $this->assertEquals(file_get_name("/var/log/nginx/access.log"), "access");
        $this->assertEquals(file_get_name("/var/log/nginx/ACCESS.LOG"), "ACCESS");
        $this->assertEquals(file_get_name("/var/archive.tar.gz"), "archive.tar");
    }
    public function testRandFromString() {
        $arr = array(rand_from_string("a"), rand_from_string("b"), rand_from_string("c"));
        $this->assertTrue(count(array_filter($arr, 'is_numeric')) === 3);
        $this->assertTrue(count(array_unique($arr)) === 3);
    }
    public function testGauss() {
        $total = 10000;
        $collector = array();
        for($i = 0; $i < $total; $i++) $collector[] = gauss();
        $min = min($collector);
        $max = max($collector);
        $range = $max - $min;
        $min15 = $min + ($range * 0.15);
        $min30 = $min + ($range * 0.30);
        $min45 = $min + ($range * 0.45);
        $max15 = $max - ($range * 0.15);
        $max30 = $max - ($range * 0.30);
        $max45 = $max - ($range * 0.45);
        $c0_15 = count(array_filter($collector, function ($elem) use ($min, $min15, $min30, $min45) {
            return (($min <= $elem) and ($elem <= $min15));
        }));
        $c15_30 = count(array_filter($collector, function ($elem) use ($min, $min15, $min30, $min45) {
            return (($min15 <= $elem) and ($elem <= $min30));
        }));
        $c30_45 = count(array_filter($collector, function ($elem) use ($min, $min15, $min30, $min45) {
            return (($min30 <= $elem) and ($elem <= $min45));
        }));
        $this->assertTrue($c0_15 < $c15_30);
        $this->assertTrue($c15_30 < $c30_45);
        $c0_15 = count(array_filter($collector, function ($elem) use ($max, $max15, $max30, $max45) {
            return (($max15 <= $elem) and ($elem <= $max));
        }));
        $c15_30 = count(array_filter($collector, function ($elem) use ($max, $max15, $max30, $max45) {
            return (($max30 <= $elem) and ($elem <= $max15));
        }));
        $c30_45 = count(array_filter($collector, function ($elem) use ($max, $max15, $max30, $max45) {
            return (($max45 <= $elem) and ($elem <= $max30));
        }));
        $this->assertTrue($c0_15 < $c15_30);
        $this->assertTrue($c15_30 < $c30_45);
    }
    public function testGetUserAgent() {
        $ua = getUserAgent();
        $this->assertTrue(is_string($ua) and strlen($ua));
        $one = getUserAgent(null, 'seed');
        $two = getUserAgent(null, 'seed');
        $three = getUserAgent(null, 'double seed');
        $this->assertTrue($one and $two and $three);
        $this->assertTrue($one === $two);
        $this->assertTrue($one != $three);
        //
        $chrome = getUserAgent('chrome');
        $this->assertTrue(is_numeric(stripos($chrome, 'chrome')));
        $msie = getUserAgent('msie');
        $this->assertTrue(is_numeric(stripos($msie, 'msie')));
        //
        $one = getUserAgent('Macintosh', 'seed');
        $two = getUserAgent('Macintosh', 'seed');
        $three = getUserAgent('Macintosh', 'double seed');
        $this->assertTrue($one and $two and $three);
        $this->assertTrue($one === $two);
        $this->assertTrue($one != $three);
    }
    public function testRealURL() {
        $baseRoot = "http://site.com/";
        $baseAsd = "http://site.com/asd";
        $baseAsdSlash = "http://site.com/asd/";
        $baseAsdSlashD = "http://site.com/asd/./";
        $baseAsdSlashDD = "http://site.com/asd/../";
        //
        $this->assertTrue(realurl("http://site.com") === "http://site.com/");
        $this->assertTrue(realurl("http://site.com/") === "http://site.com/");
        $this->assertTrue(realurl("http://site.com/./") === "http://site.com/");
        $this->assertTrue(realurl("http://site.com/asd") === "http://site.com/asd");
        $this->assertTrue(realurl("http://site.com/asd/") === "http://site.com/asd/");
        $this->assertTrue(realurl("http://site.com/../") === "http://site.com/");
        $this->assertTrue(realurl("http://site.com/../../../asd/") === "http://site.com/asd/");
        $this->assertTrue(realurl("http://site.com/123/456/../asd/") === "http://site.com/123/asd/");
        //
        $this->assertTrue(realurl("/", "http://site.com/") === "http://site.com/");
        $this->assertTrue(realurl("/", "http://site.com/asd") === "http://site.com/");
        $this->assertTrue(realurl("/./", "http://site.com/asd") === "http://site.com/");
        $this->assertTrue(realurl("/./../", "http://site.com/asd") === "http://site.com/");
        //
        $this->assertTrue(
            realurl("index.html", "http://site.com/asd/contacts.html")
                ===
            "http://site.com/asd/index.html"
        );
        $this->assertTrue(
            realurl("?q=1", "http://site.com/asd/../contacts.html")
                ===
            "http://site.com/contacts.html?q=1"
        );
        $this->assertTrue(
            realurl("../page?q=1", "http://site.com/asd/path/")
                ===
            "http://site.com/asd/page?q=1"
        );
        //
        $this->assertTrue(realurl("//site.com", 'https://site2.com') === "https://site.com/");
        $this->assertTrue(realurl("//site.com", '//site2.com') === "http://site.com/");
    }
    public function testArrayColumn() {
        $one = array(
            array('user_id' => 1, 'user' => 'V'),
            array('user_id' => 2, 'user' => 'M'),
        );
        $arr = array_filter(array_column($one, 'user_id'), 'is_numeric');
        $this->assertTrue(count($arr) === 2);
        $arr = array_filter(array_keys(array_column($one, null, 'user_id')), 'is_numeric');
        $this->assertTrue(count($arr) === 2);
        $arr = array_filter(array_keys(array_column($one, 'user', 'user_id')), 'is_numeric');
        $this->assertTrue(count($arr) === 2);
        $arr = array_filter(array_column($one, 'user', 'user_id'), 'is_numeric');
        $this->assertTrue(count($arr) === 0);
    }
    public function testCurl() {
        $ua = getUserAgent();
        $content = curl('http://ejz.ru/ua', array(
            CURLOPT_USERAGENT => $ua
        ));
        // preg_match("~<textarea[^>]*>([^<]+)</textarea>~i", $content, $match);
        $_ua = trim($content);
        $this->assertTrue($ua === $_ua);
        //
        $result = curl($_ = 'http://ejz.ru/', array('format' => 'array'));
        $this->assertTrue(isset($result[$_]));
    }
    // public function testFilePostContents() {
    //     $url = "http://posttestserver.com/post.php";
    //     $rand = mt_rand() . '';
    //     $content = file_post_contents($url, array(
    //         'random' => $rand
    //     ));
    //     preg_match("~http://.*~", $content, $match);
    //     $content = file_get_contents($match[0]);
    //     $this->assertTrue(is_numeric(strpos($content, $rand)));
    // }
    public function testCheckIP() {
        $this->assertTrue(checkIP("127.0.0.1", array('*')));
        $this->assertTrue(checkIP("127.0.0.1", '*'));
        $this->assertTrue(checkIP("128.0.0.1", '127.*,128.*'));
        $this->assertTrue(checkIP("192.168.0.1", '192.168.0.*'));
        $this->assertTrue(!checkIP("192.168.0.1", '192.168.1.*'));
        $this->assertTrue(!checkIP("192.168.0.1", ''));
    }
    public function testGetopts() {
        $opts = getopts(array(
            'c' => false,
            'b' => false,
            'a' => false,
            'long-arg' => false,
            'long-arg-2' => true,
            'long-arg-3' => true,
        ), explode(' ', './execute -abc arg --long-arg --long-arg-2 arg2 --long-arg-3=arg3 final'));
        $this->assertEquals('./execute', $opts[0]);
        $this->assertEquals('arg', $opts[1]);
        $this->assertEquals(true, $opts['c']);
        $this->assertEquals(true, $opts['b']);
        $this->assertEquals(true, $opts['a']);
        $this->assertEquals(true, $opts['long-arg']);
        $this->assertEquals('arg2', $opts['long-arg-2']);
        $this->assertEquals('arg3', $opts['long-arg-3']);
        $this->assertEquals('final', $opts[2]);
        //
        $opts = getopts(array(
            'a' => false,
            'F' => true,
            'b' => true,
            'c' => false,
        ), explode(' ', './execute -a mmm -F fff'));
        $this->assertEquals('fff', $opts['F']);
        $this->assertTrue($opts['a']);
        $this->assertTrue(!isset($opts['b']));
        $this->assertTrue(!isset($opts['c']));
        //
        $opts = getopts(array(), explode(' ', './execute -a final'));
        $this->assertEquals(array(), $opts);
        //
        $opts = getopts(array(), explode(' ', './execute --long final'));
        $this->assertEquals(array(), $opts);
        //
        $opts = getopts(array('a' => true), explode(' ', './execute -a1'));
        $this->assertEquals(1, $opts['a']);
        //
        $opts = getopts(array('a' => true, 'b' => false), explode(' ', './execute -ba 1'));
        $this->assertEquals(array(), $opts);
        //
        $opts = getopts(array('long' => false), explode(' ', './execute --long=1'));
        $this->assertEquals(array(), $opts);
        //
        $opts = getopts(array(), explode(' ', './execute --long'));
        $this->assertEquals(array(), $opts);
        //
        $opts = getopts(array('a' => true), array('./execute', '-a '));
        $this->assertEquals(" ", $opts['a']);
        //
        $opts = getopts(array('r' => true), array('./execute', '-r', "//text()"));
        $this->assertEquals("//text()", $opts['r']);
    }
    public function testToStorage() {
        $tmp = rtrim(`mktemp --suffix=.png`);
        $dir = rtrim(`mktemp -d`);
        file_put_contents($tmp, $rand = mt_rand());
        $target = toStorage($tmp, array('dir' => $dir));
        $this->assertTrue(file_get_name($tmp) === file_get_name($target));
        $this->assertTrue(file_get_ext($tmp) === file_get_ext($target));
        $this->assertEquals($rand, file_get_contents($target));
        $this->assertTrue(is_file($tmp));
        //
        file_put_contents($tmp, mt_rand());
        $target_1 = toStorage($tmp, array('dir' => $dir));
        $this->assertTrue((file_get_name($target) . "-1") === file_get_name($target_1));
        //
        file_put_contents($tmp, $rand);
        $target_2 = toStorage($tmp, array('dir' => $dir));
        $this->assertTrue($target_2 === $target);
        //
        file_put_contents($tmp, $rand = mt_rand());
        $target = toStorage($tmp, array('dir' => $dir, 'ext' => 'bla', 'delete' => true));
        $this->assertTrue(file_get_name($tmp) === file_get_name($target));
        $this->assertTrue('bla' === file_get_ext($target));
        $this->assertEquals($rand, file_get_contents($target));
        $this->assertTrue(!is_file($tmp));
    }
    public function testEncodeDecode() {
        $string = url_base64_encode("1234567890");
        $this->assertTrue($string === "MTIzNDU2Nzg5MA");
        //
        $string = url_base64_decode($string);
        $this->assertTrue($string === "1234567890");
        //
        $key = '111';
        $one = xencrypt("1234567890", $key);
        $two = xencrypt("1234567890", $key);
        $this->assertTrue($one and $two and $one != $two);
        $this->assertTrue(xdecrypt($one, $key) === "1234567890");
        $this->assertTrue(xdecrypt($two, $key) === "1234567890");
        $this->assertTrue(!xdecrypt($two, $key . '1'));
        //
        $string = base32_encode("1234567890");
        $this->assertTrue(!!preg_match('~^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]+$~', $string));
        //
        $string = base32_decode($string);
        $this->assertTrue($string === "1234567890");
        //
        $key = '111';
        $one = oencrypt("1234567890", $key);
        $two = oencrypt("1234567890", $key);
        $this->assertTrue($one and $two and $one != $two);
        $this->assertTrue(odecrypt($one, $key) === "1234567890");
        $this->assertTrue(odecrypt($two, $key) === "1234567890");
        $this->assertTrue(!odecrypt($two, $key . '1'));
    }
    public function testNorm() {
        $this->assertTrue(normLatin('ÁΓă') === "AGa");
        $this->assertTrue(normLatin('ђÜẽ') === "djUe");
        $this->assertTrue(normLatin('Màl Śir') === "Mal Sir");
        //
        $this->assertTrue(normLatinRu('привет мир') === "privet mir");
        $this->assertTrue(normLatinRu('щука ямка хрен') === "shchuka iamka khren");
        //
        $this->assertTrue(normSpace(" \t \n Привет \t \n мир! \t \n ") === "Привет мир!");
        $this->assertTrue(normTrim(" \t \n Привет \t \n мир! \t \n ") === "Привет \t \n мир!");
        //
        $this->assertTrue(normEn("Hello, world!") === "hello world");
        $this->assertTrue(normRu("Привет, мир!") === "привет мир");
    }
    public function testConfig() {
        $configString = <<<END

[global]
secret = ""

[db-1]
user-1 = user_1
user-2 = user_2

[db-2]
user-2 = user_2
user-3 = user_3

END;
        config('.', parse_ini_string($configString, true));
        $this->assertEquals(config(), config('.'));
        $config = config();
        $this->assertEquals("", $config['global']['secret']);
        $this->assertEquals("user_1", $config['db-1']['user-1']);
        $this->assertEquals("", config('global.secret'));
        $this->assertEquals(array('secret' => ""), config('global'));
        $this->assertEquals(array("user-1" => "user_1", "user-2" => "user_2"), config('db-1'));
        config('global.secret', '1');
        $this->assertEquals("1", config('global.secret'));
        config('global', array('secret' => 2, 'key' => 'value'));
        $this->assertEquals("2", config('global.secret'));
        $this->assertEquals(array('secret' => 2, 'key' => 'value'), config('global'));
        //
        $this->assertEquals(array('db-1' => array('user-1' => 'user_1', 'user-2' => 'user_2'), 'db-2' => array('user-3' => 'user_3', 'user-2' => 'user_2')), config('db-*'));
        $this->assertEquals(array('user-1' => 'user_1', 'user-2' => 'user_2'), config('db-1.user-*'));
        $this->assertEquals(array('user-1' => 'user_1', 'user-2' => 'user_2'), config('db-1.*'));
        $config = config();
        unset($config['global']);
        $this->assertEquals($config, config('db-*.user-*'));
        $this->assertEquals(array('db-1' => array('user-1' => 'user_1')), config('db-*.user-1'));
        //
        config('array.value[]', 'one');
        config('array.value[]', 'two');
        $this->assertEquals(array('one', 'two'), config('array.value'));
    }
    public function testXpath() {
        $xml = "<root> <a> 1 </a> <b>2</b> </root>";
        list($_) = xpath($xml, '/*');
        $this->assertRegexp("~^\s*<root>\s*<a> 1 </a>\s*<b>2</b>\s*</root>\s*$~", $_);
        $_ = xpath($xml);
        $this->assertRegexp("~^\s*<root>\s*<a> 1 </a>\s*<b>2</b>\s*</root>\s*$~", $_);
        list($_) = xpath($xml, '//a/text()');
        $this->assertEquals(' 1 ', $_);
        //
        $xml = "<root><test> \n </test></root>";
        $_ = xpath($xml);
        $this->assertRegexp("~^\s*<root>\s*<test> \n </test>\s*</root>\s*$~", $_);
        //
        $xml = "<root> <a class='cl1 cl2 cl3'> 1 </a> <b>2</b> </root>";
        list($_) = xpath($xml, '//*[class(cl2)]');
        $this->assertRegexp("~^\s*<a\b[^>]*>.*?</a>\s*$~", $_);
        //
        $xml = "<root> <a>1</a> <b>2</b> <c>3</c> </root>";
        $xml = xpath($xml, '//b', function($tag) {
            $tag->parentNode->removeChild($tag);
        });
        $this->assertRegexp("~^\s*<root>\s*<a>1</a>\s*<c>3</c>\s*</root>\s*$~", $xml);
        //
        $xml = "<root><a><one>1</one><two>2</two></a></root>";
        $xml = xpath($xml, '//a', function($tag) {
            $_ = xpath($tag, '//text()');
            $tag->nodeValue = implode(' ', $_);
        });
        $this->assertRegexp("~^\s*<root>\s*<a>1 2</a>\s*</root>\s*$~", $xml);
        //
        $xml = "<root><a><one>1</one><two>2</two></a></root>";
        $xml = xpath($xml, '//a', function($tag) {
            if($tag->hasChildNodes()) {
                $collector = array();
                foreach($tag->childNodes as $child)
                    $collector[] = $child;
                for($i = 0; $i < count($collector); $i++)
                    $tag->parentNode->insertBefore($collector[$i], $tag);
            }
            $tag->parentNode->removeChild($tag);
        });
        $this->assertRegexp("~^\s*<root>\s*<one>1</one>\s*<two>2</two>\s*</root>\s*$~", $xml);
        //
        $xml = "<root> <a>1</a> <b>2</b> <c>3</c> </root>";
        $xml = xpath($xml, '//b', "remove");
        $this->assertRegexp("~^\s*<root>\s*<a>1</a>\s*<c>3</c>\s*</root>\s*$~", $xml);
        //
        $xml = "<root><a remove='1'><b>b</b><c remove='1'></c></a></root>";
        $count = 0;
        $xml = xpath($xml, '//*[@remove="1"]', function($tag) use(& $count) {
            $count += 1;
            $tag->parentNode->removeChild($tag);
        });
        $this->assertRegexp("~^\s*<root/>\s*$~", $xml);
        $this->assertEquals(1, $count);
        //
        $xml = "<root><p>a<br>b</p></root>";
        $texts = xpath($xml, '//p/text()');
        $this->assertEquals("a", $texts[0]);
        $this->assertEquals("b", $texts[1]);
        //
        $xml = <<<END
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>title</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
content
</body>
</html>
END;
        list($text) = xpath($xml, '/html/body/text()');
        $this->assertEquals("content", trim($text));
    }
    public function testGenerator() {
        $go = function($str) { return generator($str); };
        $this->assertEquals($go('[]'), '');
        $this->assertEquals($go('ab'), 'ab');
        $this->assertEquals($go('[a]'), 'a');
        $this->assertEquals($go('[a][b]'), 'ab');
        $this->assertEquals($go('[a[b]][c]'), 'abc');
        $this->assertTrue(!!preg_match('~^a|b$~', $go('a|b')));
        $this->assertTrue(!!preg_match('~^a|b|c$~', $go('a|b|c')));
        $this->assertTrue(!!preg_match('~^a[bc]$~', $go('a[b|c]')));
        $this->assertTrue(!!preg_match('~^(a|b) (1|2)$~', $go('[a|b] [1|2]')));
        foreach (range(1, 1000) as $_)
            $this->assertTrue(!!preg_match('~^(a|b|c) (x|y|z|(X|Y|Z)) (1|2|3)$~', $go('[a|b|c] [x|y|z|[X|Y|Z]] [1|2|3]')));
        $check = array();
        foreach (range(1, 100000) as $_) {
            $this->assertTrue(!!preg_match('~^(a|b|c|)$~', $__ = $go('[a(7)|b|c|]')));
            $check[$__] = (isset($check[$__]) ? $check[$__] : 0) + 1;
        }
        $sum = array_sum($check);
        array_walk($check, function (& $_) use ($sum) {
            $_ = round($_ / $sum, 1);
        });
        $this->assertTrue($check['a'] === 0.7);
        $this->assertTrue($check['b'] === 0.1);
        $this->assertTrue($check['c'] === 0.1);
        $this->assertTrue($check[''] === 0.1);
        //
        //
        //
        $_ = $go("[a|b](2)");
        $this->assertTrue($_ === "ab" or $_ === "ba");
        $_ = $go("[a(0)|b](2)");
        $this->assertTrue($_ === "b");
        $check = array();
        foreach (range(1, 100000) as $_) {
            $__ = $go('[a(9)|b](2)');
            $check[$__] = (isset($check[$__]) ? $check[$__] : 0) + 1;
        }
        $sum = array_sum($check);
        array_walk($check, function (& $_) use ($sum) {
            $_ = round($_ / $sum, 1);
        });
        $this->assertTrue($check['ab'] === 0.9);
        $this->assertTrue($check['ba'] === 0.1);
    }
    public function testImResize() {
        $tests = array(
            "100x200 -> 50x50 = 25x50",
            "100x200 -> 50x50^ = 50x100",
        );
        foreach ($tests as $test) {
            preg_match("~(?P<sw>\d+)x(?P<sh>\d+) -> (?P<size>\S+) = (?P<dw>\d+)x(?P<dh>\d+)~", $test, $m);
            $im = imagecreate($m['sw'], $m['sh']);
            $backgroundColor = imagecolorallocate($im, 0, 0, 0);
            $tmp = rtrim(`mktemp`);
            imagepng($im, $tmp);
            $target = imResize($tmp, [
                'size' => $m['size'],
                'dir' => sys_get_temp_dir(),
                'log' => true,
                'overwrite' => true
            ]);
            $this->assertTrue(is_file($target));
            list($w, $h) = getimagesize($target);
            $this->assertEquals($w, $m['dw']);
            $this->assertEquals($h, $m['dh']);
        }
    }
    public function testImCrop() {
        $tests = array(
            "100x200 -> 50x50+0+0 = 50x50"
        );
        foreach ($tests as $test) {
            preg_match("~(?P<sw>\d+)x(?P<sh>\d+) -> (?P<size>\S+) = (?P<dw>\d+)x(?P<dh>\d+)~", $test, $m);
            $im = imagecreate($m['sw'], $m['sh']);
            $backgroundColor = imagecolorallocate($im, 0, 0, 0);
            $tmp = rtrim(`mktemp`);
            imagepng($im, $tmp);
            $target = imCrop($tmp, [
                'size' => $m['size'],
                'dir' => sys_get_temp_dir(),
                'log' => true,
                'overwrite' => true
            ]);
            $this->assertTrue(is_file($target));
            list($w, $h) = getimagesize($target);
            $this->assertEquals($w, $m['dw']);
            $this->assertEquals($h, $m['dh']);
        }
    }
    public function testImTransparent() {
        $tests = array(
            "100x200 -> white = 100x200"
        );
        foreach ($tests as $test) {
            preg_match("~(?P<sw>\d+)x(?P<sh>\d+) -> (?P<color>\S+) = (?P<dw>\d+)x(?P<dh>\d+)~", $test, $m);
            $im = imagecreate($m['sw'], $m['sh']);
            $backgroundColor = imagecolorallocate($im, 0, 0, 0);
            $tmp = rtrim(`mktemp`);
            imagepng($im, $tmp);
            $target = imTransparent($tmp, [
                'transparent' => $m['color'],
                'dir' => sys_get_temp_dir(),
                'log' => true,
                'overwrite' => true
            ]);
            $this->assertTrue(is_file($target));
            list($w, $h) = getimagesize($target);
            $this->assertEquals($w, $m['dw']);
            $this->assertEquals($h, $m['dh']);
        }
    }
    public function testImBorder() {
        $tests = array(
            "100x200 -> 1 = 102x202",
            "100x200 -> 2 = 104x204"
        );
        foreach ($tests as $test) {
            preg_match("~(?P<sw>\d+)x(?P<sh>\d+) -> (?P<border>\S+) = (?P<dw>\d+)x(?P<dh>\d+)~", $test, $m);
            $im = imagecreate($m['sw'], $m['sh']);
            $backgroundColor = imagecolorallocate($im, 0, 0, 0);
            $tmp = rtrim(`mktemp`);
            imagepng($im, $tmp);
            $target = imBorder($tmp, [
                'border' => $m['border'],
                'dir' => sys_get_temp_dir(),
                'log' => true,
                'overwrite' => true
            ]);
            $this->assertTrue(is_file($target));
            list($w, $h) = getimagesize($target);
            $this->assertEquals($w, $m['dw']);
            $this->assertEquals($h, $m['dh']);
        }
    }
    public function testImCaptcha() {
        $result = imCaptcha(array());
        $this->assertTrue(!!$result);
        $this->assertTrue(strlen($result['word']) > 0);
        $this->assertTrue(is_file($result['file']));
    }
    public function testArgumentToOutput() {
        $this->assertTrue(argumentToOutput(true) === "true\n");
        $this->assertTrue(argumentToOutput("") === "");
        $this->assertTrue(argumentToOutput(array()) === "");
        $this->assertTrue(argumentToOutput(array(1, 2)) === "1\n2\n");
        $this->assertTrue(argumentToOutput(array("asd1", "asd2")) === "asd1\nasd2\n");
        $this->assertTrue(argumentToOutput(array('k' => 'v')) === "k => v\n");
    }
    public function testInputToArgument() {
        define($c = md5(microtime(true)), "bla");
        $this->assertTrue(inputToArgument("true") === true);
        $this->assertTrue(inputToArgument($c) === "bla");
        $this->assertTrue(inputToArgument("[]") === array());
        $this->assertTrue(inputToArgument("[k=>v]") === array('k' => 'v'));
        $this->assertTrue(inputToArgument("[v1,v2]") === array('v1', 'v2'));
        $this->assertTrue(inputToArgument("[v1, {$c}]") === array('v1', 'bla'));
    }
    public function testLexer() {
        $s = " a /**/  \t";
        $return = lexer($s, array());
        $this->assertEquals($return['output'], "<space/><string>a /**/</string><space/><space/><space/>");
        //
        $s = " a \n \r\n";
        $return = lexer($s, array());
        $this->assertEquals($return['output'], '<space/><string>a</string><space/><nl/><space/><nl/>');
        //
        $settings = array('ignoreBegin' => '/*', 'ignoreEnd' => '*/');
        $s = 'a*//*!*/b*//*c';
        $return = lexer($s, $settings);
        $this->assertEquals($return['output'], '<string>a*/b*/</string>');
        //
        $settings = array('escapeChar' => '\\', 'escapeSequence' => array(
            '\\' => '\\',
            '[' => '[',
            ']' => ']',
        ));
        $s = ' \\ \\a \\[]\\\\';
        $return = lexer($s, $settings);
        $this->assertEquals($return['output'], '<space/><string>\ \a []\</string>');
        //
        $settings = array('escapeChar' => '\\', 'escapeSequence' => '\\[]', 'tags' => array(
            array('[b]', '<b>'), array('[/b]', '</b>'),
        ));
        $s = '[][b][/b]\[b][b\]';
        $return = lexer($s, $settings);
        $this->assertEquals($return['output'], '<string>[]</string><b></b><string>[b][b]</string>');
        //
        $s = " a b \t c ";
        $return = lexer($s);
        $this->assertEquals($return['output'], '<space/><string>a b   c</string><space/>');
        //
        $domain = array(
            'escapeChar' => '!',
            'escapeSequence' => "!s",
            'beginDomain' => function(& $string) {
                preg_match('~^\[domain\b([^\]]*)\]~i', $string, $match);
                if (!$match) return;
                $string = mb_substr($string, mb_strlen($match[0]));
                $attr = trim($match[1]) ? " " . trim($match[1]) : '';
                return "<domain{$attr}>";
            },
            'endDomain' => function(& $string) {
                if (stripos($string, $_ = '[/domain]') !== 0) return;
                $string = mb_substr($string, mb_strlen($_));
                return "</domain>";
            }
        );
        $settings = array('escapeChar' => '\\', 'escapeSequence' => array(
            '\\' => '\\',
            '[' => '[',
            ']' => ']',
        ), 'tags' => array($domain, array('[tag]', '<tag/>')));
        $s = '\\a[tag]\[tag][Domain a=1]domain!s!!![/Domain]bla!!';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<string>\a</string><tag/><string>[tag]</string><domain a=1><string>domains!!</string></domain><string>bla!!</string>'
        );
        //
        $domain = array(
            'escapeChar' => '!',
            'escapeSequence' => "!s",
            'beginDomain' => '[domain]',
            'endDomain' => '[/domain]',
            'wrapper' => array('<domain>', '</domain>')
        );
        $settings = array('escapeChar' => '\\', 'escapeSequence' => array(
            '\\' => '\\',
            '[' => '[',
            ']' => ']',
        ), 'tags' => array($domain, array('[tag]', '<tag/>')));
        $s = '\\a[tag]\[tag][domain]domain!s!!![/domain]bla!!';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<string>\a</string><tag/><string>[tag]</string><domain><string>domains!!</string></domain><string>bla!!</string>'
        );
        //
        $domainD = array(
            'beginDomain' => 'd',
            'endDomain' => 'd',
            'wrapper' => array('<d>', '</d>')
        );
        $domainDD = array(
            'beginDomain' => 'dd',
            'endDomain' => 'dd',
            'wrapper' => array('<dd>', '</dd>')
        );
        $settings = array('tags' => array($domainDD, $domainD));
        $s = 'd d dd d dd';
        $return = lexer($s, $settings);
        $this->assertEquals(
            '<d><space/></d><space/><dd><space/><string>d</string><space/></dd>',
            $return['output']
        );
        //
        $settings = array('autoFix' => true, 'tags' => array(
            array('[b]', '<b>'),
            array('[/b]', '</b>'),
            array('[i]', '<i>'),
            array('[/i]', '</i>'),
            array('[self]', '<self/>'),
        ));
        $s = '[b][/i][b]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<b><b></b></b>'
        );
        $s = '[i][b][/i]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<i><b></b></i>'
        );
        $s = '[/i][/i][b]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<b></b>'
        );
        $s = '[b][i]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<b><i></i></b>'
        );
        $s = '[b][self][i]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<b><self/><i></i></b>'
        );
        $s = '[b][i][self]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<b><i><self/></i></b>'
        );
        //
        $tags = array(
            array('[b]', '<b>'),
            array('[/b]', '</b>'),
            array('[i]', '<i>'),
            array('[/i]', '</i>'),
            array('[self]', '<self/>'),
        );
        $ltrim = function ($s) {
            return preg_replace('~^<space/>~', '', $s);
        };
        $rtrim = function ($s) {
            return preg_replace('~<space/>$~', '', $s);
        };
        $count = 0;
        $modify = array(
            'scheme' => '(ltrim,rtrim)*',
            'map' => array('ltrim' => $ltrim, 'rtrim' => $rtrim),
            'debug' => function () use (& $count) {
                $count += 1;
            }
        );
        $settings = array('modify' => $modify, 'tags' => $tags);
        $s = '  [b][/b] ';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<b></b>'
        );
        $this->assertTrue($count == 3);
        //
        $count = 0;
        $modify = array(
            'scheme' => '(ltrim),(rtrim)',
            'map' => array('ltrim' => $ltrim, 'rtrim' => $rtrim),
            'debug' => function () use (& $count) {
                $count += 1;
            }
        );
        $settings = array('modify' => $modify, 'tags' => $tags);
        $s = '  [b][/b] ';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<space/><b></b>'
        );
        $this->assertTrue($count == 2);
        //
        $tags = array(
            array('[root]', '<root>'),
            array('[/root]', '</root>'),
            array('[b]', '<b>'),
            array('[/b]', '</b>'),
            array('[i]', '<i>'),
            array('[/i]', '</i>'),
            array('[self]', '<self tag="value" />'),
        );
        $self = array();
        $stringify = array(
            'root' => function ($_) {
                return $_;
            },
            'b' => function ($_) {
                return sprintf("<strong>%s</strong>", esc($_));
            },
            'i' => function ($_) {
                return sprintf("<em>%s</em>", esc($_));
            },
            'self' => function ($_, $extra) use (& $self) {
                $self[] = $extra;
                return '<self/>';
            },
            'string' => function ($_) {
                return $_;
            },
        );
        $settings = array('stringify' => $stringify, 'tags' => $tags);
        $s = '[root][b]bold[/b][/root]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<strong>bold</strong>'
        );
        $s = '[root][self][self][/root]';
        $return = lexer($s, $settings);
        $this->assertEquals(
            $return['output'],
            '<self/><self/>'
        );
        // $this->assertTrue($self[0]['prev'][0] === 'self');
        $this->assertTrue($self[0]['next'][0] === 'self');
        $this->assertTrue($self[0]['parents'][0] === 'root');
        $this->assertTrue($self[0]['attr']['tag'] === 'value');
        $this->assertTrue($self[1]['prev'][0] === 'self');
        // $this->assertTrue($self[1]['next'][0] === 'self');
        $this->assertTrue($self[1]['parents'][0] === 'root');
        $this->assertTrue($self[1]['attr']['tag'] === 'value');
    }
}
