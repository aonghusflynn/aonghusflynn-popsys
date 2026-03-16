<?php
/**
 * Shop archive template override.
 * Dark styling is applied via assets/css/shop.css.
 * WooCommerce renders product loop content via its own content-product.php template part.
 *
 * @package Aonghus_Photography
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<div class="woocommerce">

  <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
    <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
  <?php endif; ?>

  <?php do_action( 'woocommerce_archive_description' ); ?>

  <?php if ( woocommerce_product_loop() ) : ?>

    <?php do_action( 'woocommerce_before_shop_loop' ); ?>

    <?php woocommerce_product_loop_start(); ?>

    <?php while ( have_posts() ) : the_post(); ?>
      <?php do_action( 'woocommerce_shop_loop' ); ?>
      <?php wc_get_template_part( 'content', 'product' ); ?>
    <?php endwhile; ?>

    <?php woocommerce_product_loop_end(); ?>

    <?php do_action( 'woocommerce_after_shop_loop' ); ?>

  <?php else : ?>

    <?php do_action( 'woocommerce_no_products_found' ); ?>

  <?php endif; ?>

</div><!-- .woocommerce -->

<?php get_footer( 'shop' ); ?>
