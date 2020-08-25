#!/usr/bin/env php
<?php

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
} elseif (file_exists(__DIR__.'/../../../autoload.php')) {
    /** @noinspection PhpIncludeInspection */
    require __DIR__.'/../../../autoload.php';
} elseif (file_exists(__DIR__.'/../autoload.php')) {
    /** @noinspection PhpIncludeInspection */
    require __DIR__.'/../autoload.php';
} else {
    throw new RuntimeException('Unable to locate autoload.php file.');
}

use Sheronov\PhpMyStem\Utils\System;

echo <<<EOT
This is MyStem downloader from Yandex CDN.

By default it downloads all mystem binaries for Windows, Linux and Macos (-wsl)

For example, if you want download only for Linux, please provide argument -l

-l - bin for Linux
-w - exe for Windows
-m - bin for Macos

EOT;

$oses = [];
$currentKey = null;
for ($i = 1, $total = count($argv); $i < $total; $i++) {
    $args = str_split(trim($argv[$i], '-'));
    foreach ($args as $arg) {
        $oses[] = $arg;
    }
}

System::downloadMystem($oses, true);



