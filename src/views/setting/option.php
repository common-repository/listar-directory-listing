<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<!-- Message Handler -->
<?php if (isset($_REQUEST['action']) && sanitize_text_field($_REQUEST['action']) === 'save') { ?>
    <div id="message" class="updated fade"><p><strong><?php _e('Setting saved successfully', 'listar');?></strong></p></div>
<?php } ?>
<!-- Main Content -->
<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php _e('Settings', 'listar'); ?></h2>
    <h2 class="nav-tab-wrapper" style="margin-bottom: 5px;">
        <?php foreach($tab_options as $id => $tab) { ?>
        <a href="?post_type=listar&page=settings&tab=<?php echo esc_attr($id);?>"
            class="nav-tab <?php echo $active_tab === $id ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_attr($tab['title']);?>
        </a>
        <?php } ?>
    </h2>
    <?php if(!empty($tab_options[$active_tab]) && isset($tab_options[$active_tab]['sections']) && !empty($tab_options[$active_tab]['sections'])) {
        $section_size = sizeof($tab_options[$active_tab]['sections']);
        $section_count = 1;
    ?>
        <div class="listar-setting-box" style="display: flex; flex-direction: row;">
            <ul class="listar-nav-tab-verticle">
                <?php foreach($tab_options[$active_tab]['sections'] as $section_id => $row) { ?>
                    <li class="<?php echo $section == $section_id ? 'li-current' : '';?>">
                        <a href="?post_type=listar&page=settings&tab=<?php echo esc_attr($active_tab);?>&section=<?php echo esc_attr($section_id);?>"
                           class="<?php echo $section == $section_id ? 'link-current' : '';?>"
                        >
                            <?php echo $row['title'];?>
                        </a>
                    </li>
                <?php
                    $section_count++;
                ?>
                <?php } ?>
            </ul>
            <form  method="post" name="listar-setting" class="listar-setting-form">
                <div id="setting">
                    <div class="postbox">
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <?php 
                                        if(isset($tab_data['options']) && !empty($tab_data['options'])) { 
                                            foreach($tab_data['options'] as $option) { 
                                    ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="<?php echo esc_attr($option['id']);?>">
                                                <?php echo esc_html($option['name']); ?>
                                            </label>
                                        </th>
                                        <?php switch ($option['type']) {
                                            // Text Area
                                            case 'textarea':
                                            case 'wysiwyg':
                                                if($option['type'] == 'wysiwyg') {
                                                    ?>
                                                    <td>
                                                        <?php wp_editor( stripslashes(Setting_Model::get_option_prefix($option['id'])), $option['id'], [
                                                            'textarea_rows' => 5,
                                                        ]);?>
                                                        <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                                    </td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>
                                                        <textarea name="<?php echo $option['id']; ?>" id="<?php echo $option['id']; ?>"
                                                            type="<?php echo $option['type']; ?>"  class="widefat" cols="" rows="5"
                                                        ><?php echo esc_attr(Setting_Model::get_option_prefix($option['id'])); ;?></textarea>
                                                        <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                                    </td>
                                                    <?php
                                                }
                                            break;
                                                ?>
                                                <td>
                                                    <input class="large-text"
                                                    type="text"
                                                        name="<?php echo esc_attr($option['id']); ?>"
                                                        id="<?php echo esc_attr($option['id']); ?>"
                                                        value="<?php echo esc_attr(Setting_Model::get_option_prefix($option['id']));?>"
                                                    >
                                                    <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                                </td>
                                                <?php
                                            // Select
                                            case 'select':
                                                ?>
                                                <td>
                                                    <select name="<?php echo esc_attr($option['id']); ?>" id="<?php echo esc_attr($option['id']); ?>">
                                                        <?php foreach ($option['options'] as $value => $label) { ?>
                                                        <option value="<?php echo esc_attr($value);?>"
                                                        <?php if (Setting_Model::get_option_prefix($option['id']) == $value) {
                                                            echo 'selected="selected"';
                                                        } ?>><?php echo esc_attr($label); ?></option><?php } ?>
                                                    </select>
                                                    <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                                </td>
                                                <?php
                                            break;
                                            // Checkbox
                                            case 'checkbox':
                                                $checked = Setting_Model::get_option_prefix($option['id']) ? "checked=\"checked\"" : "";
                                                ?>
                                                <td>
                                                    <input type="checkbox"
                                                        name="<?php echo esc_attr($option['id']); ?>"
                                                        id="<?php echo esc_attr($option['id']); ?>"
                                                        value="true" <?php echo $checked; ?>
                                                    />
                                                    <label for="<?php echo esc_attr($option['id']); ?>"><?php echo $option['desc']; ?></label>
                                                </td>
                                                <?php
                                            break;
                                            // Gallery
                                            case 'gallery':
                                                $galleries = [];
                                                $files = Setting_Model::get_option_prefix($option['id']);
                                                if($files) {
                                                    $ids = explode(',', $files);
                                                    foreach ($ids as $attachment_id) {
                                                        $galleries[] = wp_get_attachment_image_src($attachment_id);
                                                    }
                                                }
                                                ?>
                                                <td>
                                                    <div class="listar-gallery-screenshot clearfix">
                                                        <?php
                                                        if($galleries && !empty($galleries)) {
                                                            foreach ($galleries as $img) {
                                                                echo '<div class="screen-thumb"><img src="' . esc_url($img[0]) . '" /></div>';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <input class="button listar-trigger-gallery" type="button" value="<?php esc_html_e('Add/Edit Gallery', 'listar') ?>"/>
                                                    <input id="clear-gallery" class="button listar-reset-gallery" type="button" value="<?php esc_html_e('Clear', 'listar') ?>"/>
                                                    <input type="hidden"
                                                        name="<?php echo esc_attr($option['id']); ?>"
                                                        id="<?php echo esc_attr($option['id']); ?>"
                                                        class="listar-gallery-ids"
                                                        value="<?php echo esc_attr($files);?>"
                                                    />
                                                    <p class="clearfix"><small><?php echo esc_html($option['desc']); ?></small></p>
                                                </td>
                                                <?php
                                                break;
                                            // Case Image
                                            case 'image':
                                                $image = Setting_Model::get_option_prefix($option['id']);
                                                $image_value = (int)$image;
                                                if($image_value > 0) { // has set value as int
                                                    $image_data =  wp_get_attachment_image_src($image_value);
                                                    if(!empty($image_data)) {
                                                        $image = $image_data[0];
                                                    }
                                                }
                                                ?>
                                                <td class="form-field">
                                                    <input type="hidden" class="listar-featured-image"
                                                    name="<?php echo esc_attr($option['id']); ?>"
                                                    id="<?php echo esc_attr($option['id']); ?>"
                                                    value="<?php echo esc_attr($image_value);?>"
                                                    >
                                                    <div class="listar-featured-image-wrapper">
                                                        <?php if( $image ) { ?>
                                                            <div class='screen-thumb'>
                                                                <img width="150px" src="<?php echo esc_attr($image)?>" />
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <p>
                                                        <input type="button" class="button button-secondary listar-trigger-image" id="btn-featured-image" name="media_button" value="<?php _e( 'Add/Edit Image', 'listar' ); ?>" />
                                                        <input type="button" class="button button-secondary listar-trigger-image-reset" id="btn-featured-image-reset" name="media_remove" value="<?php _e( 'Clear', 'listar' ); ?>" />
                                                    </p>
                                                    <p class="clearfix"><small><?php echo esc_html($option['desc']); ?></small></p>
                                                </td>
                                                <?php
                                                break;
                                            // Case sortable
                                            case 'sortable':
                                                $headers = $option['headers'];
                                                $sortable_data = Setting_Model::get_option_prefix($option['id']);
                                                $sortable_data = json_decode($sortable_data);
                                                ?>
                                                <td class="form-field">
                                                    <table class="table table-bordered table-striped listar-table">
                                                        <thead>
                                                            <tr>
                                                                <th class="col-head-1st"></th>
                                                                <?php foreach($option['headers'] as $key => $value) { ?>
                                                                    <th><?php echo esc_attr($value);?></th>
                                                                <?php } ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="elm-sortable-row hidden">
                                                                <td class="col-body-1st"><input name="listar-col-checkbox[]" class="listar-col-checkbox" type="checkbox" /></td>
                                                                <?php foreach($option['headers'] as $key => $value) { ?>
                                                                    <td><input class="regular-text" type="text" data-id="<?php echo $option['id'];?>" data-name="<?php echo $key;?>" /></td>
                                                                <?php } ?>
                                                            </tr>
                                                            <?php if(!empty($sortable_data)) { ?>
                                                                <?php foreach ($sortable_data as $index => $rows) { ?>
                                                                    <tr>
                                                                        <td class="col-body-1st"><input name="listar-col-checkbox[]" class="listar-col-checkbox" type="checkbox" /></td>
                                                                        <?php foreach($rows as $col => $value) { ?>
                                                                        <td>
                                                                            <input class="regular-text"
                                                                                type="text"
                                                                                name="<?php echo sprintf('%s[%s][%s]', $option['id'], $index, $col);?>"
                                                                                value="<?php echo esc_attr($value);?>"
                                                                            />
                                                                        </td>
                                                                        <?php } ?>
                                                                    </tr>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th colspan="<?php echo sizeof($option['headers'])+1;?>">
                                                                    <a href="javascript:void(0)" class="listar-add-sortable button"><?php echo __('+ Add account', 'listar');?></a>
                                                                    <a href="javascript:void(0)" class="listar-del-sortable button"><?php echo __('Remove selected account(s)', 'listar');?></a>
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </td>
                                                <?php
                                                break;
                                            // Default
                                            case 'text':
                                                ?>
                                                <td>
                                                    <input class="regular-text"
                                                        type="text"
                                                        name="<?php echo esc_attr($option['id']); ?>"
                                                        id="<?php echo esc_attr($option['id']); ?>"
                                                        value="<?php echo esc_attr(Setting_Model::get_option_prefix($option['id']));?>"
                                                    >
                                                    <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                                </td>
                                                <?php
                                            break;
                                        }
                                        ?>
                                    </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <input name="Submit" class="button" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
                <input type="hidden" name="action" value="save" />
            </form>
        </div>
    <?php } ?>    
</div>
<script type="text/javascript">
    jQuery(document).ready(function($){

        $("#sortable1, #sortable2, #sortable3").sortable({
            connectWith: ".filter-fields-list",
        });

        $(document).ready(function () {
            // Sortable
            $('#sortable1, #sortable2, #sortable3').sortable({
                curosr: 'move'
            });

            // Checkbox
            $('form[name="listar-setting"]').submit(function () {
                var this_master = $(this);
                this_master.find('input[type="checkbox"]').each( function () {
                    var checkbox_this = $(this);
                    if( checkbox_this.is(":checked") == true ) {
                        checkbox_this.attr('value','true');
                    } else {
                        checkbox_this.prop('checked',true);
                        checkbox_this.attr('value','false');
                    }
                })
            })
        });
    });
</script>
