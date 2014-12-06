<?php
/**
 * Plugin Name:       Collaborate Notes
 * Plugin URI:        http://wordpress.org/plugins/collaborate-notes
 * Description:       Easy create and share important notes and tasks. Automatic notifying assigned users through email.
 * Version:           1.0.4
 * Author:            Glenn Sjöström
 * Author URI:        http://glennsjostrom.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


if ( ! defined( 'WPINC' ) ) {
	die;
}


global $collaborate_notes_db_version;
$collaborate_notes_db_version = '1.0.0';

function collaborate_notes_install() {
	global $wpdb;
	global $collaborate_notes_db_version;

	$table_name = $wpdb->prefix . 'collaborate_notes';
	$table_name2 = $wpdb->prefix . 'collaborate_notes_reminders';

	$charset_collate = 'utf8';

	if ( ! empty( $wpdb->charset ) ) {
	  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
	  $charset_collate .= " COLLATE {$wpdb->collate}";
	}


		$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				user_id mediumint(9) NOT NULL,
				display_name VARCHAR(55) NOT NULL,
				description text NOT NULL,
				completed tinyint(1) DEFAULT NULL,
				created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				last_updated datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				last_updated_by mediumint(9) NOT NULL,
				log_message tinytext NOT NULL,
				assigned_to text NOT NULL,
				UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


		$sql = "CREATE TABLE $table_name2 (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				user_id mediumint(9) NOT NULL,
				note_id mediumint(9) NOT NULL,
				reminder datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	add_option( 'collaborate_notes_active', 'true' );
	add_option( 'collaborate_notes_db_version', $collaborate_notes_db_version );
}

register_activation_hook( __FILE__, 'collaborate_notes_install' );
register_uninstall_hook( __FILE__, 'collaborate_notes_uninstall' );

function collaborate_notes_uninstall() {
        global $wpdb;

		$table_name = $wpdb->prefix . 'collaborate_notes';
		$table_name2 = $wpdb->prefix . 'collaborate_notes_reminders';

		delete_option('collaborate_notes_active');
		delete_option('collaborate_notes_db_version');

		$wpdb->query("DROP TABLE IF EXISTS $table_name");
		$wpdb->query("DROP TABLE IF EXISTS $table_name2");
}


function collaborate_notes_update_db_check() {
    global $collaborate_notes_db_version;
    if ( get_site_option( 'collaborate_notes_db_version' ) != $collaborate_notes_db_version ) {
        collaborate_notes_install();
    }
}

add_action( 'plugins_loaded', 'collaborate_notes_update_db_check' );


require_once plugin_dir_path( __FILE__ ) . 'includes/class-collaborate-notes.php';
function run_collaborate_notes() {

	if ( is_admin() && get_option( 'collaborate_notes_active' ) == 'true' ) {

		$instance = new Collaborate_Notes();
		$instance->run();

	}

}
run_collaborate_notes();


function send_reminders( $user_id, $note_id ) {
	global $wpdb;

	$user_email = $wpdb->get_var("SELECT user_email FROM {$wpdb->users} WHERE ID = {$user_id}");
	$note_description = $wpdb->get_var("SELECT description FROM ".$wpdb->prefix."collaborate_notes WHERE id = {$note_id}");
	$message = $note_description."<br>---<br><a href='".admin_url()."'>Login here</a><br><br>";

	$mail_data = array(
		'to' => $user_email,
		'subject' => site_url().": This is a reminder",
		'message' => $message
	);

	wp_mail( $mail_data['to'], $mail_data['subject'], $mail_data['message'] );

}

add_filter( 'wp_mail_content_type', function($content_type){
	return 'text/html';
});

add_action( 'send_reminder_event', 'send_reminders', 1 , 2 );