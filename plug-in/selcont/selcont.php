<?php

/*
Plugin Name: Selcont
Plugin URI: http://selcont.lab.netmode.ntua.gr
Description: A sophisticated Learning Management System for Wordpress
Version: 1.0
Author: netmode
License: GPL2
*/

register_activation_hook( __FILE__, 'cem_selcont_install' );

function cem_selcont_install(){
    if(version_compare( get_bloginfo('version'), '3.1' '<')) {
        deactivate_plugins( basename( __FILE__ )); //Deactivate our plugin
    }
}


