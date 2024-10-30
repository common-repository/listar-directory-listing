<!-- Image -->
<tr class="form-field term-group-wrap">
    <th scope="row">
        <label for="featured-image"><?php _e( 'Image', 'listar' ); ?></label>
    </th>
    <td>
        <input type="hidden" class="listar-featured-image" id="featured-image" name="featured_image" value="<?php echo esc_attr( $term->featured_image ); ?>">
        <div class="listar-featured-image-wrapper">
        <?php if( $term->featured_image ) { ?>
            <div class='screen-thumb'>
                <?php echo wp_get_attachment_image( $term->featured_image); ?>
            </div>
        <?php } ?>
        </div>
        <p>
            <input type="button" class="button button-secondary listar-trigger-image" id="btn-featured-image" name="media_button" value="<?php _e( 'Add Image', 'listar' ); ?>" />
            <input type="button" class="button button-secondary listar-trigger-image-reset" id="btn-featured-image-reset" name="media_remove" value="<?php _e( 'Remove Image', 'listar' ); ?>" />
        </p>
    </td>
</tr>