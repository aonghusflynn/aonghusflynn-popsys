<?php
/**
 * Single photo_project template — square grid + GLightbox.
 */

get_header();

while ( have_posts() ) :
    the_post();

    // get_field() requires ACF plugin; fall back to empty array if not active.
    $gallery = function_exists( 'get_field' ) ? get_field( 'project_gallery' ) : [];
    $count   = is_array( $gallery ) ? count( $gallery ) : 0;
?>

<article class="project" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

  <header class="project__header">
    <a class="project__back" href="<?php echo esc_url( home_url( '/' ) ); ?>">
      &larr; <?php esc_html_e( 'Projects', 'aonghus-photography' ); ?>
    </a>
    <div class="project__heading">
      <h1 class="project__title"><?php echo esc_html( get_the_title() ); ?></h1>
      <?php if ( $count > 0 ) : ?>
        <p class="project__count">
          <?php echo esc_html( sprintf(
            /* translators: %d: number of photos */
            _n( '%d photograph', '%d photographs', $count, 'aonghus-photography' ),
            $count
          ) ); ?>
        </p>
      <?php endif; ?>
    </div>
  </header>

  <?php if ( $gallery ) : ?>

    <div class="project__grid">
      <?php foreach ( $gallery as $image ) :
        $full   = esc_url( $image['url'] );
        $thumb  = esc_url( $image['sizes']['large'] ?? $image['url'] );
        $alt    = esc_attr( $image['alt'] ?: get_the_title() );
      ?>
        <a class="project__grid-item"
           href="<?php echo $full; ?>"
           data-gallery="project-<?php the_ID(); ?>"
           aria-label="<?php echo $alt; ?>">
          <img src="<?php echo $thumb; ?>"
               alt="<?php echo $alt; ?>"
               loading="lazy"
               decoding="async">
        </a>
      <?php endforeach; ?>
    </div><!-- .project__grid -->

  <?php else : ?>

    <p class="project__no-photos">
      <?php esc_html_e( 'No photos added to this project yet.', 'aonghus-photography' ); ?>
    </p>

  <?php endif; ?>

  <?php if ( get_the_content() ) : ?>
    <div class="project__description">
      <?php the_content(); ?>
    </div>
  <?php endif; ?>

</article><!-- .project -->

<?php endwhile; ?>

<?php get_footer(); ?>
