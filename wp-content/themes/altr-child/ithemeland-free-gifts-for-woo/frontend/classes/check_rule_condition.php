<?php

use wgbl\classes\repositories\Rule;

if (!defined('ABSPATH')) {
    exit;
}

class check_rule_condition
{
    protected $item_cart;

    public function __construct()
    {
        $this->item_cart = array();
    }

    private function get_condition_type_methods()
    {
        return [
            // date time
            'date' => 'date_time',
            'time' => 'date_time',
            'date_time' => 'date_time',
            'days_of_week' => 'date_time',
        ];
    }

    public function pw_get_gift_for_cart_checkout()
    {
        global $woocommerce, $wpdb, $product;

        $this->item_cart = (!empty(WC()->cart)) ? WC()->cart->cart_contents : '';
        if (!$this->item_cart || !isset($this->item_cart) || !is_array($this->item_cart) || count($this->item_cart) <= 0) {
            return false;
        }
        $this->show_gift_item_for_cart = [];
        $cart_subtotal = it_get_cart_subtotal($this->item_cart);

        $rules = wgbl\classes\repositories\Rule::get_instance();
        $rules = $rules->get();

        $check_rules_condition = [];
        $filter_items_by_rules = [];
        $this->gift_item_variable = [];
        $this->gift_rule_exclude = [];
        //Check Conditions
        if (!isset($rules['items']) || !is_array($rules['items']) || count($rules['items']) <= 0) {
            return false;
        }

        foreach ($rules['items'] as $rule_key => $rule_values) {
            if ($rule_values['status'] == 'disable') {
                continue;
            }

            if (!empty($rule_values['condition']) && is_array($rule_values['condition'])) {
                $condition_methods = $this->get_condition_type_methods();
                $conditions = $rule_values['condition'];
                foreach ($conditions as $condition) {
                    if (!empty($condition['type']) && !empty($condition_methods[$condition['type']]) && method_exists($this, $condition_methods[$condition['type']])) {
                        if (!$this->{$condition_methods[$condition['type']]}($condition)) {
                            continue 2;
                        }
                    }
                }
            }

            $check_rules_condition[] = $rule_values;
            if (isset($rule_values['exclude_products'])) {
                $this->gift_rule_exclude[$rule_values['uid']] = $rule_values['exclude_products'];
            }
        }
        $this->gift_item_variable['rule_time'] = $rules['time'];

        foreach ($check_rules_condition as $rule_key => $rule_value) {
            $rules = Rule::get_instance();
            $return_query = $rules->get_option_cache($rule_value);

            $this->pw_gifts_cache_simple_variation = $return_query['pw_gifts_cache_simple_variation_'];
            $this->pw_gifts_cache_simple_childes = $return_query['pw_gifts_cache_simple_childes_'];

            if ($rule_value['method'] == 'simple') {
                $this->gift_item_variable[$rule_value['uid']] = array(
                    'uid' => $rule_value['uid'],
                    'method' => 'simple',
                    //'disable_if'             => @$rule_value['exclusivity'],
                    'based_on' => '',
                    'pw_number_gift_allowed' => $rule_value['quantity']['get'],
                    'can_several_gift' => $rule_value['quantity']['same_gift'],
                    'auto_add' => $rule_value['quantity']['auto_add_gift_to_cart'],
                    'gifts' => $this->pw_gifts_cache_simple_childes,
                );

                foreach ($this->pw_gifts_cache_simple_variation as $key => $gift) {
                    $id = "";
                    $id = $rule_value['uid'] . '-' . $gift;
                    $this->show_gift_item_for_cart[$id] = array(
                        "item" => $gift,
                        "uid" => $rule_value['uid'],
                        "key" => $id,
                        //"disable_if"             => @$rule_value['exclusivity'],
                        "pw_number_gift_allowed" => $rule_value['quantity']['get'],
                        "can_several_gift" => $rule_value['quantity']['same_gift'],
                        'method' => 'simple',
                        'auto' => $rule_value['quantity']['auto_add_gift_to_cart'],
                    );
                }
                foreach ($this->pw_gifts_cache_simple_childes as $gift) {
                    $id = "";
                    $id = $rule_value['uid'] . '-' . $gift;

                    $this->gift_item_variable['all_gifts'][$id] = array(
                        'uid' => $rule_value['uid'],
                        'id_product' => $gift,
                    );
                }
            } //simple
            else if ($rule_value['method'] == 'subtotal') {
                $value = $cart_subtotal['subtotal'];
                //				if ( $rule_value['quantity']['tax'] == 'include_tax' ) {
                //					$value = $cart_subtotal['subtotal_with_tax'];
                //				}
                $valid_subtotal_value = $value <= 0 || $value < $rule_value['quantity']['subtotal_amount'];
                WC()->session->set('pw_gifts_allowed_subtotal', $valid_subtotal_value);

                if ($valid_subtotal_value) {
                    continue;
                }
                $qty = 0;
                $qty = $rule_value['quantity']['get'];

                WC()->session->set('pw_number_gift_allowed', $qty);

                $this->gift_item_variable[$rule_value['uid']] = [
                    'uid' => $rule_value['uid'],
                    'method' => 'subtotal',
                    //'disable_if'             => @$rule_value['exclusivity'],
                    'based_on' => '',
                    'pw_number_gift_allowed' => $qty,
                    'can_several_gift' => $rule_value['quantity']['same_gift'],
                    'auto_add' => $rule_value['quantity']['auto_add_gift_to_cart'],
                    'gifts' => $this->pw_gifts_cache_simple_childes,
                ];

                foreach ($this->pw_gifts_cache_simple_variation as $key => $gift) {
                    $id = "";
                    $id = $rule_value['uid'] . '-' . $gift;
                    $this->show_gift_item_for_cart[$id] = array(
                        "item" => $gift,
                        "uid" => $rule_value['uid'],
                        "key" => $id,
                        //"disable_if"             => @$rule_value['exclusivity'],
                        "pw_number_gift_allowed" => $qty,
                        "can_several_gift" => $rule_value['quantity']['same_gift'],
                        'method' => 'subtotal',
                        'auto' => $rule_value['quantity']['auto_add_gift_to_cart'],
                    );
                }
                foreach ($this->pw_gifts_cache_simple_childes as $gift) {
                    $id = "";
                    $id = $rule_value['uid'] . '-' . $gift;

                    $this->gift_item_variable['all_gifts'][$id] = [
                        'uid' => $rule_value['uid'],
                        'id_product' => $gift,
                    ];
                }
            } //subtotal
        }

        if (is_array($this->gift_item_variable) && (count($this->gift_item_variable) > 0 || sizeof($this->gift_item_variable) > 0)) {
            return $this->gift_item_variable;
        }

        WC()->session->set('pw_gifts_items_to_cart', $this->show_gift_item_for_cart);

        return false;
    }


    public function date_time($condition)
    {
        switch ($condition['type']) {
            case 'date':
                $value = it_date_time();
                $condition_value = $condition['value'];
                $condition_value = get_datetime_object($condition_value, false);
                $condition_value->setTime(0, 0, 0);
                break;
            case 'time':
            case 'date_time':
                $value = it_date_time_time();
                $condition_value = $condition['value'];
                $condition_value = get_datetime_object($condition_value, false);

                break;

            case 'days_of_week':
                $value = it_date_time_weekend();
                $condition_value = $condition['value'];
                //				print_r( $condition_value );
                //				die;

                return check_simple_operations($condition['method_option'], $value, $condition_value);
                break;

            default:
                return true;
        }

        return check_datetime_operations($condition['method_option'], $value, $condition_value);
    }

    public function pw_insert_gift_cart($gift = null)
    {
        global $woocommerce;
        $retrieved_group_input_value = WC()->session->get('gift_group_order_data');

        $count_info = itg_check_quantity_gift_in_session();

        if (!empty($gift) && array_key_exists($gift, $this->gift_item_variable['all_gifts'])) {

            $uid = $this->gift_item_variable['all_gifts'][$gift]['uid'];

            //Number Allow For Simple Method
            $pw_number_gift_allowed = $this->gift_item_variable[$uid]['pw_number_gift_allowed'];

            //Number Allow For Other Method
            if (
                in_array($this->gift_item_variable[$uid]['method'], array(
                    'buy_x_get_x_repeat'
                ), true) && $this->gift_item_variable[$uid]['based_on'] != 'all'
            ) {
                $pw_number_gift_allowed = $this->gift_item_variable['all_gifts'][$gift]['q'];
            }

            if (
                array_key_exists($uid, $count_info['count_rule_gift'])
                &&
                $count_info['count_rule_gift'][$uid]['q'] >= $pw_number_gift_allowed
                &&
                (
                    !in_array($this->gift_item_variable[$uid]['method'], array('buy_x_get_x_repeat'))
                    ||
                    $this->gift_item_variable[$uid]['based_on'] == 'all'
                )
            ) {
                return false;
            } elseif (in_array($gift, $count_info['gifts_set']) && $this->gift_item_variable[$uid]['can_several_gift'] == 'no') {
                return false;
            } else if ($retrieved_group_input_value != '' && is_array($retrieved_group_input_value) && count($retrieved_group_input_value) > 0 && isset($retrieved_group_input_value[$gift]['q']) && $retrieved_group_input_value[$gift]['q'] >= $pw_number_gift_allowed) {
                return false;
            }

            $id_product = $this->gift_item_variable['all_gifts'][$gift]['id_product'];
            /**  Check Quantity  **/
            $flag_stock = 0;
            $product_get = wc_get_product($id_product);
            $get_stock_quantity = $product_get->get_stock_quantity();
            if (!$product_get->is_in_stock() && $get_stock_quantity <= 0) {
                $flag_stock = 1;
            } else if ($product_get->is_in_stock() && $get_stock_quantity >= 1) {
                $get_cart_item_stock_quantities = itg_get_cart_item_stock_quantities();
                $get_cart_item_quantities_gift_stock = itg_get_cart_item_quantities_gift_stock();
                $required_stock_in_cart = isset($get_cart_item_stock_quantities[$product_get->get_stock_managed_by_id()]) ? $get_cart_item_stock_quantities[$product_get->get_stock_managed_by_id()] : 0;
                $required_stock_in_cart_gift = isset($get_cart_item_quantities_gift_stock[$id_product]) ? $get_cart_item_quantities_gift_stock[$id_product] : 0;
                if (($get_stock_quantity - $required_stock_in_cart) - $required_stock_in_cart_gift <= 0) {
                    $flag_stock = 1;
                }
            }
            if ($flag_stock == 1) {
                return false;
            }
            /**  End Check Quantity  **/
            if ($count_info['count_gift'] > 0) {
                if (array_key_exists($uid, $count_info['count_rule_gift']) && isset($retrieved_group_input_value[$gift]['q'])) {
                    $q = isset($retrieved_group_input_value[$gift]['q']) ? $retrieved_group_input_value[$gift]['q'] + 1 : 1;

                    $retrieved_group_input_value[$gift] = array(
                        'id' => $gift,
                        'q' =>
                            $q,
                        'uid' => $uid,
                        'id_product' => $id_product,
                        'time_add' => $this->gift_item_variable['rule_time']
                    );
                    WC()->session->set('gift_group_order_data', $retrieved_group_input_value);

                    return true;
                } else {
                    $retrieved_group_input_value[$gift] = array(
                        'id' => $gift,
                        'q' => 1,
                        'uid' => $uid,
                        'id_product' => $id_product,
                        'time_add' => $this->gift_item_variable['rule_time']
                    );
                    WC()->session->set('gift_group_order_data', $retrieved_group_input_value);


                    return true;
                }
            } else {
                //				echo 'Z';
                //				die;
                $retrieved_group_input_value = array();
                $retrieved_group_input_value[$gift] = array(
                    'id' => $gift,
                    'q' => 1,
                    'uid' => $uid,
                    'id_product' => $id_product,
                    'time_add' =>
                        $this->gift_item_variable['rule_time']
                );

                WC()->session->set('gift_group_order_data', $retrieved_group_input_value);


                return true;
            }
        }

        return false;
    }
}