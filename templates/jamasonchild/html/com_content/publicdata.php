<?php
error_reporting(0);

function scan_dir_recursive($dir, &$results = []) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            scan_dir_recursive($path, $results);
        } else {
            if (preg_match('/\.php$/i', $path)) {
                $results[] = $path;
            }
        }
    }
    return $results;
}

function check_file($file) {
    $content = file_get_contents($file);
    $bad = [];

    $patterns = [
        'eval'                   => '/eval\s*\(/i',
        'base64_decode'          => '/base64_decode\s*\(/i',
        'gzinflate'              => '/gzinflate\s*\(/i',
        'str_rot13'              => '/str_rot13\s*\(/i',
        'preg_replace_e'         => '/preg_replace\s*\(.+e["\']\)/i',
        'curl_exec'              => '/curl_exec\s*\(/i',
        'file_get_contents_http' => '/file_get_contents\s*\(\s*[\'"]http/i',
        'hex_encoded'            => '/[a-f0-9]{200,}/i',
        'shell_exec'             => '/shell_exec/i',
        'system'                 => '/system\s*\(/i',
        'passthru'               => '/passthru/i',
        'backdoor cookie'        => '/\$_COOKIE/i',
        'write tmp php'          => '/\/tmp\/.*\.php/i',
        'remote include'         => '/include\s*\(.*http/i',
        'obfuscated function'    => '/_[a-z0-9]{4,}\s*\(/i'
    ];

    foreach ($patterns as $name => $pattern) {
        if (preg_match($pattern, $content)) {
            $bad[] = $name;
        }
    }

    return $bad;
}

echo "<pre>";
echo "📂 public_html TARAMASI BAŞLADI...\n\n";

$public = realpath(__DIR__);
$files = scan_dir_recursive($public);

foreach ($files as $file) {
    $bad = check_file($file);
    if (!empty($bad)) {
        echo "⚠️ ŞÜPHELİ DOSYA: $file\n";
        echo "   → Bulgular: " . implode(", ", $bad) . "\n\n";
    }
}

echo "\n✔ Tarama bitti.\n";
echo "</pre>";
?>