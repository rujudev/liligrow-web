<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
	<?php do_action('woocommerce_before_cart_table'); ?>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<tbody>
			<?php

			do_action('woocommerce_before_cart_contents');
			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
				$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
				/**
				 * Filter the product name.
				 *
				 * @since 2.1.0
				 * @param string $product_name Name of the product in the cart.
				 * @param array $cart_item The product in the cart.
				 * @param string $cart_item_key Key for the product in the cart.
				 */
				$product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

				if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
					$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
					?>
					<tr
						class="woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

						<td class="product-remove">
							<?php echo show_remove_cart_item_icon($cart_item_key, $_product, $product_id, $product_name); ?>
						</td>

						<td class="product-thumbnail">
							<?php
							$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);

							if (!$product_permalink) {
								echo $thumbnail; // PHPCS: XSS ok.
							} else {
								printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
							}
							?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e('Product', 'woocommerce'); ?>">
							<?php
							if (!$product_permalink) {
								echo wp_kses_post($product_name . '&nbsp;');
							} else {
								/**
								 * This filter is documented above.
								 *
								 * @since 2.1.0
								 */
								echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
							}

							do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

							// Meta data.
							echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.
					
							// Backorder notification.
							if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
								echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
							}
							?>
						</td>

						<td class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
							<div class="qty-container">
								<?php echo get_custom_product_quantity_input($_product, $cart_item, $cart_item_key, Type::Cart) ?>
							</div>
						</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
							<?php echo wc_price( get_item_subtotal(['product_id' => $_product->get_id(), 'quantity' => $cart_item['quantity']]) ); ?>
							<small class="tax_label"><?php echo WC()->countries->inc_tax_or_vat(); ?></small>
						</td>
					</tr>
					<?php
				}
			}
			?>

			<?php do_action('woocommerce_cart_contents'); ?>

			<tr>
				<td colspan="6" class="actions">
					<div id="actions-container">
						<?php if (wc_coupons_enabled()) { ?>
							<div class="coupon" style="justify-content: initial; gap: 5px;">
								<label for="coupon_code" class="screen-reader-text">
									<?php esc_html_e('Coupon:', 'woocommerce'); ?>
								</label>
								<input type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
									placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" />
								<button type="submit"
									class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
									name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>">
									<?php esc_html_e('Apply coupon', 'woocommerce'); ?>
								</button>
								<?php do_action('woocommerce_cart_coupon'); ?>
							</div>
						<?php } ?>
						<button type="submit"
							class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
							name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>">
							<?php esc_html_e('Update cart', 'woocommerce'); ?>
						</button>
					</div>


					<?php do_action('woocommerce_cart_actions'); ?>

					<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
				</td>
			</tr>

			<?php do_action('woocommerce_after_cart_contents'); ?>
			<?php // $nonce = wp_create_nonce("wc_store_api"); ?>
		</tbody>
	</table>

	<!-- <script id="custom-cart-script">
		const $=t=>document.querySelector(t),isValid="<?php // echo wp_verify_nonce($nonce, 'wc_store_api'); ?>";let submitCompleteNotice,widgetContainer;const headerRight=document.querySelector(".header-right"),couponInput=$(".woocommerce-cart-form .coupon #coupon_code");let updateCartButton=null,removedGiftId=null,isCartEmpty=!1,cartGiftsIds=null,totalCartGifts=0,quantityInputChanged=!1;function observeNode(t,e){if(!t)return;new MutationObserver((e=>{for(const o of e)if(console.log(o),"attributes"===o.type&&"disabled"===o.attributeName&&t===updateCartButton&&updateCartButton.removeAttribute("disabled"),"childList"===o.type){t===submitCompleteNotice&&(updateCartButton=$("button[name=update_cart]"),updateCartButton.removeAttribute("disabled"),quantityInputChanged=!1,loopCartItems(),checkGiftsSection());const e=t.querySelector(".woocommerce"),o=t.querySelector(".return-to-shop");if(o){e.setAttribute("style","display: flex; flex-direction: column;"),e.querySelector(".wc-empty-cart-message").remove();o.querySelector("a.button.wc-backward").setAttribute("href","https://www.liligrow.es")}}})).observe(t,e)}function checkGiftsSection(){const t=$(".adv-gift-section");if(t){const e=t.querySelectorAll(".owl-stage .owl-item"),o=Array.from(e);for(const t of o){const e=t.querySelector(".wgb-product-item-cnt"),o=t.querySelector(".wgb-add-gift-btn").getAttribute("data-id");cartGiftsIds.has(o)||e.classList.replace("disable-hover","hovering")}unblock(t)}}async function updateItemCart(t){const e=t.getAttribute("value"),o=t.getAttribute("name").match(/\[(.*?)\]/)[1];if(isValid)try{const t=await fetch(`<?php // echo esc_url(get_home_url()); ?>/wp-json/wc/store/v1/cart/update-item?key=${o}&quantity=${e}`,{method:"POST",headers:{"Content-Type":"application/json",Nonce:"<?php // echo esc_attr($nonce); ?>"}});200===t.status&&t.ok&&(window.location="<?php // echo get_permalink(); ?>")}catch(t){console.log(t)}}function block(t){t&&jQuery(t).addClass("loading").block({message:null,overlayCSS:{background:"#fff",opacity:.6}})}function unblock(t){t&&jQuery(t).removeClass("loading").unblock()}function updateQuantity(t,e){const o=$(".adv-gift-section"),c=$(".woocommerce .shop_table.cart"),n=$(".woocommerce .cart-collaterals");block(c),block(n),o&&block(o),t.setAttribute("value",e),quantityInputChanged=!0,updateItemCart(t)}function decrement(t,e){const o=parseInt(e.value);o>1&&!quantityInputChanged&&updateQuantity(e,o-1)}function increment(t,e){const o=parseInt(e.value);quantityInputChanged||updateQuantity(e,o+1)}function checkStock(t,e){const o=t.querySelector('input[type="number"]'),c=parseInt(o.getAttribute("value")),n=parseInt(o.getAttribute("max"));if(Boolean(n)){const o=t.querySelector(".qty-container");let r=o.querySelector(".stock-limit");r&&o.removeChild(r),c===n?(e.setAttribute("disabled","disabled"),e.classList.add("disabled"),r=document.createElement("span"),r.classList.add("stock-limit"),r.textContent='<?php // esc_html_e("Stock limit reached", "woocommerce"); ?>',o.appendChild(r)):n>c&&(e.removeAttribute("disabled"),e.classList.remove("disabled"))}}function loopCartItems(){cartGiftsIds=new Set;document.querySelectorAll(".woocommerce-cart-form__cart-item").forEach((t=>{const e=t.querySelector(".qty-container"),o=t.querySelector(".remove");if(t.querySelectorAll(".remove.gift-close-link").forEach((t=>{const e=t.getAttribute("data-id");cartGiftsIds.add(e)})),e){const o=e.querySelector("input[type=number]"),c=t.querySelector("button.decrement"),n=t.querySelector("button.increment");c.addEventListener("click",(()=>{decrement(this,o)})),n.addEventListener("click",(()=>{increment(this,o)})),checkStock(t,n)}o.addEventListener("click",(()=>{if(o.classList.contains("gift-close-link")){const t=$(".adv-gift-section");totalCartGifts=cartGiftsIds.length,removedGiftId=o.getAttribute("data-id"),block(t)}}))}))}couponInput.classList.add("woocommerce-Input","woocommerce-Input--text"),document.addEventListener("DOMContentLoaded",(function(){submitCompleteNotice=$(".woocommerce .woocommerce-notices-wrapper"),widgetContainer=$(".elementor-widget-container"),updateCartButton=$("button[name=update_cart]"),observeNode(updateCartButton,{attributes:!0,attributeFilter:["disabled"]}),observeNode(submitCompleteNotice,{childList:!0}),observeNode(widgetContainer,{childList:!0}),loopCartItems()}));
	</script> -->
</form>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<div class="cart-collaterals">
	<?php
	/**
	 * Cart collaterals hook.
	 *
	 * @hooked woocommerce_cross_sell_display
	 * @hooked woocommerce_cart_totals - 10
	 */
	do_action('woocommerce_cart_collaterals');
	?>
</div>

<?php do_action('woocommerce_after_cart_table'); ?>

<?php do_action('custom_woocommerce_cross_sell'); ?>

<?php do_action('woocommerce_after_cart'); ?>