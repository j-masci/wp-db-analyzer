<?php
/**
 * Example page used to generate meaningful screenshots. Should not be
 * active in the final plugin.
 */

namespace WP_DB_Analyzer;

if (!defined('ABSPATH')) exit;

?>

<div class="wrap">
    <h2>Examples On Fake Data</h2>

    <h2>Counts of Post Statuses (left) vs. Post Types (top)</h2>
    <?php

    $m1 = new Matrix();

    $post_type_status = function ($post_type, $published, $drafts = null, $auto_drafts = null, $inherits = null) use ($m1) {
        $m1->set('publish', $post_type, $published);
        $m1->set('draft', $post_type, $drafts);
        $m1->set('auto-draft', $post_type, $auto_drafts);
        $m1->set('inherit', $post_type, $inherits);
    };

    $post_type_status('page', 35, 3, 1);
    $post_type_status('post', 120, 10, 1);
    $post_type_status('product', 5753, 1, 0);
    $post_type_status('revision', null, null, null, 50);

    echo render_table(null, $m1->convert_to_record_set_with_headings(), [
        'skip_header' => true,
    ]);

    ?>

    <h2>Counts of Meta Keys (left) vs. Post Types (top)</h2>
    <?php

    $m2 = new Matrix();

    // I'm realizing now that generating convincing looking data takes a bit more work than maybe its worth.
    $meta_keys = function ($key, $page = null, $product = null, $post = null) use( $m2 ){
        $m2->set( $key, 'page', $page );
        $m2->set( $key, 'product', $product );
        $m2->set( $key, 'post', $post );
    };

    $meta_keys( "_edit_lock", 1, 0, 0 );
    $meta_keys( "_wp_page_template", 39, 0, 0 );
    $meta_keys( "_thumbnail_id", 39, 4970, 105 );

    echo render_table(null, $m2->convert_to_record_set_with_headings(), [
        'skip_header' => true,
    ]);

    ?>

</div>
