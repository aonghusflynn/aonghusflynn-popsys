<?php
/**
 * Single product template override.
 * Dark styling is applied via assets/css/shop.css.
 * WooCommerce renders product content via its own content-single-product.php template part.
 *
 * @package Aonghus_Photography
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<div class="woocommerce">

  <?php while ( have_posts() ) : the_post(); ?>
    <?php wc_get_template_part( 'content', 'single-product' ); ?>
  <?php endwhile; ?>

</div><!-- .woocommerce -->

<?php get_footer( 'shop' ); ?>
