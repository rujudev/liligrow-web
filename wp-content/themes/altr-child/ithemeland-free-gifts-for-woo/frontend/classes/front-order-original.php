<?php

/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

class iThemeland_front_order extends check_rule_condition
{
    public function __construct()
    {
        $this->gift_item_key = array();

        $this->settings = itg_get_settings();

        add_action('wp_enqueue_scripts', array($this, "woo_advanced_gift_js_css"));

        add_action('woocommerce_cart_loaded_from_session', array($this, 'check_session_gift'));

        //For display in Checkout
        add_action('woocommerce_review_order_after_cart_contents', array($this, 'review_order_after_cart_contents_adv_function'));

        add_action('wp', array($this, 'pw_add_free_gifts'));
        add_action('wp', [$this, 'pw_remove_gift']);

        add_action('wp_ajax_handel_pw_gift_show_variation', [$this, 'pw_gift_show_variation_function']);
        add_action('wp_ajax_nopriv_handel_pw_gift_show_variation', [$this, 'pw_gift_show_variation_function']);

        add_action('wp_ajax_handel_display_gifts_in_popup', [$this, 'display_gifts_coupon_popup']);
        add_action('wp_ajax_nopriv_handel_display_gifts_in_popup', [$this, 'display_gifts_coupon_popup']);

        add_action('woocommerce_new_order', array($this, 'add_gift_to_order_adv'), 99, 1);

        add_action('woocommerce_checkout_order_processed', array($this, 'it_woocommerce_checkout_order_processed'), 10, 3);
    }

    public function woo_advanced_gift_js_css()
    {
        $cart_page_id = wc_get_page_id('cart');
        $cart_page_id = get_permalink($cart_page_id);
        if (substr($cart_page_id, -1) == "/") {
            $cart_page_id = substr($cart_page_id, 0, -1);
        }

        //Carousel
        if ($this->settings['layout'] == 'carousel') {
            wp_register_style('it-gift-owl-carousel-style', plugin_dir_url_wc_advanced_gift . 'assets/css/owl-carousel/owl.carousel.min.css');
            wp_register_script('owl-carousel', plugin_dir_url_wc_advanced_gift . 'assets/js/owl-carousel/owl.carousel.min.js');

            wp_enqueue_script('it-owl-carousel', plugin_dir_url_wc_advanced_gift . 'assets/js/owl-carousel/owl-carousel-enhanced.js', array('jquery', 'owl-carousel'));
            wp_localize_script('it-owl-carousel', 'it_gift_carousel_ajax', array(
                'loop' => $this->settings['view_gift_in_cart']['carousel']['loop'],
                'dots' => $this->settings['view_gift_in_cart']['carousel']['dots'],
                'nav' => $this->settings['view_gift_in_cart']['carousel']['nav'],
                'speed' => $this->settings['view_gift_in_cart']['carousel']['speed'],
                'mobile' => $this->settings['view_gift_in_cart']['carousel']['mobile'],
                'tablet' => $this->settings['view_gift_in_cart']['carousel']['tablet'],
                'desktop' => $this->settings['view_gift_in_cart']['carousel']['desktop'],
            ));
        }
        //DropDown
        elseif ($this->settings['layout'] == 'dropdown') {
            wp_register_style('it-gift-dropdown-css', plugin_dir_url_wc_advanced_gift . 'assets/css/dropdown/dropdown.css');
            wp_register_script('it-gift-dropdown-js', plugin_dir_url_wc_advanced_gift . 'assets/js/dropdown/dropdown.js');
        }
        //DataTase
        elseif ($this->settings['layout'] == 'datatable') {
            wp_register_style('it-gift-datatables-style', plugin_dir_url_wc_advanced_gift . 'assets/css/datatables/jquery.dataTables.min.css');
            wp_register_script('it-gift-datatables-js', plugin_dir_url_wc_advanced_gift . 'assets/js/datatables/jquery.dataTables.min.js', array('jquery'));
        }
        $permalink = get_permalink();
        $add_to_cart_link = esc_url(add_query_arg(array('pw_add_gift' => '%s',), $permalink));
		$itg_localization_select_your_gift = get_option('itg_localization_select_your_gift', 'Select Your Gift');
        wp_register_script('pw-gift-add-jquery-adv', plugin_dir_url_wc_advanced_gift . 'assets/js/custom-jquery-gift.js', array('jquery'), '1.0.3');
        wp_localize_script('pw-gift-add-jquery-adv', 'pw_wc_gift_adv_ajax', array(
            'ajaxurl'                       => admin_url('admin-ajax.php'),
            'add_to_cart_link'              => $add_to_cart_link,
            'security'                      => wp_create_nonce('jkhKJSdd4576d234Z'),
            'action_show_variation'         => 'handel_pw_gift_show_variation',
            'action_display_gifts_in_popup' => 'handel_display_gifts_in_popup',
            'cart_page_id'                  => $cart_page_id,
			'select_your_gift'                  => $itg_localization_select_your_gift,
        ));
        //wp_enqueue_script( 'pw-gift-add-jquery-adv' );

        //Css
        wp_enqueue_style('it-gift-modal-style', plugin_dir_url_wc_advanced_gift . 'assets/css/modal/modal.css');

        //wp_register_style( 'it-gift-popup-style', plugin_dir_url_wc_advanced_gift . 'assets/css/popup/popup.css' );

        //Grid
        wp_enqueue_style('it-gift-style', plugin_dir_url_wc_advanced_gift . 'assets/css/style/style.css', [], '1.0.2');
        wp_enqueue_style('it-gift-popup', plugin_dir_url_wc_advanced_gift . 'assets/css/popup/popup.css', [], '1.0.2');

        //		wp_register_script( 'it-gift-grid-jquery', plugin_dir_url_wc_advanced_gift . 'assets/js/grid/grid.js' );

        //Scrollbar
        wp_enqueue_script('pw-gift-scrollbar-js', plugin_dir_url_wc_advanced_gift . 'assets/js/scrollbar/jquery.scrollbar.min.js');
    }

    public function check_session_gift()
    {
        global $woocommerce;
        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
        if (!($this->gift_item_key = $this->pw_get_gift_for_cart_checkout())) {
            if (is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0) {
                wc_add_notice('Your Free Gift(s) were removed because your current cart contents is not eligible for a free gift', 'error');
            }

            WC()->session->set('gift_group_order_data', '');
            return '';
        }
		//Get session for check again
        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
        $show_notice = false;
        //Fetch settings
        if (!is_array($this->settings) || count($this->settings) <= 0) {
            $this->settings = itg_get_settings();
        }


        $this->product_qty_in_cart  = itg_get_cart_item_stock_quantities();


        /**  Check Quantity  **/
        if (is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0) {
            $product_qty_in_cart_gift = itg_get_cart_item_quantities_gift_stock(); //can't public
            foreach ($retrieved_group_input_value as $index => $set) {
                $product_get        = wc_get_product($set['id_product']);
                $get_stock_quantity = $product_get->get_stock_quantity();
                if ($product_get->is_in_stock() && $get_stock_quantity >= 1) {
                    $x                           = 0;
                    $required_stock_in_cart_gift = isset($product_qty_in_cart_gift[$set['id_product']]) ? $product_qty_in_cart_gift[$set['id_product']] : 0;
                    $required_stock_in_cart      = isset($this->product_qty_in_cart[$product_get->get_stock_managed_by_id()]) ? $this->product_qty_in_cart[$product_get->get_stock_managed_by_id()] : 0;
                    $x                           = $get_stock_quantity - $required_stock_in_cart;
                    if ($x < $required_stock_in_cart_gift) {
                        if ($x <= 0) {
                            unset($retrieved_group_input_value[$index]);
                            $show_notice = true;
                        } else {
                            $retrieved_group_input_value[$index]['q'] = $x;
                        }
                    }
                }
            }
        }
        /**  End Check Quantity  **/
		WC()->session->set('gift_group_order_data', $retrieved_group_input_value);
        $count_info = itg_check_quantity_gift_in_session();
        if (
            is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0 &&
            $retrieved_group_input_value != ''
        ) {

            foreach ($retrieved_group_input_value as $gift => $value) {
                if (!isset($this->gift_item_variable['all_gifts'][$value['id']])) {
                    unset($retrieved_group_input_value[$gift]);
                    $show_notice = true;
                    continue;
                }

                $uid = $this->gift_item_variable['all_gifts'][$gift]['uid'];
                //For Check if rule is changed and gift was added to cart (so gift from rule must remove from cart)
                if ($retrieved_group_input_value[$gift]['time_add'] != $this->gift_item_variable['rule_time']) {
                    unset($retrieved_group_input_value[$gift]);
                    $show_notice = true;
                    continue;
                }

                if (!array_key_exists($gift, $this->gift_item_variable['all_gifts'])) {
                    unset($retrieved_group_input_value[$gift]);
                    $show_notice = true;
                    continue;
                }

                //Number Allow For Simple Method
                $pw_number_gift_allowed = $this->gift_item_variable[$uid]['pw_number_gift_allowed'];
				
                for ($i = 1; $i < $count_info['count_gift']; $i++) {
                    if (
                        isset($count_info['count_rule_gift'][$value['uid']]['q']) && $count_info['count_rule_gift'][$value['uid']]['q'] >
                        $pw_number_gift_allowed && !in_array(
                            $this->gift_item_variable[$uid]['method'],
                            array(
                                'buy_x_get_x_repeat'
                            ),
                            true
                        )
                    ) {
                        if ($retrieved_group_input_value[$gift]['q'] <= 1) {
                            unset($retrieved_group_input_value[$gift]);
                            $show_notice = true;
                        } else {
                            $retrieved_group_input_value[$gift]['q']--;
                        }
                        $count_info['count_rule_gift'][$value['uid']]['q']--;
                    }
                }

                //For Quantity is update
                if (array_key_exists(
                    $value['uid'],
                    $count_info['count_rule_gift']
                ) && $retrieved_group_input_value != '' && count($retrieved_group_input_value) > 0 && $value['q'] > $pw_number_gift_allowed) {
                    //if Quantity gift is <= 1  from Quantity less , else  kol less ko
                    if (isset($retrieved_group_input_value[$gift]['q']) && $retrieved_group_input_value[$gift]['q'] < 1) {
                        unset($retrieved_group_input_value[$gift]);
                        $show_notice = true;
                    } else {
                        $retrieved_group_input_value[$gift]['q'] = $pw_number_gift_allowed;
                    }
                    $count_info['count_rule_gift'][$value['uid']]['q']--;
                }

                //check if any confuse and if item session wasn't in apply gift
                if (!array_key_exists(
                    $gift,
                    $this->gift_item_variable['all_gifts']
                ) || count($this->gift_item_variable['all_gifts'][$gift]) <= 0) {
                    $show_notice = true;
                    unset($retrieved_group_input_value[$gift]);
                    continue;
                }
            }
            WC()->session->set('gift_group_order_data', $retrieved_group_input_value);
        }

        if ($show_notice) {
            wc_add_notice('Your Free Gift(s) were removed because your current cart contents is not eligible for a free gift', 'error');
        }

        add_action('woocommerce_cart_contents', [$this, 'display_gifts_added_in_cart'],1);

        if ($this->settings['position'] == 'bottom_cart') {
            add_action('woocommerce_after_cart_table', [$this, 'display_gifts_bottom_cart']);
        } elseif ($this->settings['position'] == 'beside_coupon') {
            add_action('woocommerce_cart_coupon', [$this, 'display_gifts_in_Coupon_dropdown']);
        } elseif ($this->settings['position'] == 'popup') {
            add_action('wp_head', array($this, 'display_gifts_in_popup'));
        }
    }

    public function display_gifts_bottom_cart()
    {

        global $woocommerce;
        $url  = '';
        $file = 'parent';
        if ($this->settings['child'] == 'true') {
            $file = 'child';
        }
		$url = plugin_dir_path_wc_adv_gift . 'views/dropdown/simple.php';
        if ($this->settings['layout'] == 'carousel') {
            $url = plugin_dir_path_wc_adv_gift . 'views/carousel/' . $file . '.php';
        } elseif ($this->settings['layout'] == 'grid') {
            $url = plugin_dir_path_wc_adv_gift . 'views/grid/' . $file . '.php';
        } elseif ($this->settings['layout'] == 'datatable') {
            $url = plugin_dir_path_wc_adv_gift . 'views/datatable/' . $file . '.php';
        }
        require $url;


		$is_child=false;
		if($this->settings['child'] == 'true' )
		{
			$is_child=true;	
		}
		$atts=[
			'gift_rule_exclude'   => $this->gift_rule_exclude,		
			'quantity_products_in_cart' => $this->product_qty_in_cart,
			'gifts_items_cart'        => $this->show_gift_item_for_cart,
			'all_gift_items'          => $this->gift_item_variable,
			'settings'  => $this->settings,
			'multi_level' => false ,
			'is_child'  => $is_child,
		];
		//$atts=itg_get_gift_lite_products_data_multilevel( $atts );
		//echo '<pre>';print_r($atts);die;
		//itg_get_template($template, $atts);		
    }

    public function display_gifts_in_popup()
    {

        if (!is_cart() && !is_checkout()) {
            return;
        }
        require plugin_dir_path_wc_adv_gift . 'views/modal/autoload-popup.php';
    }

    public function display_gifts_in_Coupon_dropdown()
    {
        if ($this->settings['layout'] == "dropdown") {
            require plugin_dir_path_wc_adv_gift . 'views/dropdown/simple.php';
        } else {
            wp_enqueue_style('it-gift-datatables-style');
            wp_enqueue_script('it-gift-datatables-js');

            // wp_enqueue_style('it-gift-style');
            wp_enqueue_script('pw-gift-add-jquery-adv');

            echo '<button type="button" class="button btn_select_gift_in_coupon">' . __('Select Gift', 'ithemeland-free-gifts-for-woocommerce-lite') . '</button>';
        }
    }

    public function display_gifts_added_in_cart()
    {

        global $woocommerce, $product;
        $cart_page_id = get_permalink(wc_get_page_id('cart'));
        if (substr($cart_page_id, -1) == "/") {
            $cart_page_id = substr($cart_page_id, 0, -1);
        }
        if (strpos($cart_page_id, '?') !== false) {
            $cart_page_id = $cart_page_id . '&';
        } else {
            $cart_page_id = $cart_page_id . '?';
        }
		$txt_free = get_option('itg_localization_txt_free', 'Free');
        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
        if ($retrieved_group_input_value != '' && is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0) {
            foreach ($retrieved_group_input_value as $key => $index) {
                $gift_index = "";
                $img_html = '';
                $product = wc_get_product($index['id_product']);
                if (!($product instanceof \WC_Product)) {
                    return false;
                }

                $img_url = (!empty($product->get_image_id())) ? wp_get_attachment_image_src($product->get_image_id(), [50, 50]) : wc_placeholder_img_src([50, 50]);
                $img_url = (is_array($img_url) && !empty($img_url[0])) ? $img_url[0] : $img_url;
                $gift_count = !empty($index['q']) ? intval($index['q']) : 1;
                $title = $product->get_title();

                $price = $txt_free;
                $price_total = $txt_free;
                if ($this->settings['display_price'] == 'yes') {
                    $price = $product->get_price_html();
                    $price_total = $product->get_price();
                    if (!$product->get_price()) {
                        $price_total = 0;
                    }
                    if (is_numeric($price_total)) {
                        $price_total = $price_total * $gift_count;
                    }
                    $price_total = wc_price($price_total);
                    $price = '<del>' . $price . '</del> ' . $txt_free;
                    $price_total = '<del>' . $price_total . '</del> ' . $txt_free;
                }
                if ($product->post_type == 'product_variation') {
                    $title = $product->get_name();
                }
                $title_gift = apply_filters('woocommerce_checkout_product_title', $title, $product);

                $img_html   = itg_render_product_image($product, false);

                echo
                '<tr class="woocommerce-cart-form__cart-item cart_item">
						<td class="product-remove">
							<a class="remove gift-close-link" href="' . esc_url($cart_page_id) . 'it_gift_remove=' .
                    $index['id'] . '">×</a>
						</td>
						<td class="product-thumbnail">' . sprintf("%s", $img_html) . '</td>
						<td class="product-name" data-title="' . esc_attr($title_gift) . '"><a href="' .
                    get_permalink($index['id_product']) . '">' . sprintf("%s", $title_gift) . '</a></td>
						<td class="product-price" data-title="' . __("Price", 'ithemeland-free-gifts-for-woocommerce-lite') . '">' . $price . '</td>
						<td class="product-quantity" data-title="' . __("Quantity", 'ithemeland-free-gifts-for-woocommerce-lite') . '">' . sprintf("%s", $gift_count) . '</td>
						<td class="product-subtotal" data-title="' . __("Total", 'ithemeland-free-gifts-for-woocommerce-lite') . '">' . $price_total . '</td>
					</tr>';
            }
        }
    }

    public function review_order_after_cart_contents_adv_function()
    {
        global $woocommerce;
        if (!($this->gift_item_key = $this->pw_get_gift_for_cart_checkout())) {
            return '';
        }
        if (!is_array($this->gift_item_key) || count($this->gift_item_key) <= 0) {
            return;
        }

		$txt_free = get_option('itg_localization_txt_free', 'Free');
        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
        if ($retrieved_group_input_value != '' && is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0) {
            foreach ($retrieved_group_input_value as $key => $index) {
                $img_html = '';
                $product = wc_get_product($index['id_product']);
                if (!($product instanceof \WC_Product)) {
                    return false;
                }

                $img_url = (!empty($product->get_image_id())) ? wp_get_attachment_image_src($product->get_image_id(), [50, 50]) : wc_placeholder_img_src([50, 50]);
                $img_url = (is_array($img_url) && !empty($img_url[0])) ? $img_url[0] : $img_url;
                $title = $product->get_title();
                if ($product->post_type == 'product_variation') {
                    $title = $product->get_name();
                }
                $price_p = $txt_free;
                $count   = isset($retrieved_group_input_value[$key]['q']) ? $retrieved_group_input_value[$key]['q'] : 1;
                echo '<tr>
                          <td class="product-name">' .
                    apply_filters('woocommerce_checkout_product_title', $title, $product) . ' ' .
                    '<strong class="product-quantity">' . sprintf('%s', $count) . '</strong>' .
                    '</td>
                          <td class="product-total" style="color: #00aa00;">' . sprintf('%s', $price_p) . '</td>
                      </tr>';
            }
        }
    }

    public function add_gift_to_order_adv($order_id)
    {
        global $woocommerce;
        if (!($this->gift_item_key = $this->pw_get_gift_for_cart_checkout())) {
            if (!empty(WC()->session) && method_exists(WC()->session, 'set')) {
                WC()->session->set('gift_group_order_data', '');
            }

            return '';
        }
        global $wpdb;
        if (is_array($this->gift_item_variable['all_gifts']) && count($this->gift_item_variable['all_gifts']) > 0) {
            $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
            if ($retrieved_group_input_value != '' && is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0) {
                //For Limit
                $set_rule_limit = '';

                //End for limit
                $order                = new WC_Order($order_id);
                $note                 = 'The Gifts for order Added: ';
                $set_gift             = false;
                $user_id              = $order->get_customer_id('view');
                $pw_gift_rule_counter = get_user_meta($user_id, 'pw_gift_rule_counter', true);
                foreach ($retrieved_group_input_value as $key => $index) {

                    $product_id = "";
                    $product_id = $index['id_product'];
                    $uid        = $index['uid'];
                    $_product   = wc_get_product($product_id);

                    $item                 = array();
                    $item['variation_id'] = $this->get_variation_id($_product);
                    @$item['variation_data'] = $item['variation_id'] ? $this->get_variation_attributes($_product) : '';

                    $title = $_product->get_title();
                    if ($_product->post_type == 'product_variation') {
                        $product_id = wp_get_post_parent_id($product_id);
                        $title      = $_product->get_name();
                    }

                    if ($_product->is_in_stock()) {
                        $item_id = wc_add_order_item($order_id, array(
                            'order_item_name' =>
                            $title,
                            'order_item_type' => 'line_item'
                        ));
                        if ($item_id) {
                            $note .= $_product->get_title() . '(' . $_product->get_sku() . ') , ';
                            wc_add_order_item_meta($item_id, '_qty', $retrieved_group_input_value[$key]['q']);
                            wc_add_order_item_meta($item_id, '_tax_class', $_product->get_tax_class());
                            wc_add_order_item_meta($item_id, '_product_id', $product_id);
                            wc_add_order_item_meta($item_id, '_variation_id', $this->get_variation_id($_product));
                            wc_add_order_item_meta($item_id, '_line_subtotal', wc_format_decimal(0, 4));
                            wc_add_order_item_meta($item_id, '_line_total', wc_format_decimal(0, 4));
                            wc_add_order_item_meta($item_id, '_line_tax', wc_format_decimal(0, 4));
                            wc_add_order_item_meta($item_id, '_line_subtotal_tax', wc_format_decimal(0, 4));
                            wc_add_order_item_meta($item_id, '_free_gift', 'yes');
                            wc_add_order_item_meta($item_id, '_rule_id_free_gift', $uid);
                            $set_gift = true;
                            if (@$item['variation_data'] && is_array($item['variation_data'])) {
                                foreach ($item['variation_data'] as $key => $value) {
                                    wc_add_order_item_meta(
                                        $item_id,
                                        esc_attr(str_replace('attribute_', '', $key)),
                                        $value
                                    );
                                }
                            }

                            //For Limit
                            if ($uid != $set_rule_limit) {
                                //								$rules[ $uid ]['limit_counter']['count'] ++;
                                if ($user_id > 0) {
                                    if ('' === $pw_gift_rule_counter) {
                                        $pw_gift_rule_counter           = array();
                                        $pw_gift_rule_counter[$uid][] = array(
                                            'uid'    => $uid,
                                            'number' => 1,
                                        );
                                    } else {
                                        $pw_gift_rule_counter[$uid]['number']++;
                                    }
                                }
                                $set_rule_limit = $uid;
                            }
                        }
                    }
                }

                update_user_meta($user_id, 'pw_gift_rule_counter', $pw_gift_rule_counter);

                WC()->session->set('gift_group_order_data', '');
                if ($set_gift) {
                    $order->add_order_note($note);
                    update_post_meta($order_id, 'gift_set', 'yes');
                    update_post_meta($order_id, 'gift_array', $retrieved_group_input_value);
					
					// Set id in the order.
					// Improvement for HPOS compatibility.
					$order->add_meta_data('gift_set', 'yes');
					$order->add_meta_data('gift_array', $retrieved_group_input_value);
					$order->save();								
                }
            }
        }
    }

    public function it_woocommerce_checkout_order_processed($order_id, $post, $order)
    {
        $giftcoupon = get_post_meta($order_id, "gift_set", true);
        if ($giftcoupon != 'yes') {
            return;
        }
        $order = wc_get_order($order_id);
        $flag  = false;
        foreach ($order->get_items() as $item_id => $item) {
            $free_gift = wc_get_order_item_meta($item_id, '_free_gift');
            if ($free_gift == 'yes') {
                $flag = true;
                break;
            }
        }
        if ($flag) {
            return;
        }
        $set_gift   = false;
        $note       = 'The Gifts for order Added After Change Status: ';
        $gift_array = get_post_meta($order_id, "gift_array", true);
        foreach ($gift_array as $key => $index) {
            $product_id = "";

            $product_id = $index['id_product'];
            $rule_id    = $index['rule_id'];
            $_product   = wc_get_product($product_id);

            $title = $_product->get_title();
            if ($_product->post_type == 'product_variation') {
                $product_id = wp_get_post_parent_id($product_id);
                $title      = $_product->get_name();
            }

            $item                 = array();
            $item['variation_id'] = $this->get_variation_id($_product);
            @$item['variation_data'] = $item['variation_id'] ? $this->get_variation_attributes($_product) : '';

            if ($_product->is_in_stock()) {
                $item_id = wc_add_order_item($order_id, array(
                    'order_item_name' =>
                    $title,
                    'order_item_type' => 'line_item'
                ));
                if ($item_id) {
                    $note .= $_product->get_title() . '(' . $_product->get_sku() . ') , ';
                    wc_add_order_item_meta($item_id, '_qty', $gift_array[$key]['q']);
                    wc_add_order_item_meta($item_id, '_tax_class', $_product->get_tax_class());
                    wc_add_order_item_meta($item_id, '_product_id', $product_id);
                    wc_add_order_item_meta($item_id, '_variation_id', $this->get_variation_id($_product));
                    wc_add_order_item_meta($item_id, '_line_subtotal', wc_format_decimal(0, 4));
                    wc_add_order_item_meta($item_id, '_line_total', wc_format_decimal(0, 4));
                    wc_add_order_item_meta($item_id, '_line_tax', wc_format_decimal(0, 4));
                    wc_add_order_item_meta($item_id, '_line_subtotal_tax', wc_format_decimal(0, 4));
                    wc_add_order_item_meta($item_id, '_free_gift', 'yes');
                    wc_add_order_item_meta($item_id, '_rule_id_free_gift', $rule_id);
                    $set_gift = true;
                    if (@$item['variation_data'] && is_array($item['variation_data'])) {
                        foreach ($item['variation_data'] as $key => $value) {
                            wc_add_order_item_meta($item_id, esc_attr(str_replace('attribute_', '', $key)), $value);
                        }
                    }
                }
            }
        }
        if ($set_gift) {
            $order->add_order_note($note);
        }
    }

    protected function get_variation_id($_product)
    {
        if (version_compare(WC()->version, "2.7.0") >= 0) {
            return $_product->get_id();
        } else {
            return $_product->variation_id;
        }
    }

    protected function get_variation_attributes($_product)
    {
        if (version_compare(WC()->version, "2.7.0") >= 0) {
            return wc_get_product_variation_attributes($_product->get_id());
        } else {
            return $_product->get_variation_attributes();
        }
    }

    public function pw_add_free_gifts()
    {
        if (!isset($_REQUEST['pw_add_gift'])) {
            return;
        }
        // Return if cart object is not initialized.
        if (!is_object(WC()->cart)) {
            return;
        }

        // return if cart is empty
        if (WC()->cart->get_cart_contents_count() == 0) {
            return;
        }

        if (!($this->gift_item_key = $this->pw_get_gift_for_cart_checkout())) {
            return;
        }

        $gift      = sanitize_text_field($_REQUEST['pw_add_gift']);
        if (!array_key_exists($gift, $this->gift_item_variable['all_gifts'])) {
            wp_safe_redirect(get_permalink());
            exit();
        }

        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
        $count_info                 = itg_check_quantity_gift_in_session();



        $uid        = $this->gift_item_variable['all_gifts'][$gift]['uid'];
        $id_product = $this->gift_item_variable['all_gifts'][$gift]['id_product'];

        //Number Allow For Simple Method
        $pw_number_gift_allowed = $this->gift_item_variable[$uid]['pw_number_gift_allowed'];

        //Check all gift Rule added Qty
        $product_get = wc_get_product($id_product);

        if (array_key_exists($uid, $count_info['count_rule_gift']) && $count_info['count_rule_gift'][$uid]['q'] >= $pw_number_gift_allowed && !in_array($this->gift_item_variable[$uid]['method'], array('buy_x_get_x_repeat'), true)) {
            wp_safe_redirect(get_permalink());
            exit();
        } elseif (in_array(
            $gift,
            $count_info['gifts_set']
        ) && $this->gift_item_variable[$uid]['can_several_gift'] == 'no') {
            wp_safe_redirect(get_permalink());
            exit();
        } elseif (
            $retrieved_group_input_value != '' && is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0 &&
            $retrieved_group_input_value[$gift]['q'] >=
            $pw_number_gift_allowed
        ) {
            wp_safe_redirect(get_permalink());
            exit();
        }

        /**  Check Quantity  **/
        $flag_stock         = 0;
        $get_stock_quantity = $product_get->get_stock_quantity();
        if (!$product_get->is_in_stock() && $get_stock_quantity <= 0) {
            $flag_stock = 1;
        } elseif ($product_get->is_in_stock() && $get_stock_quantity >= 1) {
            $get_cart_item_stock_quantities      = itg_get_cart_item_stock_quantities();
            $get_cart_item_quantities_gift_stock = itg_get_cart_item_quantities_gift_stock();
            $required_stock_in_cart              = isset($get_cart_item_stock_quantities[$product_get->get_stock_managed_by_id()]) ? $get_cart_item_stock_quantities[$product_get->get_stock_managed_by_id()] : 0;
            $required_stock_in_cart_gift         = isset($get_cart_item_quantities_gift_stock[$id_product]) ? $get_cart_item_quantities_gift_stock[$id_product] : 0;
            if (($get_stock_quantity - $required_stock_in_cart) - $required_stock_in_cart_gift <= 0) {
                $flag_stock = 1;
            }
        }
        if ($flag_stock == 1) {
            wp_safe_redirect(get_permalink());
            exit();
        }
        /**  End Check Quantity  **/

        if ($count_info['count_gift'] > 0) {
            if (array_key_exists($uid, $count_info['count_rule_gift']) && isset($retrieved_group_input_value[$gift]['q'])) {
                $retrieved_group_input_value[$gift] = array(
                    'id'         => $gift,
                    'q'          =>
                    $retrieved_group_input_value[$gift]['q'] + 1,
                    'uid'        => $uid,
                    'id_product' => $id_product,
                    'time_add'   =>
                    $this->gift_item_variable['rule_time']
                );
                WC()->session->set('gift_group_order_data', $retrieved_group_input_value);
            } else {
                $retrieved_group_input_value[$gift] = array(
                    'id'         => $gift,
                    'q'          => 1,
                    'uid'        => $uid,
                    'id_product' => $id_product,
                    'time_add'   => $this->gift_item_variable['rule_time']
                );
                WC()->session->set('gift_group_order_data', $retrieved_group_input_value);
            }
        } else {
            $retrieved_group_input_value          = array();
            $retrieved_group_input_value[$gift] = array(
                'id'         => $gift,
                'q'          => 1,
                'uid'        => $uid,
                'id_product' => $id_product,
                'time_add'
                => $this->gift_item_variable['rule_time']
            );
            WC()->session->set('gift_group_order_data', $retrieved_group_input_value);
        }
        wc_add_notice('Gift product added successfully', 'ithemeland-free-gifts-for-woocommerce-lite');
        wp_safe_redirect(get_permalink());
        exit();
    }

    public function display_gifts_coupon_popup()
    {
        $nonce = $_REQUEST['security'];
        if (!wp_verify_nonce($nonce, 'jkhKJSdd4576d234Z')) {
            wp_die('Forbidden!!!');
        }
        global $woocommerce;

        wp_enqueue_style('it-gift-datatables-style');
        wp_enqueue_script('it-gift-datatables-js');

        wp_enqueue_script('pw-gift-add-jquery-adv');

        wc_get_template('btn-popup.php', array(
            'products_ids'        => $this->show_gift_item_for_cart,
            'gift_item_variable'  => $this->gift_item_variable,
            'gift_rule_exclude'   => $this->gift_rule_exclude,
            'product_qty_in_cart' => $this->product_qty_in_cart,
            'settings'            => $this->settings,
        ), '', plugin_dir_path_wc_adv_gift . 'views/modal/');

        wp_die();
    }

    public function pw_gift_show_variation_function()
    {
        $ret = '';

        $nonce = $_REQUEST['security'];
        if (!wp_verify_nonce($nonce, 'jkhKJSdd4576d234Z')) {
            wp_die('Forbidden!!!');
        }

        global $woocommerce;
        if (!isset($_POST['pw_gift_variable'])) {
            wp_die();
        }

        $view = 'tables.php';

        wp_enqueue_script('pw-gift-add-jquery-adv');

        $variable  = sanitize_text_field($_POST['pw_gift_variable']);
        $p_product = wc_get_product($variable);

        $product_type = $p_product->get_type();

        if ($product_type == 'variable') {
            $variation_ids = version_compare(
                WC()->version,
                '2.7.0',
                '>='
            ) ? $p_product->get_visible_children() : $p_product->get_children(true);

            wc_get_template($view, array(
                'products_ids'        => $variation_ids,
                'uid'                 => sanitize_text_field($_POST['pw_gift_uid']),
                'gift_item_variable'  => $this->gift_item_variable,
                'gift_rule_exclude'   => $this->gift_rule_exclude,
                'product_qty_in_cart' => $this->product_qty_in_cart,
                'settings'            => $this->settings,
                'view'                => 'modal',
            ), '', plugin_dir_path_wc_adv_gift . 'views/modal/');
        }

        wp_die();
    }

    public function pw_remove_gift($gift = null)
    {
        global $woocommerce;
        if (!isset($_REQUEST['it_gift_remove'])) {
            return;
        }

        // Return if cart object is not initialized.
        if (!is_object(WC()->cart)) {
            return;
        }

        // return if cart is empty
        if (WC()->cart->get_cart_contents_count() == 0) {
            return;
        }
        //Remove Gift Cart
        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');
        if ($retrieved_group_input_value != '' && is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0 && array_key_exists($_GET['it_gift_remove'], $retrieved_group_input_value)) {
            unset($retrieved_group_input_value[$_GET['it_gift_remove']]);
            WC()->session->set('gift_group_order_data', $retrieved_group_input_value);
        }
        wc_add_notice('Your Free Gifts  were removed', 'notice');
        wp_safe_redirect(get_permalink());
        exit();
    }
}

new iThemeland_front_order();
