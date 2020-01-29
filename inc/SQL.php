<?php

namespace Database_Analyzer;

use JMasci\MatrixBuilder\Matrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Static methods for building report data.
 *
 * Class SQL
 * @package WP_DB_Analyzer
 */
Class SQL {

    /**
     * @param $table
     * @return int
     */
    public static function count_rows_in_table( $table ) {

        global $wpdb;
        return (int) $wpdb->get_var( "SELECT count(*) AS count FROM " . esc_sql( $table ) . ";" );
    }

    /**
     * @param array $database_tables
     * @return Matrix
     */
    public static function count_table_records_report( array $database_tables ) {

        $matrix = new Matrix();

        foreach ( $database_tables as $table ) {
            $matrix->set( "Records", sanitize_text_field( $table ), self::count_rows_in_table( $table ) );
        }

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function posts_report() {

        global $wpdb;
        $posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} ORDER BY post_type ASC " );

        $matrix = new Matrix();

        foreach ( $posts as $post ) {
            $matrix->set( $post->post_status, $post->post_type, $matrix::get_incrementer() );
        }

        $matrix->set_row_totals( $matrix::get_array_summer() );
        $matrix->set_column_totals( $matrix::get_array_summer() );

        $matrix->sort_columns( function ( $keys ) {

            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * todo: accepting generic date format forces us to query all posts and use php to group which is most likely
     * ok but I don't know the performance implications if we had say 1m rows. It might be worth looking into
     * casting the date in SQL and grouping by that but I don't know the differences between php and sql
     * date formatting.
     *
     * todo: timezone...
     *
     * @param $date_format
     * @return Matrix
     */
    public static function post_date_report( $date_format = "Y-m-d" ) {

        global $wpdb;

        // best to use group by here and let SQL do the hard work since there
        // could be 1m+ rows otherwise.
        $q = "SELECT post_date, post_type FROM {$wpdb->posts} ORDER BY post_date ASC";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        foreach ( $rows as $row ) {

            // date format can be user input and can easily contain xxs.
            // sanitizing the resulting date string should avoid breaking valid date formats using special characters.
            $date = sanitize_text_field( format_date_time_string( $row->post_date, $date_format ) );
            $matrix->set( $date, @$row->post_type, $matrix::get_incrementer( 1 ) );
        }

        $matrix->set_row_totals( $matrix::get_array_summer() );
        $matrix->set_column_totals( $matrix::get_array_summer() );

        $matrix->sort_rows( function ( $keys ) {

            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function post_meta_report() {

        global $wpdb;

        // best to use group by here and let SQL do the hard work since there
        // could be 1m+ rows otherwise.
        // todo: I think this query is returning what we want but i'm not entirely sure
        $q = "SELECT pm.*, p.post_type, count( p.post_type ) AS count FROM {$wpdb->postmeta} AS pm ";
        $q .= "INNER JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id ";
        $q .= "GROUP BY p.post_type, pm.meta_key ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        foreach ( $rows as $row ) {
            $matrix->set( $row->meta_key, $row->post_type, $row->count );
        }

        $matrix->set_row_totals( $matrix::get_array_summer() );
        $matrix->set_column_totals( $matrix::get_array_summer() );

        $matrix->sort_columns( function ( $keys ) {

            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function user_meta_report() {

        global $wpdb;

        // hit post meta table once via sql then we'll loop through it in php
        $user_meta = $wpdb->get_results( "SELECT user_id, meta_key FROM {$wpdb->usermeta} ORDER BY umeta_id" );

        $user_meta_matrix = new Matrix();

        foreach ( $user_meta as $user_meta_record ) {
            $user_meta_matrix->set( $user_meta_record->user_id, $user_meta_record->meta_key, null );
        }

        $matrix = new Matrix();

        // passing in an empty array to this returns no users instead of all of them.
        $users = new \WP_User_Query( [
            'nothing' => 'nothing'
        ] );

        // todo: this loop could get too large. Probably some SQL solution but the issue we have to address is the serialized user roles.
        if ( ! empty( $users->get_results() ) ) {
            foreach ( $users->get_results() as $user ) {
                if ( is_array( $user->roles ) ) {

                    // most users have one role but its possible to have more than 1.
                    // the table data will look odd if users have more than 1 role.
                    foreach ( $user->roles as $role ) {
                        foreach ( $user_meta_matrix->get_row( $user->ID ) as $meta_key => $nothing ) {
                            $matrix->set( $meta_key, $role, $matrix::get_incrementer() );
                        }
                    }
                }
            }
        }

        $matrix->set_row_totals( $matrix::get_array_summer() );
        $matrix->set_column_totals( $matrix::get_array_summer() );

        $matrix->sort_columns( function ( $keys ) {

            return [ 'administrator', 'editor', 'author', 'contributor', 'subscriber' ];
        } );

        return $matrix;
    }

    /**
     * Count transients grouped by expiry time.
     *
     * @return Matrix
     */
    public static function transients_report() {

        // SELECT *  FROM `wpjm_options` WHERE `option_name` LIKE '_transient_%' AND `option_name` NOT LIKE '_transient_timeout_%'
        //

        $t_values = self::get_transients();
        $t_timeouts = self::get_transient_timeouts();

        // todo: timezone?
        $now = time();

        $matrix = new Matrix();

        foreach ( $t_values as $t_name => $t_obj ) {

            // timestamp when transient expires
            $t_expires = isset( $t_timeouts[ $t_name ]->option_value ) ? (int) $t_timeouts[ $t_name ]->option_value : 0;

            // number of hours until this transient expires.
            $hours = $t_expires > 0 ? (int) floor( ( $t_expires - $now ) / 3600 ) : 0;

            $matrix->set( $hours, "transients_count", $matrix::get_incrementer() );
        }

        $matrix->sort_rows( function ( $keys ) {

            sort( $keys, SORT_NUMERIC );
            return $keys;
        } );

        $matrix->set( "total_count", "transients_count", array_reduce( $matrix->get_column( 'transients_count' ), function ( $carry, $count ) {

            return $carry += $count;
        }, 0 ) );

        $matrix->sort_rows( function ( $keys ) {

            unset( $keys[ 'total' ] );
            return array_merge( $keys, [ 'total' ] );
        } );

        return $matrix;
    }

    /**
     * Count terms in each taxonomy and shows the object types each
     * taxonomy is registered to.
     *
     * @return Matrix
     */
    public static function term_meta_report() {

        global $wpdb;

        $q = "        
        SELECT tt.*, t.*, tm.meta_key, count(*) AS count FROM $wpdb->terms AS t
        INNER JOIN $wpdb->termmeta AS tm ON tm.term_id = t.term_id
        INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id
        GROUP BY tm.meta_key 
        ORDER BY tt.taxonomy ASC, t.name ASC        
        ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        if ( $rows ) {
            foreach ( $rows as $row ) {
                $matrix->set( $row->meta_key, $row->taxonomy, $row->count );
            }
        }

        $matrix->set_row_totals( $matrix::get_array_summer() );
        $matrix->set_column_totals( $matrix::get_array_summer() );

        $matrix->sort_columns( function ( $keys ) {

            return [ 'page', 'post', 'attachment' ];
        } );

        return $matrix;
    }

    /**
     * Count terms in each taxonomy and shows the object types each
     * taxonomy is registered to.
     *
     * @return Matrix
     */
    public static function term_taxonomy_report() {

        global $wpdb;
        global $wp_taxonomies;

        $rows = $wpdb->get_results( "SELECT *, count(*) AS count FROM $wpdb->term_taxonomy GROUP BY taxonomy ORDER BY taxonomy;" );

        $matrix = new Matrix();

        if ( $rows ) {
            foreach ( $rows as $row ) {

                // the post types the taxonomy is registered to
                $object_types = isset( $wp_taxonomies[ $row->taxonomy ]->object_type ) && is_array( $wp_taxonomies[ $row->taxonomy ]->object_type ) ? $wp_taxonomies[ $row->taxonomy ]->object_type : [];
                $object_types_str = implode( ", ", $object_types );

                $matrix->set( "count", $row->taxonomy, $row->count );
                $matrix->set( "object_types", $row->taxonomy, $object_types_str );
            }
        }

        $matrix->set_row_totals( function ( $row, $key ) {

            if ( $key === "count" ) {
                return array_sum( $row );
            }

            return "N/A";
        } );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function term_relationships_report() {

        global $wpdb;

        // todo: is this query sufficient for taxonomies registered to multiple object types? (is group by correct?)
        $q = "
        SELECT t.*, tt.*, tr.*, p.post_type, p.ID, count(object_id) AS count FROM $wpdb->term_relationships AS tr
        INNER JOIN $wpdb->posts AS p ON p.ID = tr.object_id
        INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
        INNER JOIN $wpdb->terms AS t ON t.term_id = tt.term_id         
        GROUP BY tt.term_id, p.post_type
        ORDER BY tt.taxonomy ASC, t.name ASC
        ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        if ( $rows ) {
            foreach ( $rows as $row ) {
                $n = sanitize_text_field( $row->name );
                $t = $row->taxonomy;
                $matrix->set( "[$t] $n", $row->post_type, $row->count );
            }
        }

        $matrix->set_column_totals( $matrix::get_array_summer() );
        $matrix->set_row_totals( $matrix::get_array_summer() );

        $matrix->sort_rows( function ( $keys ) {

            asort( $keys );
            return $keys;
        } );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function comments_report() {

        global $wpdb;

        $q = "
        SELECT p.post_type, u.display_name, u.user_login, c.user_id AS user_id, c.comment_ID, count(*) AS count FROM $wpdb->comments AS c
        LEFT JOIN $wpdb->posts AS p ON p.ID = c.comment_post_ID
        LEFT JOIN $wpdb->users AS u ON u.ID = c.user_id        
        GROUP BY p.post_type, u.ID
        ORDER BY p.post_type ASC, u.ID                 
        ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        if ( $rows ) {
            foreach ( $rows as $row ) {

                // check user exists or did exist when the comment was saved.
                if ( $row->user_id && $row->user_id > 0 ) {
                    if ( $row->user_login ) {
                        // unsure if the display name can be empty.
                        $user = $row->display_name ? $row->display_name : '[user_without_a_display_name]';
                    } else {
                        $user = '[deleted_user]';
                    }
                } else {
                    $user = "[no_user]";
                }

                $matrix->set( sanitize_text_field( $user ), $row->post_type, $row->count );
            }
        }

        $matrix->set_column_totals( $matrix::get_array_summer() );
        $matrix->set_row_totals( $matrix::get_array_summer() );

        return $matrix;
    }

    /**
     * @return Matrix
     */
    public static function comment_meta_report() {

        global $wpdb;

        $q = "
        SELECT cm.meta_key, p.post_type, count(*) AS count FROM $wpdb->comments AS c
        INNER JOIN $wpdb->commentmeta AS cm ON cm.comment_id = c.comment_ID
        INNER JOIN $wpdb->posts AS p ON c.comment_post_ID = p.ID
        GROUP BY cm.meta_key, p.post_type
        ORDER BY c.comment_ID ASC         
        ";

        $rows = $wpdb->get_results( $q );

        $matrix = new Matrix();

        if ( $rows ) {
            foreach ( $rows as $row ) {
                $matrix->set( $row->meta_key, $row->post_type, $row->count );
            }
        }

        $matrix->set_column_totals( $matrix::get_array_summer() );

        return $matrix;
    }

    /**
     * Gets wp_options rows for transient VALUES, but not the rows for transient timeouts.
     *
     * The return value adds a property for, and, is indexed by the transient name. The transient
     * name is used in set_transient(), and is a substring of option_name.
     *
     * The option value is not included in the return (for performance reasons).
     *
     * @return array
     */
    public static function get_transients() {

        global $wpdb;
        $transients = $wpdb->get_results( "SELECT option_id, option_name, autoload FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_name NOT LIKE '_transient_timeout_%' " );

        $prefix_length = strlen( "_transient_" );

        $ret = [];

        if ( $transients && is_array( $transients ) ) {
            foreach ( $transients as $trans ) {
                $transient_name = substr( $trans->option_name, $prefix_length, strlen( $trans->option_name ) - $prefix_length );
                $trans->transient_name = $transient_name;
                $ret[ $transient_name ] = $trans;
            }
        }

        return $ret;
    }

    /**
     * Gets wp_options rows for transient TIMEOUTS, but not the rows that store the values.
     *
     * The return value adds a property for, and, is indexed by the transient name. The transient
     * name is used in set_transient(), and is a substring of option_name.
     *
     * The expiry is found in the form of a timestamp in the option_value column.
     *
     * @return array
     */
    public static function get_transient_timeouts() {

        global $wpdb;

        $transients = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' ORDER BY option_id ASC " );

        $prefix_length = strlen( "_transient_timeout_" );

        $ret = [];

        if ( $transients && is_array( $transients ) ) {
            foreach ( $transients as $trans ) {
                $transient_name = substr( $trans->option_name, $prefix_length, strlen( $trans->option_name ) - $prefix_length );
                $trans->transient_name = $transient_name;
                $ret[ $transient_name ] = $trans;
            }
        }

        return $ret;
    }
}