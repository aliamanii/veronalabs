<?php

namespace BookManager\Providers;

use Rabbit\Contracts\BootablePluginProviderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rabbit\Nonces\Nonce;

class BookServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface, BootablePluginProviderInterface {

    /**
     * Publisher Taxonomy slug
     *
     * @var string
     */
    public static $publishers_taxonomy = 'publishers';

    /**
     * Rewrite for Publisher
     *
     * @var string
     */
    public static $publishers_rewrite = 'publishers';

    /**
     * Authors Taxonomy slug
     *
     * @var string
     */
    public static $authors_taxonomy = 'authors';

    /**
     * Rewrite for Authors
     *
     * @var string
     */
    public static $authors_rewrite = 'authors';

	public function register() {
		// Register services or dependencies
	}

	public function boot() {
        add_action( 'init', array( $this, 'registerPostTypes' ) );
        add_action( 'init', array( $this, 'registerTaxonomies' ) );
        add_action( 'add_meta_boxes', array( $this, 'registerMetaBoxes' ) );
        add_action( 'save_post', array( $this, 'saveBook' ) );
	}

    public function registerPostTypes() {

        $labels = array(
            'name'               => __( 'Books', 'book-library' ),
            'singular_name'      => __( 'Book', 'book-library' ),
            'menu_name'          => __( 'Books', 'book-library' ),
            'name_admin_bar'     => __( 'Book', 'book-library' ),
            'add_new'            => __( 'Add New', 'book-library' ),
            'add_new_item'       => __( 'Add New Book', 'book-library' ),
            'new_item'           => __( 'New Book', 'book-library' ),
            'edit_item'          => __( 'Edit Book', 'book-library' ),
            'view_item'          => __( 'View Book', 'book-library' ),
            'all_items'          => __( 'All Books', 'book-library' ),
            'search_items'       => __( 'Search Books', 'book-library' ),
            'parent_item_colon'  => __( 'Parent Book:', 'book-library' ),
            'not_found'          => __( 'No Books found.', 'book-library' ),
            'not_found_in_trash' => __( 'No Books found in Trash.', 'book-library' ),
        );

        $args = array(
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => true,
            'query_var'     => false,
            'rewrite'       => array( 'slug' => 'book' ),
            'has_archive'   => false,
            'menu_position' => 56,
            'menu_icon'     => 'dashicons-book-alt',
            'supports'      => array( 'title', 'editor', 'thumbnail' ),
            'show_in_rest'  => false
        );

        register_post_type( 'book', $args );

    }

    public function registerTaxonomies() {
        $publishers_labels = array(
            'name'                  => __( 'Publishers', 'book-library' ),
            'singular_name'         => __( 'Publisher', 'book-library' ),
            'menu_name'             => __( 'Publishers', 'book-library' ),
            'search_items'          => __( 'Search Publishers', 'book-library' ),
            'all_items'             => __( 'All Publishers', 'book-library' ),
            'parent_item'           => __( 'Parent Publisher', 'book-library' ),
            'parent_item_colon'     => __( 'Parent Publisher:', 'book-library' ),
            'edit_item'             => __( 'Edit Publisher', 'book-library' ),
            'update_item'           => __( 'Update Publisher', 'book-library' ),
            'add_new_item'          => __( 'Add new Publisher', 'book-library' ),
            'new_item_name'         => __( 'New Publisher name', 'book-library' ),
            'not_found'             => __( 'No Publishers found', 'book-library' ),
            'item_link'             => __( 'Publisher Link', 'book-library' ),
            'item_link_description' => __( 'A link to a Publisher.', 'book-library' ),
        );

        $publishers_args = array(
            'label'        => __( 'Publishers', 'book-library' ),
            'labels'       => $publishers_labels,
            'show_in_rest' => true,
            'show_ui'      => true,
            'query_var'    => true,
            'hierarchical' => true,
            'rewrite'      => array(
                'slug'         => self::$publishers_rewrite,
                'with_front'   => false,
                'hierarchical' => true,
            )
        );

        register_taxonomy( self::$publishers_taxonomy, 'book', $publishers_args );

        $authors_labels = array(
            'name'                  => __( 'Authors', 'book-library' ),
            'singular_name'         => __( 'Author', 'book-library' ),
            'menu_name'             => __( 'Authors', 'book-library' ),
            'search_items'          => __( 'Search Authors', 'book-library' ),
            'all_items'             => __( 'All Authors', 'book-library' ),
            'parent_item'           => __( 'Parent Author', 'book-library' ),
            'parent_item_colon'     => __( 'Parent Author:', 'book-library' ),
            'edit_item'             => __( 'Edit Author', 'book-library' ),
            'update_item'           => __( 'Update Author', 'book-library' ),
            'add_new_item'          => __( 'Add new Author', 'book-library' ),
            'new_item_name'         => __( 'New Author name', 'book-library' ),
            'not_found'             => __( 'No Authors found', 'book-library' ),
            'item_link'             => __( 'Author Link', 'book-library' ),
            'item_link_description' => __( 'A link to a Author.', 'book-library' ),
        );

        $authors_args = array(
            'label'        => __( 'Authors', 'book-library' ),
            'labels'       => $authors_labels,
            'show_in_rest' => true,
            'show_ui'      => true,
            'query_var'    => true,
            'hierarchical' => true,
            'rewrite'      => array(
                'slug'         => self::$authors_rewrite,
                'with_front'   => false,
                'hierarchical' => true,
            )
        );

        register_taxonomy( self::$authors_taxonomy, 'book', $authors_args );
    }

    public function registerMetaBoxes() {
        add_meta_box( 'books_meta', __( 'Book ISBN', 'book-library' ), array(
            $this,
            'books_meta_callback'
        ), 'book', 'side', 'high' );
    }

    public function books_meta_callback( $post ) {
        $isbn = get_post_meta( $post->ID, 'isbn', true );
        ?>
        <input type="text" name="isbn" id="isbn" size="25" autocomplete="off" value="<?php echo $isbn; ?>">
        <?php
        $nonce = new Nonce( 'custom_book_data' );
        echo $nonce->render();
    }

    public function saveBook( $post_id ) {
        $is_autosave = wp_is_post_autosave( $post_id );
        $is_revision = wp_is_post_revision( $post_id );

        if ( $is_autosave || $is_revision ) {
            return;
        }

        $post_type = get_post_type( $post_id );

        if ( 'book' == $post_type ) {

            $nonce = new Nonce( 'custom_book_data' );
            $token = $_POST['_custom_book_data-nonce'] ?? '';
            if ( $nonce->check( $token ) ) {

                if ( isset ( $_POST['isbn'] ) && $_POST['isbn'] !== '' ) {
                    $isbn = sanitize_text_field( $_POST['isbn'] );
                    update_post_meta( $post_id, 'isbn', $isbn );
                    $this->updateISBN( $post_id, $isbn );
                } else {
                    delete_post_meta( $post_id, 'isbn' );
                }
            }
        }
    }

    private function updateISBN( $id, $isbn ) {
        global $wpdb;

        $where = array(
            'post_id' => $id,
            'isbn'    => $isbn
        );

        $wpdb->replace( "{$wpdb->prefix}books_info", $where );
    }

	public function bootPlugin() {

		$instance = $this;

		$this->getContainer()::macro(
			'book',
			function () use ( $instance ) {
				return $instance->getContainer()->get( 'book' );
			}
		);

	}
}