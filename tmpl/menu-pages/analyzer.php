<?php
/**
 * A menu page in wp-admin
 */

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

$plugin = Plugin::get_instance();

$report_id = @$_GET[ 'report' ];

if ( $report_id ) {
    include $plugin->settings[ 'path' ] . '/tmpl/partials/report-single.php';
} else {
    include $plugin->settings[ 'path' ] . '/tmpl/partials/reports-landing.php';
}
