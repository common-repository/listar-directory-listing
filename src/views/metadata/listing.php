<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<table class="form-table listar-fields">
    <tbody>
        <tr>
            <th>
                <label><?php _e('Address', 'listar');?></label>
            </th>
            <td>
                <div class="inline">
                    <div class="form-group">
                        <label for="listar-country"><?php _e('Country', 'listar');?></label>
                        <select name="country" id="listar-country">
                            <option value='0'><?php _e('Select Country', 'listar');?></option>
                            <?php if(isset($countries) && !empty($countries)) { ?>
                                <?php foreach($countries as $item) { ?>
                                    <option value=<?php echo absint($item->term_id);?>
                                        <?php echo $post->country == $item->term_id ? 'selected="selected"' : '';?>
                                    >
                                        <?php echo esc_attr($item->name);?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group pad-left-20">
                        <label for="listar-state"><?php _e('State', 'listar');?></label>
                        <select name="state" id="listar-state">
                            <option value='-1'><?php _e('State / Province / Region', 'listar');?></option>
                            <?php if(isset($states) && !empty($states)) { ?>
                                <?php foreach($states as $item) { ?>
                                    <option value=<?php echo absint($item->term_id);?>
                                        <?php echo $post->state == $item->term_id ? 'selected="selected"' : '';?>
                                    >
                                        <?php echo esc_attr($item->name);?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group pad-left-20">
                        <label for="listar-city"><?php _e('City', 'listar');?></label>
                        <select name="city" id="listar-city">
                            <option value='-1'><?php _e('City', 'listar');?></option>
                            <?php if(isset($cities) && !empty($cities)) { ?>
                                <?php foreach($cities as $item) { ?>
                                    <option value=<?php echo absint($item->term_id);?>
                                        <?php echo $post->city == $item->term_id ? 'selected="selected"' : '';?>
                                    >
                                        <?php echo esc_attr($item->name);?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <!-- Address -->
                <div class="inline pad-top-20">
                    <div class="form-group">
                        <label for="address" class="w100"><?php _e('Address/Street', 'listar');?></label>
                        <input class="regular-text" type="text" name="address" id="address" value="<?php echo $post->address; ?>" />
                    </div>
                    <div class="form-group pad-left-20">
                        <label for="zip_code"><?php _e('Postal / Zip Code', 'listar');?></label>
                        <input type="text" name="zip_code" id="zip_code" class="regular-text w100p" value="<?php echo $post->zip_code; ?>" />
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                <label for="phone"><?php _e('Phone', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text" type="text" name="phone" id="phone" value="<?php echo $post->phone; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="whatsapp"><?php _e('WhatsApp', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text" type="text" name="whatsapp" id="whatsapp" value="<?php echo $post->whatsapp; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="fax"><?php _e('Fax', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text" type="text" name="fax" id="fax" value="<?php echo $post->fax; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="email"><?php _e('Email', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text" type="text" name="email" id="email" value="<?php echo $post->email; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="website"><?php _e('Website', 'listar');?></label>
            </th>
            <td>
                <input class="regular-text" type="text" name="website" id="website" value="<?php echo $post->website; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="color"><?php _e('Color', 'listar');?></label>
            </th>
            <td>
                <input type="text" name="color" id="color" class="regular-text color-field" value="<?php echo $post->color; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="icon"><?php _e('Icon', 'listar');?></label>
            </th>
            <td>
                <button class="button"><i class="<?php echo $post->icon ? esc_attr($post->icon) : 'fas fa-star'; ?>"></i></button>
                <input class="icp iconpicker button"
                    id="icon"
                    name="icon"
                    value="<?php echo $post->icon ? esc_attr($post->icon) : 'fas fa-star'; ?>"
                    data-placement="right"
                />
            </td>
        </tr>
        <tr>
            <th>
                <label for="status"><?php _e('Status', 'listar');?></label>
            </th>
            <td>
                <input type="text" name="status" class="regular-text" id="status" value="<?php echo $post->status; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="date_establish"><?php _e('Date Established', 'listar');?></label>
            </th>
            <td>
                <input type="text" name="date_establish" id="date_establish" class="regular-text listar-datepicker" value="<?php echo $post->date_establish; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="galleries"><?php _e('Galleries', 'listar');?></label>
            </th>
            <td>
                <div class="listar-gallery-screenshot clearfix">
                    <?php
                        if($post->galleries && !empty($post->galleries)) {
                            foreach ($post->galleries as $img) {
                                echo '<div class="screen-thumb"><img src="' . esc_url($img['thumb']['url']) . '" /></div>';
                            }
                        }
                    ?>
                </div>
                <input class="button listar-trigger-gallery" type="button" value="<?php esc_html_e('Add/Edit Gallery', 'listar') ?>"/>
                <input id="clear-gallery" class="button listar-reset-gallery" type="button" value="<?php esc_html_e('Clear', 'listar') ?>"/>
                <input type="hidden" name="gallery" id="gallery" class="listar-gallery-ids" value="<?php echo $post->gallery; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="attachments"><?php _e('Attachments', 'listar');?></label>
            </th>
            <td>
                <div class="listar-attachment-list clearfix">
                    <?php
                    if($post->attachments && !empty($post->attachments)) {
                        foreach ($post->attachments as $file) {
                            ?>
                            <div class="file-attachment">
                                <span class="file-name">
                                    <a target="_blank" href="<?php echo $file['url'];?>">
                                        <?php echo $file['name'];?>
                                    </a>
                                </span>
                                <span class="file-size"><?php echo $file['size'];?></span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <input class="button listar-trigger-attachment" type="button" value="<?php esc_html_e('Add/Edit Attachment', 'listar') ?>"/>
                <input id="clear-attachment" class="button listar-reset-attachment" type="button" value="<?php esc_html_e('Clear', 'listar') ?>"/>
                <input type="hidden" name="attachment" id="attachment" class="listar-attachment-ids" value="<?php echo $post->attachment; ?>">
            </td>
        </tr>
        <tr>
            <th>
                <label for="video_url"><?php _e('Video Embed', 'listar');?></label>
            </th>
            <td>
                <input type="text" name="video_url" class="regular-text" id="video_url" value="<?php echo $post->video_url; ?>" />
            </td>
        </tr>
        <tr>
            <th>
                <label for="amount"><?php _e('Price range', 'listar');?> (<?php echo esc_attr(Setting_Model::get_option('unit_price'));?>)</label>
            </th>
            <td>
                <div class="inline">
                    <div class="form-group">
                        <label for="price-min" class="w100"><?php _e('Min', 'listar');?></label>
                        <input class="regular-text w100p" type="text" name="price_min" id="price-min" value="<?php echo $post->price_min; ?>" />
                    </div>
                    <div class="form-group  pad-left-20">
                        <label for="price-max" class="w100"><?php _e('Max', 'listar');?></label>
                        <input class="regular-text w100p" type="text" name="price_max" id="price-max" value="<?php echo $post->price_max; ?>" />
                    </div>
                </div>
            </td>
        </tr>
        <?php if($map_use) { ?>
        <tr>
            <th>
                <label><?php _e('Map', 'listar');?></label>
            </th>
            <td>
                <div class="inline pad-bottom-20">
                    <input id="map-search"  type="text" placeholder="<?php _e('Search Box', 'listar');?>" class="regular-text">
                    <a href="edit.php?post_type=listar&page=settings&tab=option&section=map" class="pull-right pad-left-20"><?php _e('Settings', 'listar');?></a>
                </div>
                <div id="map" style="height: 300px; width:100%"></div>
                <div class="pad-top-20 inline">
                    <div class="form-group">
                        <label for="longitude"><?php _e('Longitude', 'listar');?></label>
                        <input type="text" class="regular-text long-lat-field" name="longitude" id="longitude" placeholder="<?php _e('Longitude', 'listar');?>" value="<?php echo $post->longitude; ?>" />
                    </div>
                    <div class="form-group pad-left-20">
                        <label for="latitude"><?php _e('Latitude', 'listar');?></label>
                        <input type="text" class="regular-text long-lat-field" name="latitude" id="latitude" placeholder="<?php _e('Latitudes', 'listar');?>" value="<?php echo $post->latitude; ?>" />
                    </div>
                </div>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
