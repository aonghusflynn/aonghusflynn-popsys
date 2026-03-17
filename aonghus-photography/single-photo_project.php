<?php
/**
 * Single photo_project template — square grid + GLightbox.
 */

get_header();

while ( have_posts() ) :
    the_post();

    // Retrieve gallery from native post meta (stored as JSON array of attachment IDs).
    $stored      = get_post_meta( get_the_ID(), '_project_gallery', true );
    $gallery_ids = json_decode( $stored ?: '[]', true );
    $gallery_ids = is_array( $gallery_ids ) ? array_map( 'absint', $gallery_ids ) : [];

    // Build $gallery array in the same structure expected by the template below.
    $gallery = [];
    foreach ( $gallery_ids as $attachment_id ) {
        $full  = wp_get_attachment_image_src( $attachment_id, 'full' );
        $large = wp_get_attachment_image_src( $attachment_id, 'large' );
        $alt   = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
        if ( ! $full ) continue;
        $gallery[] = [
            'url'   => $full[0],
            'sizes' => [ 'large' => $large ? $large[0] : $full[0] ],
            'alt'   => $alt,
        ];
    }
    $count = count( $gallery );
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
