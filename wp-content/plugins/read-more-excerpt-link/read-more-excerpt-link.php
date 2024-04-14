<?php
/*
Plugin Name: Read More Excerpt Link
Plugin URI: https://wordpress.org/plugins/read-more-excerpt-link/
Description: Create "Read More" link after post excerpt instead of ellipsis [...] Also modify excerpt length.
Version: 1.6.1
Author: Tim Eckel
Author URI: https://www.dogblocker.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: read-more-excerpt
*/

/*
	Copyright 2023  Tim Eckel  (email : eckel.tim@gmail.com)

	Read More Excerpt Link is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	any later version.

	Read More Excerpt Link is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Read More Excerpt Link; if not, see https://www.gnu.org/licenses/gpl-3.0.html
*/

if ( !defined( 'ABSPATH' ) ) exit;

function teckel_init_read_more_excerpt_link() {
	function teckel_read_more_excerpt_link( $more ) {
		$link_val = get_option( 'read_more_excerpt_link_text' );
		if ( !$link_val ) $link_val = 'Read More';
		if ( get_option( 'read_more_excerpt_include_ellipsis' ) == 1 ) $ellipsis = '&hellip;'; else $ellipsis = '';
		return $ellipsis.' <a class="read-more-link" href="' . get_permalink( get_the_ID() ) . '">' . $link_val . '</a>';
	}
	add_filter( 'excerpt_more', 'teckel_read_more_excerpt_link' );

	function teckel_get_the_excerpt( $output ) {
		if ( get_option( 'read_more_excerpt_more_often' ) == 1 && !is_attachment() && get_post()->post_content ) {
			$link_val = get_option( 'read_more_excerpt_link_text' );
			$length_val = get_option( 'read_more_excerpt_word_length' );
			if ( get_option( 'read_more_excerpt_include_ellipsis' ) == 1 ) $ellipsis = '&hellip;'; else $ellipsis = '';
			if ( !$link_val ) $link_val = 'Read More';
			if ( intval($length_val) <= 0 ) $length_val = 55;
			if ( has_excerpt() ) {
				$output .= ' <a class="read-more-link" href="' . get_permalink( get_the_ID() ) . '">' . $link_val . '</a>';
			} else if ( !has_excerpt() && strpos(get_the_content(),'#more-') && count(explode( ' ', $output)) <= $length_val ) {
				$output .= $ellipsis.' <a class="read-more-link" href="' . get_permalink( get_the_ID() ) . '">' . $link_val . '</a>';
			}
		}
		return $output;
	}
	add_filter( 'get_the_excerpt', 'teckel_get_the_excerpt' );
}
add_action( 'init', 'teckel_init_read_more_excerpt_link', 0 );

function teckel_read_more_excerpt_link_menu() {
	add_options_page( 'Read More Excerpt Link Options', 'Read More Excerpt', 'manage_options', 'read_more_excerpt_link_admin', 'read_more_excerpt_link_menu_options' );
}
add_action( 'admin_menu', 'teckel_read_more_excerpt_link_menu' );

function teckel_custom_excerpt_length( $length ) {
	$length_val = get_option( 'read_more_excerpt_word_length' );
	if ( intval($length_val) <= 0 ) $length_val = 55;
	return $length_val;
}
add_filter( 'excerpt_length', 'teckel_custom_excerpt_length', 999 );

function read_more_excerpt_link_menu_options() {
	if ( !current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	$link_val = get_option( 'read_more_excerpt_link_text' );
	$length_val = get_option( 'read_more_excerpt_word_length' );
	$include_ellipsis = get_option( 'read_more_excerpt_include_ellipsis' );
	$more_often = get_option( 'read_more_excerpt_more_often' );
	if ( !$link_val ) $link_val = 'Read More';
	if ( intval($length_val) <= 0 ) $length_val = 55;
	if ( $include_ellipsis != 1 ) $include_ellipsis = 0;
	if ( $more_often != 1 ) $more_often = 0;
	if( isset($_POST[ 'read_more_excerpt_link_submit_hidden' ]) && $_POST[ 'read_more_excerpt_link_submit_hidden' ] == 'Y' ) {
		if ( !wp_verify_nonce($_POST[ 'read_more_excerpt_nonce' ], 'read-more-excerpt-nonce') )
			wp_die( __( 'Form failed nonce verification.' ) );		if ( isset( $_POST[ 'read_more_excerpt_link_text' ] ) ) $link_val = filter_var ( $_POST[ 'read_more_excerpt_link_text' ], FILTER_SANITIZE_STRING );
		if ( isset( $_POST[ 'read_more_excerpt_word_length' ] ) ) $length_val = filter_var ( $_POST[ 'read_more_excerpt_word_length' ], FILTER_SANITIZE_NUMBER_INT );
		if ( isset( $_POST[ 'read_more_excerpt_include_ellipsis' ] ) ) $include_ellipsis = filter_var ( $_POST[ 'read_more_excerpt_include_ellipsis' ], FILTER_SANITIZE_NUMBER_INT ); else $include_ellipsis = 0;
		if ( isset( $_POST[ 'read_more_excerpt_more_often' ] ) ) $more_often = filter_var ( $_POST[ 'read_more_excerpt_more_often' ], FILTER_SANITIZE_NUMBER_INT ); else $more_often = 0;
		if ( !$link_val ) $link_val = 'Read More';
		if ( intval($length_val) <= 0 ) $length_val = 55;
		if ( $include_ellipsis != 1 ) $include_ellipsis = 0;
		if ( $more_often != 1 ) $more_often = 0;
		update_option( 'read_more_excerpt_link_text', $link_val );
		update_option( 'read_more_excerpt_word_length', $length_val );
		update_option( 'read_more_excerpt_include_ellipsis', $include_ellipsis );
		update_option( 'read_more_excerpt_more_often', $more_often );
		echo '<div class="updated"><p><strong>' . __( 'Settings saved.', 'read-more-excerpt' ) . '</strong></p></div>';
	}
?>

<div class="wrap">
	<h2>Read More Excerpt Link Settings</h2>
	<form name="form1" method="post" action="">
		<input type="hidden" name="read_more_excerpt_nonce" value="<?php echo wp_create_nonce('read-more-excerpt-nonce'); ?>">
		<input type="hidden" name="read_more_excerpt_link_submit_hidden" value="Y">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label><?php echo __( '"Read More" Link Text', 'read-more-excerpt' ); ?></label>
					</th>
					<td>
						<input type="text" name="read_more_excerpt_link_text" value="<?php echo $link_val; ?>" size="20" placeholder="Read More" class="medium-text">
						<p class="description">Enter a custom read more text link (defaults to "Read More").</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php echo __( 'Excerpt Word Length', 'read-more-excerpt' ); ?></label>
					</th>
					<td>
						<input type="number" name="read_more_excerpt_word_length" value="<?php echo $length_val; ?>" size="5" step="1" min="1" placeholder="55" class="medium-text">
						<p class="description">Enter a custom excerpt word length (defaults to 55 words).</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php echo __( 'Include Ellipsis after Excerpt', 'read-more-excerpt' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="read_more_excerpt_include_ellipsis" value="1"<?php echo ($include_ellipsis==1?' checked':''); ?>>
						<p class="description">Include an ellipsis "&hellip;" at the end of a truncated excerpt (default: off).</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php echo __( 'Show More Frequently', 'read-more-excerpt' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="read_more_excerpt_more_often" value="1"<?php echo ($more_often==1?' checked':''); ?>>
						<p class="description">Show "Read More" link even if excerpt is specified or read more tag is used in content (default: off).</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
		</p>
	</form>
</div>

<?php

}

?>