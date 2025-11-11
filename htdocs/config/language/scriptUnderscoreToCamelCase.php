<?php
$files = glob("*.json");
foreach($files as $file)
{
    echo 'Handling '.$file."\n";
    $arr = json_decode(file_get_contents($file), true);
    $arr = unCamelify($arr, ($file == 'de.json' ? true : null));
    file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

function unCamelify($arr, $dmp = null)
{
    $newArr = array();
    foreach ($arr as $k => $v) {
        $camelCase = underScore2CamelCase($k);

        if ($camelCase == $k) {
            if (is_array($v)) {
                $v = unCamelify($v, $dmp != null ? ($dmp.'.'.$k) : null);
            }
            $newArr[$k] = $v;
            continue;
        }
        if (is_array($v)) {
            $v = unCamelify($v, $dmp != null ? ($dmp.'.'.$k) : null);
        }
        echo $dmp.'.'.$k. ' -> ' . $dmp.'.'.$camelCase.PHP_EOL;
        $newArr[$camelCase] = $v;
    }
    return $newArr;
}

function underScore2CamelCase($str)
{
    while (($pos = strpos($str, '_')) !== false) {
        $str[$pos+1] = strtoupper($str[$pos+1]);
        $str = substr($str, 0, $pos).substr($str, $pos + 1);
    }
    return $str;
}
