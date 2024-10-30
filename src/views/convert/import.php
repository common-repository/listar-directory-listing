<div class="wrap">
    <h2><?php _e('Import', 'listar'); ?></h2>
    <h2 class="nav-tab-wrapper" style="margin-bottom: 5px;">
        <?php foreach($tab_options as $id => $tab) { ?>
        <a href="?post_type=listar&page=import&tab=<?php echo esc_attr($id);?>"
            class="nav-tab <?php echo $active_tab === $id ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_attr($tab);?>
        </a>
        <?php } ?>
    </h2>
    <!-- Message Handler -->
    <?php if (isset($_REQUEST['action']) && sanitize_text_field($_REQUEST['action']) === 'save' && $post_error == '') { ?>
        <div id="message" class="updated fade"><p><strong><?php _e('Imported successfully', 'listar');?></strong></p></div>
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
        <p><?php echo 'PostID#'.$post_id.': '.esc_attr($error);?></p>
        <?php } ?>
    </div>
    <?php } ?>
    <!-- Error Single Handler -->
    <?php if(!empty($post_error)) { ?>
        <div class="error notice">
            <p><?php echo $post_error;?></p>
        </div>
    <?php } ?>
    <!-- Listing Content -->
    <?php if(!$active_tab || $active_tab == 'listing') { ?>
    <div class="narrow">
        <p>
            <?php echo esc_attr('Upload your directory listing file and we’ll import the title, image, excerpt, country, city, state, address, zip_code, phone, fax, email, website ... into this site.');?>
        </p>
        <p>
            <?php echo esc_attr('Choose a CSV (.csv) file to upload, then click Upload file and import.');?>
        </p>
        <form  method="post" enctype="multipart/form-data">
            <div id="setting" class="ui-sortable meta-box-sortables">
                <div class="postbox">
                    <div class="inside">
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="status"><?php echo esc_attr('Status');?></label>
                                </th>
                                <td>
                                    <select name="status" id="status">
                                        <?php foreach ($status as $value => $label) { ?>
                                            <option value="<?php echo esc_attr($value);?>"
                                            <?php if ($post_status == $value) {
                                                echo 'selected="selected"';
                                            } ?>><?php echo esc_attr($label); ?></option><?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="exist"><?php echo esc_attr('Update Exist', 'listar');?></label>
                                </th>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="exist"
                                        id="exist"
                                        value="true"
                                        <?php echo esc_attr($post_exist) === true ? "checked='checked'" : '' ;?>
                                    >
                                    <label for="exist"><?php echo esc_html('Existing directory listing that match by Title will be updated otherwise will be inserted'); ?></label>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p>
                <label for="upload"><?php echo esc_attr('Maximum size: '.$max_file_size);?></label>
                <input name="upload" id="upload" type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
            </p>
            <p>
                <input name="Submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Upload file and import'); ?>" />
                <input type="hidden" name="action" value="save" />
            </p>
        </form>
    </div>
    <!-- Taxonomy Content -->
    <?php } else if($active_tab == 'taxonomy') {  ?>
        <div class="narrow">
        <p>
            <?php echo esc_attr('Upload your directory listing file and we’ll import the title, description, image, color, icon');?>
        </p>
        <p>
            <?php echo esc_attr('Choose a CSV (.csv) file to upload, then click Upload file and import.');?>
        </p>
        <form  method="post" enctype="multipart/form-data">
            <div id="setting" class="ui-sortable meta-box-sortables">
                <div class="postbox">
                    <div class="inside">
                        <h3><?php echo esc_attr_e('Choose what to import', 'listar');?></h3>
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
                <label for="upload"><?php echo esc_attr('Maximum size: '.$max_file_size);?></label>
                <input name="upload" id="upload" type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
            </p>
            <p>
                <input name="Submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Upload file and import'); ?>" />
                <input type="hidden" name="action" value="save" />
            </p>
        </form>
    </div>    
    <?php } ?>    
</div>
