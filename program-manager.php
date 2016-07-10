<?php
  /*
  Plugin Name: Program Manager
  Version: 1
  Author: Mustafa Kalash
  */

  defined('ABSPATH') or die('Unauthorized access.');
  global $wpdb;

  // install hook
  function pm_install() {
    require(ABSPATH.'wp-admin/includes/upgrade.php');
    $program_table_name = $wpdb->prefix.'pm_programs';
    $application_table_name = $wpdb->prefix.'pm_applications';
    $charset_collate = $wpdb->get_charset_collate();

    $program_sql = "
      CREATE TABLE $program_table_name (
        `token` CHAR(32) NOT NULL,
        `name` TINYTEXT NOT NULL,
        `slots` SMALLINT NOT NULL,
        `filled_slots` SMALLINT NOT NULL,
        PRIMARY KEY `token` (`token` (32))
      )
      $charset_collate;
    ";
    $application_sql = "
      CREATE TABLE $application_table_name (
        `token` CHAR(32) NOT NULL,
        `first_name` TINYTEXT NOT NULL,
        `last_name` TINYTEXT NOT NULL,
        `program` CHAR(32) NOT NULL,
        `approved` BOOL NOT NULL,
        INDEX `program` (`program` (32)),
        PRIMARY KEY `token` (`token` (32))
      )
      $charset_collate;
    ";

    dbDelta($program_sql);
    dbDelta($application_sql);
  }
  register_activation_hook(__FILE__, 'pm_install');

  // uninstall hook
  function pm_uninstall() {
    require(ABSPATH.'wp-admin/includes/upgrade.php');
    $program_table_name = $wpdb->prefix.'pm_programs';
    $application_table_name = $wpdb->prefix.'pm_application';

    dbDelta("DROP TABLE $program_table_name; DROP TABLE $application_table_name;");
  }
  register_deactivation_hook(__FILE__, 'pm_uninstall');

  // admin menu
  function pm_add_admin_menu() {
    add_menu_page('Program Manager', 'Program Manager', 'publish_pages', 'pm-program-manager', 'pm_build_admin_menu');
  }
  function pm_build_admin_menu() {
    if(!current_user_can('publish_pages')) {
      wp_die(__('You do not have sufficient permissions to access this page (publish_pages).'));
    }
    ob_start();
    require('admin-pages/main.php');
    ob_end_flush();
  }
  add_action('admin_menu', 'pm_add_admin_menu');

  // [programmanager]
  function pm_build_site_page() {
    ob_start();
    require('site-pages/main.php');
    ob_end_flush();
  }
  add_shortcode('programmanager', 'pm_build_site_page');
?>
