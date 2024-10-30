<?php 
use ListarWP\Plugin\Models\Setting_Model;
?>
<div class="listar-booking-box">
    <!-- Booking -->
    <div class="listar-fields" id="user-info" >
        <div class="inline">
            <div class="form-group w100p">
                <label for="email" class="bold"><?php _e('Email', 'listar');?></label>
                <input class="w100p auto-complete-user" type="text" name="email" id="email" value="<?php echo $claim->get_value('_billing_email'); ?>" />
                <input type="hidden" name="user_id" id="user_id" value="<?php echo $claim->get_value('_customer_user'); ?>" />
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="phone" class="bold"><?php _e('Phone', 'listar');?></label>
                <input type="text" name="phone" id="phone" class="w100p" value="<?php echo $claim->get_value('_billing_phone'); ?>" />
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="first_name" class="bold"><?php _e('First Name', 'listar');?></label>
                <input class="w100p" type="text" name="first_name" id="first_name" value="<?php echo $claim->get_value('_billing_first_name'); ?>" />
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="last_name" class="bold"><?php _e('Last Name', 'listar');?></label>
                <input type="text" name="last_name" id="last_name" class="w100p" value="<?php echo $claim->get_value('_billing_last_name'); ?>" />
            </div>
        </div>
        <!-- Memo -->
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="memo" class="bold"><?php _e('Memo', 'listar');?></label>
                    <textarea name="memo" id="memo" type="textarea" class="widefat" cols="" rows="5"
                    ><?php echo $claim->get_memo();?></textarea>
            </div>
        </div>
    </div>
    <!-- Payment -->
    <div class="listar-fields pad-left-20" id="booking-info" >
        <?php if(is_edit_page('edit')) { ?>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Method of charge', 'listar');?></label>
                <span><?php echo $claim->get_claim_method_charge_name();?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Fee', 'listar');?></label>
                <span><?php echo $claim->get_claim_charge_fee_disc();?></span>
            </div>
        </div>     
        <!-- Payment -->
        <div class="inline pad-top-10 margin-top-20 listar-hr">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Payment', 'listar');?></label>
                <span><?php echo $claim->get_payment_method() !== '' ? __('Yes') : __('No');?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Payment Method', 'listar');?></label>
                <span><?php echo $claim->get_payment_method_name();?></span>
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Transaction ID', 'listar');?></label>
                <span><?php echo $claim->get_txn_id();?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Create On', 'listar');?></label>
                <span><?php echo $claim->get_created_date();?></span>
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment_total" class="bold"><?php _e('Payment Total', 'listar');?></label>
                <span><?php echo $claim->get_total() > 0 ? Setting_Model::currency_format($claim->get_total(), $claim->get_currency()) : '--';?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment_on" class="bold"><?php _e('Paid On', 'listar');?></label>
                <span><?php echo $claim->get_paid_date() ? $claim->get_paid_date() : '--';?></span>
            </div>
        </div>
        <div class="inline pad-top-10">
            <div class="form-group w100p">
                <label for="payment" class="bold"><?php _e('Status', 'listar');?></label>
                <span style="color:<?php echo $claim->get_status_color();?>;"><?php echo $claim->get_status_name();?></span>
            </div>
            <div class="form-group pad-left-20 w100p">
                <label for="payment" class="bold"><?php _e('Created Via', 'listar');?></label>
                <span><?php echo $claim->get_value('_created_via');?></span>
            </div>
        </div>
        <!-- Memo -->
        <div class="inline pad-top-10 margin-top-20 listar-hr">
            <div class="form-group w100p">
                <label for="payment_total" class="bold"><?php _e('Memo', 'listar');?></label>
                <p>
                    This information from payment memo
                </p>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
