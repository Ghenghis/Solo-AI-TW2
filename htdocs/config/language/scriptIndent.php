<?php
$files = glob("*.json");
foreach($files as $file)
{
    $arr = json_decode(file_get_contents($file), true);
    ksort($arr);
    file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}