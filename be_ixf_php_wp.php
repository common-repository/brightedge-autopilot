<?php
/**
 * Plugin Name: BrightEdge Autopilot
 * Plugin URI: https://www.brightedge.com/
 * Description: Enables BrightEdge Autopilot on your Wordpress site.
 * Version: 1.1.16
 * Author: BrightEdge
 * Author URI: https://www.brightedge.com/
 * Text Domain: be_ixf_php_wp
 * Domain Path: /languages/
 * Copyright: BrightEdge Technologies, Inc.
 * License: www.brightedge.com/infrastructure-product-terms
 */

//Your access to and use of BrightEdge Foundations is governed by the
//Infrastructure Product Terms located at: www.brightedge.com/infrastructure-product-terms.
//Customer acknowledges and agrees it has read, understands and agrees to be bound by the
//Infrastructure Product Terms.

namespace BrightEdge\Wordpress;

include_once "src/Loader.php";

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('BEIXF_VENDOR_DIR', dirname(__FILE__).'/vendor');

if (file_exists(BEIXF_VENDOR_DIR.'/autoload.php')) {
    require_once(BEIXF_VENDOR_DIR.'/autoload.php');
}

register_uninstall_hook(
    __FILE__,
    [
        __NAMESPACE__ . '\BEIXFLoader',
        'uninstallHook',
    ]
);

register_activation_hook(
    __FILE__,
    [
        __NAMESPACE__ . '\BEIXFLoader',
        'activationHook',
    ]
);

/*********** custom code start ******************/

// Check if WP-CLI is active to prevent conflicts with other plugins
if (defined('WP_CLI') && WP_CLI && php_sapi_name() == 'cli') {
    // Exit early if WP-CLI is active to skip frontend and admin hooks
    return;
}
/*********** custom code end ******************/


// Ensure actions and loader are only run in non-CLI mode
if (!defined('WP_CLI') || !WP_CLI) {
    if (function_exists('add_action')) {
        add_action('init', function () {
            $base_path = plugin_dir_path(__FILE__);
            new BEIXFLoader($base_path);
        });

        // If using widgets: sets up sidebar and widgets
        add_action('widgets_init', __NAMESPACE__ . '\BEIXFLoader::registerWidget');
    }
}