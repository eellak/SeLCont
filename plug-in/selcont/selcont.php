<?php

/*
Plugin Name: Selcont
Plugin URI: http://selcont.lab.netmode.ntua.gr
Description: A sophisticated Learning Management System for Wordpress
Version: 1.0
Author: netmode
License: GPL2
*/

require_once( 'classes/class-selcont.php' );

add_action('init', 'netmode_selcont_cb');

function netmode_selcont_cb() {
    new NETMODE_Selcont();
}
