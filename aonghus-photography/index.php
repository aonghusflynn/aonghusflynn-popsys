<?php
/**
 * Fallback template — WordPress requires this file to exist.
 * In practice, more specific templates (front-page.php, page.php, etc.)
 * will always be used first.
 *
 * @package Aonghus_Photography
 */

get_header();
?>
<div class="page-content">
  <p><?php esc_html_e( 'Nothing found.', 'aonghus-photography' ); ?></p>
</div>
<?php get_footer(); ?>
