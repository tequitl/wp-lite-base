<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ws_url        = isset( $ws_url ) ? (string) $ws_url : '';
$enable_player = isset( $enable_player ) ? (bool) $enable_player : true;

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2>Settings</h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'freeradio_save_settings', 'freeradio_nonce' ); ?>
		<input type="hidden" name="action" value="freeradio_save_settings" />
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="freeradio_ws_url">WebSocket URL</label></th>
				<td>
					<input name="freeradio_ws_url" id="freeradio_ws_url" type="text" class="regular-text" value="<?php echo esc_attr( $ws_url ); ?>" placeholder="ws://localhost:8080" />
				</td>
			</tr>
			<tr>
				<th scope="row">Floating player</th>
				<td>
					<label>
						<input name="freeradio_enable_player" type="checkbox" <?php checked( (bool) $enable_player ); ?> />
						Enable floating player on frontend
					</label>
				</td>
			</tr>
		</table>
		<p>
			<button type="submit" class="button button-primary">Save</button>
		</p>
	</form>

	<hr />

	<h2>TRANSMIT</h2>
	<p>
		<button type="button" class="button button-primary" data-freeradio-transmit="start">Start Transmit</button>
		<button type="button" class="button" data-freeradio-transmit="stop" disabled>Stop</button>
	</p>
	<p>
		Status: <strong data-freeradio-transmit-status="1">Idle</strong>
	</p>
	<p>
		<small>Microphone access requires HTTPS or localhost.</small>
	</p>
</div>

