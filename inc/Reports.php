<?php

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Static method for things related to report(s).
 *
 * Class Reports
 * @package WP_DB_Analyzer
 */
Class Reports {

    /**
     * A string that generally means "all reports"
     */
    const REPORT_ID_ALL = '__all';

    /**
     * Wraps self::render() and adds a few things.
     *
     * @param array $report
     * @param array $request
     * @return false|string
     */
    public static function render_extended( array $report, array $request = [] ) {

        ob_start();

        // get the html and track the time
        list( $report_body, $report_desc, $report_time ) = call_user_func( function () use ( $report, $request ) {

            $t1 = microtime( true );

            $desc = Reports::get_description( $report );

            $body = Reports::render( $report, $request );

            $time = round( microtime( true ) - $t1, 8 );

            return [ $body, $desc, $time ];
        } );

        // $report_desc could contain a link (likely does not)
        // $report_body is html.
        ?>
        <h2><?= sanitize_text_field( $report[ 'title' ] ); ?></h2>
        <?= $report_desc ? '<p>' . $report_desc . '</p>' : ''; ?>
        <p>Report generation time: <strong><?= $report_time; ?></strong> seconds.</p>
        <?= $report_body; ?>
        <?php

        return ob_get_clean();
    }

    /**
     * Invokes the callable render index of $report and returns what it prints.
     *
     * @param array $report
     * @param array $request
     * @return string
     */
    public static function render( array $report, array $request = [] ) {

        ob_start();

        if ( isset( $report[ 'render' ] ) && is_callable( $report[ 'render' ] ) ) {
            call_user_func_array( $report[ 'render' ], [ $report, $request ] );
        } else if ( isset( $report[ 'template' ] ) && file_exists( $report[ 'template' ] ) ) {
            include $report[ 'template' ];
        }

        return ob_get_clean();
    }

    /**
     * Wrap form fields inside of this if your report requires settings.
     *
     * Note: call this function inside of your render callback (or template)
     *
     * @param $report
     * @param $func
     * @return false|string
     */
    public static function render_settings_form( $report, $func ) {

        ob_start();

        $p = Plugin::get_instance();
        $action = get_reports_landing_page_url();

        echo '<form action="' . esc_attr( $action ) . '" method="get" class="wpdba-settings-form" data-report="' . esc_attr( @$report[ 'id' ] ) . '">';

        echo '<input type="hidden" name="page" value="' . $p->settings[ 'menu_slug' ] . '">';
        echo '<input type="hidden" name="report" value="' . esc_attr( @$report[ 'id' ] ) . '">';

        // callback should echo its output
        if ( is_object( $func ) && is_callable( $func ) ) {
            call_user_func( $func );
        }

        echo '</form>';

        return ob_get_clean();
    }

    /**
     * Gets the url where the report is displayed.
     *
     * @param array $report
     * @return string
     */
    public static function get_url( array $report ) {

        return get_report_url( @$report[ 'id' ] );
    }

    /**
     * Returns an anchor tag linking to the report.
     *
     * @param array $report
     * @return false|string
     */
    public static function link( array $report ) {

        $desc = self::get_description( $report );

        return '<a href="' . esc_url( self::get_url( $report ) ) . '" title="' . esc_attr( $desc ) . '">' . sanitize_text_field( @$report[ 'title' ] ) . '</a>';
    }

    /**
     * @param array $report
     * @return string
     */
    public static function get_description( array $report ) {

        if ( isset( $report[ 'get_desc' ] ) && is_callable( $report[ 'get_desc' ] ) ) {
            return call_user_func_array( $report[ 'get_desc' ], $report );
        }

        return "";
    }


    /**
     * Filters the reports which are assigned to a given database table.
     *
     * @param array $reports
     * @param $table
     * @return array
     */
    public static function filter_by_database_table( array $reports, $table ) {

        return array_filter( $reports, function ( $report ) use ( $table ) {

            return isset( $report[ 'tables' ] ) && is_array( $report[ 'tables' ] ) && in_array( $table, $report[ 'tables' ] );
        } );
    }

    /**
     * Prints anchor tags linking to one or more reports.
     *
     * @param $reports
     * @return string
     */
    public static function link_reports( $reports ) {

        return implode( ", ", array_map( function ( $report ) {

            return Reports::link( $report );
        }, $reports ) );
    }
}