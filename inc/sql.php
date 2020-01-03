<?php

namespace WP_DB_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Store queries or things related to queries here for easier
 * re-use.
 *
 * Class SQL
 * @package WP_DB_Analyzer
 */
Class SQL
{
    /**
     * @return Matrix
     */
    public static function posts_report()
    {
        global $wpdb;
        $posts = $wpdb->get_results("SELECT * FROM {$wpdb->posts} ORDER BY post_type ASC ");

        $matrix = new Matrix();

        foreach ($posts as $post) {
            $matrix->set( $post->post_status, $post->post_type, $matrix->get_incrementer() );
        }

        $matrix->sort_columns( function( $keys ) {
            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function post_date_report(){

        global $wpdb;

        // best to use group by here and let SQL do the hard work since there
        // could be 1m+ rows otherwise.
        // todo: I think this query is returning what we want but i'm not entirely sure
        $q = "SELECT post_date, post_type, count(*) AS count FROM {$wpdb->posts} GROUP BY CAST(post_date AS DATE), post_type ORDER BY post_date DESC ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        foreach ($rows as $row) {
            $matrix->set( @$row->post_type, format_date_time_string( @$row->post_date, "Y-m-d" ), @$row->count );
        }

        $matrix->sort_rows( function( $keys ) {
            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function post_meta_report(){

        global $wpdb;

        // best to use group by here and let SQL do the hard work since there
        // could be 1m+ rows otherwise.
        // todo: I think this query is returning what we want but i'm not entirely sure
        $q = "SELECT pm.*, p.post_type, count( p.post_type ) AS count FROM {$wpdb->postmeta} AS pm ";
        $q .= "INNER JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id ";
        $q .= "GROUP BY p.post_type, pm.meta_key ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        foreach ($rows as $row) {
            $matrix->set( $row->meta_key, $row->post_type, $row->count );
        }

        $matrix->sort_columns( function( $keys ) {
            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * Count transients grouped by expiry time.
     *
     * @return Matrix
     */
    public static function transients_report(){

        // SELECT *  FROM `wpjm_options` WHERE `option_name` LIKE '_transient_%' AND `option_name` NOT LIKE '_transient_timeout_%'
        //

        $t_values = get_transients();
        $t_timeouts = get_transient_timeouts();

        // todo: timezone?
        $now = time();

        $matrix = new Matrix();

        foreach ( $t_values as $t_name => $t_obj ) {

            // timestamp when transient expires
            $t_expires = isset( $t_timeouts[$t_name]->option_value ) ? (int) $t_timeouts[$t_name]->option_value : 0;

            // number of hours until this transient expires.
            $hours = $t_expires > 0 ? (int) floor( ( $t_expires - $now ) / 3600 ) : 0;

            $matrix->set( $hours, "Number of Transients", $matrix->get_incrementer() );
        }

        $matrix->sort_rows( function( $keys ) {
            sort( $keys, SORT_NUMERIC );
            return $keys;
        });

        return $matrix;
    }
}