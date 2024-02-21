<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 6.1.0
 */

defined('ABSPATH') || exit;

global $product;

$attribute_keys = array_keys($attributes);
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form'); ?>

<form class="variations_form cart"
    action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
    method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>"
    data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($available_variations) && false !== $available_variations): ?>
        <p class="stock out-of-stock">
            <?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?>
        </p>
    <?php else: ?>
        <div class="variations">
            <?php foreach ($attributes as $attribute_name => $options): ?>
                <span>
                    <?php echo wc_attribute_label($attribute_name); ?>
                </span>
                <?php
                wc_dropdown_variation_attribute_options(
                    array(
                        'options' => $options,
                        'attribute' => $attribute_name,
                        'product' => $product,
                    )
                );
                echo end($attribute_keys) === $attribute_name ? wp_kses_post(apply_filters('woocommerce_reset_variations_link', '<a class="reset_variations hide" href="#">' . esc_html__('Clear', 'woocommerce') . '</a>')) : '';
                ?>
            <?php endforeach; ?>
        </div>
        <?php do_action('woocommerce_after_variations_table'); ?>

        <div class="single_variation_wrap">
            <?php
            /**
             * Hook: woocommerce_before_single_variation.
             */
            do_action('woocommerce_before_single_variation');

            /**
             * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
             *
             * @since 2.4.0
             * @hooked woocommerce_single_variation - 10 Empty div for variation data.
             * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
             */
            do_action('woocommerce_single_variation');

            /**
             * Hook: woocommerce_after_single_variation.
             */
            do_action('woocommerce_after_single_variation');
            ?>
        </div>
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>

<script>
    const variationsForm = document.querySelector('.variations_form.cart');
    const priceContainer = document.querySelector('.summary.entry-summary .price');
    let variation = document.querySelector('.woocommerce-variation.single_variation');
    let price = variation.querySelector('.price');
    
    const selectFormat = variationsForm.querySelector('select');
    const resetVariations = variationsForm.querySelector('.reset_variations');

    let product = JSON.parse(JSON.stringify(<?php echo $product; ?>));
    const initPrice = `<?php echo apply_filters( 'woocommerce_variable_price_html' , '', $product); ?>`;
    const base64Credentials = btoa(`<?php echo CK_WOO; ?>:<?php echo CS_WOO; ?>`);

    async function getProductVariation(productId, variationId) {
        return await fetch(`https://liligrow.es/wp-json/wc/v3/products/${productId}/variations/${variationId}`, { 
            method: 'GET', 
            headers: { 
                'Authorization': `Basic ${base64Credentials}`, 
                'Content-Type': 'application/json' 
            } 
        });
    }

    function observeNode(node, options) {
        if (!node) return;
        
        const observer = new MutationObserver((mutationList) => {
            for (const mutation of mutationList) {
                if (mutation.type === 'childList') {
                    if (node === selectFormat) {
                        const value = mutation.target.value;

                        if (value === '') {
                            priceContainer.innerHTML = initPrice;
                        } else {
                            variation = document.querySelector('.woocommerce-variation.single_variation');
                            price = variation.querySelector('.price');

                            const input = document.querySelector('#product-qty-container input[type=number]');
                            
                            priceContainer.innerHTML = price.innerHTML;
                        }
                    }
                }
            }
        });

        observer.observe(node, options);
    }

    document.addEventListener('DOMContentLoaded', async function() {
        const productWasAdded = JSON.parse(localStorage.getItem('added_variable_product'));

        if (productWasAdded) {
            const { id: productId, addedItemQuantity, existingItemQuantity, name: nameAdded, variationId } = productWasAdded;
            
            const variationProductAdded = await fetch(`https://liligrow.es/wp-json/wc/v3/products/${productId}/variations/${variationId}`, { 
                method: 'GET', 
                headers: { 
                    'Authorization': `Basic ${base64Credentials}`, 
                    'Content-Type': 'application/json' 
                } 
            });

            const { attributes, stock_quantity: stockQuantity } = await variationProductAdded.json();
            const { name: attributeName, option: attributeOption } = attributes[0];

            let toastConfig = {
                icon: 'success',
                title: addedItemQuantity > 1 ? `${addedItemQuantity}x uds de ${nameAdded} - ${attributeOption} añadidas al carrito` : `${nameAdded} - ${attributeOption} se ha añadido al carrito`
            }

            if (addedItemQuantity + existingItemQuantity > stockQuantity) {
                toastConfig = {
                    icon: 'error',
                    title: `Máximo de stock alcanzado. Máximo: ${stockQuantity} - Añadido: ${addedItemQuantity + existingItemQuantity}`,
                    showConfirmButton: false
                }
            }

            const { isConfirmed } = await Toast.fire(toastConfig)
            
            if (isConfirmed){
                window.location = "https://liligrow.es/cart";
            };

            localStorage.removeItem('added_variable_product');
        }
        resetVariations.classList.add('hide');

        const productForm = document.querySelector('.variations_form.cart');
        const productQtyContainer = this.querySelector('#product-qty-container');
        const addToCartButton = this.querySelector('.single_add_to_cart_button');

        const decrementButton = productQtyContainer.querySelector('button.decrement');
        const incrementButton = productQtyContainer.querySelector('button.increment');
        const quantityInput = productQtyContainer.querySelector('input[type=number]');
        quantityInput.setAttribute('name', 'quantity');
        let quantity = 1;
        let url = `?add-to-cart=<?php echo $product->get_id() ?>`;
        let variationId = 0;

        decrementButton.addEventListener('click', (e) => {
            e.preventDefault();
            quantity = parseInt(quantityInput.getAttribute('value'));

            if (quantity > 1) {
                quantityInput.setAttribute('value', quantity - 1);
                quantityInput.value = quantity - 1;
            }
        });

        incrementButton.addEventListener('click', (e) => {
            e.preventDefault();
            quantity = parseInt(quantityInput.getAttribute('value'));

            quantityInput.setAttribute('value', quantity + 1);
            quantityInput.value = quantity + 1;
        });

        quantityInput.addEventListener('input', function(e) {
            e.preventDefault();

            const newQuantity = parseInt(e.target.value);
            const max = parseInt(this.getAttribute('max'));

            if ( newQuantity > max ) {
                this.setAttribute('value', max)
                this.value = max;
            } else {
                this.setAttribute('value', newQuantity);
                this.value = newQuantity;
            }
        });

        quantityInput.addEventListener('keydown', function(e) {
            const key = e.keyCode;

            if (key === 13) {
                e.preventDefault();

                addToCartButton.click();
            }
        });
        
        addToCartButton.addEventListener('click', async function(e) {
            quantity = parseInt(quantityInput.getAttribute('value'));
            variationId = parseInt(document.querySelector('input[name=variation_id]').getAttribute('value'));
            const url = `?add-to-cart=<?php echo $product->get_id() ?>&variation_id=${variationId}&quantity=${quantity}`;
            
            jQuery(addToCartButton).addClass('loading').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            const result = await fetch(url, { method: 'GET' });
            
            if (result.ok && result.status === 200) {
                jQuery(addToCartButton).removeClass('loading').unblock();

                const { cart_contents: cartContents } = JSON.parse(`<?php echo wp_json_encode(WC()->cart); ?>`)
                const variationProduct = Object.values(cartContents).find((item) => item.variation_id === variationId);

                product = {
                    ...product,
                    addedItemQuantity: quantity,
                    existingItemQuantity: variationProduct ? variationProduct.quantity : 0,
                    variationId
                }

                localStorage.setItem('added_variable_product', JSON.stringify(product));

                window.location = "<?php echo $product->get_permalink(); ?>";
            }
        })

        quantityInput.addEventListener('keydown', async function(e) {
            const key = e.keyCode;
            // añadir el efecto de loading de jQuery

            if (key === 13) {
                e.preventDefault();
                
                quantity = parseInt(quantityInput.getAttribute('value'));
                variationId = parseInt(document.querySelector('input[name=variation_id]').getAttribute('value'));

                jQuery(addToCartButton).addClass('loading').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                const result = await fetch(url, { method: 'GET' });
            
                if (result.ok && result.status === 200) {
                    jQuery(addToCartButton).removeClass('loading').unblock();

                    window.location = "<?php echo $product->get_permalink(); ?>";
                }
            }
        });

        productForm.addEventListener('submit', function(e) {
            e.preventDefault();
        })

        selectFormat.addEventListener('change', function(e) {
            const value = parseInt(e.target.value);

            value === '' 
                ? woocommerceVariationAddToCart.setAttribute('style', 'display: none;')
                : woocommerceVariationAddToCart.removeAttribute('style')
        })

        const woocommerceVariationAddToCart = variationsForm.querySelector('.woocommerce-variation-add-to-cart');

        if (selectFormat.value !== '') {
            woocommerceVariationAddToCart.removeAttribute('style');
        }

        observeNode(selectFormat, { attributes: true, childList: true, subtree: true });
    })

    resetVariations.addEventListener('click', function (e) {
        this.classList.toggle('hide');
    })
</script>