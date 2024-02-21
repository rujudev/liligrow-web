const $ = (selector) => document.querySelector(selector);
const isValid = `<?php echo wp_verify_nonce($nonce, 'wc_store_api'); ?>`;
let submitCompleteNotice, widgetContainer;

const headerRight = document.querySelector('.header-right');
const couponInput = $('.woocommerce-cart-form .coupon #coupon_code');
let updateCartButton = null;
let removedGiftId = null;
let isCartEmpty = false;

let cartGiftsIds = null;
let totalCartGifts = 0;
let quantityInputChanged = false;

function observeNode(node, options) {
    if (!node) return;
    
    const observer = new MutationObserver((mutationList) => {
        for (const mutation of mutationList) {
            console.log(mutation);
            if (mutation.type === 'attributes') {
                if (mutation.attributeName === 'disabled') {
                    if (node === updateCartButton) {
                        updateCartButton.removeAttribute('disabled');
                    }
                }
            }

            if (mutation.type === 'childList') {
                if (node === submitCompleteNotice) {				
                    updateCartButton = $('button[name=update_cart]');
                    updateCartButton.removeAttribute('disabled');
                    quantityInputChanged = false;

                    loopCartItems();
                    checkGiftsSection();
                }

                const woocommerce = node.querySelector('.woocommerce');
                const returnToHome = node.querySelector('.return-to-shop');
                
                if (returnToHome) {
                    woocommerce.setAttribute('style', 'display: flex; flex-direction: column;');
                    woocommerce.querySelector('.wc-empty-cart-message').remove();
                    const link = returnToHome.querySelector('a.button.wc-backward');
                    link.setAttribute('href', 'https://www.liligrow.es');
                }
            }
            
        }
    });

    observer.observe(node, options);
}

function checkGiftsSection() {
    const advGiftSection = $('.adv-gift-section');

    if (advGiftSection) {
        const gifts = advGiftSection.querySelectorAll('.owl-stage .owl-item');
        const giftsArray = Array.from(gifts);

        for (const gift of giftsArray) {
            const giftContent = gift.querySelector('.wgb-product-item-cnt');
            const giftId = gift.querySelector('.wgb-add-gift-btn').getAttribute('data-id');

            if (!cartGiftsIds.has(giftId)) {
                giftContent.classList.replace('disable-hover', 'hovering');
            }
        }

        unblock(advGiftSection);
    }
}

async function updateItemCart(qtyInput) {
    const quantity = qtyInput.getAttribute('value');
    const itemKey = qtyInput.getAttribute('name').match(/\[(.*?)\]/)[1];

    if (isValid) {
        try {
            const result = await fetch(`<?php echo esc_url(get_home_url()); ?>/wp-json/wc/store/v1/cart/update-item?key=${itemKey}&quantity=${quantity}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Nonce': `<?php echo esc_attr($nonce); ?>`
            }});

            if (result.status === 200 && result.ok) {
                window.location = `<?php echo get_permalink(); ?>`;
            }
        } catch (error) {
            console.log(error);
        }
    }
}

function block(selector) {
    if (!selector) return;

    jQuery(selector).addClass('loading').block({
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    });
}

function unblock(selector) {
    if (!selector) return;

    jQuery(selector).removeClass('loading').unblock();
}

function updateQuantity(input, newQuantity) {
    const advGiftSection = $('.adv-gift-section');
    const woocommerceCartTable = $('.woocommerce .shop_table.cart');
    const cartCollaterals = $('.woocommerce .cart-collaterals');

    block(woocommerceCartTable);
    block(cartCollaterals);

    if (advGiftSection) {
        block(advGiftSection);
    }

    input.setAttribute('value', newQuantity);

    quantityInputChanged = true;
    updateItemCart(input);
}

function decrement(button, input) {
    const quantity = parseInt(input.value);

    if (quantity > 1 && !quantityInputChanged) {
        updateQuantity(input, quantity - 1);
    }
}

function increment(button, input) {
    const quantity = parseInt(input.value);

    if (!quantityInputChanged) {
        updateQuantity(input, quantity + 1);
    }
}

function checkStock(item, incrementButton) {
    const qtyInput = item.querySelector('input[type="number"]');
    const value = parseInt(qtyInput.getAttribute('value'));
    const max = parseInt(qtyInput.getAttribute('max'));

    if (Boolean(max)) {
        const quantityContainer = item.querySelector('.qty-container');
        let stockLimit = quantityContainer.querySelector('.stock-limit');

        if (stockLimit) {
            quantityContainer.removeChild(stockLimit);
        }
        
        if (value === max) {
            incrementButton.setAttribute('disabled', 'disabled');
            incrementButton.classList.add('disabled');

            stockLimit = document.createElement('span');
            stockLimit.classList.add('stock-limit');
            stockLimit.textContent = `<?php esc_html_e("Stock limit reached", "woocommerce"); ?>`;

            quantityContainer.appendChild(stockLimit);

        } else if (max > value) {
            incrementButton.removeAttribute('disabled');
            incrementButton.classList.remove('disabled');
        }
    }
}

function loopCartItems() {
    cartGiftsIds = new Set();
    const items = document.querySelectorAll('.woocommerce-cart-form__cart-item');

    items.forEach(item => {
        const qtyContainer = item.querySelector('.qty-container');
        const removeButton = item.querySelector('.remove');
        const giftsRemoveButton = item.querySelectorAll('.remove.gift-close-link');

        giftsRemoveButton.forEach((button) => {
            const id = button.getAttribute('data-id');

            cartGiftsIds.add(id);
        });

        if (qtyContainer) {
            const input = qtyContainer.querySelector('input[type=number]');

            const decrementButton = item.querySelector('button.decrement');
            const incrementButton = item.querySelector('button.increment');

            decrementButton.addEventListener('click', () => {
                decrement(this, input);
            });

            incrementButton.addEventListener('click', () => {
                increment(this, input);
            });

            checkStock(item, incrementButton);
        }

        removeButton.addEventListener('click', () => {
            if (removeButton.classList.contains('gift-close-link')) {
                const advGiftSection = $('.adv-gift-section');

                totalCartGifts = cartGiftsIds.length;
                removedGiftId = removeButton.getAttribute('data-id');

                block(advGiftSection);
            } 
        })
    });
}

couponInput.classList.add('woocommerce-Input', 'woocommerce-Input--text');

document.addEventListener('DOMContentLoaded', function () {
    submitCompleteNotice = $('.woocommerce .woocommerce-notices-wrapper');
    widgetContainer = $('.elementor-widget-container');
    updateCartButton = $('button[name=update_cart]');

    observeNode(updateCartButton, { attributes: true, attributeFilter: ['disabled'] });
    observeNode(submitCompleteNotice, { childList: true });
    observeNode(widgetContainer, { childList: true });

    loopCartItems();
});