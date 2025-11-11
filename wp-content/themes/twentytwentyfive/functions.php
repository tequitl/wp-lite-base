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
        'adminAjaxUrl'   => network_site_url('/wp-admin/admin-ajax.php'),
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

/**
 * Get WordPress admin color scheme and apply to frontend
 */
function classicmicroblog_get_admin_colors() {
    // Get current user's admin color scheme
    $current_user = wp_get_current_user();
    $admin_color = get_user_meta($current_user->ID, 'admin_color', true);
    
    // Default to 'fresh' if no color scheme is set
    if (empty($admin_color)) {
        $admin_color = 'fresh';
    }
    
    // WordPress default color schemes
    $color_schemes = array(
        'fresh' => array(
            'name' => 'Por defecto',
            'css-file' => get_template_directory_uri() . '/style-dark.css', 
            'colors' => array(
                'primary' => '#0073aa',
                'secondary' => '#005177',
                'accent' => '#00a0d2',
                'highlight' => '#0085ba',
                'notification' => '#d63638',
                'menu_bg' => '#23282d',
                'menu_text' => '#eee',
                'menu_hover' => '#0073aa',
                'admin_bar' => '#32373c'
            )
        ),
        'light' => array(
            'name' => 'Luminoso',
            'colors' => array(
                'primary' => '#e5a818',
                'secondary' => '#d1a017',
                'accent' => '#70a8bb',
                'highlight' => '#e5a818',
                'notification' => '#d63638',
                'menu_bg' => '#e5e5e5',
                'menu_text' => '#333',
                'menu_hover' => '#d1a017',
                'admin_bar' => '#f1f1f1'
            )
        ),
        'modern' => array(
            'name' => 'Moderno',
            'colors' => array(
                'primary' => '#3858e9',
                'secondary' => '#183ad6',
                'accent' => '#00d084',
                'highlight' => '#3858e9',
                'notification' => '#d63638',
                'menu_bg' => '#1e1e1e',
                'menu_text' => '#f0f0f1',
                'menu_hover' => '#3858e9',
                'admin_bar' => '#1e1e1e'
            )
        ),
        'blue' => array(
            'name' => 'Azul',
            'colors' => array(
                'primary' => '#096484',
                'secondary' => '#07526c',
                'accent' => '#4796b3',
                'highlight' => '#096484',
                'notification' => '#d63638',
                'menu_bg' => '#52accc',
                'menu_text' => '#fff',
                'menu_hover' => '#096484',
                'admin_bar' => '#738e96'
            )
        ),
        'coffee' => array(
            'name' => 'Café',
            'colors' => array(
                'primary' => '#46403c',
                'secondary' => '#383330',
                'accent' => '#c7a589',
                'highlight' => '#46403c',
                'notification' => '#d63638',
                'menu_bg' => '#59524c',
                'menu_text' => '#fff',
                'menu_hover' => '#c7a589',
                'admin_bar' => '#6f6f6f'
            )
        ),
        'ectoplasm' => array(
            'name' => 'Ectoplasma',
            'colors' => array(
                'primary' => '#523f6d',
                'secondary' => '#46365d',
                'accent' => '#a3b745',
                'highlight' => '#523f6d',
                'notification' => '#d63638',
                'menu_bg' => '#413256',
                'menu_text' => '#fff',
                'menu_hover' => '#a3b745',
                'admin_bar' => '#6f6f6f'
            )
        ),
        'midnight' => array(
            'name' => 'Medianoche',
            'colors' => array(
                'primary' => '#e14d43',
                'secondary' => '#dd382d',
                'accent' => '#77a6b9',
                'highlight' => '#e14d43',
                'notification' => '#d63638',
                'menu_bg' => '#363b3f',
                'menu_text' => '#fff',
                'menu_hover' => '#77a6b9',
                'admin_bar' => '#6f6f6f'
            )
        ),
        'ocean' => array(
            'name' => 'Océano',
            'colors' => array(
                'primary' => '#627c83',
                'secondary' => '#576e74',
                'accent' => '#aa9d88',
                'highlight' => '#627c83',
                'notification' => '#d63638',
                'menu_bg' => '#738e96',
                'menu_text' => '#fff',
                'menu_hover' => '#aa9d88',
                'admin_bar' => '#6f6f6f'
            )
        ),
        'sunrise' => array(
            'name' => 'Amanecer',
            'colors' => array(
                'primary' => '#dd823b',
                'secondary' => '#d97426',
                'accent' => '#aa9d88',
                'highlight' => '#dd823b',
                'notification' => '#d63638',
                'menu_bg' => '#cf4944',
                'menu_text' => '#fff',
                'menu_hover' => '#dd823b',
                'admin_bar' => '#6f6f6f'
            )
        )
    );
    
    return isset($color_schemes[$admin_color]) ? $color_schemes[$admin_color] : $color_schemes['fresh'];
}

/**
 * Output admin color scheme as CSS custom properties
 */
function classicmicroblog_admin_color_css() {
    $color_scheme = classicmicroblog_get_admin_colors();
    $colors = $color_scheme['colors'];


    
    echo '<style id="classicmicroblog-admin-colors">';
    echo ':root {';
    echo '--admin-primary: ' . esc_attr($colors['primary']) . ';';
    echo '--admin-secondary: ' . esc_attr($colors['secondary']) . ';';
    echo '--admin-accent: ' . esc_attr($colors['accent']) . ';';
    echo '--admin-highlight: ' . esc_attr($colors['highlight']) . ';';
    echo '--admin-notification: ' . esc_attr($colors['notification']) . ';';
    echo '--admin-menu-bg: ' . esc_attr($colors['menu_bg']) . ';';
    echo '--admin-menu-text: ' . esc_attr($colors['menu_text']) . ';';
    echo '--admin-menu-hover: ' . esc_attr($colors['menu_hover']) . ';';
    echo '--admin-bar: ' . esc_attr($colors['admin_bar']) . ';';
    
    // Override theme colors with admin colors
    echo '.btn { background:'. esc_attr($colors['menu_bg']) .'!important }';
    echo '--primary: ' . esc_attr($colors['primary']) . ';';
    echo '--secondary: ' . esc_attr($colors['secondary']) . ';';
    echo '--accent: ' . esc_attr($colors['accent']) . ';';
    echo '--highlight: ' . esc_attr($colors['highlight']) . ';';
    echo '}';
    echo '</style>';
    if (isset($colors['css-file'])) {
        echo '<style src="' . esc_attr($colors['css-file']) . '"/>;';
    }
}
add_action('wp_head', 'classicmicroblog_admin_color_css');

/**
 * Add admin color scheme info to JavaScript
 */
function classicmicroblog_add_color_scheme_to_js() {
    $color_scheme = classicmicroblog_get_admin_colors();
    
    wp_localize_script('classicmicroblog-app', 'ClassicMicroBlogColors', array(
        'scheme_name' => $color_scheme['name'],
        'colors' => $color_scheme['colors']
    ));
}
add_action('wp_enqueue_scripts', 'classicmicroblog_add_color_scheme_to_js', 20);

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

// Ensure subsite uploads use /wp-content/uploads/sites/{blog_id}
function classicmicroblog_fix_multisite_upload_dir($dirs) {
    if (is_multisite()) {
        global $blog_id;
        $hasSitesSegment = (strpos($dirs['baseurl'], '/sites/') !== false);
        if (!$hasSitesSegment) {
            $dirs['baseurl'] = site_url('/wp-content/uploads/sites/' . $blog_id);
            $dirs['basedir'] = WP_CONTENT_DIR . '/uploads/sites/' . $blog_id;
        }
    }
    return $dirs;
}
add_filter('upload_dir', 'classicmicroblog_fix_multisite_upload_dir');

// Ensure subsite assets load from root wp-* paths
function classicmicroblog_fix_asset_src($src) {
    if (is_multisite() && !empty($src)) {
        $path  = parse_url($src, PHP_URL_PATH);
        $query = parse_url($src, PHP_URL_QUERY);
        if (preg_match('#^/[_0-9a-zA-Z-]+/(wp-(?:content|includes|admin)/.+)$#', $path, $m)) {
            $scheme = is_ssl() ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'];
            $new    = $scheme . '://' . $host . '/' . $m[1];
            if ($query) {
                $new .= '?' . $query;
            }
            return $new;
        }
    }
    return $src;
}
add_filter('script_loader_src', 'classicmicroblog_fix_asset_src', 10);
add_filter('style_loader_src', 'classicmicroblog_fix_asset_src', 10);