<?php
/**
 * A menu page in wp-admin
 */

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

$plugin = WP_DB_Analyzer_Plugin::get_instance();

$report_id = @$_GET['report'];

if ( $report_id ) {
    include $plugin->settings['path'] . '/tmpl/partials/report-single.php';
} else {
    include $plugin->settings['path'] . '/tmpl/partials/reports-landing.php';
}
