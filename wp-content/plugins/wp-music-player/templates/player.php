<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'music_playlists';
$playlist = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name WHERE id = %d",
    $atts['playlist_id']
));

if (!$playlist) {
    return '<p>Playlist not found.</p>';
}

$songs = json_decode($playlist->songs, true);
?>
<div class="audio-player" data-playlist-id="<?php echo esc_attr($atts['playlist_id']); ?>">
    <div class="player-controls">
        <div class="album-info">
            <div class="album-art">
                <i class="dashicons dashicons-format-audio"></i>
            </div>
            <div class="song-info">
                <div id="song-title">Select a song</div>
                <div class="playlist-title"><?php echo esc_html($playlist->name); ?></div>
            </div>
        </div>

        <div class="controls">
            <div class="prev-control">
                <button class="prev" aria-label="Previous">
                    <i class="dashicons dashicons-controls-skipback"></i>
                </button>
            </div>
            <div class="play-control">
                <button class="play" aria-label="Play">
                    <i class="dashicons dashicons-controls-play"></i>
                </button>
            </div>
            <div class="next-control">
                <button class="next" aria-label="Next">
                    <i class="dashicons dashicons-controls-skipforward"></i>
                </button>
            </div>
        </div>

        <div class="progress">
            <div class="progress-current">0:00</div>
            <div class="progress-bar">
                <div class="progress-loaded"></div>
            </div>
            <div class="progress-total">0:00</div>
        </div>

        <div class="volume-container">
            <button class="volume" aria-label="Volume">
                <i class="dashicons dashicons-volume-medium"></i>
            </button>
            <div class="volume-slider">
                <div class="volume-percentage"></div>
            </div>
        </div>
    </div>

    <div class="playlist">
        <ul class="playlist-songs">
            <?php foreach ($songs as $index => $song): ?>
                <li class="playlist-song" data-index="<?php echo esc_attr($index); ?>" data-url="<?php echo esc_url($song['url']); ?>">
                    <span class="song-number"><?php echo esc_html($index + 1); ?></span>
                    <span class="song-title"><?php echo esc_html($song['title']); ?></span>
                    <span class="song-duration">-:--</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>