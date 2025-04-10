<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create new playlist
 */
function wp_music_player_create_playlist() {
    check_ajax_referer('wp_music_player_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $playlist_name = sanitize_text_field($_POST['playlist_name']);
    $songs = array();
    
    // Sanitize and organize song data
    $song_titles = isset($_POST['song_title']) ? $_POST['song_title'] : array();
    $song_urls = isset($_POST['song_url']) ? $_POST['song_url'] : array();
    
    for ($i = 0; $i < count($song_titles); $i++) {
        if (!empty($song_titles[$i]) && !empty($song_urls[$i])) {
            $songs[] = array(
                'title' => sanitize_text_field($song_titles[$i]),
                'url' => esc_url_raw($song_urls[$i])
            );
        }
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'music_playlists';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'name' => $playlist_name,
            'songs' => json_encode($songs)
        ),
        array('%s', '%s')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to create playlist');
    }
    
    wp_send_json_success(array(
        'message' => 'Playlist created successfully',
        'playlist_id' => $wpdb->insert_id
    ));
}
add_action('wp_ajax_wp_music_player_create_playlist', 'wp_music_player_create_playlist');

/**
 * Update playlist
 */
function wp_music_player_update_playlist() {
    check_ajax_referer('wp_music_player_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $playlist_id = intval($_POST['playlist_id']);
    $playlist_name = sanitize_text_field($_POST['playlist_name']);
    $songs = array();
    
    // Sanitize and organize song data
    $song_titles = isset($_POST['song_title']) ? $_POST['song_title'] : array();
    $song_urls = isset($_POST['song_url']) ? $_POST['song_url'] : array();
    
    for ($i = 0; $i < count($song_titles); $i++) {
        if (!empty($song_titles[$i]) && !empty($song_urls[$i])) {
            $songs[] = array(
                'title' => sanitize_text_field($song_titles[$i]),
                'url' => esc_url_raw($song_urls[$i])
            );
        }
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'music_playlists';
    
    $result = $wpdb->update(
        $table_name,
        array(
            'name' => $playlist_name,
            'songs' => json_encode($songs)
        ),
        array('id' => $playlist_id),
        array('%s', '%s'),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to update playlist');
    }
    
    wp_send_json_success('Playlist updated successfully');
}
add_action('wp_ajax_wp_music_player_update_playlist', 'wp_music_player_update_playlist');

/**
 * Delete playlist
 */
function wp_music_player_delete_playlist() {
    check_ajax_referer('wp_music_player_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $playlist_id = intval($_POST['playlist_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'music_playlists';
    
    $result = $wpdb->delete(
        $table_name,
        array('id' => $playlist_id),
        array('%d')
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to delete playlist');
    }
    
    wp_send_json_success('Playlist deleted successfully');
}
add_action('wp_ajax_wp_music_player_delete_playlist', 'wp_music_player_delete_playlist');

/**
 * Get playlist data
 */
function wp_music_player_get_playlist() {
    check_ajax_referer('wp_music_player_nonce', 'nonce');
    
    $playlist_id = intval($_GET['playlist_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'music_playlists';
    
    $playlist = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $playlist_id
    ));
    
    if (!$playlist) {
        wp_send_json_error('Playlist not found');
    }
    
    wp_send_json_success(array(
        'name' => $playlist->name,
        'songs' => json_decode($playlist->songs, true)
    ));
}
add_action('wp_ajax_wp_music_player_get_playlist', 'wp_music_player_get_playlist');
add_action('wp_ajax_nopriv_wp_music_player_get_playlist', 'wp_music_player_get_playlist');