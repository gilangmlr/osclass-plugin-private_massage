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
    @mkdir(osc_content_path().'uploads/private-message/');
    osc_set_preference('upload_path', osc_content_path().'uploads/private-message/', 'private_message', 'STRING');
  }

  function custom_function_call_after_uninstall() {
    $conn = getConnection() ;
    $conn->autocommit(false);
    try {
      $conn->osc_dbExec('DROP TABLE %st_message', DB_TABLE_PREFIX);
      $conn->osc_dbExec('DROP TABLE %st_message_room', DB_TABLE_PREFIX);
      $conn->commit();
    } catch (Exception $e) {
      $conn->rollback();
      echo $e->getMessage();
    }
    $conn->autocommit(true);
    $dirname = osc_get_preference('upload_path', 'private_message');
    @array_map('unlink', glob("$dirname/*"));
    @rmdir($dirname);
    osc_delete_preference('upload_path', 'private_message');
  }

  function hide_contact() {
    require_once(osc_plugins_path() . 'private_message/hide_contact.php');
  }

  osc_add_route('private-message', 'private-message/([0-9]+)', 'private-message/{message_room_id}', osc_plugin_folder(__FILE__).'private_message.php');
  osc_add_route('private-message-start', 'private-message/start/([0-9]+)', 'private-message/start/{item_id}', osc_plugin_folder(__FILE__).'private_message_start.php');
  osc_add_route('private-message-list', 'private-message/list/([0-9]*)', 'private-message/list/{item_id}', osc_plugin_folder(__FILE__).'private_message_list.php');

  osc_add_hook('footer', 'hide_contact');
  osc_register_plugin(osc_plugin_path(__FILE__), 'custom_function_call_after_install') ;
  osc_add_hook(osc_plugin_path(__FILE__)."_uninstall", 'custom_function_call_after_uninstall');
?>