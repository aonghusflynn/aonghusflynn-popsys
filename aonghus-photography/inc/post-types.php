<?php
/**
 * Custom Post Types
 *
 * Registers the photo_project custom post type for the Aonghus Photography theme.
 *
 * @package Aonghus_Photography
 */

/**
 * Registers the photo_project custom post type.
 */
function aonghus_register_photo_project_cpt() {
    $labels = [
        'name'               => 'Projects',
        'singular_name'      => 'Project',
        'add_new_item'       => 'Add New Project',
        'edit_item'          => 'Edit Project',
        'new_item'           => 'New Project',
        'view_item'          => 'View Project',
        'search_items'       => 'Search Projects',
        'not_found'          => 'No projects found.',
        'not_found_in_trash' => 'No projects found in trash.',
    ];

    $args = [
        'labels'       => $labels,
        'public'       => true,
        // Archive disabled; homepage serves as the project listing via front-page.php.
        'has_archive'  => false,
        'rewrite'      => [ 'slug' => 'projects' ],
        'supports'     => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
        'menu_icon'    => 'dashicons-camera',
        // REST disabled intentionally; REST API / Gutenberg block theme are out of scope.
        'show_in_rest' => false,
    ];

    register_post_type( 'photo_project', $args );
}
add_action( 'init', 'aonghus_register_photo_project_cpt' );
