<table class="form-table listar-fields">
    <tbody>
        <tr>
            <th>
                <label for="job_title"><?php _e('Ads URL', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text"
                   type="text"
                   name="ads_url"
                   id="ads_url"
                   value="<?php echo esc_attr($post->ads_url); ?>" />
            </td>
        </tr>
    </tbody>
</table>
