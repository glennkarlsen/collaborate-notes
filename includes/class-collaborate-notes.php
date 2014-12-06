<?php

class Collaborate_Notes {

	protected $loader;
	protected $plugin_slug;
	protected $version;

	public function __construct() {

		$this->plugin_slug = 'collaborate-notes-slug';
		$this->version = '1.0.4';

		$this->load_dependencies();
		$this->define_admin_hooks();
	}


	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-collaborate-notes-admin.php';

		require_once plugin_dir_path( __FILE__ ) . 'class-collaborate-notes-loader.php';
		$this->loader = new Collaborate_Notes_Loader();

	}


	private function define_admin_hooks() {
		$admin = new Collaborate_Notes_Admin( $this->get_version(), $this->get_counts() );
		
		$this->loader->add_action( 'wp_mail_content_type', $admin, 'set_html_content_type' );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_bar_menu', $admin, 'add_admin_menu', 999 );
		//$this->loader->add_action( 'admin_menu', $admin, 'register_settings_page' );	
		$this->loader->add_action( 'admin_footer', $admin, 'render_modal_box' );	

		$this->loader->add_action( 'wp_ajax_get_user_list', $admin, 'get_user_list_callback' );	
		$this->loader->add_action( 'wp_ajax_get_all_notes', $admin, 'get_all_notes_callback' );	
		$this->loader->add_action( 'wp_ajax_add_note', $admin, 'add_note_callback' );	
		$this->loader->add_action( 'wp_ajax_delete_note', $admin, 'delete_note_callback' );	
		$this->loader->add_action( 'wp_ajax_update_note', $admin, 'update_note_callback' );	
		$this->loader->add_action( 'wp_ajax_get_correct_userlist', $admin, 'get_correct_userlist_callback' );
		$this->loader->add_action( 'wp_ajax_notify_users', $admin, 'notify_users_callback' );	

		$this->loader->add_action( 'wp_ajax_add_reminder', $admin, 'add_reminder_callback' );	
		$this->loader->add_action( 'wp_ajax_update_reminder', $admin, 'update_reminder_callback' );	
		$this->loader->add_action( 'wp_ajax_delete_reminder', $admin, 'delete_reminder_callback' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_version() {
		return $this->version;
	}

	public function get_counts() {
		global $wpdb;

		if ( !function_exists('wp_get_current_user') ) {
			function wp_get_current_user() {
				require (ABSPATH . WPINC . '/pluggable.php');
				global $current_user;
				get_currentuserinfo();
				return $current_user;
			}
		}

		$all_notes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."collaborate_notes WHERE completed = 0");
		$user_id = wp_get_current_user()->ID;
		$viewData = array();

		foreach ($all_notes as $key => $value) {
			$assigned_to = array();

			foreach (json_decode($value->assigned_to) as $key2 => $value2) {
				$assigned_to[] = $value2->user_id;
			}

			if (in_array($user_id, $assigned_to) || $user_id == $value->user_id) {
			    $viewData[] = array(
			      'note_id' => $value->id
			    );
			}
		}
		return count($viewData);
	}

}
