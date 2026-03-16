<?php
/**
 * ACF Field Groups
 *
 * Registers ACF field groups for the Aonghus Photography theme via PHP.
 * No JSON import needed — field groups are defined here.
 *
 * Requires: Advanced Custom Fields plugin (free, by WP Engine).
 *
 * @package Aonghus_Photography
 */

/**
 * Registers ACF field group for photo_project via PHP (no JSON import needed).
 * Requires: Advanced Custom Fields plugin (free).
 */
function aonghus_register_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return; // ACF not active — fail silently.
    }

    acf_add_local_field_group( [
        'key'    => 'group_photo_project',
        'title'  => 'Project Fields',
        'fields' => [
            [
                'key'           => 'field_project_gallery',
                'label'         => 'Photos',
                'name'          => 'project_gallery',
                'type'          => 'gallery',
                'instructions'  => 'Upload project photos. Drag to reorder.',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'insert'        => 'append',
                'library'       => 'all',
                'min'           => 0,
                'max'           => 0,
            ],
        ],
        'location' => [
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'photo_project' ] ],
        ],
        'menu_order'            => 0,
        'position'              => 'normal',
        'style'                 => 'default',
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
    ] );
}
add_action( 'acf/init', 'aonghus_register_acf_fields' );
