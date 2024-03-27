document.querySelectorAll('.woocommerce-mini-cart-item').forEach(item => {
    let decrementButton = item.querySelector('button.decrement');
    let incrementButton = item.querySelector('button.increment');
    let removeButton = item.querySelector('.remove');

    let quantityContainer = item.querySelector('.qty-container');
    let quantityResult = quantityContainer.querySelector('.qty-result');
    let quantityInput = item.querySelector('input[type=number]');
    let quantityInputContainer = item.querySelector('.quantity-container');

    let totalCartItemsSubtotal = 0;
    let totalCartItemsCount = 0;

    const itemKeyString = quantityInput.getAttribute('name');
    const itemKey = itemKeyString.match(/\[([^\]]+)\]/g).map(stringValue => stringValue.slice(1, -1))[0];

    
    const headerRight = document.querySelector('.header-right');
    const siteHeaderCart = headerRight.querySelector('.site-header-cart');
    let responseUpdateItem = null;
    let stockLimit = document.createElement('span');

    function checkStock() {
        const value = parseInt(quantityInput.getAttribute('value'));
        const max = parseInt(quantityInput.getAttribute('max'));


        if (Boolean(max)) {
            if (value >= max) {
                if (stockLimit) {
                    stockLimit.remove();
                }

                stockLimit = document.createElement('span')
                stockLimit.classList.add('stock-limit');
                stockLimit.textContent = stockLimitText;
                
                quantityContainer.appendChild(stockLimit);
                    
                incrementButton.setAttribute('disabled', 'disabled');
                incrementButton.classList.add('disabled');
            } else if (value < max) {
                if (stockLimit) stockLimit.remove();
                incrementButton.removeAttribute('disabled');
                incrementButton.classList.remove('disabled');	
            }
        }
    }

    async function updateItemCart() {
        const quantity = quantityInput.getAttribute('value');

        if (isValid) {
            const result = await fetch(`https://www.liligrow.es/wp-json/wc/store/v1/cart/update-item?key=${itemKey}&quantity=${quantity}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Nonce': nonce
                },
            });

            if (result.status === 200 && result.ok) {
                jQuery(siteHeaderCart).removeClass('loading').unblock();
                
                const miniCartContentCount = headerRight.querySelector('.mini-cart-contents .count');
                const miniCartContentPrice = headerRight.querySelector('.mini-cart-contents .amount-cart bdi');
                const headerRightMiniCartQtyCount = headerRight.querySelector('.top-header-cart #mini-cart-products-qty');
                const headerRightMiniCartSubTotal = headerRight.querySelector('.woocommerce-mini-cart__total.total .woocommerce-Price-amount.amount bdi');

                let splitProductPrice = quantityInput.getAttribute('data-product_price').split('.');
                splitProductPrice[1] = splitProductPrice[1].slice(0, 2);
                const itemPrice = parseFloat(parseFloat(splitProductPrice.join('.')).toFixed(2));

                const updatedItemPrice = itemPrice * parseInt(quantity);
                const itemPriceHTML = quantityResult.querySelector('.qty-price .woocommerce-Price-amount.amount bdi')
                const newItemPrice = parseFloat(updatedItemPrice).toFixed(2).replace('.', ',').split(',');

                const newItemIntegerPart = newItemPrice[0];
                const newItemIntegerPartHTML = `<span class="integer">${newItemIntegerPart}</span>`;
                const newItemDecimalPart = newItemPrice[1].substring(0, 2);
                const newItemDecimalPartHTML = `<span class="decimal">${newItemDecimalPart}</span>`;
                
                itemPriceHTML.innerHTML = `${newItemIntegerPartHTML},${newItemDecimalPartHTML}€`;
                
                const itemsPrice = Array.from(document.querySelectorAll('.woocommerce-mini-cart-item')).map((item) => {
                    return item.querySelector('.qty-price .woocommerce-Price-amount.amount bdi').textContent.split('€').join('').replace(',', '.');
                }, 0);

                totalCartItemsSubtotal = itemsPrice.reduce((total, item) => parseFloat(item) + total, 0);

                const cartQtyItems = Array.from(document.querySelectorAll('input[type=number]')).map((item) => {
                    return item.value;
                })

                totalCartItemsCount = cartQtyItems.reduce((total, item) => parseInt(item) + total, 0);

                const newSubtotalPrice = parseFloat(totalCartItemsSubtotal).toFixed(2).replace('.', ',').split(',');
                const newSubtotalIntegerPart = newSubtotalPrice[0];
                const newSubtotalIntegerPartHTML = `<span class="integer">${newSubtotalIntegerPart}</span>`;
                const newSubtotalDecimalPart = newSubtotalPrice[1].substring(0, 2);
                const newSubtotalDecimalPartHTML = `<span class="decimal">${newSubtotalDecimalPart}</span>`;
                
                miniCartContentCount.innerText = totalCartItemsCount;
                miniCartContentPrice.innerText = `${newSubtotalIntegerPart},${newSubtotalDecimalPart}€`;
                headerRightMiniCartQtyCount.innerText = totalCartItemsCount;
                headerRightMiniCartSubTotal.innerHTML = `${newSubtotalIntegerPartHTML},${newSubtotalDecimalPartHTML}€`;

                checkStock();
            }
        }
    }

    decrementButton.addEventListener('click', (e) => {
        e.preventDefault();
        const quantity = parseInt(quantityInput.getAttribute('value'));

        if (quantity > 1) {
            jQuery(siteHeaderCart).addClass('loading').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            quantityInput.setAttribute('value', quantity - 1);
            quantityInput.value = quantity - 1;
            updateItemCart();
        }
    });

    incrementButton.addEventListener('click', (e) => {
        e.preventDefault();
        const quantity = parseInt(quantityInput.getAttribute('value'));

        jQuery(siteHeaderCart).addClass('loading').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        quantityInput.setAttribute('value', quantity + 1);
        quantityInput.value = quantity + 1;
        updateItemCart();
    });

    removeButton.addEventListener('click', function(e) {
        e.preventDefault();
        const shoppingCart = headerRight.querySelector('.widget_shopping_cart');

        const shoppingCartObserver = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                const element = mutation.target;

                const cartQty = document.querySelector('.mini-cart-contents .count');
                const cartQtyValue = parseInt(cartQty.innerText);
                const cartSubtotal = document.querySelector('.mini-cart-contents .amount-cart bdi');
                const cartSubtotalValue = parseFloat(cartSubtotal.innerText.split('€')[0].replace(',', '.'));
                const itemSubtotal = item.querySelector('.amount bdi');
                const itemSubtotalValue = parseFloat(itemSubtotal.innerText.split('€')[0].replace(',', '.'));

                const miniCartQty = headerRight.querySelector('.top-header-cart #mini-cart-products-qty');
                const miniCartQtyValue = parseInt(miniCartQty.innerText);
                const quantity = parseInt(quantityInput.getAttribute('value'));

                const miniCartSubtotal = headerRight.querySelector('.woocommerce-mini-cart__total.total .woocommerce-Price-amount.amount bdi');

                const miniCartSubTotalText = miniCartSubtotal?.textContent ?? '0,00€';
                const newCartTotalPrice = miniCartSubTotalText.replace('.', ',');
                const formattedNewCartTotalPrice = newCartTotalPrice.split(',');
                const newCartTotalPriceIntegerPart = formattedNewCartTotalPrice[0];
                const newCartTotalPriceIntegerPartHTML = `<span class="integer">${newCartTotalPriceIntegerPart}</span>`;
                const newCartTotalPriceDecimalPart = formattedNewCartTotalPrice[1].substring(0, 2);
                const newCartTotalPriceDecimalPartHTML = `<span class="decimal">${newCartTotalPriceDecimalPart}</span>`;

                cartQty.innerText = cartQtyValue - quantity;
                cartSubtotal.innerHTML = `${newCartTotalPriceIntegerPartHTML},${newCartTotalPriceDecimalPartHTML}€`;
                miniCartQty.innerText = miniCartQtyValue - quantity;

                shoppingCartObserver.disconnect();
            }
        });

        shoppingCartObserver.observe(shoppingCart, {
            childList: true
        });
    });

    quantityInput.addEventListener('keydown', function(e) {
        const key = e.keyCode;
        const newQuantity = parseInt(e.target.value);
        const max = parseInt(this.getAttribute('max'));


        if (key === 13) {
            e.preventDefault();

            if (newQuantity === NaN) return;

            jQuery(siteHeaderCart).addClass('loading').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            if ( newQuantity > max ) {
                this.setAttribute('value', max)
                this.value = max;
            } else {
                this.setAttribute('value', newQuantity);
                this.value = newQuantity;
            }
            
            updateItemCart();
        }
    });

    checkStock();
});