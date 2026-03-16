<?php
/**
 * Archive template for photo_project — redirects to homepage.
 * The project slideshow IS the homepage; no separate archive page is needed.
 */
wp_redirect( home_url( '/' ), 301 );
exit;
