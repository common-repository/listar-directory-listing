<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<style type="text/css">
    .end-time {
    }
</style>
<div class="listar-side">
    <!-- Element clone hidden -->
    <div class="hidden div-opening-hour">
        <div class="opening-hour">
            <select class="start-time">
                <option value="0"><?php _e('Start Time', 'listar');?></option>
                <?php foreach($range_time as $key => $time) {?>
                    <option value="<?php echo $key;?>"><?php echo $time;?></option>
                <?php } ?>
            </select>
            <select class="end-time">
                <option value="0"><?php _e('End Time', 'listar');?></option>
                <?php foreach($range_time as $key => $time) {?>
                    <option value="<?php echo $key;?>"><?php echo $time;?></option></option>
                <?php } ?>
            </select>
            <!-- Button remove -->
            <input type="button" class="button listar-del-opening-hour" value="-">
        </div>
    </div>
    <!-- Opening Hour -->
    <div class="form-side form-table table-hours">
        <?php foreach($day_of_weeks as $row) { ?>
        <div>
            <div class="day">
                <?php echo esc_html($row['label']);?>
            </div>
            <div class="hours">
                <!-- Case Edit -->
                <?php if(!empty($post->opening_hour) && isset($post->opening_hour[$row['day_of_week']])) { ?>
                <?php foreach($post->opening_hour[$row['day_of_week']]['start'] as $key => $open_time) { ?>
                    <div class="opening-hour">
                        <select class="start-time" name="opening_hour[<?php echo $row['day_of_week'];?>][start][]">
                            <option value="0"><?php _e('Start Time', 'listar');?></option>
                            <?php foreach($range_time as $time_index => $time) {?>
                                <option value="<?php echo esc_attr($time_index);?>"
                                    <?php echo $time_index == $open_time ? 'selected' : '';?>
                                >
                                    <?php echo esc_attr($time);?>
                                </option>
                            <?php } ?>
                        </select>
                        <select class="end-time" name="opening_hour[<?php echo $row['day_of_week'];?>][end][]">
                            <option value="0"><?php _e('End Time', 'listar');?></option>
                            <?php foreach($range_time as $time_index => $time) {?>
                                <option value="<?php echo esc_attr($time_index);?>"
                                    <?php echo $time_index == $post->opening_hour[$row['day_of_week']]['end'][$key] ? 'selected' : '';?>>
                                    <?php echo esc_attr($time);?>
                                </option>
                            <?php } ?>
                        </select>
                        <!-- Only show add button with 1st row -->
                        <?php if($key == 0) { ?>
                            <!-- Button Add -->
                            <input type="button"
                                data-day-of-week="<?php echo esc_attr($row['day_of_week']);?>"
                                class="button listar-add-opening-hour" value="+">
                        <?php } else { ?>
                            <!-- Button Remove -->
                            <input type="button" class="button listar-del-opening-hour" value="-">
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php } else { ?>
                    <!-- Case Add -->
                    <div class="opening-hour">
                        <select class="start-time" name="opening_hour[<?php echo $row['day_of_week'];?>][start][]">
                            <option value="0"><?php _e('Start Time', 'listar');?></option>
                            <?php foreach($range_time as $time_index => $time) {?>
                                <option value="<?php echo esc_attr($time_index);?>"
                                    <?php echo esc_attr(Setting_Model::get_option('time_min')) == $time_index
                                        ? "selected='selected'" : ''; ?>
                                >
                                    <?php echo esc_attr($time);?>
                                </option>
                            <?php } ?>
                        </select>
                        <select class="end-time" name="opening_hour[<?php echo $row['day_of_week'];?>][end][]">
                            <option value="0"><?php _e('End Time', 'listar');?></option>
                            <?php foreach($range_time as $time_index => $time) {?>
                                <option value="<?php echo $time_index;?>"
                                    <?php echo esc_attr(Setting_Model::get_option('time_max')) == $time_index
                                        ? "selected='selected'" : ''; ?>
                                >
                                    <?php echo esc_attr($time);?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="button"
                            data-day-of-week="<?php echo $row['day_of_week'];?>"
                            class="button listar-add-opening-hour" value="+">
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
