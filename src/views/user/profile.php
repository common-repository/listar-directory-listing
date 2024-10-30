<h3><?php _e('Token Push Notification', 'listar'); ?></h3>

<table class="form-table">
    <thead>
        <th><?php _e('Model', 'listar'); ?></th>
        <th><?php _e('Type', 'listar'); ?></th>
        <th><?php _e('Token', 'listar'); ?></th>
    </thead>
    <?php if(!empty($tokens)) { ?>
        <?php foreach($tokens as $token) {
            $item = json_decode($token);
        ?>
            <tr>
                <td><?php echo esc_attr($item->device_model).' ('.esc_attr($item->device_version).')';?></td>
                <td><?php echo esc_attr($item->type);?></td>
                <td><?php echo esc_attr($item->push_token);?></td>
            </tr>
        <?php } ?>
    <?php } ?>
</table>

<table class="form-table">
    <tr>
        <th><label for="favorite_color"><?php _e('Block Account', 'listar');?></label></th>
        <td>
            <input
                type="checkbox"
                <?php echo $is_blocked  ? 'checked="checked"' : FALSE; ?>
                name="listar_block_account"
                id="listar_block_account"
                value="<?php $is_blocked ? 1 : ''; ?>"
            />
            <label for="listar_block_account">
                <?php echo $is_blocked ?
                    sprintf(__('The account is not permitted to login the website. Block at %s'),
                        get_user_meta($user->ID, 'listar_block_time', TRUE))
                    : '';
                ?>
            </label>
        </td>
    </tr>
</table>