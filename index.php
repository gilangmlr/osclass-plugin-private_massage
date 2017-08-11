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

  }

  function hide_contact() {
    require_once(osc_plugins_path() . 'private_message/hide_contact.php');
  }

  osc_add_route('private-message', 'private-message/([0-9]+)/([0-9]+)', 'private-message/{user}/{item}', osc_plugin_folder(__FILE__).'private_message.php'); 

  osc_add_hook('footer', 'hide_contact');
  osc_register_plugin(osc_plugin_path(__FILE__), 'custom_function_call_after_install') ;
?>