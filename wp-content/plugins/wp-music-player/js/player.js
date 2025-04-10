jQuery(document).ready(function($) {
    $('.audio-player').each(function() {
        const player = $(this);
        const audio = new Audio();
        let currentTrack = 0;
        let isPlaying = false;

        // Get playlist songs
        const songs = player.find('.playlist-song').map(function() {
            return {
                url: $(this).data('url'),
                title: $(this).find('.song-title').text()
            };
        }).get();

        // Initialize audio events
        audio.addEventListener('loadedmetadata', function() {
            updateDuration(currentTrack);
        });

        audio.addEventListener('timeupdate', function() {
            updateProgress();
        });

        audio.addEventListener('ended', function() {
            playNext();
        });

        // Play/Pause button
        player.find('.play').on('click', function() {
            if (isPlaying) {
                pauseTrack();
            } else {
                playTrack();
            }
        });

        // Previous button
        player.find('.prev').on('click', function() {
            playPrev();
        });

        // Next button
        player.find('.next').on('click', function() {
            playNext();
        });

        // Progress bar click
        player.find('.progress-bar').on('click', function(e) {
            const progressBar = $(this);
            const position = e.pageX - progressBar.offset().left;
            const percentage = position / progressBar.width();
            audio.currentTime = audio.duration * percentage;
        });

        // Volume control
        const volumeBtn = player.find('.volume');
        const volumeSlider = player.find('.volume-slider');
        let lastVolume = 1;

        volumeBtn.on('click', function() {
            if (audio.volume > 0) {
                lastVolume = audio.volume;
                audio.volume = 0;
                updateVolumeIcon(0);
            } else {
                audio.volume = lastVolume;
                updateVolumeIcon(lastVolume);
            }
            updateVolumeBar();
        });

        volumeSlider.on('click', function(e) {
            const position = e.pageX - volumeSlider.offset().left;
            const percentage = position / volumeSlider.width();
            audio.volume = Math.max(0, Math.min(1, percentage));
            updateVolumeIcon(audio.volume);
            updateVolumeBar();
        });

        // Playlist song click
        player.find('.playlist-song').on('click', function() {
            const index = $(this).data('index');
            if (index !== currentTrack) {
                currentTrack = index;
                playTrack();
            } else if (!isPlaying) {
                playTrack();
            } else {
                pauseTrack();
            }
        });

        // Helper functions
        function playTrack() {
            if (songs.length === 0) return;

            const song = songs[currentTrack];
            if (audio.src !== song.url) {
                audio.src = song.url;
            }

            audio.play();
            isPlaying = true;
            updatePlayerState();
        }

        function pauseTrack() {
            audio.pause();
            isPlaying = false;
            updatePlayerState();
        }

        function playNext() {
            if (songs.length === 0) return;
            currentTrack = (currentTrack + 1) % songs.length;
            playTrack();
        }

        function playPrev() {
            if (songs.length === 0) return;
            currentTrack = (currentTrack - 1 + songs.length) % songs.length;
            playTrack();
        }

        function updatePlayerState() {
            // Update play/pause button
            const playButton = player.find('.play i');
            playButton.removeClass('dashicons-controls-play dashicons-controls-pause')
                     .addClass(isPlaying ? 'dashicons-controls-pause' : 'dashicons-controls-play');

            // Update active song in playlist
            player.find('.playlist-song').removeClass('active');
            player.find(`.playlist-song[data-index="${currentTrack}"]`).addClass('active');

            // Update song info
            if (songs[currentTrack]) {
                player.find('.song-title').text(songs[currentTrack].title);
            }
        }

        function updateProgress() {
            const current = formatTime(audio.currentTime);
            const total = formatTime(audio.duration);
            const percentage = (audio.currentTime / audio.duration) * 100 || 0;

            player.find('.progress-current').text(current);
            player.find('.progress-total').text(total);
            player.find('.progress-loaded').css('width', `${percentage}%`);
        }

        function updateDuration(index) {
            const duration = formatTime(audio.duration);
            player.find(`.playlist-song[data-index="${index}"] .song-duration`).text(duration);
        }

        function updateVolumeIcon(volume) {
            const icon = volumeBtn.find('i');
            icon.removeClass('dashicons-volume-off dashicons-volume-low dashicons-volume-medium dashicons-volume-high');

            if (volume === 0) {
                icon.addClass('dashicons-volume-off');
            } else if (volume < 0.33) {
                icon.addClass('dashicons-volume-low');
            } else if (volume < 0.67) {
                icon.addClass('dashicons-volume-medium');
            } else {
                icon.addClass('dashicons-volume-high');
            }
        }

        function updateVolumeBar() {
            player.find('.volume-percentage').css('width', `${audio.volume * 100}%`);
        }

        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const minutes = Math.floor(seconds / 60);
            seconds = Math.floor(seconds % 60);
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        // Initialize first track
        if (songs.length > 0) {
            audio.src = songs[0].url;
        }

        // Set initial volume
        audio.volume = 1;
        updateVolumeBar();
        updateVolumeIcon(1);
    });
});