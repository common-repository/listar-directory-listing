<!-- Image -->
<div class="form-field term-group">
    <label for="featured-image"><?php _e('Image', 'listar'); ?></label>
    <input type="hidden" class="listar-featured-image" id="featured-image" name="featured_image" value="">
    <div class="listar-featured-image-wrapper"></div>
    <p>
        <input type="button" class="button button-secondary listar-trigger-image" id="btn-featured-image" name="media_button" value="<?php _e( 'Add Image', 'listar' ); ?>" />
        <input type="button" class="button button-secondary listar-trigger-image-reset" id="btn-featured-image-reset" name="media_remove" value="<?php _e( 'Remove Image', 'listar' ); ?>" />
    </p>
</div>