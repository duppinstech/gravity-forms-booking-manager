<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checks GitHub releases for plugin updates (Dashboard → Plugins → Update).
 */
class GFBM_Updater {
    private $plugin_file;
    private $plugin_slug;
    private $version;
    private $github_repo;

    public function __construct($plugin_file, $version, $github_repo) {
        $this->plugin_file  = $plugin_file;
        $this->plugin_slug  = plugin_basename($plugin_file);
        $this->version      = $version;
        $this->github_repo  = $github_repo;

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
    }

    public function check_for_update($transient) {
        if (!is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release();

        if (!$release || empty($release['version']) || empty($release['package'])) {
            return $transient;
        }

        if (version_compare($this->version, $release['version'], '<')) {
            $transient->response[$this->plugin_slug] = (object) [
                'slug'        => dirname($this->plugin_slug),
                'plugin'      => $this->plugin_slug,
                'new_version' => $release['version'],
                'url'         => $release['url'],
                'package'     => $release['package'],
                'tested'      => get_bloginfo('version'),
            ];
        }

        return $transient;
    }

    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || empty($args->slug)) {
            return $result;
        }

        if ($args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }

        $release = $this->get_latest_release();

        if (!$release) {
            return $result;
        }

        return (object) [
            'name'          => 'Gravity Forms Booking Manager',
            'slug'          => dirname($this->plugin_slug),
            'version'       => $release['version'],
            'author'        => '<a href="https://github.com/' . esc_attr($this->github_repo) . '">GitHub</a>',
            'homepage'      => $release['url'],
            'download_link' => $release['package'],
            'sections'      => [
                'description' => 'Inline booking calendar and time slots for Gravity Forms.',
                'changelog'   => $release['notes'],
            ],
        ];
    }

    private function get_latest_release() {
        $cache_key = 'gfbm_github_release_' . md5($this->github_repo);
        $cached    = get_transient($cache_key);

        if (is_array($cached)) {
            return $cached;
        }

        $response = wp_remote_get(
            'https://api.github.com/repos/' . $this->github_repo . '/releases/latest',
            [
                'timeout' => 15,
                'headers' => [
                    'Accept'     => 'application/vnd.github+json',
                    'User-Agent' => 'GravityFormsBookingManager/' . $this->version . '; WordPress/' . get_bloginfo('version'),
                ],
            ]
        );

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }

        $body    = json_decode(wp_remote_retrieve_body($response), true);
        $version = isset($body['tag_name']) ? ltrim((string) $body['tag_name'], 'v') : '';
        $package = '';
        $notes   = isset($body['body']) ? (string) $body['body'] : '';

        if (!empty($body['assets']) && is_array($body['assets'])) {
            foreach ($body['assets'] as $asset) {
                if (!empty($asset['browser_download_url']) && !empty($asset['name']) && preg_match('/\.zip$/i', $asset['name'])) {
                    $package_url = (string) $asset['browser_download_url'];
                    $package_host = wp_parse_url($package_url, PHP_URL_HOST);
                    $allowed_hosts = ['github.com', 'objects.githubusercontent.com', 'release-assets.githubusercontent.com'];

                    if (stripos($package_url, 'https://') === 0 && in_array($package_host, $allowed_hosts, true)) {
                        $package = $package_url;
                        break;
                    }
                }
            }
        }

        $release = [
            'version' => $version,
            'package' => $package,
            'url'     => !empty($body['html_url']) ? $body['html_url'] : 'https://github.com/' . $this->github_repo,
            'notes'   => $notes,
        ];

        set_transient($cache_key, $release, 12 * HOUR_IN_SECONDS);

        return $release;
    }
}
