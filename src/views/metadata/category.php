<!-- Color -->
<div class="form-field term-group">
    <label for="color"><?php _e('Color', 'listar');?></label>
    <input type="text" name="color" id="color" class="color-field" value="<?php echo $term->color ? esc_attr($term->color) : ''; ?>" />
</div>
<!-- Icon -->
<div  class="form-field term-group">
    <label for="Icon"><?php _e('Icon', 'listar');?></label>
    <button class="button"><i class="<?php echo $term->icon ? esc_attr($term->icon) : 'fas fa-star'; ?>"></i></button>
    <input data-placement="right" class="icp iconpicker button" name="icon" value="fa-archive">
</div>
<!-- Image -->
<div class="form-field term-group">
    <label for="featured-image"><?php _e('Image', 'listar'); ?></label>
    <input type="hidden" class="listar-featured-image" id="featured-image"  name="featured_image" value="">
    <div class="listar-featured-image-wrapper"></div>
    <p>
        <input type="button" class="button button-secondary listar-trigger-image" id="btn-featured-image" name="media_button" value="<?php _e( 'Add Image', 'listar' ); ?>" />
        <input type="button" class="button button-secondary listar-trigger-image-reset" id="btn-featured-image-reset" name="media_remove" value="<?php _e( 'Remove Image', 'listar' ); ?>" />
    </p>
</div>