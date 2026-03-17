<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$records        = isset( $records ) && is_array( $records ) ? $records : array();
$current_record = isset( $current_record ) && is_array( $current_record ) ? $current_record : null;

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<h2>RECORDS</h2>

	<style>
		.freeradio-admin-grid{display:grid;grid-template-columns:1fr 2fr;gap:16px;align-items:start;margin-top:16px}
		.freeradio-admin-col{background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:12px}
		.freeradio-admin-col h3{margin:0 0 12px}
		.freeradio-admin-records-table td{vertical-align:top}
		@media (max-width: 782px){.freeradio-admin-grid{grid-template-columns:1fr}}
	</style>

	<div class="freeradio-admin-grid">
		<div class="freeradio-admin-col">
			<h3>Current record</h3>
			<?php if ( is_array( $current_record ) && ! empty( $current_record['url'] ) ) : ?>
				<p><strong><?php echo esc_html( (string) ( $current_record['title'] ?? '' ) ); ?></strong></p>
				<audio controls preload="none" src="<?php echo esc_url( (string) $current_record['url'] ); ?>" style="width:100%;"></audio>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;">
					<?php wp_nonce_field( 'freeradio_clear_current_record', 'freeradio_nonce' ); ?>
					<input type="hidden" name="action" value="freeradio_clear_current_record" />
					<button type="submit" class="button">Clear current</button>
				</form>
			<?php else : ?>
				<p>No current record selected.</p>
			<?php endif; ?>
			<hr />
			<h3>Add record</h3>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'freeradio_add_record', 'freeradio_nonce' ); ?>
				<input type="hidden" name="action" value="freeradio_add_record" />
				<p>
					<label for="freeradio_record_title"><strong>Title</strong></label><br />
					<input id="freeradio_record_title" name="freeradio_record_title" type="text" class="regular-text" />
				</p>
				<p>
					<label for="freeradio_record_url"><strong>Audio URL</strong></label><br />
					<input id="freeradio_record_url" name="freeradio_record_url" type="url" class="regular-text" placeholder="https://..." required />
				</p>
				<p>
					<button type="submit" class="button button-primary">Add</button>
				</p>
			</form>
		</div>

		<div class="freeradio-admin-col">
			<h3>List of records</h3>
			<?php if ( empty( $records ) ) : ?>
				<p>No records yet.</p>
			<?php else : ?>
				<table class="widefat striped freeradio-admin-records-table" style="margin-top:0;">
					<thead>
						<tr>
							<th>Title</th>
							<th>Preview</th>
							<th style="width:220px;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $records as $record ) : ?>
							<?php
							if ( ! is_array( $record ) ) {
								continue;
							}
							$id    = isset( $record['id'] ) ? (string) $record['id'] : '';
							$title = isset( $record['title'] ) ? (string) $record['title'] : '';
							$url   = isset( $record['url'] ) ? (string) $record['url'] : '';
							if ( '' === $id || '' === $url ) {
								continue;
							}
							$is_current = is_array( $current_record ) && isset( $current_record['id'] ) && (string) $current_record['id'] === $id;
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $title ); ?></strong>
									<?php if ( $is_current ) : ?>
										<div><span class="tag">Current</span></div>
									<?php endif; ?>
									<div><code><?php echo esc_html( $id ); ?></code></div>
								</td>
								<td>
									<audio controls preload="none" src="<?php echo esc_url( $url ); ?>" style="width:100%;max-width:420px;"></audio>
								</td>
								<td>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:6px;">
										<?php wp_nonce_field( 'freeradio_set_current_record', 'freeradio_nonce' ); ?>
										<input type="hidden" name="action" value="freeradio_set_current_record" />
										<input type="hidden" name="freeradio_record_id" value="<?php echo esc_attr( $id ); ?>" />
										<button type="submit" class="button button-small" <?php disabled( $is_current ); ?>>Set current</button>
									</form>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
										<?php wp_nonce_field( 'freeradio_delete_record', 'freeradio_nonce' ); ?>
										<input type="hidden" name="action" value="freeradio_delete_record" />
										<input type="hidden" name="freeradio_record_id" value="<?php echo esc_attr( $id ); ?>" />
										<button type="submit" class="button button-small">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<hr />

			<p>
				Shortcode for frontend records list: <code>[freeradio_records]</code>
			</p>
		</div>
	</div>
</div>

