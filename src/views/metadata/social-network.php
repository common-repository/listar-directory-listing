<?php 
use ListarWP\Plugin\Models\Place_Model;
?>
<div class="listar-side">
    <div class="social-network">
        <?php foreach(Place_Model::$social_network as $item) { ?>
            <div class="item">
                <label><?php echo esc_html($item['label']);?></label>
                <input type="text"
                    name="social_network[<?php echo esc_attr($item['field']);?>]"
                    placeholder="<?php echo esc_attr($item['placeholder']);?>"
                    value="<?php echo isset($post->social_network[$item['field']]) ? esc_attr($post->social_network[$item['field']]) : '';?>"
                />
            </div>
        <?php } ?>
    </div>
</div>
