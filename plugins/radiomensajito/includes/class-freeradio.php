<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FreeRadio {
	private const OPTION_WS_URL         = 'freeradio_ws_url';
	private const OPTION_ENABLE_PLAYER  = 'freeradio_enable_player';
	private const OPTION_RECORDS        = 'freeradio_records';
	private const OPTION_CURRENT_RECORD = 'freeradio_current_record';

	private static bool $frontend_assets_needed = false;

	public static function init() : void {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );

		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );
		add_action( 'wp_footer', array( __CLASS__, 'maybe_render_floating_player' ), 30 );

		add_action( 'admin_post_freeradio_save_settings', array( __CLASS__, 'handle_save_settings' ) );
		add_action( 'admin_post_freeradio_add_record', array( __CLASS__, 'handle_add_record' ) );
		add_action( 'admin_post_freeradio_delete_record', array( __CLASS__, 'handle_delete_record' ) );
		add_action( 'admin_post_freeradio_set_current_record', array( __CLASS__, 'handle_set_current_record' ) );
		add_action( 'admin_post_freeradio_clear_current_record', array( __CLASS__, 'handle_clear_current_record' ) );
	}

	private static function view( string $file, array $vars = array() ) : void {
		$path = trailingslashit( (string) FREERADIO_VIEWS ) . ltrim( $file, '/' );
		if ( ! file_exists( $path ) ) {
			return;
		}
		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP );
		}
		require $path;
	}

	public static function admin_menu() : void {
		add_menu_page(
			'FreeRadio',
			'FreeRadio',
			'manage_options',
			'freeradio',
			array( __CLASS__, 'render_transmit' ),
			'dashicons-microphone',
			80
		);

		add_submenu_page(
			'freeradio',
			'Transmit',
			'Transmit',
			'manage_options',
			'freeradio',
			array( __CLASS__, 'render_transmit' )
		);

		add_submenu_page(
			'freeradio',
			'Records',
			'Records',
			'manage_options',
			'freeradio-records',
			array( __CLASS__, 'render_records' )
		);
	}

	public static function admin_enqueue_scripts( string $hook_suffix ) : void {
		$page = isset( $_GET['page'] ) ? sanitize_key( (string) $_GET['page'] ) : '';
		if ( ! in_array( $page, array( 'freeradio', 'freeradio-records' ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'freeradio-admin',
			FREERADIO_URL . 'assets/admin.js',
			array(),
			(string) FREERADIO_VERSION,
			true
		);

		wp_add_inline_script(
			'freeradio-admin',
			'window.FreeRadioAdmin=' . wp_json_encode(
				array(
					'wsUrl' => (string) self::get_ws_url(),
				),
				JSON_HEX_TAG | JSON_UNESCAPED_SLASHES
			) . ';',
			'before'
		);
	}

	public static function wp_enqueue_scripts() : void {
		if ( is_admin() ) {
			return;
		}

		$enable_player = (bool) get_option( self::OPTION_ENABLE_PLAYER, true );
		if ( $enable_player || self::$frontend_assets_needed ) {
			wp_enqueue_style(
				'freeradio-frontend',
				FREERADIO_URL . 'assets/frontend.css',
				array(),
				(string) FREERADIO_VERSION
			);

			wp_enqueue_script(
				'freeradio-frontend',
				FREERADIO_URL . 'assets/frontend.js',
				array(),
				(string) FREERADIO_VERSION,
				true
			);

			wp_add_inline_script(
				'freeradio-frontend',
				'window.FreeRadioFrontend=' . wp_json_encode(
					array(
						'wsUrl' => (string) self::get_ws_url(),
					),
					JSON_HEX_TAG | JSON_UNESCAPED_SLASHES
				) . ';',
				'before'
			);
		}
	}

	public static function maybe_render_floating_player() : void {
		if ( is_admin() ) {
			return;
		}
		$enable_player = (bool) get_option( self::OPTION_ENABLE_PLAYER, true );
		if ( ! $enable_player ) {
			return;
		}

		$ws_url = (string) self::get_ws_url();
		echo '<div class="freeradio-floating" data-freeradio-player="1" data-ws-url="' . esc_attr( $ws_url ) . '">';
		echo '<div class="freeradio-floating__row">';
		echo '<strong>FreeRadio</strong>';
		echo '<span class="freeradio-status" data-freeradio-status="1">Offline</span>';
		echo '</div>';
		echo '<audio class="freeradio-audio" data-freeradio-audio="1" controls preload="none"></audio>';
		echo '</div>';
	}

	public static function render_transmit() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::view(
			'admin-transmit.php',
			array(
				'ws_url'        => (string) self::get_ws_url(),
				'enable_player' => (bool) get_option( self::OPTION_ENABLE_PLAYER, true ),
			)
		);
	}

	public static function render_records() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$records        = self::get_records();
		$current_record = self::get_current_record();

		self::view(
			'admin-records.php',
			array(
				'records'        => $records,
				'current_record' => $current_record,
			)
		);
	}

	public static function handle_save_settings() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'freeradio_save_settings', 'freeradio_nonce' );

		$ws_url = isset( $_POST['freeradio_ws_url'] ) ? trim( (string) wp_unslash( $_POST['freeradio_ws_url'] ) ) : '';
		$ws_url = sanitize_text_field( $ws_url );
		if ( '' !== $ws_url ) {
			update_option( self::OPTION_WS_URL, $ws_url );
		}

		$enable_player = isset( $_POST['freeradio_enable_player'] );
		update_option( self::OPTION_ENABLE_PLAYER, (bool) $enable_player );

		wp_safe_redirect( admin_url( 'admin.php?page=freeradio' ) );
		exit;
	}

	public static function handle_add_record() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'freeradio_add_record', 'freeradio_nonce' );

		$title = isset( $_POST['freeradio_record_title'] ) ? trim( (string) wp_unslash( $_POST['freeradio_record_title'] ) ) : '';
		$title = sanitize_text_field( $title );

		$url = isset( $_POST['freeradio_record_url'] ) ? trim( (string) wp_unslash( $_POST['freeradio_record_url'] ) ) : '';
		$url = esc_url_raw( $url );

		if ( '' !== $url ) {
			$records   = self::get_records();
			$records[] = array(
				'id'         => self::new_record_id(),
				'title'      => '' !== $title ? $title : wp_parse_url( $url, PHP_URL_PATH ),
				'url'        => $url,
				'created_at' => time(),
			);
			update_option( self::OPTION_RECORDS, $records );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=freeradio-records' ) );
		exit;
	}

	public static function handle_delete_record() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'freeradio_delete_record', 'freeradio_nonce' );

		$id = isset( $_POST['freeradio_record_id'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['freeradio_record_id'] ) ) : '';
		if ( '' === $id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=freeradio-records' ) );
			exit;
		}

		$records = array_values(
			array_filter(
				self::get_records(),
				static function ( $record ) use ( $id ) : bool {
					return is_array( $record ) && isset( $record['id'] ) && (string) $record['id'] !== $id;
				}
			)
		);
		update_option( self::OPTION_RECORDS, $records );

		$current = (string) get_option( self::OPTION_CURRENT_RECORD, '' );
		if ( $current === $id ) {
			update_option( self::OPTION_CURRENT_RECORD, '' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=freeradio-records' ) );
		exit;
	}

	public static function handle_set_current_record() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'freeradio_set_current_record', 'freeradio_nonce' );

		$id = isset( $_POST['freeradio_record_id'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['freeradio_record_id'] ) ) : '';
		if ( '' !== $id ) {
			update_option( self::OPTION_CURRENT_RECORD, $id );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=freeradio-records' ) );
		exit;
	}

	public static function handle_clear_current_record() : void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'freeradio_clear_current_record', 'freeradio_nonce' );

		update_option( self::OPTION_CURRENT_RECORD, '' );

		wp_safe_redirect( admin_url( 'admin.php?page=freeradio-records' ) );
		exit;
	}

	public static function register_shortcodes() : void {
		add_shortcode( 'freeradio_records', array( __CLASS__, 'shortcode_records' ) );
		add_shortcode( 'freeradio_player', array( __CLASS__, 'shortcode_player' ) );
	}

	public static function shortcode_player( array $atts = array(), string $content = '' ) : string {
		self::$frontend_assets_needed = true;

		$ws_url = (string) self::get_ws_url();
		return '<div class="freeradio-embed" data-freeradio-player="1" data-ws-url="' . esc_attr( $ws_url ) . '"><div class="freeradio-embed__row"><strong>FreeRadio</strong> <span class="freeradio-status" data-freeradio-status="1">Offline</span></div><audio class="freeradio-audio" data-freeradio-audio="1" controls preload="none"></audio></div>';
	}

	public static function shortcode_records( array $atts = array(), string $content = '' ) : string {
		self::$frontend_assets_needed = true;

		$records        = self::get_records();
		$current_record = self::get_current_record();

		ob_start();

		echo '<div class="freeradio-records">';
		echo '<div class="freeradio-records__grid">';
		echo '<div class="freeradio-records__col freeradio-records__current">';
		echo '<h3>Current record</h3>';
		if ( is_array( $current_record ) && ! empty( $current_record['url'] ) ) {
			$title = isset( $current_record['title'] ) ? (string) $current_record['title'] : '';
			echo '<div class="freeradio-records__current-title">' . esc_html( $title ) . '</div>';
			echo '<audio controls preload="none" src="' . esc_url( (string) $current_record['url'] ) . '"></audio>';
		} else {
			echo '<div class="freeradio-records__empty">No current record selected.</div>';
		}
		echo '</div>';

		echo '<div class="freeradio-records__col freeradio-records__list">';
		echo '<h3>Records</h3>';
		if ( empty( $records ) ) {
			echo '<div class="freeradio-records__empty">No records yet.</div>';
		} else {
			echo '<ul class="freeradio-records__items">';
			foreach ( $records as $record ) {
				if ( ! is_array( $record ) ) {
					continue;
				}
				$title = isset( $record['title'] ) ? (string) $record['title'] : '';
				$url   = isset( $record['url'] ) ? (string) $record['url'] : '';
				if ( '' === $url ) {
					continue;
				}
				echo '<li class="freeradio-records__item"><span class="freeradio-records__item-title">' . esc_html( $title ) . '</span><audio controls preload="none" src="' . esc_url( $url ) . '"></audio></li>';
			}
			echo '</ul>';
		}
		echo '</div>';

		echo '</div>';
		echo '</div>';

		return (string) ob_get_clean();
	}

	private static function get_ws_url() : string {
		$ws_url = (string) get_option( self::OPTION_WS_URL, 'ws://localhost:8080' );
		$ws_url = trim( $ws_url );
		return '' !== $ws_url ? $ws_url : 'ws://localhost:8080';
	}

	private static function get_records() : array {
		$records = get_option( self::OPTION_RECORDS, array() );
		return is_array( $records ) ? $records : array();
	}

	private static function new_record_id() : string {
		return 'rec_' . wp_generate_uuid4();
	}

	private static function get_current_record() : ?array {
		$current_id = (string) get_option( self::OPTION_CURRENT_RECORD, '' );
		if ( '' === $current_id ) {
			return null;
		}

		foreach ( self::get_records() as $record ) {
			if ( is_array( $record ) && isset( $record['id'] ) && (string) $record['id'] === $current_id ) {
				return $record;
			}
		}

		return null;
	}
}

