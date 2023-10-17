<?php
/**
 * Plugin Name: Custom Fee
 * Version: 1.0
 * Author: wdraihan
 */

// Add a custom fee based on the selected payment method.
function add_custom_fee_based_on_payment_method($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    // Get the selected payment method from the session data.
    $chosen_payment_method = WC()->session->get('chosen_payment_method');

    if ($chosen_payment_method == 'stripe_cc' || $chosen_payment_method == 'ppcp' || $chosen_payment_method == 'stripe_googlepay') {
        // Calculate the fee for PayPal (9.9% of the cart subtotal).
        $online_payment_fee = get_option('online_payment_fee', 9.9);
        $fee = $cart->subtotal * ($online_payment_fee / 100);
        $cart->add_fee('Buyers Premium', $fee);
    } elseif ($chosen_payment_method == 'stripe_ach') {
        // Calculate the fee for bank payment (6.9% of the cart subtotal).
        $bank_payment_fee = get_option('bank_payment_fee', 6.9);
        $fee = $cart->subtotal * ($bank_payment_fee / 100);
        $cart->add_fee('Buyers Premium', $fee);
    }
}
add_action('woocommerce_cart_calculate_fees', 'add_custom_fee_based_on_payment_method');

add_action('wp_footer', function(){
	if(is_checkout()){
		?>
		<script>
			jQuery(document.body).on('change', 'input[name="payment_method"]', function () {
				jQuery(document.body).trigger('update_checkout');
			});
		</script>
		<?php
	}
});


// Add the custom fields to the General Settings page
function add_custom_settings_fields() {
    // Register the settings
    register_setting('general', 'online_payment_fee', 'sanitize_text_field');
    register_setting('general', 'bank_payment_fee', 'sanitize_text_field');
    register_setting('general', 'fee_notice', 'sanitize_text_field');

    // Add Online Payment Fee field
    add_settings_field(
        'online_payment_fee',
        'Online Payment Fee',
        'display_online_payment_fee_field',
        'general'
    );

    // Add Bank Payment Fee field
    add_settings_field(
        'bank_payment_fee',
        'Bank Payment Fee',
        'display_bank_payment_fee_field',
        'general'
    );
    // Add Bank Payment Fee field
    add_settings_field(
        'fee_notice',
        'Fee Notice',
        'display_payment_fee_notice_field',
        'general'
    );
}
add_action('admin_init', 'add_custom_settings_fields');

// Display the Online Payment Fee field
function display_online_payment_fee_field() {
    $online_payment_fee = get_option('online_payment_fee', '');
    echo '<input type="text" name="online_payment_fee" value="' . esc_attr($online_payment_fee) . '" />%';
}

// Display the Bank Payment Fee field
function display_bank_payment_fee_field() {
    $bank_payment_fee = get_option('bank_payment_fee', '');
    echo '<input type="text" name="bank_payment_fee" value="' . esc_attr($bank_payment_fee) . '" />%';
}

// Display the Bank Payment Fee field
function display_payment_fee_notice_field() {
    $fee_notice = get_option('fee_notice', '');
    echo '<input type="text" name="fee_notice" value="' . esc_attr($fee_notice) . '" />';
}


// fee description to show the percentage.
function wd_display_fee_description($cart_object) {
    $fee_notice = get_option('fee_notice', '');
	echo '<p class="fee_notice">'.$fee_notice.'</p>';
}
add_action('woocommerce_checkout_before_order_review', 'wd_display_fee_description');



add_filter( 'dokan_prepare_for_calculation', 'wd_dokan_prepare_for_calculation', 10, 6 );
function wd_dokan_prepare_for_calculation($earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id){
    global $woocommerce;
    $order = wc_get_order($order_id);
    $payment_method = $order->get_payment_method();
    
    if ($payment_method == 'stripe_cc' || $payment_method == 'ppcp' || $payment_method == 'stripe_googlepay') {
        // Calculate the fee for PayPal (9.9% of the cart subtotal).
        $online_payment_fee = get_option('online_payment_fee', 9.9);
        
        $fee_amount = $woocommerce->cart->subtotal * ($online_payment_fee / 100);

        return $earning - $fee_amount;

    } elseif ($payment_method == 'stripe_ach') {
        // Calculate the fee for bank payment (6.9% of the cart subtotal).
        $bank_payment_fee = get_option('bank_payment_fee', 6.9);
        
        $fee_amount = $woocommerce->cart->subtotal * ($bank_payment_fee / 100);
        
        return $earning - $fee_amount;
        
    }
    
}
