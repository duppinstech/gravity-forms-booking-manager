<?php

$root = dirname(__DIR__);
$files = [
    $root . '/gravity-forms-booking-manager.php',
    $root . '/uninstall.php',
];

foreach (glob($root . '/includes/*.php') as $file) {
    $files[] = $file;
}

$pattern = '/(?:__|esc_html__|esc_attr__|_e|esc_html_e|esc_attr_e)\(\s*\'((?:\\\\\'|[^\'])*)\'\s*,\s*\'gravity-forms-booking-manager\'\s*\)/';
$strings = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        continue;
    }

    $lines = preg_split("/\r\n|\n|\r/", $content);
    foreach ($matches[1] as $index => $match) {
        $text = stripcslashes($match[0]);
        $offset = $matches[0][$index][1];
        $line = substr_count(substr($content, 0, $offset), "\n") + 1;
        $strings[$text][] = [
            'file' => ltrim(str_replace($root, '', $file), '/\\'),
            'line' => $line,
        ];
    }
}

ksort($strings);
$out = [];
$out[] = 'msgid ""';
$out[] = 'msgstr ""';
$out[] = '"Project-Id-Version: Gravity Forms Booking Manager 0.4.0\n"';
$out[] = '"Language: \n"';
$out[] = '"MIME-Version: 1.0\n"';
$out[] = '"Content-Type: text/plain; charset=UTF-8\n"';
$out[] = '"Content-Transfer-Encoding: 8bit\n"';
$out[] = '"X-Domain: gravity-forms-booking-manager\n"';
$out[] = '';

foreach ($strings as $text => $locations) {
    foreach ($locations as $location) {
        $out[] = '#: ' . str_replace('\\', '/', $location['file']) . ':' . $location['line'];
    }
    $out[] = 'msgid "' . addcslashes($text, "\"\n\\") . '"';
    $out[] = 'msgstr ""';
    $out[] = '';
}

$dir = $root . '/languages';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

file_put_contents($dir . '/gravity-forms-booking-manager.pot', implode("\n", $out));
echo 'Wrote ' . count($strings) . " strings\n";
