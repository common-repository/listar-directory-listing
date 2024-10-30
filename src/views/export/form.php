<div class="wrap">
    <h2><?php _e('Import', 'listar'); ?></h2>
    <h2 class="nav-tab-wrapper" style="margin-bottom: 5px;">
        <?php foreach($tab_options as $id => $tab) { ?>
        <a href="?post_type=listar&page=export&tab=<?php echo esc_attr($id);?>"
            class="nav-tab nav-tab-active">
            <?php echo esc_attr($tab);?>
        </a>
        <?php } ?>
    </h2>
    <!-- Message Handler -->
    <?php if (isset($_REQUEST['action']) && sanitize_text_field($_REQUEST['action']) === 'save' && $post_error == '') { ?>
        <div id="message" class="updated fade"><p><strong><?php _e('Export successfully', 'listar');?></strong></p></div>
    <?php } ?>
    <!-- Notice -->
    <?php if(isset($post_import['total']) && $post_import['total'] > 0) { ?>
    <div class="updated fade">
        <p><?php echo _('Total records: '.$post_import['total']);?></p>
        <p><?php echo _('Successfully: '.$post_import['success']);?></p>
        <p><?php echo _('Error: '.$post_import['error']);?></p>
    </div>
    <?php } ?>
    <!-- Error Handler -->
    <?php if(!empty($post_errors)) { ?>
    <div class="error notice">
        <?php foreach($post_errors as $post_id => $error) { ?>
        <p><?php echo 'ID#'.$post_id.': '.esc_attr($error);?></p>
        <?php } ?>
    </div>
    <?php } ?>
    <!-- Error Single Handler -->
    <?php if(!empty($post_error)) { ?>
        <div class="error notice">
            <p><?php echo $post_error;?></p>
        </div>
    <?php } ?>
    
    <div class="narrow">
        <p>
            <?php echo esc_attr('When you click the button below WordPress will create an CSV file for you to save to your computer.');?>
        </p>
        <p>
            <?php echo esc_attr('The format of CSV file will include the title, description, image, color, icon');?>
        </p>
        <form  method="get">
            <div id="setting" class="ui-sortable meta-box-sortables">
                <div class="postbox">
                    <div class="inside">
                        <h3><?php echo esc_attr_e('Choose what to export', 'listar');?></h3>
                        <fieldset>
                        <?php foreach($taxonomies as $id => $name) { ?>
                            <p>
                            <label for="taxonomy">
                                <input type="radio" 
                                    name="taxonomy" 
                                    id="<?php echo esc_attr($id);?>" 
                                    value="<?php echo esc_attr($id);?>" 
                                    <?php echo esc_attr($id) === $post_taxonomy ? "checked='checked'" : '' ;?>
                                >
                            <?php echo esc_html($name); ?>
                        </label></p>
                        <?php } ?>
                        </fieldset>
                    </div>
                </div>
            </div>
            <p>
                <input name="Submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Download'); ?>" />
                <input type="hidden" name="listar_export_csv" value="save" />
            </p>
        </form>
    </div>    
</div>
