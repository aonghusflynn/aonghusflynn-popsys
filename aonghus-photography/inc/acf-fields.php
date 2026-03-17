<?php
/**
 * Photo Gallery Metabox
 *
 * Native WordPress gallery metabox for photo_project posts.
 * Uses wp.media (WordPress core) and stores attachment IDs in post meta.
 * No plugin dependency required.
 *
 * @package Aonghus_Photography
 */

/**
 * Enqueue scripts for the gallery metabox on the project edit screen.
 *
 * @param string $hook Current admin page hook.
 */
function aonghus_gallery_admin_scripts( $hook ) {
    global $post;
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) return;
    if ( ! isset( $post ) || 'photo_project' !== $post->post_type ) return;

    wp_enqueue_media();
    wp_enqueue_script( 'jquery-ui-sortable' );
}
add_action( 'admin_enqueue_scripts', 'aonghus_gallery_admin_scripts' );

/**
 * Register the gallery meta box.
 */
function aonghus_register_gallery_metabox() {
    add_meta_box(
        'aonghus_project_gallery',
        'Photos',
        'aonghus_gallery_metabox_html',
        'photo_project',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'aonghus_register_gallery_metabox' );

/**
 * Render the gallery meta box HTML and inline JS.
 *
 * @param WP_Post $post Current post object.
 */
function aonghus_gallery_metabox_html( $post ) {
    $stored = get_post_meta( $post->ID, '_project_gallery', true );
    $ids    = json_decode( $stored ?: '[]', true );
    $ids    = is_array( $ids ) ? array_map( 'absint', $ids ) : [];

    wp_nonce_field( 'aonghus_save_gallery_' . $post->ID, 'aonghus_gallery_nonce' );
    ?>
    <div id="aonghus-gallery-metabox">
        <p class="description" style="margin-bottom:12px;">Upload project photos. Drag to reorder.</p>

        <div id="aonghus-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;min-height:48px;margin-bottom:12px;padding:8px;border:1px solid #ddd;border-radius:3px;">
            <?php foreach ( $ids as $attachment_id ) :
                $thumb = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
                if ( ! $thumb ) continue;
            ?>
                <div class="gallery-item" data-id="<?php echo esc_attr( $attachment_id ); ?>" style="position:relative;">
                    <img src="<?php echo esc_url( $thumb[0] ); ?>" alt=""
                         style="width:80px;height:80px;object-fit:cover;display:block;border:2px solid #ddd;cursor:move;">
                    <button type="button" class="remove-img"
                            style="position:absolute;top:0;right:0;background:#c0392b;color:#fff;border:none;cursor:pointer;font-size:11px;line-height:1;padding:2px 5px;">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>

        <input type="hidden" id="aonghus-gallery-ids" name="project_gallery_ids"
               value="<?php echo esc_attr( wp_json_encode( $ids ) ); ?>">

        <button type="button" id="aonghus-gallery-add" class="button button-primary">Add Photos</button>
        <button type="button" id="aonghus-gallery-clear" class="button" style="margin-left:6px;">Clear All</button>
    </div>

    <script>
    jQuery(function($) {
        var $preview = $('#aonghus-gallery-preview');
        var $input   = $('#aonghus-gallery-ids');
        var frame;

        // Drag-to-reorder via jQuery UI Sortable
        $preview.sortable({
            update: function() { syncIds(); }
        });

        // Remove individual image
        $preview.on('click', '.remove-img', function(e) {
            e.preventDefault();
            $(this).closest('.gallery-item').remove();
            syncIds();
        });

        // Clear all images
        $('#aonghus-gallery-clear').on('click', function(e) {
            e.preventDefault();
            $preview.empty();
            $input.val('[]');
        });

        // Open WordPress media library
        $('#aonghus-gallery-add').on('click', function(e) {
            e.preventDefault();

            if ( frame ) { frame.open(); return; }

            frame = wp.media({
                title:    'Select Photos',
                button:   { text: 'Add to Gallery' },
                multiple: 'add'
            });

            frame.on('select', function() {
                frame.state().get('selection').each(function(attachment) {
                    var id    = attachment.id;
                    var sizes = attachment.attributes.sizes;
                    var thumb = sizes && sizes.thumbnail ? sizes.thumbnail.url : attachment.attributes.url;

                    // Skip duplicates already in the preview
                    if ( $preview.find('.gallery-item[data-id="' + id + '"]').length ) return;

                    var $item = $('<div class="gallery-item" style="position:relative;">').attr('data-id', id);
                    $item.append(
                        '<img src="' + thumb + '" alt="" style="width:80px;height:80px;object-fit:cover;display:block;border:2px solid #ddd;cursor:move;">'
                    );
                    $item.append(
                        '<button type="button" class="remove-img" style="position:absolute;top:0;right:0;background:#c0392b;color:#fff;border:none;cursor:pointer;font-size:11px;line-height:1;padding:2px 5px;">&times;</button>'
                    );
                    $preview.append($item);
                });
                syncIds();
            });

            frame.open();
        });

        /**
         * Read current gallery-item order and write IDs back to the hidden input.
         */
        function syncIds() {
            var ids = [];
            $preview.find('.gallery-item').each(function() {
                ids.push( parseInt( $(this).data('id'), 10 ) );
            });
            $input.val( JSON.stringify(ids) );
        }
    });
    </script>
    <?php
}

/**
 * Save the gallery IDs when a photo_project post is saved.
 *
 * @param int $post_id Post ID.
 */
function aonghus_save_gallery_meta( $post_id ) {
    if ( ! isset( $_POST['aonghus_gallery_nonce'] ) ) return;
    if ( ! wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['aonghus_gallery_nonce'] ) ),
        'aonghus_save_gallery_' . $post_id
    ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $raw = isset( $_POST['project_gallery_ids'] )
        ? sanitize_text_field( wp_unslash( $_POST['project_gallery_ids'] ) )
        : '[]';

    $ids = json_decode( $raw, true );

    if ( is_array( $ids ) ) {
        $ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
        update_post_meta( $post_id, '_project_gallery', wp_json_encode( $ids ) );
    }
}
add_action( 'save_post_photo_project', 'aonghus_save_gallery_meta' );
