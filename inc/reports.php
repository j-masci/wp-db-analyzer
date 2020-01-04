<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the IDs of all or most reports, not including
 * reports that might have been added via hooks.
 *
 * Also has a static method to build most reports.
 *
 * Class Report_IDs
 * @package WP_DB_Analyzer
 */
Class Report_IDs{

    const POST_STATUS = 'post_status';
    const META_KEYS = 'meta_keys';
    const POST_DATES = 'post_dates';
    const TRANSIENT_TIMEOUTS = 'transient_timeouts';

    /**
     * Returns an associative array of class constants mapped to their values.
     *
     * Use this for loops.
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function get_all_ids(){
        $r = new \ReflectionClass(self::class);
        return $r->getConstants();
    }

    /**
     * This is where all reports are registered. (although, there is a hook
     * at the bottom, allowing reports to be registered elsewhere as well).
     *
     * @return array
     */
    public static function build_reports(){

        $reports = [];

        $reports[] = Report::build( Report_IDs::POST_STATUS)->set_render_callback( function( $args ) {

            ?>
            <h2>Post Type / Post Status Report</h2>
            <p>Early work in progress version...</p>
            <?php

            // leave the SQL in another class in case we want to use it separately
            echo render_table( null, SQL::posts_report()->convert_to_record_set_with_headings(), [
                'skip_header' => true,
            ] );
        });

        $reports[] = Report::build( Report_IDs::META_KEYS)->set_render_callback( function( $args ) {

            ?>
            <h2>Meta Key Report</h2>
            <p>Early work in progress version...</p>
            <?php

            echo render_table( null, SQL::post_meta_report()->convert_to_record_set_with_headings(), [
                'skip_header' => true,
            ] );
        });

        $reports[] = Report::build( Report_IDs::POST_DATES)->set_render_callback( function( $args ) {

            ?>
            <h2>Post Published Date Report</h2>
            <p>Early work in progress version...</p>
            <?php

            echo render_table( null, SQL::post_date_report()->convert_to_record_set_with_headings(), [
                'skip_header' => true,
            ] );
        });

        $reports[] = Report::build( Report_IDs::TRANSIENT_TIMEOUTS)->set_render_callback( function( $args ) {

            ?>
            <h2>Transients Timeout Report</h2>
            <p>Early work in progress version...</p>
            <?php

            echo render_table( null, SQL::transients_report()->convert_to_record_set_with_headings( "Hours Until Expiry" ), [
                'skip_header' => true,
            ] );
        });

        /**
         * Add or remove reports (or, let other people break my code).
         *
         * If you add a report, please ensure that it is an object
         * containing at least a report_id and callable _render property.
         */
        $reports = apply_filters( 'wpdba/reports', $reports );

        // index the return values by the report ID
        $ret = [];
        foreach ( $reports as $index => $report ) {
            $ret[$report->report_id] = $report;
        }
        return $ret;
    }
}



