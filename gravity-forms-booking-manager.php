<?php
/**
 * Plugin Name: Gravity Forms Booking Manager
 * Plugin URI: https://github.com/duppinstech/gravity-forms-booking-manager
 * Description: Adds an inline booking calendar and available time slots field to Gravity Forms.
 * Version: 0.4.1
 * Author: Duppins Technology
 * Author URI: https://duppinstech.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gravity-forms-booking-manager
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Update URI: https://github.com/duppinstech/gravity-forms-booking-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

define('GFBM_VERSION', '0.4.1');
define('GFBM_FILE', __FILE__);
define('GFBM_PATH', plugin_dir_path(__FILE__));
define('GFBM_URL', plugin_dir_url(__FILE__));

require_once GFBM_PATH . 'includes/class-gfbm-github-updater.php';

GFBM_GitHub_Updater::boot(
    [
        'file'    => GFBM_FILE,
        'version' => GFBM_VERSION,
        'owner'   => 'duppinstech',
        'repo'    => 'gravity-forms-booking-manager',
        'prefix'  => 'gfbm',
    ]
);

require_once GFBM_PATH . 'includes/class-gravity-forms-booking-manager-plugin-info.php';

GFBM_Plugin_Info::boot(
    [
        'file'         => GFBM_FILE,
        'settings_url' => '',
        'help_url'     => '',
        'support_url'  => 'mailto:support@duppinstech.com',
    ]
);

add_action('gform_loaded', ['GF_Booking_Manager_Bootstrap', 'load'], 5);

class GF_Booking_Manager_Bootstrap {
    public static function load() {
        if (!class_exists('GFForms')) {
            return;
        }

        require_once GFBM_PATH . 'includes/class-gfbm-slot-lock.php';
        require_once GFBM_PATH . 'includes/class-gf-booking-manager.php';
        require_once GFBM_PATH . 'includes/class-gfbm-helpers.php';
        require_once GFBM_PATH . 'includes/class-gf-booking-manager-field.php';
        require_once GFBM_PATH . 'includes/class-gfbm-form-editor.php';
        require_once GFBM_PATH . 'includes/class-gfbm-admin.php';

        GF_Fields::register(new GF_Booking_Manager_Field());

        GF_Booking_Manager::instance();
        GFBM_Form_Editor::init();

        if (is_admin()) {
            GFBM_Admin::init();
        }
    }
}
