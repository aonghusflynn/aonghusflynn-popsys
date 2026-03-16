<?php
/**
 * Generic page template — About, Contact, etc.
 *
 * @package Aonghus_Photography
 */

get_header();

while ( have_posts() ) :
    the_post();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
  <h1 class="entry-title"><?php echo esc_html( get_the_title() ); ?></h1>
  <div class="entry-content">
    <?php the_content(); ?>
  </div>
</article>

<?php endwhile; ?>

<?php get_footer(); ?>
