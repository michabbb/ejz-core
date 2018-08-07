# Core 

Core is a set of help functions. Added to global scope.

### Install

```bash
$ mkdir myproject && cd myproject
$ curl -sS 'https://getcomposer.org/installer' | php
$ php composer.phar require ejz/core:~1.0
```

To use it, just include `vendor/autoload.php` in your script.

### Requirements

PHP 5.5 or above (with cURL and GD library installed).

### Functions

Each function is added to global scope. You shouldn't include any namespace.

### esc

-----

Encode basic HTML chars: `>`, `<` and `&`.

##### *Arguments:*

+ *s:* string to work with
+ *decode:* set this to true to perform backward decoding

##### *Examples:*

```php
$s = esc("HTML: <>&");
// $s => HTML: &lt;&gt;&amp;
$s = esc($s, $decode = true);
// $s => HTML: <>&
```

### fesc

-----

Encode HTML chars: `>`, `<`, `&`, `'` and `"`. Useful for encoding attribute values.

##### *Arguments:*

+ *s:* string to work with
+ *decode:* set this to true to perform backward decoding

##### *Examples:*

```php
$s = fesc("HTML: <>&, '\"");
// $s => HTML: &lt;&gt;&amp;, &#039;&quot;
$s = fesc($s, $decode = true);
// $s => HTML: <>&, '"
```

### cloakHTML

-----

Hide some sensitive value in HTML.

##### *Arguments:*

+ *s:* string to work with

##### *Examples:*

```php
$value = cloakHTML("value");
echo <<<END
<input type="hidden" name="name" value="{$value}" />
END;
// => <input type="hidden" name="name" value="&#118;&#97;&#108;&#117;&#101;" />
```

### str_truncate

-----

Truncate long string.

##### *Arguments:*

+ *string:* goes without saying
+ *len:* maximum length (`40`)
+ *center:* show tail or not (`true`)
+ *replacer:* string that replaces missing part (`...`)

##### *Examples:*

```php
$s = str_truncate("Hello, world!", 5);
// $s => H...!
$s = str_truncate("Hello, world!", 5, $center = false, '..');
// $s => Hel..
```

### mt_shuffle

-----

Shuffles array using `mt_rand()` function.

##### *Arguments:*

+ *items:* reference to an array
+ *seed:* seed for shuffling algorithm (`null`)

##### *Examples:*

```php
$a = array(1, 2, 3, 4, 5);
array_shuffle($a);
// $a => [3, 1, 5, 4, 2]
```

### file_get_ext

-----

Get extension from filename.

##### *Arguments:*

+ *file:* filename

##### *Examples:*

```php
$ext = file_get_ext("/etc/passwd");
// $ext => ""
$ext = file_get_ext("/etc/nginx/nginx.conf");
// $ext => conf
```

### file_get_name

-----

Get name without extension from filename.

##### *Arguments:*

+ *file:* filename

##### *Examples:*

```php
$name = file_get_name("/etc/passwd");
// $name => passwd
$name = file_get_name("/etc/nginx/nginx.conf");
// $name => nginx
```

### curl

-----

Complex function for web crawling. Supports multithreading (MT).

##### *Arguments:*

+ *urls:* single URL or URL list
+ *settings:* associative array:

  Key | Description
  --- | ---
  CURLOPT_* | All such keys are transfered to cURL handler as is.
  threads | Number of threads (`5`).
  retry | Number of retries (`1`).
  sleep | Sleep in seconds before retry (`5`).
  delay | Delay in seconds between MT loops (`0`).
  verbose | Be verbose (`false`).
  format | Return format: `simple`, `array` or `complex`.
  checker | Additional check to trigger retry. Function receives `url` and `ch` (cURL handler).
  modifyContent | Modify HTML content. Function receives `url` and `content`.

##### *Examples:*

```php
$content = curl("http://github.com");
preg_match('~<title>(.*?)</title>~', $content, $title);
@ $title = $title[1];
// $title => How people build software · GitHub
```

```php
$content = curl("http://ejz.ru/ua", array(
    CURLOPT_USERAGENT => "Custom User Agent"
));
// $content => Custom User Agent
```

### template

-----

Implements templating.

##### *Arguments:*

+ *template:* template file
+ *vars:* variables that are used inside

##### *Examples:*

```html
<!-- test.tpl -->
<html>
    <head>
        <title><?=$title?></title>
    </head>
    <body>
        <?=$body?>
    </body>
</html
```

```php
$vars = array(
    'title' => 'test title',
    'body' => 'test body',
);
echo template("test.tpl", $vars);
```

### validateHost

-----

Validate a hostname.

##### *Arguments:*

+ *host:* hostname

### host

-----

Extracts (and validates) hostname from an URL.

##### *Arguments:*

+ *url:* URL

##### *Examples:*

```php
echo host("http://site.com");
// => site.com
```

### curdate

-----

Returns current date in `Y-m-d` format.

##### *Arguments:*

+ *days:* add some days to current date (`0`)

##### *Examples:*

```php
echo curdate();
// => 2016-09-20
echo curdate(-1);
// => 2016-09-19
```

### now

-----

Returns current datetime in `Y-m-d H:i:s` format.

##### *Arguments:*

+ *seconds:* add some seconds to current time (`0`)

##### *Examples:*

```php
echo now();
// => 2016-09-20 12:20:58
echo now(-1);
// => 2016-09-20 12:20:57
```

### getTagAttr

-----

Returns tag attributes (or a certain one).

##### *Arguments:*

+ *tag:* string that represents a tag
+ *attr:* get a certain attribute (`null`)

##### *Examples:*

```php
$tag = '<a href="/about/">About</a>';
$attrs = getTagAttr($tag);
// $attrs => [href => /about/]
$href = getTagAttr($tag, 'href');
// $href => /about/
```

### nsplit

-----

Splits a string to an array, strings are trimed, empty one are filtered out.

##### *Arguments:*

+ *string:* your string you want to explode

##### *Examples:*

```php
$string = "\nhello\n world!\n\n";
$lines = nsplit($string);
// $lines => ["hello", "world"]
```

### is_closure

-----

Check whether or not argument is a callable closure.

##### *Arguments:*

+ *obj:* closure to check

##### *Examples:*

```php
$call = function () {
    echo "Hello, world!\n";
};
$flag = is_closure($call);
// $flag => true
```

### is_ip

-----

Check whether or not argument is a valid IP address.

##### *Arguments:*

+ *ip:* IP to check

##### *Examples:*

```php
$flag = is_ip("192.168.0.1");
// $flag => true
$flag = is_ip("0.0.0.0");
// $flag => true
$flag = is_ip("1.0.0");
// $flag => false
$flag = is_ip("256.0.0.0");
// $flag => false
```

### str_replace_once

-----

Replace just one occurrence of the search string with the replacement string.

##### *Arguments:*

+ *needle:* string you search
+ *replace:* replacement value
+ *haystack*: string being searched

##### *Examples:*

```php
echo str_replace_once("o", "O", "Hello, world!");
// => HellO, world!
```

### Authors

- [Ejz Cernisev](http://ejz.ru) | [GitHub](https://github.com/Ejz) | <ejz@ya.ru>

### License

[Core](https://github.com/Ejz/Core) is licensed under the [WTFPL License](https://en.wikipedia.org/wiki/WTFPL) (see [LICENSE](LICENSE)).
