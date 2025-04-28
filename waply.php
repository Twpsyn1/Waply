<?php
/*
Plugin Name: Waply – WhatsApp Account Manager & Button Integration
Description: Manage multiple WhatsApp accounts, create customizable chat buttons, and add draggable, themeable WhatsApp buttons to your site.
Version: 0.1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// Autoload includes
foreach ([
    'includes/class-account-manager.php',
    'includes/class-settings.php',
    'includes/class-frontend.php',
    'includes/class-ajax.php',
] as $file) {
    $path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($path)) require_once $path;
}

// Init core classes
define('WAPLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WAPLY_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', function() {
    new Waply_Account_Manager();
    new Waply_Settings();
    new Waply_Frontend();
    new Waply_Ajax();
});
