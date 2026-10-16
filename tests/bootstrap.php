<?php

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

$GLOBALS['gfbm_now'] = '2026-01-01 08:00:00';
$GLOBALS['gfbm_options'] = [];

function gfbm_test_now($value) {
    $GLOBALS['gfbm_now'] = $value;
}

function gfbm_test_timestamp() {
    return strtotime($GLOBALS['gfbm_now']);
}

function current_time($type, $gmt = 0) {
    $timestamp = gfbm_test_timestamp();

    if ($type === 'Y-m-d') {
        return date('Y-m-d', $timestamp);
    }

    if ($type === 'H') {
        return date('H', $timestamp);
    }

    if ($type === 'i') {
        return date('i', $timestamp);
    }

    if ($type === 'mysql') {
        return date('Y-m-d H:i:s', $timestamp);
    }

    return $timestamp;
}

function absint($value) {
    return abs((int) $value);
}

function __($text, $domain = 'default') {
    return $text;
}

function esc_html__($text, $domain = 'default') {
    return $text;
}

function esc_attr__($text, $domain = 'default') {
    return $text;
}

function esc_html_e($text, $domain = 'default') {
    echo $text;
}

function esc_attr_e($text, $domain = 'default') {
    echo $text;
}

function selected($selected, $current = true, $echo = true) {
    $result = ((string) $selected === (string) $current) ? ' selected="selected"' : '';

    if ($echo) {
        echo $result;
    }

    return $result;
}

function wp_json_encode($data, $options = 0, $depth = 512) {
    return json_encode($data);
}

function sanitize_text_field($text) {
    return trim((string) $text);
}

function is_wp_error($thing) {
    return false;
}

function rgar($array, $key) {
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    }

    return '';
}

function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
    if (array_key_exists($option, $GLOBALS['gfbm_options'])) {
        return false;
    }

    $GLOBALS['gfbm_options'][$option] = $value;
    return true;
}

function get_option($option, $default = false) {
    if (!array_key_exists($option, $GLOBALS['gfbm_options'])) {
        return $default;
    }

    return $GLOBALS['gfbm_options'][$option];
}

function delete_option($option) {
    unset($GLOBALS['gfbm_options'][$option]);
    return true;
}

function update_option($option, $value, $autoload = null) {
    $GLOBALS['gfbm_options'][$option] = $value;
    return true;
}

require_once dirname(__DIR__) . '/includes/class-gfbm-slot-lock.php';
require_once dirname(__DIR__) . '/includes/class-gf-booking-manager.php';
require_once dirname(__DIR__) . '/includes/class-gfbm-helpers.php';
