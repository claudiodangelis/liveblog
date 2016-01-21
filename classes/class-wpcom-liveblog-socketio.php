<?php

/**
 * The class responsible for adding WebSocket support
 * if the constant LIVEBLOG_USE_SOCKETIO is true and
 * requirements are met.
 *
 * PHP sends messages to a Socket.io server via a Redis
 * server using socket.io-php-emitter.
 */
class WPCOM_Liveblog_Socketio {

	/**
	 * @var SocketIO\Emitter
	 */
	private static $emitter;

	/**
	 * Load everything that is necessary to use WebSocket
	 *
	 * @return void
	 */
	public static function load() {
		if ( ! self::is_enabled() ) {
			return;
		}

		// load socket.io-php-emitter
		require( dirname( __FILE__ ) . '/../vendor/autoload.php' );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		self::$emitter = new SocketIO\Emitter();
	}

	/**
	 * Use socket.io instead of AJAX to update clients
	 * when new entries are created?
	 *
	 * @return bool whether socket.io is enabled or not
	 */
	public static function is_enabled() {
		return defined( 'LIVEBLOG_USE_SOCKETIO' ) && LIVEBLOG_USE_SOCKETIO;
	}

	/**
	 * Enqueue the necessary CSS and JS that the WebSocket support needs to function.
	 * Nothing is enqueued if not viewing a Liveblog post.
	 *
	 * @return void
	 */
	public static function enqueue_scripts() {
		if ( ! WPCOM_Liveblog::is_viewing_liveblog_post() ) {
			return;
		}

		wp_enqueue_script( 'socket.io', plugins_url( '../js/socket.io.min.js', __FILE__ ), array(), '1.4.4', true );
		wp_enqueue_script(
			'liveblog-socket.io',
			plugins_url( '../js/liveblog-socket.io.js', __FILE__ ),
			array( 'jquery', 'socket.io', WPCOM_Liveblog::key ),
			WPCOM_Liveblog::version,
			true
		);
	}

	/**
	 * Emits a message to all connected socket.io clients
	 * via Redis.
	 *
	 * @param string $name the name of the message
	 * @param string|array $data the content of the message
	 * @return void
	 */
	public static function emit( $name, $data ) {
		self::$emitter->json->emit( $name, $data );
		exit;
	}
}
