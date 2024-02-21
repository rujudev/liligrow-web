<?php

/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

wp_enqueue_style('it-gift-style');
wp_enqueue_script('pw-gift-add-jquery-adv');


wp_enqueue_style('it-gift-owl-carousel-style');
wp_enqueue_script('it-gift-owl-carousel-jquery');


$product_item               = '';
$retrieved_group_input_value = WC()->session->get('gift_group_order_data');
$count_info                 = itg_check_quantity_gift_in_session();

$add_gift = esc_html(get_option('itg_localization_add_gift','Add Gift'));


foreach ($this->show_gift_item_for_cart as $gift_item_key => $gift) {
    if (isset($gift['auto']) && $gift['auto'] == 'yes') {
        continue;
    }
    $text_stock_qty = 'in stock';
    $item_hover     = 'hovering';
    $disable        = false;
    $img_html       = $title_html = '';

    $product      = wc_get_product($gift['item']);
	if (!($product instanceof \WC_Product)) {
		 continue;
	}

    print_r($product);
    $product_type = $product->get_type();
    if ($product_type == 'variable') {
        $variation_ids = version_compare(
            WC()->version,
            '2.7.0',
            '>='
        ) ? $product->get_visible_children() : $product->get_children(true);
        foreach ($variation_ids as $product_id) {
            $_product = wc_get_product($product_id);
            $gift_id  = $gift['uid'] . '-' . $product_id;
            //For exclude in select variations
            if (isset($this->gift_rule_exclude[$gift['uid']]) && in_array(
                $product_id,
                $this->gift_rule_exclude[$gift['uid']]
            )) {
                continue;
            }
            $item_hover = 'hovering';

            $array_return   = itg_quantities_gift_stock($_product, $this->product_qty_in_cart, $product_id, $product_type, $this->settings, $item_hover);
            $item_hover     = $array_return['item_hover'];
            $text_stock_qty = $array_return['text_stock_qty'];
            $stock_status = $array_return['stock_status'];

            $flag_count = false;

            if (in_array($gift['method'], array('buy_x_get_x_repeat',), true) && $gift['base_q'] == 'ind') {
                if (array_key_exists($gift_item_key, $retrieved_group_input_value) && $retrieved_group_input_value[$gift_item_key]['q'] >= $this->gift_item_variable['all_gifts'][$gift_item_key]['q']) {
                    $flag_count = true;
                }
            } elseif (array_key_exists($gift['uid'], $count_info['count_rule_gift']) && $count_info['count_rule_gift'][$gift['uid']]['q'] >= $gift['pw_number_gift_allowed']) {
                $flag_count = true;
            }

            if (
                $flag_count ||
                (in_array($gift_id, $count_info['gifts_set']) && $gift['can_several_gift'] == 'no')
                ||
                (in_array($gift_id, $count_info['gifts_set']) && $this->gift_item_variable[$gift['uid']]['can_several_gift'] == 'no')
            ) {
                $item_hover = 'disable-hover';
            }
            $title = $_product->get_title();
            if ($_product->post_type == 'product_variation') {
                $title = $_product->get_name();
            }
            $product_type = $product->get_type();

            $class_btn_add = 'btn-add-gift-button';

            $product_item .= '
        <div class="wgb-product-item-cnt ' . esc_attr($item_hover) . ' ' . esc_attr($stock_status) . '">
            <div class="wgb-item-thumb">
                 ' . itg_render_product_image($_product, false) . '
                <div class="wgb-item-overlay"></div>
                <div class="wgb-stock"><div class="gift-product-stock">' . sprintf("%s", $text_stock_qty) . '</div></div>
            </div>
            <div class="wgb-item-content">
                <h1 class="wgb-item-title font-weight-bold">
                    <a href="#">' . sprintf("%s", $title) . '</a>
                </h1>';
			$product_item .= apply_filters('it_free_gift_after_button_add_gift', '', $gift['item'] , $gift);
            $product_item .= '
                    <div class="wgb-add-gift-btn ' . $class_btn_add . '" data-id="' . esc_attr($gift_id) . '">
                        <div class="wgb-loading-icon wgb-d-none">
                            <div class="wgb-spinner wgb-spinner--2"></div>
                        </div>
                       <span>' . $add_gift . '</span>
                    </div>';
            $product_item .= '
            </div>
        </div>';
        }
    } //End Variable
    else {
        $flag_count = false;
        $array_return = itg_quantities_gift_stock($product, $this->product_qty_in_cart, $gift['item'], $product_type, $this->settings, $item_hover);
        $item_hover = $array_return['item_hover'];
        $text_stock_qty = $array_return['text_stock_qty'];
        $stock_status = $array_return['stock_status'];

        if (in_array($gift['method'], array('buy_x_get_x_repeat',), true) && $gift['base_q'] == 'ind') {
            if (array_key_exists($gift_item_key, $retrieved_group_input_value) && $retrieved_group_input_value[$gift_item_key]['q'] >= $this->gift_item_variable['all_gifts'][$gift_item_key]['q']) {
                $flag_count = true;
            }
        } elseif (array_key_exists($gift['uid'], $count_info['count_rule_gift']) && $count_info['count_rule_gift'][$gift['uid']]['q'] >= $gift['pw_number_gift_allowed']) {
            $flag_count = true;
        }

        if ($flag_count || (in_array($gift_item_key, $count_info['gifts_set']) && $gift['can_several_gift'] == 'no')) {
            $item_hover = 'disable-hover';
        }

        $title = $product->get_title();
        if ($product->post_type == 'product_variation') {
            $title = $product->get_name();
        }
        $class_btn_add = 'btn-add-gift-button';


        print_r($product);
        $product_item .= '
        <div class="wgb-product-item-cnt ' . esc_attr($item_hover) . ' ' . esc_attr($stock_status) . '">
            <div class="wgb-item-thumb">
               ' . itg_render_product_image($product, false) . '
                <div class="wgb-item-overlay"></div>
                <div class="wgb-stock"><div class="gift-product-stock">' . sprintf("%s", $text_stock_qty) . '</div></div>
            </div>
            <div class="wgb-item-content">
                <h1 class="wgb-item-title font-weight-bold">
                    <a href="#">' . sprintf("%s", $title) . '</a>
                </h1>';

        $gift_key = ($item_hover != 'disable-hover') ? $gift['key'] : '';
		$product_item .= apply_filters('it_free_gift_after_button_add_gift','', $gift['item'] , $gift);
        $product_item .= '
                        <div class="wgb-add-gift-btn ' . $class_btn_add . '" data-id="' . esc_attr($gift_key) . '">
                            <div class="wgb-loading-icon wgb-d-none">
                                <div class="wgb-spinner wgb-spinner--2"></div>
                            </div>
                            <span>' . $add_gift . '</span>
                        </div>';

        $product_item .= '
            </div>
        </div>';
    }
}

if ($product_item == '') {
    return;
}

?>

<div class="adv-gift-section wgb-product-cnt wgb-frontend-gifts wgb-item-layout2">
    <div class="wgb-header-cnt">
        <h2 class="wgb-title text-capitalize font-weight-bold"><?php echo esc_html(get_option('itg_localization_our_gift','Our Gift')); ?></h2>
    </div>
    <div class="wgb-owl-carousel owl-carousel it-owl-carousel-items" id="pw_slider_adv_gift">
        <?php echo wp_kses_post($product_item); ?>
    </div>
</div>