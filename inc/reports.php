<?php
/**
 * Holds Report_IDs, Reports, and Report classes.
 */

namespace WP_DB_Analyzer;

if (!defined('ABSPATH')) exit;

/**
 * Holds report IDs as class constants.
 *
 * Class Report_IDs
 * @package WP_DB_Analyzer
 */
Class Report_IDs
{

    const POST_STATUS = 'post_status';
    const POST_META = 'post_meta';
    const POST_DATES = 'post_dates';
    const TRANSIENT_TIMEOUTS = 'transient_timeouts';
    const USER_META = 'user_meta';
    const TERM_RELATIONSHIPS_REPORT = 'term_rel';

    /**
     * Similar to, but quite possibly not identical to, array_keys( Reports::get_all() )
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function get_all()
    {
        $r = new \ReflectionClass(self::class);
        return $r->getConstants();
    }
}

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
Class Reports
{

    /**
     * A string that generally means "all reports"
     */
    const REPORT_ID_ALL = '__all';

    /**
     * An example report with comments.
     *
     * @return array
     */
    public static function example_report()
    {

        global $wpdb;

        return [
            // Each report requires a unique ID.
            'id' => 'example-report-id',
            // An array of associated database tables. When reports refer to more than one table,
            // it might be best to just put them in one table. On the report landing page, the report
            // links show up next to the database table(s) you put here. If you omit this index
            // or set it to an empty array, then most likely, your report shows up in a category
            // named "Other Reports" or similar.
            'tables' => [$wpdb->posts],
            // A required title for the report so that we can display links that show the report.
            'title' => 'Example Report',
            // An optional short description of the report that can supplement the title.
            // This is a function in case it has to do any work, and so that we don't do that work
            // during report registration.
            // Likely shows up in the title attribute of anchor tags that link to reports.
            // If you want a long description, put more stuff in the render function.
            'get_desc' => function ($self) {
                return "This example report doesn't show anything.";
            },
            // A callable function to run the report AND render its output. All heavy lifting
            // must be done within here. $report is "this" array. $request might be $_GET.
            // WARNING: use $request, not $_GET in the callback. We may have a page that lists all
            // reports, which will pass in an empty $request array. Also, there are other reasons.
            // Note: an example of $request would be to pass in a date format used in reports that
            // categorize items by date. This can make the report dynamic and do many things while
            // at the same time providing a permalink to access the same version of the report again.
            'render' => function ($report, $request) {
                echo "html...";
            }
        ];

    }

    /**
     * Builds and returns all reports (an array of arrays).
     *
     * Please see the example report for documentation.
     *
     * @return array
     */
    public static function get_all()
    {

        global $wpdb;

        $reports = [];

        $reports[Report_IDs::POST_STATUS] = [
            'tables' => [$wpdb->posts],
            'title' => 'Post Status Report',
            'get_desc' => function () {
                return "Compares post status with post types, showing the distribution of items in the wp_posts table.";
            },
            'render' => function ($self) {
                echo render_table(null, SQL::posts_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);
            }
        ];

        $reports[Report_IDs::POST_META] = [
            'tables' => [$wpdb->postmeta],
            'title' => 'Post Meta Report',
            'get_desc' => function () {
                return "Compares post types with meta keys, showing how each of them contribute to the size of your wp_postmeta table.";
            },
            'render' => function ($self) {
                echo render_table(null, SQL::post_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);
            }
        ];

        $reports[Report_IDs::POST_DATES] = [
            'tables' => [$wpdb->posts],
            'title' => 'Post Published Date Report',
            'get_desc' => function () {
                return "Displays the dates that posts were published, broken down by post type.";
            },
            'render' => function ($self) {
                echo render_table(null, SQL::post_date_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);
            }
        ];

        $reports[Report_IDs::TRANSIENT_TIMEOUTS] = [
            'tables' => [$wpdb->options],
            'title' => 'Transient Timeout Report',
            'get_desc' => function () {
                return "Displays the number of transients in the wp_options table categorized by how long until they expire.";
            },
            'render' => function ($self) {
                echo render_table(null, SQL::transients_report()->convert_to_record_set_with_headings("Hours Until Expiry"), [
                    'skip_header' => true,
                ]);
            }
        ];

        $reports[Report_IDs::USER_META] = [
            'tables' => [$wpdb->usermeta],
            'title' => 'User Meta Report',
            'get_desc' => function () {
                return "Compares user roles with user meta keys, showing how each of them contribute to the size of your wp_usermeta table.";
            },
            'render' => function ($self) {
                echo render_table(null, SQL::user_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);
            }
        ];

        $reports[Report_IDs::TERM_RELATIONSHIPS_REPORT] = [
            'tables' => [$wpdb->terms],
            'title' => 'Terms/Taxonomies Report(s)',
            'get_desc' => function () {
                return "...";
            },
            'render' => function ($self) {

                global $wpdb;

                echo '<p>The terms table grows when you add insert new terms (ie. categories, tags, etc.). The term_taxonomy table is usually the size of the terms table but can be larger when some taxonomies are assigned to multiple object types. The termmeta table grows when terms store additional meta information (like custom fields). The term_relationships table grows when objects (ie. post types) are put into terms/categories/tags.</p>';

                echo SQL::render_table_counts( [ $wpdb->terms, $wpdb->term_taxonomy, $wpdb->termmeta, $wpdb->term_relationships] );

                echo '<p>Taxonomies (columns) vs. the number of terms and the object types that the taxonomy is assigned to. The columns and the term counts are derived from the database, therefore, if a taxonomy is registered but has no terms, it will not show up (as a column). The object types are derived from code and therefore they depend on the state of your active plugins and themes. If a taxonomy is not given an object type, this means that the data still exists in the database but the plugin or theme responsible for the taxonomy has likely changed or been de-activated (ie. the data is probably stale).</p>';

                echo render_table(null, SQL::term_taxonomy_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);

                echo '<p>Number of objects (usually, posts) assigned to terms (rows) categorized by object type (columns). When a taxonomy is registered to multiple object types, you might see multiple entries in the same row. Generally speaking, you can find the same numbers natively through WordPress by looking at the Count column when viewing a term.</p>';

                echo render_table(null, SQL::term_relationships_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);

                echo '<p>Term Meta...</p>';

                echo render_table(null, SQL::term_meta_report()->convert_to_record_set_with_headings(), [
                    'skip_header' => true,
                ]);

            }
        ];

        // adds id index to each report
        $index = function (array $reports) {
            foreach ($reports as $report_id => $report) {
                $reports[$report_id]['id'] = $report_id;
            }
            return $reports;
        };

        // Add the IDs before applying filters.
        $reports = $index($reports);

        /**
         * Hook to modify reports array.
         */
        $reports = apply_filters('wpdba/reports', $reports);

        // Do the IDs again, after applying filters.
        $reports = $index($reports);

        return $reports;
    }

    /**
     * @param $table
     */
    public static function filter_by_database_table(array $reports, $table)
    {
        return array_filter($reports, function ($report) use ($table) {
            return isset($report['tables']) && is_array($report['tables']) && in_array($table, $report['tables']);
        });
    }

    /**
     * Returns anchor tag(s) for one or more reports (or empty string).
     *
     * @param $reports
     * @return string
     */
    public static function link_reports($reports)
    {
        return implode(", ", array_map(function ($report) {
            return Report::link($report);
        }, $reports));
    }


}

/**
 * Holds static methods for reports. A "report" is an array containing
 * data and callables.
 *
 * This class is not instantiated. It's simpler like this for several reasons:
 *
 * - Easier to build reports.
 * - All/most static methods are be pure, and are easily tested.
 * - No inheritance and no need to declare new classes for each report.
 * - A report is not tightly coupled to an interface. Doing so would
 * force us to define many redundant methods.
 * - etc.
 *
 * Class Report
 * @package WP_DB_Analyzer
 */
Class Report
{

    /**
     * This is one way to render sort of the "extended" report which
     * includes the report title, description, render time, and the body
     * of the report, which is returned from self::render().
     *
     * @param array $report
     * @param array $request
     */
    public static function render_extended(array $report, array $request = [])
    {
        ob_start();

        // get the html and track the time
        list($report_body, $report_desc, $report_time) = call_user_func(function () use ($report, $request) {

            $t1 = microtime(true);

            $desc = Report::get_description($report);

            $body = Report::render($report, $request);

            $time = round(microtime(true) - $t1, 8);

            return [$body, $desc, $time];
        });

        ?>
        <h2><?= sanitize_text_field($report['title']); ?></h2>
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
    public static function render(array $report, array $request = [])
    {

        ob_start();

        if (isset($report['render']) && is_callable($report['render'])) {
            call_user_func_array($report['render'], [$report, $request]);
        }

        return ob_get_clean();
    }

    /**
     * Gets the url where the report is displayed.
     *
     * @param array $report
     * @return string
     */
    public static function get_link(array $report)
    {
        return WP_DB_Analyzer_Plugin::get_instance()->get_report_url(@$report['id']);
    }

    /**
     * Returns an anchor tag linking to the report.
     *
     * @param array $report
     * @return false|string
     */
    public static function link(array $report)
    {

        $desc = self::get_description($report);

        return '<a href="' . esc_url(self::get_link($report)) . '" title="' . esc_attr($desc) . '">' . sanitize_text_field(@$report['title']) . '</a>';
    }

    /**
     * @param array $report
     * @return string
     */
    public static function get_description(array $report)
    {

        if (isset($report['get_desc']) && is_callable($report['get_desc'])) {
            return call_user_func_array($report['get_desc'], $report);
        }

        return "";
    }

}