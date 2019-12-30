<?php
/**
 * Plugin Name: WP Database Analyzer
 * Plugin URI:  https://github.com/j-masci/wp-database-analyzer
 * Description: Displays useful information about your WordPress database
 * Version:     1.0
 * Author:      Joel Masci
 * Author URI:  https://github.com/j-masci
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: wp-db-analyzer
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('WP_DB_ANALYZER_DIR', dirname(__FILE__));

Class WP_DB_Analyzer
{
    const MENU_SLUG = 'wp-database-analyzer';
    const MENU_POSITION = 90;

    /**
     * plugin directory path
     */
    const DIR = WP_DB_ANALYZER_DIR;

    public function __construct()
    {
        self::includes();
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    public static function admin_menu()
    {

        $title = "WP Database Analyzer";

        add_menu_page($title, $title, 'manage_options', self::MENU_SLUG, function () {
            include self::DIR . '/tmpl/menu-page.php';
        }, 'dashicons-visibility', self::MENU_POSITION);

    }

    /**
     *
     */
    public static function includes()
    {
        // include self::DIR . '/inc/eg.php';
    }

}

/**
 * Configure the WordPress environment.
 *
 * Class Main
 * @package WP_DB_Analyzer
 */
Class Main
{

    private static $instance;

    public function __construct()
    {


    }
}

function main()
{

    global $_wp_db_analyzer;

    if ($_wp_db_analyzer === null) {
        $_wp_db_analyzer = new Main();
    }
}

main();