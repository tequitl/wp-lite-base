// Event listeners
jQuery(document).ready(function($) {
    let mediaUploader;
    let currentPlaylistId = null;

    $('#songs-container').sortable({
        items: 'li.playlist-song',
        cursor: 'move',
    });
    // Handle playlist selection
    $(document).on('click', '.playlist-item', function() {
        $('.playlist-item').removeClass('active');
        $(this).addClass('active');
        currentPlaylistId = $(this).data('id');
        loadPlaylistSongs(currentPlaylistId);
    });

    // Handle media library button click
    $('.add-to-playlist').on('click', function(e) {
        e.preventDefault();
        
        mediaUploader = wp.media({
            title: 'Select Audio Files',
            button: {
                text: 'Add to Playlist'
            },
            multiple: true,
            library: {
                type: 'audio'
            }
        });

        mediaUploader.on('select', function() {
            const attachments = mediaUploader.state().get('selection').toJSON();
            const songs = attachments.map(attachment => ({
                title: attachment.title,
                url: attachment.url
            }));
            appendSongsContainer(songs);
        });

        mediaUploader.open();
    });

    // Create new playlist
    $('.create-new-playlist').on('click', function() {
        const playlistName = prompt('Enter playlist name:');
        if (!playlistName) return;
        $('#playlist_id').val('');
        $('.playlist-name').val(playlistName);
        $('#list_editor').fadeIn(300);
        $('#songs-container').empty();
    });

    $('#songs-container').on('click', '.remove-song', function() {
        const songEntry = $(this).closest('li');
        songEntry.remove();
    });
    // Save playlist
    $('.save-playlist').on('click', function() {
        var playlistName = $('.playlist-name').val();
        if (!playlistName) {
            alert('Please enter a playlist name');
            return;
        }
        var playlist_id = $('#playlist_id').val();
        var action='wp_music_player_create_playlist_json';
        if (playlist_id!='') {
            action='wp_music_player_update_playlist_json';
        }
        var songs = [];
        $('.playlist-song').each(function() {
            songs.push({
                title: $(this).attr('title'),
                url: $(this).attr('url')
            });
        });
        
        const playlistData = {
            id: playlist_id,
            name: playlistName,
            songs: songs
        };
        console.log(JSON.stringify(playlistData));
        $.ajax({
            url: wpMusicPlayer.ajaxUrl,
            type: 'POST',
            data: {
                action: action,
                playlist_payload: JSON.stringify(playlistData),
                nonce: wpMusicPlayer.nonce
            },
            success: function(response) {
                if (response.success) {
                   // alert('Playlist saved successfully');
                    location.reload();
                } else {
                    alert('Failed to save playlist: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to save playlist. Please try again.');
            }
        });
    });
    // Function to delete playlist
    $('.delete-playlist').on('click', function() {
        const playlistId = $(this).attr('data-id');
        if (!playlistId) {
            alert('Please select a playlist to delete');
            return;
        }
        const confirmation = confirm('Are you sure you want to delete this playlist?');
        if (!confirmation) return;
        $.ajax({
            url: wpMusicPlayer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_music_player_delete_playlist',
                playlist_id: playlistId,
                nonce: wpMusicPlayer.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Playlist deleted successfully');
                    location.reload();
                }
            }
        });
    });
    // Function to load playlist songs
    function loadPlaylistSongs(playlistId) {
        $.ajax({
            url: wpMusicPlayer.ajaxUrl,
            type: 'GET',
            data: {
                action: 'wp_music_player_get_playlist',
                playlist_id: playlistId,
                nonce: wpMusicPlayer.nonce
            },
            success: function(response) {
                if (response.success) {
                    displaySongs(response.data.songs);
                }
            },
            error: function() {
                alert('Failed to load playlist songs. Please try again.');
            }
        });
    }

    // Function to append songs to container
    function appendSongsContainer(songs) {
        const container = $('#songs-container');
        songs.forEach(function(song) {
            const songEntry = `
            <li class="playlist-song" url="${song.url}" title="${song.title}">
                <span class="dashicons dashicons-arrow-up-alt"></span>
                <span class="dashicons dashicons-arrow-down-alt"></span>
                <span class="song-number">N/A</span>
                <span class="song-title">${song.title}</span>
                <button type="button" class="button button-link-delete remove-song"><span class="dashicons dashicons-trash"></span></button>
            </li>
        `;
            container.append(songEntry);
        });
    }
});