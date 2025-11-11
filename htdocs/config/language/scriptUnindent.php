<?php
$files = glob("*.json");
foreach($files as $file)
{
    file_put_contents($file, json_encode(json_decode(file_get_contents($file)), JSON_UNESCAPED_UNICODE));
}