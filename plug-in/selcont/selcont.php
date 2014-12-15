<?php
/**
 * Plugin Name: SeLCont
 * Plugin URI: http://www.netmode.ntua.gr/
 * Description: SeLCont - Synchronized e-Learning Content Toolkit.
 * Author: NETMODE
 * Version: 0.0.1
 * Author URI: http://www.netmode.ntua.gr/
 *
 */

defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

require_once( 'classes/class-selcont.php' );

add_action('init', 'netmode_selcont_cb');

function netmode_selcont_cb() {
    new NETMODE_Selcont();
}