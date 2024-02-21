<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

global $product;

if (!$product->is_purchasable()) {
    return;
}

$low_stock_amount = get_post_meta($product->get_id(), '_low_stock_amount', true);

if ($product->is_in_stock() && $product->get_stock_quantity() <= $low_stock_amount) {
    echo wc_get_stock_html($product); // WPCS: XSS ok.
}

if ($product->is_in_stock()): ?>

    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <form class="cart"
        action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
        method="post" enctype='multipart/form-data'>
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <div id="product-qty-container">
            <?php
            echo get_custom_product_quantity_input($product, null, null, Type::Product);

            do_action('woocommerce_after_add_to_cart_quantity');
            ?>
        </div>

        <a id="add-to-cart" data-quantity="1" data-product_id="<?php echo $product->get_id(); ?>" class="single_add_to_cart_button add_to_cart_button ajax_add_to_cart button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>">
            <span><?php echo esc_html($product->single_add_to_cart_text()); ?></span>
        </a>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded",(function(){"<?php echo $product->get_permalink(); ?>"!==window.location.href&&(window.location="<?php echo $product->get_permalink(); ?>");const t=this.querySelector("#product-qty-container"),e=this.querySelector("a#add-to-cart"),u=t.querySelector("button.decrement"),n=t.querySelector("button.increment"),r=t.querySelector("input[type=number]");let a=0;u.addEventListener("click",(t=>{t.preventDefault(),a=parseInt(r.getAttribute("value")),a>1&&(r.setAttribute("value",a-1),r.value=a-1,e.setAttribute("data-quantity",`${r.getAttribute("value")}`))})),n.addEventListener("click",(t=>{t.preventDefault(),a=parseInt(r.getAttribute("value")),r.setAttribute("value",a+1),r.value=a+1,e.setAttribute("data-quantity",`${r.getAttribute("value")}`)})),r.addEventListener("input",(function(t){t.preventDefault();const u=parseInt(t.target.value),n=parseInt(this.getAttribute("max"));u>n?(this.setAttribute("value",n),this.value=n):(this.setAttribute("value",u),this.value=u),e.setAttribute("data-quantity",`${r.getAttribute("value")}`)})),r.addEventListener("keydown",(function(t){13===t.keyCode&&(t.preventDefault(),e.click())}))}));
    </script>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

<?php endif; ?>
