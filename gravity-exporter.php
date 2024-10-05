<?php

/**
 * Plugin Name: Gravity Exporter
 * Description: Allows you to export your Gravity Forms forms to PHP code.
 * Author: Tom de Visser
 * Version: 1.0.0
 */

defined('ABSPATH') or die;

define('GE_DIR', __DIR__ . '/');

define('GE_INC', GE_DIR . 'includes/');

require_once GE_INC . 'menu-page.php';
