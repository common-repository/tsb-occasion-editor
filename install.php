<?php
/**
 * @package TSB_Occasions_Editor
 * @version 1.2.1
 */
/*
Plugin Name: TSB Occasions Editor
Plugin URI: http://google.com
Description: Add, delete, and modify occasions for The Style Blogger iPhone app.
Author: Ryan Nystrom
Version: 1.2.1
Author URI: http://whoisryannystrom.com
*/

function occ_install(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'tsb_occassions_editor';

	$create_table_sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  title VARCHAR(128) NOT NULL,
	  UNIQUE KEY id (id)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($create_table_sql);

	$table_name = $wpdb->prefix . 'tsb_occassions_images';

	$create_table_sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  tag_id mediumint(9) NOT NULL,
	  title VARCHAR(128) NOT NULL,
	  url TEXT NOT NULL,
	  descr TEXT NOT NULL,
	  UNIQUE KEY id (id)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($create_table_sql);
}

function occ_install_init_data(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'tsb_occassions_editor';

	$init_data = array(
		array(
			'tag' => 'funeral',
			'title' => 'Funeral'
		),
		array(
			'tag' => 'wedding',
			'title' => 'Wedding'
		)
	);

	foreach($init_data as $insert_statement){
		$rows_affected = $wpdb->insert($table_name, $insert_statement);
	}
}

// Runs the install functions called with a hook
register_activation_hook(__FILE__,'occ_install');

// Unnecessary
// register_activation_hook(__FILE__,'occ_install_init_data');

require_once(ABSPATH . 'wp-content/plugins/tsb-occasion-editor/menu.php');

?>