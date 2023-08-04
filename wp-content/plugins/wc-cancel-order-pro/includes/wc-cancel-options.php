<?php
defined( 'ABSPATH' ) || exit;
$settings = WC_Cancel_Order_Init()->get_settings();
//echo '<pre>'; print_r($settings); echo '</pre>';
//die;

$statuses = WC_Cancel_Order_Init()->wc_cancel_get_order_statuses();
$cancel_statuses = $statuses;
$text_input = isset($settings['reason-options']) ? explode("\r\n",$settings['reason-options']) : '';
?>
<div class="wc-cancel-pro-main">
    <div class="wc-cancel-pro-in">
        <div class="wcc-pro-row">
            <label><?php echo __('Request cancellation','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <select name="wc-cancel[req-status][]" class="wc-enhanced-select wc-cancel-req-status-select" multiple="multiple">
		            <?php
		            if(is_array($statuses) && !empty($statuses)){
			            foreach($statuses as $status_key=>$status_label){
			                if(isset($settings['cancel-status']) && is_array($settings['cancel-status']) && in_array($status_key,$settings['cancel-status'])){
			                    continue;
                            }
				            $selected = isset($settings['req-status']) && is_array($settings['req-status']) && in_array($status_key,$settings['req-status']) ? 'selected' : '';
				            echo '<option value="'.$status_key.'" '.$selected.'>'.$status_label.'</option>';
				            if(isset($cancel_statuses[$status_key]) && in_array($status_key,$settings['req-status'])){
					            unset($cancel_statuses[$status_key]);
				            }
			            }
		            }
		            ?>
                </select>
            </div>
            <p class="description"><?php echo __('Customers will be able to send only cancellation request with selected order status.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Allow cancellation','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <select name="wc-cancel[cancel-status][]" class="wc-enhanced-select wc-cancel-status-select" multiple="multiple">
		            <?php
		            if(is_array($cancel_statuses) && !empty($cancel_statuses)){
			            foreach($cancel_statuses as $status_key2=>$status_label2){
				            $selected = isset($settings['cancel-status']) && is_array($settings['cancel-status']) && in_array($status_key2,$settings['cancel-status']) ? 'selected' : '';
				            echo '<option value="'.$status_key2.'" '.$selected.'>'.$status_label2.'</option>';
			            }
		            }
		            ?>
                </select>
            </div>
            <p class="description"><?php echo __('Customers will be able cancel their order directly with selected order status.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Approval status','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <select name="wc-cancel[approval]" class="wc-enhanced-select">
		            <?php
		            $wcc_statuses = WC_Cancel_Order_Init()->wc_cancel_get_statuses();
		            if(is_array($wcc_statuses) && !empty($wcc_statuses)){
			            foreach($wcc_statuses as $status_key=>$status_label){
				            $selected = isset($settings['approval']) && $settings['approval']==$status_key ? 'selected' : '';
				            echo '<option value="'.$status_key.'" '.$selected.'>'.$status_label.'</option>';
			            }
		            }
		            ?>
                </select>
            </div>
            <p class="description"><?php echo __('Order status if cancellation request is approved.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Decline status','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <select name="wc-cancel[decline]" class="wc-enhanced-select">
		            <?php
		            if(is_array($wcc_statuses) && !empty($wcc_statuses)){
			            foreach($wcc_statuses as $status_key=>$status_label){
				            $selected = isset($settings['decline']) && $settings['decline']==$status_key ? 'selected' : '';
				            echo '<option value="'.$status_key.'" '.$selected.'>'.$status_label.'</option>';
			            }
		            }
		            ?>
                </select>
            </div>
            <p class="description"><?php echo __('Order status if cancellation request is declined.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('User role','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <select name="wc-cancel[role][]" class="wc-enhanced-select" multiple="multiple">
		            <?php
		            $roles = WC_Cancel_Order_Init()->get_roles();
		            if(is_array($roles) && !empty($roles)){
			            foreach($roles as $role_key=>$role){
				            $selected = isset($settings['role']) && is_array($settings['role']) && in_array($role_key,$settings['role']) ? 'selected' : '';
				            echo '<option value="'.$role_key.'" '.$selected.'>'.$role['name'].'</option>';
			            }
		            }
		            ?>
                </select>
            </div>
            <p class="description"><?php echo __('Display cancellation request/cancel button for the selected user roles (Example: dealer).','wc-cancel-order'); ?></p>
        </div>

        <div class="wcc-pro-row">
            <label><?php echo __('Cancellation options','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <textarea name="wc-cancel[reason-options]" class="wc-cancel-reason-options"><?php echo isset($settings['reason-options']) ? $settings['reason-options'] : ''; ?></textarea>
            </div>
            <p class="description"><?php echo __('Add cancellation options one line per row without any html tag (will appear as radio button).','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Cancellation option required','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <input type="checkbox" name="wc-cancel[reason]" value="1" <?php echo isset($settings['reason']) && $settings['reason']=='1' ? 'checked' : ''; ?>>
                <p class="description-in"><?php echo __('Make cancellation option required.','wc-cancel-order'); ?></p>
            </div>
        </div>

        <div class="wcc-pro-row">
            <label><?php echo __('Text input','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <select name="wc-cancel[text-input]" class="wc-cancel-text-input">
		            <?php
                    $selected_1 = isset($settings['text-input']) && $settings['text-input']=="display-always" ? "selected" : "";
                    $selected_2 = isset($settings['text-input']) && $settings['text-input']=="disable-always" ? "selected" : "";
		            echo '<option value="display-always" '.$selected_1.'>'.__('Display always','wc-cancel-order').'</option>';
		            echo '<option value="disable-always" '.$selected_2.'>'.__('Disable','wc-cancel-order').'</option>';
		            if(is_array($text_input) && !empty($text_input)){
		                echo '<optgroup class="wc-cancel-optgroup" label="'.__('Cancellation reason to display text input','wc-cancel-order').'">';
			            foreach($text_input as $text_in){
				            $selected_3 = isset($settings['text-input']) && $settings['text-input']==$text_in ? "selected" : "";
				            echo '<option value="'.$text_in.'" '.$selected_3.'>'.$text_in.'</option>';
			            }
			            echo '</optgroup>';
		            }
		            ?>
                </select>
            </div>
            <p class="description"><?php echo __('Display Text Input if above defined cancellation options is chosen.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Text input required','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <input type="checkbox" name="wc-cancel[text-required]" value="1" <?php echo isset($settings['text-required']) && $settings['text-required']=='1' ? 'checked' : ''; ?>>
                <p class="description-in"><?php echo __('Make Text input required.','wc-cancel-order'); ?></p>
            </div>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Hide cancel button','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <input id="wc-cancel-hide" class="wc-cancel-toggle" type="checkbox" name="wc-cancel[hide-cancel]" value="1" <?php echo isset($settings['hide-cancel']) && $settings['hide-cancel']=='1' ? 'checked' : ''; ?>>
                <p class="description-in"><?php echo __('Remove cancel button after specific time period.','wc-cancel-order'); ?></p>
            </div>
        </div>
        <div class="wcc-pro-row wc-cancel-hide-row">
            <label><?php echo __('Time option','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <input type="number" name="wc-cancel[time-interval]" class="wc-cancel-select-t" value="<?php echo isset($settings['time-interval']) ? $settings['time-interval'] : ''; ?>">
                <select name="wc-cancel[time-period]" class="wc-cancel-select-p">
                    <option value="minutes" <?php echo $settings['time-period']=='minutes' ? 'selected' : ''; ?>><?php echo __('Minutes','wc-cancel-order'); ?></option>
                    <option value="hour" <?php echo $settings['time-period']=='hour' ? 'selected' : ''; ?>><?php echo __('Hour','wc-cancel-order'); ?></option>
                    <option value="day" <?php echo $settings['time-period']=='day' ? 'selected' : ''; ?>><?php echo __('Day','wc-cancel-order'); ?></option>
                    <option value="month" <?php echo $settings['time-period']=='month' ? 'selected' : ''; ?>><?php echo __('Month','wc-cancel-order'); ?></option>
                    <option value="year" <?php echo $settings['time-period']=='year' ? 'selected' : ''; ?>><?php echo __('Year','wc-cancel-order'); ?></option>
                </select>
            </div>
            <p class="description"><?php echo __('Remove cancel button after certain period of order time.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Customer note','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <textarea name="wc-cancel[confirm-note]"><?php echo isset($settings['confirm-note']) ? $settings['confirm-note'] : ''; ?></textarea>
            </div>
            <p class="description"><?php echo __('Customer note will appear in cancellation request popup.','wc-cancel-order'); ?></p>
        </div>
        <div class="wcc-pro-row">
            <label><?php echo __('Allow guest cancellation','wc-cancel-order'); ?></label>
            <div class="wc-cancel-input">
                <input type="checkbox" name="wc-cancel[guest-cancel]" value="1" <?php echo isset($settings['guest-cancel']) && $settings['guest-cancel']=='1' ? 'checked' : ''; ?>>
                <p class="description-in"><?php echo __('Guest users will be able to cancel their order using the link sent in their order email.','wc-cancel-order'); ?></p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    var q1 = ".wc-cancel-req-status-select";
    var q2 = ".wc-cancel-status-select";
    var questions = <?php echo json_encode($statuses); ?>

    function rebuildList(option,id){

        var currentValues = jQuery(id).val();
        jQuery(id).empty();
        jQuery.each(questions,function(key,value){
            if(option.indexOf(key)===-1){
                var selected = currentValues.indexOf(key)===-1 ? "" : "selected";
                jQuery(id).append(jQuery('<option '+selected+'></option>').val(key).html(value));
            }
        });
    }

    jQuery(function($){

        $('.wc-cancel-toggle').change(function(){
            var id = $(this).attr('id');
            if(this.checked){
                $('.'+id+'-row').show();
            }
            else
            {
                $('.'+id+'-row').hide();
            }
        });

        $(document).find('input[type="checkbox"].wc-cancel-toggle').each(function(i){
            var id = $(this).attr('id');
            if(this.checked){
                $('.'+id+'-row').show();
            }
            else
            {
                $('.'+id+'-row').hide();
            }
        });

        $(q1).change(function(){
            var s1 = $(this).val();
            rebuildList(s1,q2);
            $(document.body).trigger('wc-enhanced-select-init');
            return false;
        });

        $(q2).change(function () {
            var s2 = $(this).val();
            rebuildList(s2,q1);
            $(document.body).trigger('wc-enhanced-select-init');
            return false;
        });

        $('textarea.wc-cancel-reason-options').keyup(function(){
            var reason = $(this).val().split('\n');
            var $optGroup = $('optgroup.wc-cancel-optgroup');
            $optGroup.empty();
            if(reason.length && $.isArray(reason)){
                for(var r=0;r<reason.length;r++){
                    $('<option value="'+reason[r]+'">').text(reason[r]).appendTo($optGroup);
                }
            }
        });

    });
</script>