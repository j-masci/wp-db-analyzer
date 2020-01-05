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
define( 'WP_DB_ANALYZER_URL', plugins_url( 'wp-database-analyzer' ) );

/**
 * Bootstraps the plugin, handles the settings, and does a few
 * other things.
 *
 * Class WP_DB_Analyzer_Plugin
 */
Class WP_DB_Analyzer_Plugin
{
    /**
     * Plugin version
     */
    const VERSION = "1.0";

    /**
     * @var array
     */
    public $settings;

    /**
     * Stores the singleton instance of self.
     *
     * @var null|self
     */
    private static $instance;

    /**
     * Helps prevent including (php) files twice.
     *
     * @var array
     */
    private $included_steps = [];

    /**
     * Helps prevent enqueuing scripts/styles twice.
     *
     * @var bool
     */
    private $scripts_enqueued = false;

    /**
     * WP_DB_Analyzer_Plugin constructor.
     * @throws Exception
     */
    public function __construct()
    {

        $this->includes( 1 );

        $this->settings = [
            'menu_slug' => 'wp-database-analyzer',
            'menu_position' => 90,
            'path' => dirname( __FILE__ ),
            'dir' => 'wp-database-analyzer',
            'url' => plugins_url( 'wp-database-analyzer' ),
        ];

        add_action('admin_menu', [$this, 'admin_menu']);

        // add_action('init', [$this, 'init']);
        // add_action('admin_init', [$this, 'admin_init']);
    }

    /**
     * @return WP_DB_Analyzer_Plugin|null
     * @throws Exception
     */
    public static function get_instance(){

        if ( self::$instance ) {
            return self::$instance;
        }

        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Add menu pages to wp-admin.
     *
     * @hooked 'admin_menu'
     */
    public function admin_menu()
    {
        $cap = 'manage_options';
        $menu_slug = $this->settings['menu_slug'];
        $menu_slug_settings = $menu_slug . '-settings';

        add_menu_page( __("Database Analyzer",'wpda'), __("Database Analyzer",'wpda'), $cap, $menu_slug, false, 'dashicons-visibility', $this->settings['menu_position'] );

        add_submenu_page( $menu_slug, __('Analyzer','wpda'), __('Analyzer','wpda'), $cap, $menu_slug, function(){
            $this->includes( 2 );
            $this->enqueue_scripts();
            include $this->settings['path'] . '/tmpl/analyzer.php';

        } );

        add_submenu_page( $menu_slug, __('Settings','wpda'), __('Settings','wpda'), $cap, $menu_slug_settings, function(){
            $this->includes( 2 );
            $this->enqueue_scripts();
            include $this->settings['path'] . '/tmpl/settings.php';
        } );

        add_submenu_page( $menu_slug, __('Examples','wpda'), __('Examples','wpda'), $cap, 'wp-db-analyzer-examples', function(){
            $this->includes( 2 );
            $this->enqueue_scripts();
            include $this->settings['path'] . '/tmpl/examples.php';

        } );
    }

    /**
     * @hooked 'init'
     */
    // public function init(){}

    /**
     * @hooked 'admin_init'
     */
    // public function admin_init(){}

    /**
     * Register/enqueue scripts/styles. You can call many times.
     *
     * Not hooked onto 'admin_enqueue_scripts'. Lazy loaded instead.
     */
    public function enqueue_scripts(){

        if ( $this->scripts_enqueued ) {
            return;
        }

        $this->scripts_enqueued = true;
        $url = $this->settings['url'];

        wp_enqueue_style( 'wp_database_analyzer_css', $url . '/css/master.css', [], self::VERSION );
    }

    /**
     * Include dependencies.
     *
     * On plugin init (every page load), pass in 1.
     *
     * On plugin specific pages, pass in 2.
     *
     * If lazy-loading some of the code causes issues, just pass in 2 on init (and
     * remove remaining references to the method).
     *
     * @param $step
     */
    public function includes( $step )
    {
        if ( in_array( $step, $this->included_steps ) ) {
            return;
        }

        $this->included_steps[] = $step;

        // prefer to use absolute paths to include files.
        $p = $this->settings['path'];

        switch( $step ) {
            case 1:

                break;
            case 2:

                include $p . '/inc/utility-functions.php';
                include $p . '/inc/sql.php';
                include $p . '/inc/tables.php';
                include $p . '/inc/matrix.php';
                include $p . '/inc/reports.php';
                break;
            default:
        }
    }
}

WP_DB_Analyzer_Plugin::get_instance();
