<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$like = $wpdb->esc_like('gfbm_lock_') . '%';
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like));

delete_site_option('gfbm_github_release');

$transient_like = $wpdb->esc_like('_transient_gfbm_github_release_') . '%';
$timeout_like = $wpdb->esc_like('_transient_timeout_gfbm_github_release_') . '%';
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
    $transient_like,
    $timeout_like
));
