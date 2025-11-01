<?php
/**
 * ClassicMicroBlog theme bootstrap.
 */
function classicmicroblog_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style('classicmicroblog-style', get_stylesheet_uri(), array(), '1.0.0');

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

    // Enqueue comments app on single posts and pages
    if (is_single() || is_page()) {
        wp_enqueue_script(
            'classicmicroblog-comments-app',
            get_template_directory_uri() . '/assets/js/comments-app.js',
            array('vue'),
            '1.0.0',
            true
        );

        // Pass comment configuration to JS
        $post_id = get_the_ID();
        wp_localize_script('classicmicroblog-comments-app', 'CMBCommentsConfig', array(
            'postId'         => $post_id,
            'commentsQuery'  => rest_url('wp/v2/comments?post=' . $post_id),
            'submitUrl'      => site_url('/wp-comments-post.php'),
            'requireNameEmail' => get_option('require_name_email', 1),
        ));
    }
    // Icon font for Vue-friendly action icons
    wp_enqueue_style(
        'material-symbols-outlined',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400&display=swap',
        [],
        null
    );
}
add_action('wp_enqueue_scripts', 'classicmicroblog_enqueue_assets');

// AJAX: create a post (supports logged-in or Basic Auth)
function cmb_create_post_ajax() {
    // Resolve user: prefer cookie-auth, else Basic Auth
    $user = null;
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
    }

    if (!$user || empty($user->ID)) {
        wp_send_json_error(array('message' => 'Authentication required'));
    }
    
    if (!current_user_can('publish_posts')) {
        wp_send_json_error(array('message' => 'Not authorized to create posts'));
    }

    // Inputs
    $title   = isset($_REQUEST['title']) ? sanitize_text_field(wp_unslash($_REQUEST['title'])) : '';
    $content = isset($_REQUEST['content']) ? wp_kses_post(wp_unslash($_REQUEST['content'])) : '';

    if ($content === '') {
        wp_send_json_error(array('message' => 'Content is required'));
    }
    if ($title === '') {
        $title = mb_substr(wp_strip_all_tags($content), 0, 60);
        if ($title === '') {
            $title = 'Post';
        }
    }

    // Create post
    $post_id = wp_insert_post(array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'post_author'  => $user->ID,
    ), true);

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => $post_id->get_error_message()));
    }

    wp_send_json_success(array(
        'message'   => 'Post created',
        'post_id'   => (int) $post_id,
        'permalink' => get_permalink($post_id),
    ));
}
add_action('wp_ajax_cmb_create_post_ajax', 'cmb_create_post_ajax');
add_action('wp_ajax_nopriv_cmb_create_post_ajax', 'cmb_create_post_ajax');