<?php

namespace Database_Analyzer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Holds report IDs as class constants.
 *
 * Class Report_IDs
 * @package WP_DB_Analyzer
 */
Class Report_IDs {

    const POST_STATUS = 'post_status';
    const POST_META = 'post_meta';
    const POST_DATES = 'post_dates';
    const TRANSIENT_TIMEOUTS = 'transient_timeouts';
    const USER_META = 'user_meta';
    const TERMS = 'terms';
    const COMMENTS = 'comments';

    /**
     * Similar to, but possibly not identical to array_keys( Reports::get_all() )
     *
     * @return array
     * @throws \ReflectionException
     */
    public static function get_all() {

        $r = new \ReflectionClass( self::class );
        return $r->getConstants();
    }
}