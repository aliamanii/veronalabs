<?php

namespace BookManager\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BooksListTable extends \WP_List_Table {
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Book', 'book-library' ),
			'plural'   => __( 'Books', 'book-library' ),
			'ajax'     => false

		] );
	}

	public static function get_books( $per_page = 20, $page_number = 1 ) {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}books_info";
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	public static function delete_book( $id ) {
		global $wpdb;
		$wpdb->delete(
			"{$wpdb->prefix}books_info",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}books_info";

		return $wpdb->get_var( $sql );
	}

	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'books_delete_row' );
		$title        = '<strong><a class="row-title" href="' . get_edit_post_link( $item['post_id'] ) . '" target="_blank">' . get_the_title( $item['post_id'] ) . '</a></strong>';
		$actions      = [
			'edit'   => '<a target="_blank" href="' . get_edit_post_link( $item['post_id'] ) . '">' . __( 'Edit', 'book-library' ) . '</a>',
			'delete' => sprintf( '<a href="?page=%s&action=%s&book=%s&_wpnonce=%s">' . __( 'Delete', 'book-library' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'post_id':
				return $this->column_name( $item );
			case 'isbn':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	function get_columns() {
		return [
			'cb'      => '<input type="checkbox" />',
			'post_id' => __( 'Book', 'book-library' ),
			'isbn'    => __( 'ISBN', 'book-library' ),
		];
	}

	public function get_bulk_actions() {
		return [
			'bulk-delete' => __( 'Remove', 'book-library' ),
		];
	}

	public function prepare_items() {

		$this->process_bulk_action();

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = $this->get_items_per_page( 'books_per_page' );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->items = self::get_books( $per_page, $current_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	public function process_bulk_action() {

		if ( 'delete' === $this->current_action() ) {

			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( wp_verify_nonce( $nonce, 'books_delete_row' ) ) {
				self::delete_book( absint( $_GET['book'] ) );
			}
		}

		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			foreach ( $delete_ids as $id ) {
				self::delete_book( $id );
			}
		}
	}
}