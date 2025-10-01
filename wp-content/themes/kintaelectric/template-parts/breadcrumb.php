<?php
/**
 * Template part for displaying breadcrumbs
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Only show breadcrumbs if not on homepage
if ( ! is_front_page() && ! is_home() ) {
    kintaelectric_breadcrumb();
}
?>
