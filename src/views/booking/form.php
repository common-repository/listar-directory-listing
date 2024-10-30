<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<div class="listar-booking-box">
    <!-- Booking -->
    <div class="listar-fields" id="user-info" >
        <div class="inline">
            <div class="form-group w100p">
                <label for="email" class="bold"><?php _e('Email', 'listar');?></label>
                <input class="w100p auto-complete-user" type="text" name="email" id="email" value="<?php echo $booking->get_value('_billing_email'); ?>" />
                <input type="hidden" name="user_id" id="user_id" value="<?php echo $booking->get_value('_customer_user'); ?>" />
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="phone" class="bold"><?php _e('Phone', 'listar');?></label>
                <input type="text" name="phone" id="phone" class="w100p" value="<?php echo $booking->get_value('_billing_phone'); ?>" />
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="first_name" class="bold"><?php _e('First Name', 'listar');?></label>
                <input class="w100p" type="text" name="first_name" id="first_name" value="<?php echo $booking->get_value('_billing_first_name'); ?>" />
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="last_name" class="bold"><?php _e('Last Name', 'listar');?></label>
                <input type="text" name="last_name" id="last_name" class="w100p" value="<?php echo $booking->get_value('_billing_last_name'); ?>" />
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p w100p">
                <label for="address" class="bold"><?php _e('Address', 'listar');?></label>
                <input class="w100p" type="text" name="address" id="address" value="<?php echo $booking->get_value('_billing_address_1'); ?>" />
            </div>
        </div>
        <!-- Resource -->
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="listing" class="bold"><?php _e('Resource', 'listar');?> <span id="listar-estimate-price"></span></label>
                <input class="w100p auto-complete-listing" type="text" name="listing" id="listing"
                       value="<?php echo isset($resource['name']) ? $resource['name'] : '';?>" />
                <input type="hidden" name="resource_id" id="resource_id"
                       value="<?php echo isset($resource['resource_id']) ? $resource['resource_id'] : '';?>" />
            </div>
        </div>
        <!-- Message -->
        <div class="inline listar-field">
            <div class="form-group w100p error-message" id="msg_error"></div>
        </div>
        <!-- Person -->
        <div class="inline pad-top-10">
            <div class="form-group">
                <label for="adult" class="bold"><?php _e('Adult', 'listar');?></label>
                <select name="adult" id="adult">
                    <?php for($i=1;$i<=10;$i++) { ?>
                        <option value='<?php echo $i;?>'
                            <?php echo $booking->get_value('_adult') == $i ? 'selected="selected"' : '';?>>
                            <?php echo $i;?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group pad-left-20">
                <label for="children" class="bold"><?php _e('Children', 'listar');?></label>
                <select name="children" id="children">
                    <option value=""><?php _e('Select', 'listar');?></option>
                    <?php for($i=1;$i<=5;$i++) { ?>
                        <option value='<?php echo $i;?>'
                            <?php echo $booking->get_value('_children') == $i ? 'selected="selected"' : '';?>>
                            <?php echo $i;?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <!-- Start Date / Start Time -->
        <div id="booking-start-date" class="inline pad-top-10 <?php echo $support_fields['start_date']['hidden'] ? 'hidden' : '';?>">
            <div class="form-group">
                <label for="start_date" class="bold"><?php _e('Start Date', 'listar');?></label>
                <input class="booking-date-picker" type="text" name="start_date" id="start_date"
                       value="<?php echo $booking->get_value('_start_date');?>" />
            </div>
            <div id="booking-start-time" class="form-group pad-left-20 <?php echo $support_fields['start_time']['hidden'] ? 'hidden' : '';?>">
                <label for="start_time" class="bold"><?php _e('Start Time', 'listar');?></label>
                <?php
                $start_time_selected = false;
                $stat_time = $booking->get_value('_start_time');
                ?>
                <select name="start_time" id="start_time">
                    <option value=""><?php _e('Select Time', 'listar');?></option>
                    <?php foreach($range_time as $key => $time) {?>
                        <option value="<?php echo $key;?>"
                            <?php
                            if($booking->get_value('_start_time') == $key) {
                                echo 'selected="selected"';
                                $start_time_selected = true;
                            } else {
                                echo '';
                            }
                            ?>
                        >
                            <?php echo $time;?>
                        </option>
                    <?php } ?>
                    <!-- Select undefined time -->
                    <?php if(!$start_time_selected && $stat_time) { ?>
                        <option value="<?php echo $stat_time;?>" selected="selected"><?php echo $stat_time;?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <!-- End Date / End Time -->
        <div id="booking-end-date" class="inline pad-top-10 <?php echo $support_fields['end_date']['hidden'] ? 'hidden' : '';?>">
            <div class="form-group">
                <label for="end_date" class="bold"><?php _e('End Date', 'listar');?></label>
                <input class="booking-date-picker" type="text" name="end_date" id="end_date"
                       value="<?php echo $booking->get_value('_end_date');?>" />
            </div>
            <div class="form-group pad-left-20">
                <label for="end_time" class="bold"><?php _e('End Time', 'listar');?></label>
                <select name="end_time" id="end_time">
                    <option value=""><?php _e('Select Time', 'listar');?></option>
                    <?php foreach($range_time as $key => $time) {?>
                        <option value="<?php echo $key;?>"
                            <?php echo $booking->get_value('_end_time') == $key ? 'selected="selected"' : '';?>
                        >
                            <?php echo $time;?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <!-- Time Slot -->
        <div id="booking-slot" class="inline pad-top-10  <?php echo $support_fields['time_slot']['hidden'] ? 'hidden' : '';?>">
            <div class="form-group">
                <label for="time_slot" class="bold"><?php _e('Time Slot', 'listar');?></label>
                <?php
                $time_slot_selected = false;
                $time_slot_booked = sprintf($booking->get_value('_start_time').'|'.$booking->get_value('_end_time'));
                ?>
                <select name="time_slot" id="time_slot">
                    <option value=""><?php _e('Select Time', 'listar');?></option>
                    <?php foreach($time_slot as $key => $time) {?>
                        <option value="<?php echo sprintf($time['start'].'|'.$time['end']);?>"
                            data-start-time="<?php echo $time['start'];?>"
                            data-end-time="<?php echo $time['end'];?>"
                            <?php
                            if($time_slot_booked == sprintf($time['start'].'|'.$time['end'])) {
                                echo 'selected="selected"';
                                $time_slot_selected = true;
                            } else {
                                echo '';
                            }
                            ?>
                        >
                            <?php echo $time['format'];?>
                        </option>
                    <?php } ?>
                    <!-- Select undefined time -->
                    <?php if(!$time_slot_selected) { ?>
                        <option value="<?php echo $time_slot_booked;?>" selected="selected"><?php echo $time_slot_booked;?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <!-- Memo -->
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="memo" class="bold"><?php _e('Memo', 'listar');?></label>
                    <textarea name="memo" id="memo" type="textarea" class="widefat" cols="" rows="5"
                    ><?php echo $booking->get_memo();?></textarea>
            </div>
        </div>
    </div>
    <!-- Payment -->
    <div class="listar-fields pad-left-20" id="booking-info" >
        <?php if(is_edit_page('edit')) { ?>
        <!-- Payment -->
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Payment', 'listar');?></label>
                <span><?php echo $booking->get_payment_method() !== '' ? __('Yes') : __('No');?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Payment Method', 'listar');?></label>
                <span><?php echo $booking->get_payment_method_name();?></span>
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Transaction ID', 'listar');?></label>
                <span><?php echo $booking->get_txn_id();?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Create On', 'listar');?></label>
                <span><?php echo $booking->get_created_date();?></span>
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment_total" class="bold"><?php _e('Payment Total', 'listar');?></label>
                <span><?php echo Setting_Model::currency_format($booking->get_total(), $booking->get_currency());?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment_on" class="bold"><?php _e('Paid On', 'listar');?></label>
                <span><?php echo $booking->get_paid_date();?></span>
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Status', 'listar');?></label>
                <span style="color:<?php echo $booking->get_status_color();?>;"><?php echo $booking->get_status_name();?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Created Via', 'listar');?></label>
                <span><?php echo $booking->get_value('_created_via');?></span>
            </div>
        </div>
        <div class="inline pad-top-10 margin-top-20 listar-hr">
            <table class="table table-bordered table-striped listar-table w100">
                <thead>
                <tr>
                    <th class="txt-left"><?php _e('Item', 'listar');?></th>
                    <th class="txt-left"><?php _e('Qty', 'listar');?></th>
                    <th class="txt-left"><?php _e('Total', 'listar');?></th>
                </tr>
                </thead>
                <tbody>
                <?php if($booking && !empty($resources = $booking->get_resources())) { ?>
                    <?php foreach($resources as $item) { ?>
                        <tr>
                            <td class="txt-left"><?php echo $item['name'];?></td>
                            <td class="txt-left"><?php echo $item['qty'];?></td>
                            <td class="txt-left"><?php echo Setting_Model::currency_format($item['total'], $booking->get_currency());?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <!-- Memo -->
        <div class="inline pad-top-10 margin-top-20 listar-hr">
            <div class="form-group w100p">
                <label for="payment_total" class="bold"><?php _e('Memo', 'listar');?></label>
                <p>
                    <?php echo $booking->get_memo();?>
                </p>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
