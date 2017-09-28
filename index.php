<?php
/*
Plugin Name: Private Message
Plugin URI: mailto:gilangmlr@gmail.com
Description: This plugin adds private message functionality at the product of every page.
Version: 0.0.1
Author: Gilang Gumilar
Author URI: mailto:gilangmlr@gmail.com
Short Name: pm
Plugin update URI: mailto:gilangmlr@gmail.com
*/

  function custom_function_call_after_install() {
    $conn = getConnection() ;
    $conn->autocommit(false);
    try {
      $path = osc_plugin_resource('private_message/struct.sql');
      $sql = file_get_contents($path);
      $conn->osc_dbImportSQL($sql);
      $conn->commit();
    } catch (Exception $e) {
      $conn->rollback();
      echo $e->getMessage();
    }
    $conn->autocommit(true);
  }

  function custom_function_call_after_uninstall() {
    $conn = getConnection() ;
    $conn->autocommit(false);
    try {
      $conn->osc_dbExec('DROP TABLE %st_message_room', DB_TABLE_PREFIX);
      $conn->osc_dbExec('DROP TABLE %st_message', DB_TABLE_PREFIX);
      $conn->commit();
    } catch (Exception $e) {
      $conn->rollback();
      echo $e->getMessage();
    }
    $conn->autocommit(true);
  }

  function hide_contact() {
    require_once(osc_plugins_path() . 'private_message/hide_contact.php');
  }

  osc_add_route('private-message', 'private-message/([0-9]+)', 'private-message/{message_room_id}', osc_plugin_folder(__FILE__).'private_message.php'); 

  osc_add_hook('footer', 'hide_contact');
  osc_register_plugin(osc_plugin_path(__FILE__), 'custom_function_call_after_install') ;
  osc_add_hook(osc_plugin_path(__FILE__)."_uninstall", 'custom_function_call_after_uninstall');
?>