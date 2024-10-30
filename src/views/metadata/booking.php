<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<table class="form-table listar-fields">
    <tbody>        
        <tr>
            <th>
                <label><?php _e('Booking Style', 'listar');?></label>
            </th>
            <td>
                <div class="inline">
                    <select name="booking_style" id="listar-booking-style">
                        <option value=''><?php _e('Select booking style', 'listar');?></option>
                        <?php if(isset($booking_style_list) && !empty($booking_style_list)) { ?>
                            <?php foreach($booking_style_list as $item) { ?>
                                <option value=<?php echo esc_attr($item['value']);?>
                                    <?php echo $post->booking_style === $item['value'] ? 'selected="selected"' : '';?>
                                >
                                    <?php echo esc_attr($item['title']);?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <a href="edit.php?post_type=listar&page=settings&tab=booking" class="pull-right pad-left-20"><?php _e('Settings', 'listar');?></a>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                <label><?php _e('Booking Price', 'listar');?>
                    (<?php echo esc_attr(Setting_Model::get_option('unit_price'));?>)
                </label>
            </th>
            <td>
                <input type="text" name="booking_price" id="booking_price" class="regular-text" value="<?php echo $post->booking_price; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label><?php _e('Booking Disable', 'listar');?></label>
            </th>
            <td>
                <select name="booking_disable" id="booking_disable">
                    <option value="0" <?php echo $post->booking_disable == 0 ? "selected=\"selected\"" : "";?>><?php _e('No', 'listar');?></option>
                    <option value="1" <?php echo $post->booking_disable == 1 ? "selected=\"selected\"" : "";?>><?php _e('Yes', 'listar');?></option>
                </select>
                <p><small><?php _e('The user can not make a booking whether the general setting is enabled.'); ?></small></p>
            </td>
        </tr>
    </tbody>
</table>
