<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build and registers all available reports.
 *
 * When it comes time to print reports, we can print all, or just a subset of them.
 *
 * Each report is an array with at least an 'id' and 'render' index (possibly more).
 *
 * Class Reports
 * @package WP_DB_Analyzer
 */
Class Reports{

    /**
     * Invokes the render index of an array and returns what it outputs.
     *
     * Your render callback should echo the HTML, but this method will return it.
     *
     * @param array $report
     * @return false|string
     */
    public static function render( array $report ) {
        if ( isset( $report['render'] ) && is_callable( $report['render'] ) ) {
            ob_start();
            call_user_func( $report['render'], $report );
            return ob_get_clean();
        }
    }

    /**
     * Meant to be called from within your render function. Therefore,
     * will print an empty tag if the title index is not set.
     *
     * @param array $report
     */
    public static function print_title( array $report ) {
        ?>
        <h2><?= @$report['title']; ?></h2>
        <?php
    }

    /**
     * Builds and returns all reports (an array of arrays).
     *
     * As a reminder, all heavy lifting must be done in the render callback in each report.
     *
     * Expensive operations must not be done inside of this function otherwise.
     *
     * @return array
     */
    public static function get_all(){

        $reports = [];

        $reports[Report_IDs::POST_STATUS] = [
            'title' => 'Post Status Report',
            'render' => function( $self ){
                self::print_title( $self );
                echo render_table( null, SQL::posts_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[Report_IDs::META_KEYS] = [
            'title' => 'Post Meta Report',
            'render' => function( $self ){
                self::print_title( $self );
                echo render_table( null, SQL::post_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[Report_IDs::POST_DATES] = [
            'title' => 'Post Published Date Report',
            'render' => function( $self ){
                self::print_title( $self );
                echo render_table( null, SQL::post_date_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[Report_IDs::TRANSIENT_TIMEOUTS] = [
            'title' => 'Transient Timeout Report',
            'render' => function( $self ){
                self::print_title( $self );
                echo render_table( null, SQL::transients_report()->convert_to_record_set_with_headings( "Hours Until Expiry" ), [
                    'skip_header' => true,
                ] );
            }
        ];

        // adds id index to each report
        $index = function( array $reports ) {
            foreach ( $reports as $report_id => $report ) {
                $ret[$report_id]['id'] = $report_id;
            }
            return $reports;
        };

        // Add the IDs before applying filters.
        $reports = $index( $reports );

        /**
         * Hook to modify reports array.
         */
        $reports = apply_filters( 'wpdba/reports', $reports );

        // Do the IDs again, after applying filters.
        $reports = $index( $reports );

        return $reports;
    }
}

/**
 * Holds report IDs as class constants.
 *
 * Class Report_IDs
 * @package WP_DB_Analyzer
 */
Class Report_IDs{

    const POST_STATUS = 'post_status';
    const META_KEYS = 'meta_keys';
    const POST_DATES = 'post_dates';
    const TRANSIENT_TIMEOUTS = 'transient_timeouts';

    public static function get_all(){
        $r = new \ReflectionClass(self::class);
        return $r->getConstants();
    }
}