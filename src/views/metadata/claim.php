<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<table class="form-table listar-fields">
    <tbody>
        <tr>
            <th>
                <label><?php _e('Mark as claimed', 'listar');?></label>
            </th>
            <td>
                <select name="claim_use" id="claim_use">
                    <option value="0" <?php echo $post->claim_use == 0 ? "selected=\"selected\"" : "";?>><?php _e('No', 'listar');?></option>
                    <option value="1" <?php echo $post->claim_use == 1 ? "selected=\"selected\"" : "";?>><?php _e('Yes', 'listar');?></option>
                </select>
                <p><small><?php _e('Other user can not make a request to claim whether the general setting is enabled.'); ?></small></p>
            </td>
        </tr>
        <tr>
            <th><?php _e('Method Of Charging', 'listar');?></th>
            <td>
                <div class="inline">
                    <select name="claim_method_charge" id="listar-claim-method-charge">
                        <option value=''><?php _e('Select method of charging', 'listar');?></option>
                        <?php if(isset($options) && !empty($options)) { ?>
                            <?php foreach($options as $item) { ?>
                                <option value=<?php echo esc_attr($item['value']);?>
                                    <?php echo $post->claim_method_charge === $item['value'] ? 'selected="selected"' : '';?>
                                >
                                    <?php echo esc_attr($item['title']);?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <a href="edit.php?post_type=listar&page=settings&tab=claim" class="pull-right pad-left-20"><?php _e('Settings', 'listar');?></a>
                </div>
                <div class='listar-desc'>
                    <p><small><?php _e('The value will be defined by default setting if there is not selected'); ?></small></p>
                </div>
            </td>
        </tr>        
        <tr>
            <th>
                <label><?php _e('Claim Fee', 'listar');?>
                    (<?php echo esc_attr(Setting_Model::get_option('unit_price'));?>)
                </label>
            </th>
            <td>
                <input type="text" name="claim_price" id="claim_price" class="regular-text" value="<?php echo $post->claim_price; ?>" />
            </td>
        </tr>
    </tbody>
</table>
