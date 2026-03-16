<?php
/**
 * Homepage template — full-screen scroll-snap project slideshow.
 */

get_header();

$projects = new WP_Query( [
    'post_type'      => 'photo_project',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'post_status'    => 'publish',
] );
?>

<?php if ( $projects->have_posts() ) : ?>

  <?php
  // Collect dot labels during the main loop to avoid a second WP_Query loop.
  $dot_labels = [];
  ?>

  <div class="slideshow" role="region" aria-label="<?php esc_attr_e( 'Photo projects', 'aonghus-photography' ); ?>">

    <?php while ( $projects->have_posts() ) : $projects->the_post(); ?>

      <?php $dot_labels[] = get_the_title(); ?>

      <section class="slideshow__slide"
               role="group"
               aria-label="<?php echo esc_attr( get_the_title() ); ?>">

        <a class="slideshow__link"
           href="<?php echo esc_url( get_permalink() ); ?>"
           aria-label="<?php echo esc_attr( sprintf( __( 'View project: %s', 'aonghus-photography' ), get_the_title() ) ); ?>">

          <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'full', [ 'class' => 'slideshow__cover', 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
          <?php endif; ?>

          <div class="slideshow__meta">
            <h2 class="slideshow__title"><?php echo esc_html( get_the_title() ); ?></h2>
          </div>

        </a>

      </section><!-- .slideshow__slide -->

    <?php endwhile; wp_reset_postdata(); ?>

  </div><!-- .slideshow -->

  <!-- Dot navigation — built from $dot_labels collected above -->
  <nav class="slideshow__dots" aria-label="<?php esc_attr_e( 'Project navigation', 'aonghus-photography' ); ?>">
    <?php foreach ( $dot_labels as $idx => $label ) : ?>
      <button class="slideshow__dot <?php echo $idx === 0 ? 'slideshow__dot--active' : ''; ?>"
              aria-current="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
              aria-label="<?php echo esc_attr( $label ); ?>">
      </button>
    <?php endforeach; ?>
  </nav>

<?php else : ?>

  <div class="slideshow slideshow--empty">
    <p class="slideshow__empty-msg">
      <?php esc_html_e( 'No projects yet — add your first project in wp-admin.', 'aonghus-photography' ); ?>
    </p>
  </div>

<?php endif; ?>

<?php get_footer(); ?>
