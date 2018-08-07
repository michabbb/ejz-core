<?php

function imWrapper($image, $settings = array()) {
    $output = shell_exec("/usr/bin/env convert -version");
    if (stripos($output, "ImageMagick") === false)
        _log("ImageMagick is not installed!", E_USER_ERROR);
    if (!extension_loaded('gd'))
        _log("GD library is not installed!", E_USER_ERROR);
    if (!is_file($image) or !getimagesize($image) or !$settings['action'])
        return null;
    if (!isset($settings['dir'])) $settings['dir'] = dirname($image);
    if (!is_dir($settings['dir'])) mkdir($settings['dir']);
    $md5 = md5(md5_file($image) . $settings['action']);
    $name = $settings['prefix'] . substr($md5, 0, 10);
    $ext = image_type_to_extension(exif_imagetype($image));
    if (!$ext) {
        _log("Invalid extension: {$image}", E_USER_WARNING);
        return null;
    }
    $target = "{$settings['dir']}/{$name}{$ext}";
    $log = isset($settings['log']) ? $settings['log'] : false;
    $overwrite = isset($settings['overwrite']) ? $settings['overwrite'] : false;
    if (!is_file($target) or $overwrite) {
        $output = shell_exec($cmd = sprintf(
            "convert %s %s %s 2>&1",
            escapeshellarg($image),
            $settings['action'],
            escapeshellarg($target)
        ));
        $ok = is_file($target);
        if ($ok and $log) _log($cmd . ': ' . trim($output), E_USER_NOTICE);
        elseif (!$ok) {
            _log($cmd . ': ' . trim($output), E_USER_WARNING);
            return null;
        }
    }
    return $target;
}

function imResize($image, $settings) {
    if (!$settings['size']) return null;
    $size = escapeshellarg($settings['size']);
    $_ = array('action' => "-resize {$size}");
    return imWrapper($image, $_ + $settings + ['prefix' => __FUNCTION__]);
}

function imTransparent($image, $settings) {
    if (!isset($settings['transparent'])) $settings['transparent'] = 'white';
    $transparent = escapeshellarg($settings['transparent']);
    $_ = array('action' => "-fuzz 5% -transparent {$transparent}");
    return imWrapper($image, $_ + $settings + ['prefix' => __FUNCTION__]);
}

function imCrop($image, $settings) {
    if (!$settings['size']) return null;
    $size = escapeshellarg($settings['size']);
    $_ = array('action' => "-gravity Center -crop {$size} +repage");
    return imWrapper($image, $_ + $settings + ['prefix' => __FUNCTION__]);
}

function imBorder($image, $settings) {
    if (!isset($settings['border'])) $settings['border'] = '1';
    if (!isset($settings['borderColor'])) $settings['borderColor'] = 'black';
    $border = escapeshellarg($settings['border']);
    $borderColor = escapeshellarg($settings['borderColor']);
    $_ = array('action' => "-border {$border} -bordercolor {$borderColor}");
    return imWrapper($image, $_ + $settings + ['prefix' => __FUNCTION__]);
}

function imCaptcha($settings = array()) {
    $width = isset($settings['width']) ? $settings['width'] : null;
    $height = isset($settings['height']) ? $settings['height'] : 50;
    $length = isset($settings['length']) ? $settings['length'] : 6;
    $padding = isset($settings['padding']) ? $settings['padding'] : 4;
    $padding = intval($padding) > 0 ? intval($padding) : 4;
    $length = intval($length) > 0 ? intval($length) : 6;
    $height = intval($height) > 0 ? intval($height) : 50;
    $paddingLR = $padding;
    $paddingTB = $padding;
    $size = $height - 2 * $padding;
    $fonts = glob(__DIR__ . "/fonts/*.ttf");
    if (!$fonts) return array();
    $font = $fonts[mt_rand(0, count($fonts) - 1)];
    $box = imagettfbbox($size, 0, $font, "H");
    $_width = abs($box[2] - $box[0]);
    $_shift = abs($box[6] - $box[0]);
    $_height = abs($box[7] - $box[1]);
    //
    if (!$width) $width = ($length * $_width) + (($length + 1) * $padding) + $_shift;
    //
    $image = imagecreatetruecolor($width, $height);
    if (!$image) return array();
    $letters = 'ABCDEFGHJKMNPRSTUVWXYZ'; // no "I", "L", "O", "Q"
    $backgroundColor = imagecolorallocate($image, 255, 255, 255);
    $lineColor = imagecolorallocate($image, 64, 64, 64);
    $pixelColor = imagecolorallocate($image, 0, 0, 255);
    $textColor = imagecolorallocate($image, 0, 0, 0);
    imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);
    // 3 lines
    imageline($image, 0, mt_rand() % $height, $width, mt_rand() % $height, $lineColor);
    imageline($image, 0, mt_rand() % $height, $width, mt_rand() % $height, $lineColor);
    imageline($image, 0, mt_rand() % $height, $width, mt_rand() % $height, $lineColor);
    // add noise
    for ($i = 0; $i < 500; $i++)
        imagesetpixel($image, mt_rand() % $width, mt_rand() % $height, $pixelColor);
    $len = strlen($letters);
    $word = "";
    for ($i = 0; $i < $length; $i++) {
        $letter = $letters[mt_rand(0, $len - 1)];
        $angle = (-5 + mt_rand(0, 10));
        imagettftext(
            $image,
            $size,
            $angle,
            ($i * ($padding + $_width)) + $padding,
            $padding + $_height,
            $textColor,
            $font,
            $letter
        );
        $word .= $letter;
    }
    $file = rtrim(`mktemp --suffix=.png`);
    imagepng($image, $file);
    imagedestroy($image);
    $imBorder = array();
    if (isset($settings['borderColor']))
        $imBorder['borderColor'] = $settings['borderColor'];
    if (isset($settings['border']))
        $imBorder['border'] = $settings['border'];
    $file = imBorder($file, $imBorder);
    if (!$file) return array();
    return array('file' => $file, 'word' => $word);
}
