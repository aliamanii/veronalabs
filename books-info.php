<?php
/**
 * Plugin Name:     Book Library
 * Plugin URI:      https://github.com/aliamanii/veronalabs
 * Plugin Prefix:   BOL
 * Description:     Test plugin.
 * Author:          Ali Amani
 * Author URI:      https://github.com/aliamanii
 * Text Domain:     book-library
 * Domain Path:     /languages
 * Version:         1.0
 */

namespace BookManager;

use Exception;
use League\Container\Container;
use Rabbit\Application;
use Rabbit\Redirects\AdminNotice;
use Rabbit\Utils\Singleton;
use Rabbit\Plugin;
use BookManager\Admin\BookAdminProvider;
use BookManager\Providers\BookServiceProvider;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload.php';
}

/**
 * Class BookManagerPlugin
 * @package BookManagerPlugin
 */
class BookManagerPlugin extends Singleton {
	/**
	 * @var Container
	 */
	private $application;

	/**
	 * WPSmsWooPro constructor.
	 */
	public function __construct() {
		$this->application = Application::get()->loadPlugin( __DIR__, __FILE__, 'config' );
		$this->init();
	}

	public function init() {
		try {

			/**
			 * Load service providers
			 */
			$this->application->addServiceProvider( BookServiceProvider::class );
			$this->application->addServiceProvider( BookAdminProvider::class );

			/**
			 * Activation hooks
			 */
			$this->application->onActivation( function () {
				global $wpdb;
				$table_name      = $wpdb->prefix . 'books_info';
				$charset_collate = $wpdb->get_charset_collate();

				$sql = "CREATE TABLE $table_name (
		            id bigint(20) NOT NULL AUTO_INCREMENT,
		            post_id bigint(20) NOT NULL,
		            isbn varchar(17) NOT NULL,
		            PRIMARY KEY (id),
		            UNIQUE KEY post_id (post_id)
		        ) $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			} );

			/**
			 * Deactivation hooks
			 */
//			$this->application->onDeactivation(function () {
//				// Clear events, cache or something else
//			});

			$this->application->boot( function ( Plugin $plugin ) {
				$plugin->loadPluginTextDomain();
			} );

		} catch ( Exception $e ) {
			/**
			 * Print the exception message to admin notice area
			 */
			add_action( 'admin_notices', function () use ( $e ) {
				AdminNotice::permanent( [ 'type' => 'error', 'message' => $e->getMessage() ] );
			} );

			/**
			 * Log the exception to file
			 */
			add_action( 'init', function () use ( $e ) {
				if ( $this->application->has( 'logger' ) ) {
					$this->application->get( 'logger' )->warning( $e->getMessage() );
				}
			} );
		}
	}

	/**
	 * @return Container
	 */
	public function getApplication() {
		return $this->application;
	}
}

/**
 * Returns the main instance of RabbitExamplePlugin.
 * @return BookManagerPlugin
 */
function RabbitExamplePlugin() {
	return BookManagerPlugin::get();
}

RabbitExamplePlugin();