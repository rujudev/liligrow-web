<?php
/**
 * Single variation cart button
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button" style="display: none;">
    <?php do_action('woocommerce_before_add_to_cart_button'); ?>

    <?php do_action('woocommerce_before_add_to_cart_quantity'); ?>

    <div id="product-qty-container">
        <?php
        echo get_custom_product_quantity_input($product, null, null, Type::Product);

        do_action('woocommerce_after_add_to_cart_quantity');
        ?>
    </div>
    
    <button type="button"
        class="single_add_to_cart_button button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
        <span>
            <?php echo esc_html($product->single_add_to_cart_text()); ?>
        </span>
    </button>

    <?php do_action('woocommerce_after_add_to_cart_button'); ?>

    <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
    <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
    <input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
