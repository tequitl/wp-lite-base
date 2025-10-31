<?php
/**
 * ClassicMicroBlog theme bootstrap.
 */
function classicmicroblog_enqueue_assets() {
    // Main stylesheet (cache-busted with filemtime)
    wp_enqueue_style(
        'classicmicroblog-style',
        get_stylesheet_uri(),
        array(),
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    // Vue 3 (CDN)
    wp_enqueue_script(
        'vue',
        'https://unpkg.com/vue@3/dist/vue.global.prod.js',
        array(),
        null,
        true
    );

    // App script
    wp_enqueue_script(
        'classicmicroblog-app',
        get_template_directory_uri() . '/js/app.js',
        array('vue'),
        '1.0.0',
        true
    );

    // Pass API URLs to JS
    wp_localize_script('classicmicroblog-app', 'ClassicMicroBlog', array(
        'restPostsUrl'   => rest_url('wp/v2/posts'),
        'adminAjaxUrl'   => admin_url('admin-ajax.php'),
        'siteUrl'        => home_url('/'),
        'defaultPerPage' => 10,
    ));
}
add_action('wp_enqueue_scripts', 'classicmicroblog_enqueue_assets');