


add_filter('woocommerce_general_settings', 'wd_general_settings_shop_phone');
function wd_general_settings_shop_phone($settings) {
    $key = 0;

    foreach( $settings as $values ){
        $new_settings[$key] = $values;
        $key++;

        if($values['id'] == 'woocommerce_calc_discounts_sequentially'){
            $new_settings[$key] = array(
                'title'    => __('Buyer protection fee'),
                'desc'     => __('Add buyer protection fee in %. Default 10%'),
                'id'       => 'woocommerce_store_buyer_protection_fee', // <= The field ID (important)
                'default'  => '10',
                'type'     => 'number',
                'desc_tip' => true, // or false
            );
            $key++;
        }
    }
    return $new_settings;
}

// Add custom fee to WooCommerce checkout
function add_buyer_protection_fee() {
    global $woocommerce;

    // Check if the fee is already added to avoid duplicates
    if (sizeof($woocommerce->cart->get_applied_coupons()) > 0) return;

    // Retrieve the fee percentage from store settings
    $fee_percentage = get_option('woocommerce_store_buyer_protection_fee', 0);

    // Calculate the fee amount
    $fee_amount = $woocommerce->cart->subtotal * ($fee_percentage / 100);

    // Add the fee
    $woocommerce->cart->add_fee('Buyer Protection Fee', $fee_amount, true, 'standard');
}
add_action('woocommerce_cart_calculate_fees', 'add_buyer_protection_fee');

add_filter( 'dokan_prepare_for_calculation', 'wd_dokan_get_seller_amount_from_order', 10, 6 );
function wd_dokan_get_seller_amount_from_order($earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id){
	global $woocommerce;
	$fee_percentage = get_option('woocommerce_store_buyer_protection_fee', 0);

    // Calculate the fee amount
    $fee_amount = $woocommerce->cart->subtotal * ($fee_percentage / 100);
	
	$total = $earning - $fee_amount;
	return $total;
}

