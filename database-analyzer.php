<?php
/**
 * Database Analyzer
 *
 * Plugin Name: Database Analyzer
 * Plugin URI:  https://wordpress.org/plugins/database-analyzer/
 * Description: Reports on the size and structure of your database. Shows how your data is distributed among your database tables.
 * Version:     1.0
 * Author:      Joel Masci
 * Author URI:  https://github.com/j-masci
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: database-analyzer
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WP_DB_ANALYZER_DIR', dirname( __FILE__ ) );
define( 'WP_DB_ANALYZER_URL', plugins_url( 'wp-database-analyzer' ) );

/**
 * Settings/Bootstrap/etc.
 *
 * Class Plugin
 */
Class Plugin {
    /**
     * Plugin version
     */
    const VERSION = "1.0";

    /**
     * @var array
     */
    public $settings;

    /**
     * Singleton instance
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
     */
    public function __construct() {
        // rtrim might be redundant
        $path = rtrim( dirname( __FILE__ ), '/' );

        $this->settings = [
            'menu_slug' => 'database-analyzer',
            'menu_position' => 90,
            'path' => $path,
            'dir' => 'database-analyzer',
            'url' => rtrim( plugins_url( 'database-analyzer' ), '/' ),
            'report_template_path' => $path . '/reports'
        ];

        $this->includes( 1 );

        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * @return Plugin|null
     */
    public static function get_instance() {

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
    public function admin_menu() {

        $cap = 'manage_options';
        $menu_slug = $this->settings[ 'menu_slug' ];

        add_menu_page( __( "Database Analyzer", 'wpda' ), __( "Database Analyzer", 'wpda' ), $cap, $menu_slug, false, 'dashicons-visibility', $this->settings[ 'menu_position' ] );

        add_submenu_page( $menu_slug, __( 'Analyzer', 'wpda' ), __( 'Analyzer', 'wpda' ), $cap, $menu_slug, function () {
            $this->includes( 2 );
            $this->enqueue_scripts();
            include $this->settings[ 'path' ] . '/tmpl/menu-pages/analyzer.php';
        } );

        // settings page not needed atm
        // $menu_slug_settings = $menu_slug . '-settings';
//        add_submenu_page( $menu_slug, __('Settings','wpda'), __('Settings','wpda'), $cap, $menu_slug_settings, function(){
//            $this->includes( 2 );
//            $this->enqueue_scripts();
//            include $this->settings['path'] . '/tmpl/settings.php';
//        } );
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
    public function enqueue_scripts() {

        if ( $this->scripts_enqueued ) {
            return;
        }

        $this->scripts_enqueued = true;
        $url = $this->settings[ 'url' ];

        wp_enqueue_style( 'wp_database_analyzer_css', $url . '/css/master.css', [], self::VERSION );

        wp_enqueue_style( 'wp_database_analyzer_js', $url . '/js/main.js', [], self::VERSION );
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
    public function includes( $step ) {
        if ( in_array( $step, $this->included_steps ) ) {
            return;
        }

        $this->included_steps[] = $step;

        // plugin absolute path
        $p = $this->settings[ 'path' ];

        switch ( $step ) {
            case 1:
                break;
            case 2:

                include $p . '/inc/etc.php';
                include $p . '/inc/Html_Table.php';
                include $p . '/inc/Report_IDs.php';
                include $p . '/inc/Reports.php';
                include $p . '/inc/Report_Factory.php';
                include $p . '/inc/SQL.php';

                break;
            default:
        }
    }

    /**
     * URL for the reports landing page
     *
     * @return string
     */
    public function get_reports_url() {
        return add_query_arg( [
            'page' => $this->settings[ 'menu_slug' ]
        ], admin_url( 'admin.php' ) );
    }

    /**
     * URL for a single report
     *
     * @param $report_id
     * @return string
     */
    public function get_report_url( $report_id ) {
        // note: $_GET['report'] is used into other places. You can't change 'report'
        // only here.
        return add_query_arg( [
            'page' => $this->settings[ 'menu_slug' ],
            'report' => sanitize_text_field( $report_id ),
        ], admin_url( 'admin.php' ) );
    }
}

include __DIR__ . '/vendor/autoload.php';

Plugin::get_instance();
