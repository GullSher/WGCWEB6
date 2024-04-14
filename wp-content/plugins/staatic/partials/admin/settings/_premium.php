<?php

namespace Staatic\Vendor;

/**
 * @var \Staatic\WordPress\Service\Formatter $_formatter
 */
?>

<h2 class="wp-heading-inline">
    <?php 
\esc_html_e('Staatic for WordPress Premium', 'staatic');
?>
</h2>

<p><?php 
\_e('First of all, thank you for choosing Staatic to make your WordPress site blazing fast and bulletproof.', 'staatic');
?></p>
<p><?php 
\_e('If you would like to support ongoing development of Staatic, please consider going Premium.', 'staatic');
?></p>
<p><?php 
\_e('In addition to helping the authors maintain Staatic, Staatic for WordPress Premium adds <strong>additional functionality</strong>.', 'staatic');
?></p>

<h2 class="wp-heading-inline">
    <span class="dashicons dashicons-flag"></span>
    <?php 
\esc_html_e('Premium Features', 'staatic');
?>
</h2>

<p><?php 
\_e('Additional features include (but not limited to):', 'staatic');
?></p>

<ul>
    <li><span class="dashicons dashicons-yes"></span> <?php 
\_e('Quick publications, publishing <strong>only changed content</strong>', 'staatic');
?></li>
    <li><span class="dashicons dashicons-yes"></span> <?php 
\_e('<strong>Automated</strong> publications based on changed content or a schedule', 'staatic');
?></li>
    <li><span class="dashicons dashicons-yes"></span> <?php 
\_e('<strong>Form submission</strong> handling', 'staatic');
?></li>
    <li><span class="dashicons dashicons-yes"></span> <?php 
\_e('<strong>Search</strong> integration', 'staatic');
?></li>
</ul>

<p><?php 
echo \sprintf(
    /* translators: 1: Link to Staatic Premium. */
    \__('<a href="%1$s" target="_blank" rel="noopener">Click here to learn more about Staatic for WordPress Premium</a>', 'staatic'),
    'https://staatic.com/wordpress/activation/'
);
?></p>
<?php 
