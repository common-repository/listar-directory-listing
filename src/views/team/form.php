<table class="form-table listar-fields">
    <tbody>
        <!-- Job Title -->
        <tr>
            <th>
                <label for="job_title"><?php _e('Job Title', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text"
                   type="text"
                   name="job_title"
                   id="job_title"
                   value="<?php echo esc_attr($post->job_title); ?>" />
            </td>
        </tr>
        <!-- Social Links -->
        <?php foreach(['facebook', 'twitter', 'instagram', 'linkedin'] as $val) { ?>
        <tr>
            <th>
                <label for="<?php echo esc_attr($val);?>_url"><?php echo esc_attr(ucfirst($val));?></label>
            </th>
            <td>
                <input class="regular-text"
                       type="text"
                       name="<?php echo esc_attr($val);?>_url"
                       id="<?php echo esc_attr($val);?>_url"
                       value="<?php echo esc_attr($post->{$val.'_url'}); ?>" />
            </td>
        </tr>
        <?php } ?>
        <!-- Bio -->
        <tr>
            <th>
                <label for="bio"><?php _e('Bio', 'listar');?></label>
            </th>
            <td>
                <textarea name="bio" id="bio" type="textarea" class="widefat" cols="" rows="5"
                ><?php echo esc_attr($post->bio); ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
