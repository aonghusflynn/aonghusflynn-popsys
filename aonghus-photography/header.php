<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">
  <?php esc_html_e( 'Skip to content', 'aonghus-photography' ); ?>
</a>

<header class="site-header" id="masthead">
  <div class="site-header__inner">

    <a class="site-header__name" href="<?php echo esc_url( home_url( '/' ) ); ?>">
      <?php bloginfo( 'name' ); ?>
    </a>

    <nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'aonghus-photography' ); ?>">
      <?php
      wp_nav_menu( [
        'theme_location' => 'primary',
        'menu_class'     => 'site-nav__list',
        'container'      => false,
        'fallback_cb'    => false,
      ] );
      ?>
    </nav>

    <button class="nav-toggle"
            aria-expanded="false"
            aria-controls="site-nav"
            aria-label="<?php esc_attr_e( 'Open menu', 'aonghus-photography' ); ?>">
      <span class="nav-toggle__bar"></span>
      <span class="nav-toggle__bar"></span>
      <span class="nav-toggle__bar"></span>
    </button>

  </div><!-- .site-header__inner -->
</header><!-- #masthead -->

<div id="page" class="site">
  <main id="primary" class="site-content">
