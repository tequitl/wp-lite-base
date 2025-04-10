<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the admin page content
 */
function wp_music_player_admin_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'music_playlists';
    $playlists = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    $playlist_id = null;
    $playlist_name = null;
    $songs = [];
    if(isset($_GET['playlist_id'])){
        $playlist_id = intval($_GET['playlist_id']);
        $playlist = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $playlist_id
        ));
        $playlist_name = $playlist->name;
        $songs = json_decode($playlist->songs, true);
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php if ($playlist_id==null):?>
        <div class="playlist-maker-container">
            <div class="playlist-maker-header">
                <button type="button" class="button button-primary create-new-playlist">Add new playlist</button>
            </div>
        </div>
        <!-- Existing Playlists -->
        <div class="card" style="max-width:none; ">
            <h2>Existing Playlists</h2>
            <?php if ($playlists): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Shortcode</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($playlists as $playlist): ?>
                            <tr>
                                <td><?php echo esc_html($playlist->name); ?></td>
                                <td><code>[music_player playlist_id="<?php echo esc_attr($playlist->id); ?>"]</code></td>
                                <td><?php echo esc_html($playlist->created_at); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-music-player&playlist_id=' . $playlist->id)); ?>" class="button edit-playlist">Edit</a>
                                    <button class="button button-link-delete delete-playlist" data-id="<?php echo esc_attr($playlist->id); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No playlists created yet.</p>
            <?php endif; ?>
        </div>
        <?php endif;?>

                <!-- Playlist Maker -->
        <div id="list_editor" class="card" style="max-width:none; width: 100%; display: <?php if( $playlist_id ==null) echo 'none'; else echo 'block'; ?>;">
            <h2>Songs in Playlists</h2>
            <div class="playlist-maker-split" style="display: flex;">
                <div class="playlist-maker-container" style="width: 50%;">
                    <div class="playlist-maker-header">
                        <input type="hidden"  id="playlist_id" value="<?php if( $playlist_id !=null) echo $playlist_id; ?>">
                        <input type="text" class="playlist-name" value="<?php if( $playlist_name !=null) echo $playlist_name; ?>"placeholder="Playlist name">
                        <button type="button" class="button button-primary add-to-playlist">Add to current playlist</button>
                        <button type="button" class="button button-primary save-playlist">Save</button>
                    </div>
            
                    <div class="playlist">
                        <ul id="songs-container" class="playlist-songs">
                            <?php foreach ($songs as $index => $song): ?>
                                <li class="playlist-song" url="<?php echo esc_url($song['url']); ?>" title="<?php echo esc_html($song['title']); ?>">
                                    <span class="dashicons dashicons-arrow-up-alt"></span>
                                    <span class="dashicons dashicons-arrow-down-alt"></span>
                                    <span class="song-number"><?php echo esc_html($index + 1); ?></span>
                                    <span class="song-title"><?php echo esc_html($song['title']); ?></span>
                                    <button type="button" class="button button-link-delete remove-song"><span class="dashicons dashicons-trash"></span></button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                 <div class="playlist-preview" style="width: 50%; padding: 0 10px;">
                    <h3>Playlist Preview</h3>
                    <?php if ($playlist_id): ?>
                        <?php echo do_shortcode('[music_player playlist_id="' . $playlist_id . '"]'); ?>
                    <?php else: ?>
                        <p>Select a playlist to preview</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
    <?php
}