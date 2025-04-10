// Event listeners
jQuery(document).ready(function($) {
    let mediaUploader;
    let currentPlaylistId = null;


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
        
        $('.playlist-name').val(playlistName);
        $('#list_editor').fadeIn(300);
    });

    $('#songs-container').on('click', '.remove-song', function() {
        const songEntry = $(this).closest('li');
        songEntry.remove();
    });
    // Save playlist
    $('.save-playlist').on('click', function() {
        const playlistName = $('.playlist-name').val();
        if (!playlistName) {
            alert('Please enter a playlist name');
            return;
        }

        $.ajax({
            url: wpMusicPlayer.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wp_music_player_create_playlist',
                playlist_name: playlistName,
                nonce: wpMusicPlayer.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to create playlist: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to create playlist. Please try again.');
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
            <li class="playlist-song" data-url="${song.url}">
                <span class="song-number">nil</span>
                <span class="song-title">${song.title}</span>
                <button type="button" class="button button-link-delete remove-song"><span class="dashicons dashicons-trash"></span></button>
            </li>
        `;
            container.append(songEntry);
        });
    }
});