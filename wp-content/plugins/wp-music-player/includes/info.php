<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the preview page content
 */
function wp_music_player_info() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="card">
            <h2>WP Music Player Features</h2>
            <div class="feature-list">
                <div class="feature-item">
                    <h3><span class="dashicons dashicons-playlist-audio"></span> Playlist Management</h3>
                    <p>Create and manage multiple playlists with an intuitive drag-and-drop interface.</p>
                </div>
                <div class="feature-item">
                    <h3><span class="dashicons dashicons-controls-play"></span> Modern Audio Player</h3>
                    <p>Responsive audio player with play/pause, seek, and volume controls.</p>
                </div>
                <div class="feature-item">
                    <h3><span class="dashicons dashicons-shortcode"></span> Easy Integration</h3>
                    <p>Use shortcode [music_player playlist_id="X"] to embed players anywhere.</p>
                </div>
            </div>
        </div>
        <div class="card">
            <h2>Quick Start</h2>
            <ol>
                <li>Go to Music Player > Playlists to create your first playlist</li>
                <li>Upload your audio files using the media library</li>
                <li>Add songs to your playlist</li>
                <li>Copy the shortcode and paste it into any post or page</li>
            </ol>
        </div>
    </div>
    <style>
    .feature-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .feature-item {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .feature-item h3 {
        margin-top: 0;
        color: #2271b1;
    }
    .feature-item .dashicons {
        font-size: 24px;
        width: 24px;
        height: 24px;
        margin-right: 10px;
    }
    .card {
        margin-top: 20px;
    }
    ol {
        margin-left: 20px;
    }
    </style>
    <?php
}