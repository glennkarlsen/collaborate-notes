<?php

class Collaborate_Notes_Admin {

	private $version;
	private $count_notes;

	public function __construct( $version, $count_notes ) {
		$this->version = $version;
		$this->count_notes = $count_notes;
	}

	public function enqueue_styles() {

		wp_enqueue_style(
			'register_admin_style',
			plugin_dir_url( __FILE__ ) . 'css/collaborate-notes-admin.css',
			array(),
			$this->version,
			FALSE
		);

		wp_enqueue_style(
			'register_bootstrap_style',
			plugin_dir_url( __FILE__ ) . 'css/bootstrap.css',
			array(),
			$this->version,
			FALSE
		);

		wp_enqueue_style(
			'register_theme_style',
			plugin_dir_url( __FILE__ ) . 'css/bootstrap-theme.css',
			array(),
			$this->version,
			FALSE
		);


		wp_enqueue_style(
			'register_chosen_style',
			plugin_dir_url( __FILE__ ) . 'css/chosen.css',
			array(),
			$this->version,
			FALSE
		);

		wp_enqueue_style(
			'register_fontawesome_style',
			plugin_dir_url( __FILE__ ) . 'css/font-awesome.css',
			array(),
			$this->version,
			FALSE
		);

		wp_enqueue_style(
			'register_datepicker_style',
			plugin_dir_url( __FILE__ ) . 'css/datepicker3.css',
			array(),
			$this->version,
			FALSE
		);

		wp_enqueue_style(
			'register_timerpicker_style',
			plugin_dir_url( __FILE__ ) . 'css/jquery.timepicker.css',
			array(),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_admin_script',
			plugin_dir_url( __FILE__ ) . 'js/collaborate-notes-admin.js',
			array('backbone', 'underscore'),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_chosen_script',
			plugin_dir_url( __FILE__ ) . 'js/chosen.jquery.js',
			array('jquery'),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_tooltip_script',
			plugin_dir_url( __FILE__ ) . 'js/tooltip.js',
			array('jquery'),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_alert_script',
			plugin_dir_url( __FILE__ ) . 'js/alert.js',
			array('jquery'),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_datepicker_script',
			plugin_dir_url( __FILE__ ) . 'js/bootstrap-datepicker.js',
			array('jquery'),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_timepicker_script',
			plugin_dir_url( __FILE__ ) . 'js/jquery.timepicker.js',
			array('jquery'),
			$this->version,
			FALSE
		);

		wp_enqueue_script(
			'register_moment_script',
			plugin_dir_url( __FILE__ ) . 'js/moment.js',
			array('jquery'),
			$this->version,
			FALSE
		);


	}


	public function add_admin_menu( $wp_admin_bar ) {
		$args = array(
			'id'    => 'notes_toolbar',
			'title' => 'Notes (<span id="count-notes">'.$this->count_notes.'</span>)',
			'href'  => '#collaborate-notes',
		);
		$wp_admin_bar->add_node( $args );
	}


	public function get_user_list_callback() {
		global $wpdb;
		global $current_user;

		$userlist = $wpdb->get_results("SELECT * FROM $wpdb->users", OBJECT);

		$userdata = array();
		foreach ($userlist as $key) {
			if ($key->ID != $current_user->ID) {
				$userdata[] = array(
					'display_name' => $key->user_nicename,
					'email' => $key->user_email,
					'total_users' => count($userlist),
					'user_id' => $key->ID
				);
			}
		}

		echo json_encode($userdata);

		die();
	}

	public function register_settings_page() {
		add_submenu_page( 'options-general.php', 'Collaborate Notes Settings', 'Collaborate Notes Settings', 'manage_options', 'collaborate-notes', array($this, 'render_modal_box') ); 
	}

	public function render_modal_box() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/collaborate-notes.php';
	}

	public function get_all_notes_callback() {
		global $wpdb;
		global $current_user;

		$all_notes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."collaborate_notes ORDER BY created DESC");
		$userlist = $wpdb->get_results("SELECT * FROM $wpdb->users", OBJECT);


		$viewData = array();

		foreach ($all_notes as $key => $value) {

			$alredy_assigned_data = json_decode($value->assigned_to);

			$assigned_to_data = array();
			$users_signed_to_note = array();

			if (empty($alredy_assigned_data)) {

				foreach ($userlist as $key => $user) {

					// Om det inte är du själv..
					if ($current_user->ID != $user->ID) {

						$assigned_to_data[] = array(
							'user_id' => $user->ID,
							'display_name' => $user->user_nicename,
							'user_email' => $user->user_email,
							'selected' => false
						);
					}
				}

			} else {

				// Loopa igenom alla redan inlagda till assigned_to för denna note
				foreach ($alredy_assigned_data as $data) {

					$users_signed_to_note[] = $data->user_id;
						// Lägg bara till users som redan är signade
						$assigned_to_data[] = array(
							'user_id' => $data->user_id,
							'display_name' => $data->display_name,
							'user_email' => $data->user_email,
							'selected' => true
						);
				}

				/*
				 * 	Lägg till resterande users till selectorlistan..
				 */
				
				// var_dump("Användare signade till note ".$value->id.":" );
				// var_dump($users_signed_to_note);

				// Loopa igenom hela listan med users..
				foreach ($userlist as $key => $user) {
					
					// lägg inte till dig själv..
					if ($current_user->ID != $user->ID) {

						if (!in_array($user->ID, $users_signed_to_note)) {

							// lägg bara till users som inte redan är inlagda
							// lägg till data
							$assigned_to_data[] = array(
								'user_id' => $user->ID,
								'display_name' => $user->user_nicename,
								'user_email' => $user->user_email,
								'selected' => false
							);
						}

					}
				}
			}

			if ($current_user->ID == $value->user_id || in_array($current_user->ID, $users_signed_to_note)) {
				$created_by_you = ($current_user->ID == $value->user_id ? true : false);

				$reminder_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."collaborate_notes_reminders WHERE note_id = '{$value->id}'");


			    $viewData[] = array(
			      'description' => $value->description,
			      'note_id' => $value->id,
			      'createdByYou' => $created_by_you,
			      'display_name' => $value->display_name,
			      'assigned_to' => json_encode($assigned_to_data),
			      'log_message' => $value->log_message,
			      'completed' => ($value->completed == 0 ? false : true),
			      'reminder' => (isset($reminder_data) ? true : false),
			      'reminder_data' => (isset($reminder_data) ? $reminder_data->reminder : '')
			    );
			}

		}

	    echo json_encode($viewData);

		die();
	}



	public function get_correct_userlist_callback() {
		global $wpdb;
		global $current_user;

		$note = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."collaborate_notes WHERE id = '{$_POST['note_id']}'");

		$userlist = $wpdb->get_results("SELECT * FROM $wpdb->users", OBJECT);

		foreach ($note as $key => $value) {

			$alredy_assigned_data = json_decode($value->assigned_to);

			$assigned_to_data = array();
			$users_signed_to_note = array();

			if (empty($alredy_assigned_data)) {

				foreach ($userlist as $key => $user) {

					// Om det inte är du själv..
					if ($current_user->ID != $user->ID) {

						$assigned_to_data[] = array(
							'user_id' => $user->ID,
							'display_name' => $user->user_nicename,
							'user_email' => $user->user_email,
							'selected' => false
						);
					}
				}

			} else {

				// Loopa igenom alla redan inlagda till assigned_to för denna note
				foreach ($alredy_assigned_data as $data) {

					$users_signed_to_note[] = $data->user_id;
						// Lägg bara till users som redan är signade
						$assigned_to_data[] = array(
							'user_id' => $data->user_id,
							'display_name' => $data->display_name,
							'user_email' => $data->user_email,
							'selected' => true
						);
				}

				/*
				 * 	Lägg till resterande users till selectorlistan..
				 */

				// var_dump("Användare signade till note ".$value->id.":" );
				// var_dump($users_signed_to_note);

				// Loopa igenom hela listan med users..
				foreach ($userlist as $key => $user) {
					
					// lägg inte till dig själv..
					if ($current_user->ID != $user->ID) {

						if (!in_array($user->ID, $users_signed_to_note)) {

							// lägg bara till users som inte redan är inlagda
							// lägg till data
							$assigned_to_data[] = array(
								'user_id' => $user->ID,
								'display_name' => $user->user_nicename,
								'user_email' => $user->user_email,
								'selected' => false
							);
						}

					}
				}

			}

		}

	    echo json_encode($assigned_to_data);

		die();
	}



	public function array_has_dupes($array) {
	   return count($array) !== count(array_unique($array));
	}


	public function add_note_callback() {
		global $wpdb;
		global $current_user;

		$remove_dupes_assigned_to = json_decode(stripslashes($_POST['assigned_to']));
		$assigned_to_data = array();
		$assigned_to_data_response = array();
		$users_signed_to_note = array();

		if (!empty($remove_dupes_assigned_to)) {

			foreach ($remove_dupes_assigned_to as $value) {
				
				$user = get_user_by('id', $value);
				$users_signed_to_note[] = $user->ID;

				$assigned_to_data[] = array(
					'user_id' => $value,
					'display_name' => $user->user_nicename,
					'user_email' => $user->user_email,
					'selected' => true
				);

				$assigned_to_data_response[] = array(
					'user_id' => $value,
					'display_name' => $user->user_nicename,
					'user_email' => $user->user_email,
					'selected' => true,
					'is_empty' => false
				);
			}

			$userlist = $wpdb->get_results("SELECT * FROM $wpdb->users", OBJECT);

			foreach ($userlist as $key => $user) {
				// lägg inte till dig själv..
				if ($current_user->ID != $user->ID) {

					if (!in_array($user->ID, $users_signed_to_note)) {

						// lägg bara till users som inte redan är inlagda
						// lägg till data
						$assigned_to_data_response[] = array(
							'user_id' => $user->ID,
							'display_name' => $user->user_nicename,
							'user_email' => $user->user_email,
							'selected' => false,
							'is_empty' => false
						);
					}
				}
			}

		} else {

			$userlist = $wpdb->get_results("SELECT * FROM $wpdb->users", OBJECT);

			foreach ($userlist as $key) {
				if ($key->ID != $current_user->ID) {

					$assigned_to_data_response[] = array(
						'user_id' => $key->ID,
						'display_name' => $key->user_nicename,
						'user_email' => $key->user_email,
						'selected' => false,
						'is_empty' => true
					);
				}
			}
		}

		$log_message = "Created by {$current_user->user_nicename} " . date( 'H:i d M', current_time( 'timestamp', 0 ));

		$data = array(
			'description' => $_POST['description'],
			'user_id' => $current_user->ID,
			'display_name' => $current_user->user_nicename,
			'created' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 )),
			'last_updated' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 )),
			'last_updated_by' => $current_user->ID,
			'completed' => false,
			'assigned_to' => json_encode($assigned_to_data),
			'log_message' => $log_message
		);

		$add_note = $wpdb->insert($wpdb->prefix.'collaborate_notes', $data);
		$last_insert_id = $wpdb->insert_id;
		$date = '';

		/* Reminder action */
		if ($_POST['useReminder'] == 'true') {

			$posted_date = explode('/', $_POST['date']);
			$date = date('Y-m-d H:i:s', strtotime($posted_date[0]."-".$posted_date[1]."-".$posted_date[2]." ".$_POST['time']));

			$data_reminder = array(
				'user_id' => $current_user->ID,
				'note_id' => $wpdb->insert_id,
				'reminder' => $date
			);

			$add_reminder = $wpdb->insert($wpdb->prefix.'collaborate_notes_reminders', $data_reminder);

			wp_schedule_single_event( strtotime( get_gmt_from_date( $date ) ), 'send_reminder_event', array( $current_user->ID, $last_insert_id ) );

		}
		/* End reminder action */


		$response = array(
			'note_id' => $last_insert_id,
			'description' => $_POST['description'],
			'assigned_to' => $assigned_to_data_response,
			'log_message' => $log_message,
			'reminder' => ($_POST['useReminder'] == 'true' ? true : false ),
			'reminder_data' => $date
		);

		echo json_encode($response);

		if (!empty($remove_dupes_assigned_to)) {
			foreach ($remove_dupes_assigned_to as $user) {
				if ($user != $current_user->user_login) {

					$user = get_user_by('id', $user);
					$message = html_entity_decode($_POST['description']);

					$mail_data = array(
						'to' => $user->user_email,
						'subject' => site_url().": You have been assigned a note by {$current_user->display_name}",
						'message' => $message."<br>---<br><a href='".admin_url()."'>Login to answer</a>"
					);

					wp_mail( $mail_data['to'], $mail_data['subject'], $mail_data['message'] );
					remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
				}
			}
		}

		die();
	}


	public function delete_note_callback() {
		global $wpdb;
		global $current_user;

		$args_reminder = array(
			'note_id' => $_POST['note_id'],
			'user_id' => $current_user->ID
		);
		
		$delete_reminder = $wpdb->delete($wpdb->prefix.'collaborate_notes_reminders', $args_reminder);

		wp_clear_scheduled_hook( 'send_reminder_event', array( $current_user->ID, intval($_POST['note_id']) ) );


		$args = array(
			'id' => $_POST['note_id'],
			'user_id' => $current_user->ID
		);

		$delete_note = $wpdb->delete($wpdb->prefix.'collaborate_notes', $args);

		if ($delete_note != 0) {
			echo 'Congrats!';
		}

		die();
	}


	public function update_note_callback() {
		global $wpdb;
		global $current_user;

		$assigned_to = isset($_POST['assigned_to']) ? json_decode(stripslashes($_POST['assigned_to'])) : null;
		$old_assigned_to = isset($_POST['note_id']) ? $wpdb->get_var("SELECT assigned_to FROM ".$wpdb->prefix."collaborate_notes WHERE id = '{$_POST['note_id']}'") : null;
		$assigned_to_data = array();

		if (count($assigned_to) > 0) {
			foreach ($assigned_to as $value) {
				$user = get_user_by( 'id', $value );

				$assigned_to_data[] = array(
					'user_id' => $value,
					'display_name' => $user->user_nicename,
					'user_email' => $user->user_email,
					'selected' => true
				);
			}
		}
		
		$log_message = "Edited by {$current_user->user_nicename} " . date( 'H:i d M', current_time( 'timestamp', 0 ));

		$args = array(
			'description' => stripslashes_deep($_POST['description']),
			'assigned_to' => json_encode($assigned_to_data),
			'log_message' => $log_message,
			'completed' => ($_POST['completed'] === 'true' ? '1' : '0' ),
			'last_updated' => date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 )),
			'last_updated_by' => $current_user->ID
		);

		$add_note = isset($_POST['note_id']) ? $wpdb->update($wpdb->prefix.'collaborate_notes', $args, array('id' => $_POST['note_id'])) : null;
		$notified = "";

			if (!is_null($old_assigned_to) && $old_assigned_to != json_encode($assigned_to_data)) {

				$a = isset($_POST['note_id']) ? json_decode($old_assigned_to) : null;
				$b = $assigned_to_data;

				$old_users = array();
				$now_users = array();

				foreach ($a as $key) {
					$old_users[] = $key->user_id;
				}

				foreach ($b as $key => $value) {
					$now_users[] = $value['user_id'];
				}

				// Det här är dem nya användarna som är tillagda.
				$new_user = array_diff($now_users, $old_users);

				foreach ($new_user as $user_id) {

					if ($user_id != $current_user->ID) {

						$user = get_user_by('id', $user_id);
						$notified .= "This is just a respone message";

						$mail_data = array(
							'to' => $user->user_email,
							'subject' => "You have been assigned a note by {$current_user->display_name}",
							'message' => $_POST['description']."<br>---<br><a href='".admin_url()."'>Login to answer</a>"
						);

						wp_mail( $mail_data['to'], $mail_data['subject'], $mail_data['message'] );
						remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
					}
				}
			}

		$date = $wpdb->get_var("SELECT reminder FROM ".$wpdb->prefix."collaborate_notes_reminders WHERE note_id = {$_POST['note_id']}");

		$response = array(
			'note_id' => isset($_POST['note_id']) ? $_POST['note_id'] : null,
			'notified' => $notified,
			'log_message' => $log_message,
			'description' => stripslashes_deep($_POST['description']),
			'assigned_to' => $assigned_to_data,
			'reminder' => $date
		);

		echo json_encode($response);
		
		die();
	}


	public function notify_users_callback() {
		global $wpdb;
		global $current_user;

		$assigned_to = isset($_POST['note_id']) ? $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."collaborate_notes WHERE id = '{$_POST['note_id']}'") : null;
		$creater_email = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE ID = '{$assigned_to->user_id}'");

		// Lägg till $creater_email i $value->user_email
		$new_json_array = json_decode($assigned_to->assigned_to);
		array_push($new_json_array,  json_decode(json_encode(array('user_email' => $creater_email)), FALSE));

		foreach ($new_json_array as $value) {

			$mail_data = array(
				'to' => $value->user_email,
				'subject' => site_url().": {$current_user->display_name} have edited a note.",
				'message' => $assigned_to->description."<br>---<br><a href='".admin_url()."'>Login to answer</a>"
			);

			wp_mail( $mail_data['to'], $mail_data['subject'], $mail_data['message'] );
			remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
		}

		echo 'Notified!';
		
		die();
	}

	public function add_reminder_callback() {
		global $wpdb;
		global $current_user;
		/* Reminder action */

		$posted_date = explode('/', $_POST['date']);
		$date = date('Y-m-d H:i:s', strtotime($posted_date[0]."-".$posted_date[1]."-".$posted_date[2]." ".$_POST['time']));

		$data_reminder = array(
			'user_id' => $current_user->ID,
			'note_id' => intval($_POST['note_id']),
			'reminder' => $date
		);

		$add_reminder = $wpdb->insert($wpdb->prefix.'collaborate_notes_reminders', $data_reminder);

		wp_schedule_single_event( strtotime( get_gmt_from_date( $date ) ), 'send_reminder_event', array( $current_user->ID, intval($_POST['note_id']) ) );
		/* End reminder action */

		echo 'success';

		die();
	}

	public function update_reminder_callback() {
		global $wpdb;
		global $current_user;

		$posted_date = explode('/', $_POST['date']);
		$date = date('Y-m-d H:i:s', strtotime($posted_date[0]."-".$posted_date[1]."-".$posted_date[2]." ".$_POST['time']));

		$args = array(
			'reminder' => $date
		);

		wp_clear_scheduled_hook( 'send_reminder_event', array( $current_user->ID, intval($_POST['note_id']) ) );
		wp_schedule_single_event( strtotime( get_gmt_from_date( $date ) ), 'send_reminder_event', array( $current_user->ID, intval($_POST['note_id']) ) );

		$update_reminder = $wpdb->update($wpdb->prefix.'collaborate_notes_reminders', $args, array('note_id' => intval($_POST['note_id'])));

		echo 'success update';

		die();
	}


	public function delete_reminder_callback() {
		global $wpdb;
		global $current_user;

		$args = array(
			'note_id' => intval($_POST['note_id']),
			'user_id' => $current_user->ID
		);

		$date = $wpdb->get_var("SELECT reminder FROM ".$wpdb->prefix."collaborate_notes_reminders WHERE note_id = {$_POST['note_id']}");

		wp_clear_scheduled_hook( 'send_reminder_event', array( $current_user->ID, intval($_POST['note_id']) ) );

		$delete_note = $wpdb->delete($wpdb->prefix.'collaborate_notes_reminders', $args);

		if ($delete_note != 0) {
			echo 'Congrats!';
		}
		
		die();
	}


	public function set_html_content_type() {
		return 'text/html';
	}


}
