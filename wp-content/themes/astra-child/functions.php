<?php
function enqueue_parent_theme_styles()
{
    wp_enqueue_style('parent-theme-styles', get_template_directory_uri() . '/style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_parent_theme_styles');


