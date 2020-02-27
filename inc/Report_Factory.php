<?php

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Holds the build method which builds all the reports.
 *
 * Class Reports
 * @package WP_DB_Analyzer
 */
Class Report_Factory {

    /**
     * Build all reports. Should not run any database queries or do
     * anything expensive if the reports are built correctly.
     *
     * @return array
     */
    public static function build() {

        global $wpdb;

        $p = Plugin::get_instance();
        $report_template_path = $p->settings[ 'report_template_path' ];
        $reports = [];

        $reports[ Report_IDs::POST_STATUS ] = [
            'tables' => [ $wpdb->posts ],
            'title' => 'Post Status Report',
            'get_desc' => function () {

                return "Counts posts grouped by post status and post type.";
            },
            'render' => function ( $report, $request ) {

                echo Html_Table::render( null, SQL::posts_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[ Report_IDs::POST_META ] = [
            'tables' => [ $wpdb->postmeta ],
            'title' => 'Post Meta Report',
            'get_desc' => function () {

                return "Counts records in wp_postmeta grouped by meta key and post type.";
            },
            'render' => function ( $report, $request ) {

                echo Html_Table::render( null, SQL::post_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[ Report_IDs::POST_DATES ] = [
            'tables' => [ $wpdb->posts ],
            'title' => 'Post Published Date Report',
            'default_date_format' => 'Y-m-d',
            'preset_date_formats' => [
                'Y-m-d',
                'F d, Y',
                'Y-m',
                'F Y',
            ],
            'get_desc' => function () {

                return "Counts the number of posts published within date ranges and grouped by post type.";
            },
            // this report is long, so we'll use a template instead.
            'template' => $report_template_path . '/post-date.php',
        ];

        $reports[ Report_IDs::TRANSIENT_TIMEOUTS ] = [
            'tables' => [ $wpdb->options ],
            'title' => 'Transient Timeout Report',
            'get_desc' => function () {

                return "Counts the number of transients in wp_options grouped by the time until they expire.";
            },
            'render' => function ( $self ) {

                echo Html_Table::render( null, SQL::transients_report()->convert_to_record_set_with_headings( "Hours Until Expiry" ), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[ Report_IDs::USER_META ] = [
            'tables' => [ $wpdb->usermeta ],
            'title' => 'User Meta Report',
            'get_desc' => function () {

                return "Counts rows in the wp_usermeta table grouped by meta keys and user roles.";
            },
            'render' => function ( $report, $request ) {

                echo Html_Table::render( null, SQL::user_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );
            }
        ];

        $reports[ Report_IDs::TERMS ] = [
            'tables' => [ $wpdb->terms ],
            'title' => 'Terms/Taxonomies Report(s)',
            'get_desc' => function(){
                return "Runs several reports related terms and taxonomies.";
            },
            'render' => function ( $report, $request ) {

                global $wpdb;

                echo '<h2>Records in Tables</h2>';
                echo '<p>The terms table grows when you add insert new terms (ie. categories, tags, etc.). The term_taxonomy table is usually the size of the terms table but can be larger when some taxonomies are assigned to multiple object types. The termmeta table grows when terms store additional meta information (like custom fields). The term_relationships table grows when objects (ie. post types) are put into terms/categories/tags.</p>';

                echo render_table_counts( [ $wpdb->terms, $wpdb->term_taxonomy, $wpdb->termmeta, $wpdb->term_relationships ] );

                echo '<h2>Terms in Taxonomies</h2>';
                echo '<p>Taxonomies (columns) vs. the number of terms and the object types that the taxonomy is assigned to. The columns and the term counts are derived from the database, therefore, if a taxonomy is registered but has no terms, it will not show up (as a column). The object types are derived from code and therefore they depend on the state of your active plugins and themes. If a taxonomy is not given an object type, this means that the data still exists in the database but the plugin or theme responsible for the taxonomy has likely changed or been de-activated (ie. the data is probably stale).</p>';

                echo Html_Table::render( null, SQL::term_taxonomy_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );

                echo '<h2>Objects in Terms</h2>';
                echo '<p>Number of objects (usually, posts) assigned to terms (rows) categorized by object type (columns). When a taxonomy is registered to multiple object types, you might see multiple entries in the same row. Generally speaking, you can find the same numbers natively through WordPress by looking at the Count column when viewing a term.</p>';

                echo Html_Table::render( null, SQL::term_relationships_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );

                echo '<h2>Term Meta Keys</h2>';

                echo Html_Table::render( null, SQL::term_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );

            }
        ];

        $reports[ Report_IDs::COMMENTS ] = [
            'tables' => [ $wpdb->comments ],
            'title' => 'Comments Report',
            'get_desc' => function () {

                return "Runs several reports related to comments, post types, and users.";
            },
            'render' => function ( $report, $request ) {

                global $wpdb;

                echo '<h2>Table Counts</h2>';
                echo render_table_counts( [ $wpdb->comments, $wpdb->commentmeta ] );

                echo '<h2>Number of Comments by User and Post Type</h2>';

                echo Html_Table::render( null, SQL::comments_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );

                echo '<h2>Number of Rows in Comment Meta Table by Meta Key and Post Type</h2>';

                echo Html_Table::render( null, SQL::comment_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ] );

            }
        ];

        // adds id index to each report
        $index = function ( array $reports ) {

            foreach ( $reports as $report_id => $report ) {
                $reports[ $report_id ][ 'id' ] = $report_id;
            }
            return $reports;
        };

        // Add the IDs before applying filters.
        $reports = $index( $reports );

        /**
         * Hook to modify reports array.
         */
        $reports = apply_filters( 'wp_db_analyzer/reports', $reports );

        // Do the IDs again, after applying filters.
        $reports = $index( $reports );

        return $reports;
    }
}