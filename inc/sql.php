<?php

namespace WP_DB_Analyzer;

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

        $ret = new Matrix();

        foreach ($posts as $post) {
            $ret->set_point( $post->post_type, $post->post_status, $ret->get_incrementor(), true);
        }

        return $ret;
    }

    /**
     * @return Matrix
     */
    public static function post_meta_report(){

        // what to show....
        // want to break down all meta keys and associate them with post types giving
        // the counts for each possibility.

        global $wpdb;

        // best to use group by here and let SQL do the hard work since there
        // could be 1m+ rows otherwise.
        // todo: I think this query is returning what we want but i'm not entirely sure
        $q = "SELECT pm.*, p.post_type, count( p.post_type ) AS count FROM {$wpdb->postmeta} AS pm ";
        $q .= "INNER JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id ";
        $q .= "GROUP BY p.post_type, pm.meta_key ";

        $rows = $wpdb->get_results( $q );

        $ret = new Matrix();

        foreach ($rows as $row) {
            $ret->set_point( $row->meta_key, $row->post_type, $row->count, true);
        }

        return $ret;
    }
}