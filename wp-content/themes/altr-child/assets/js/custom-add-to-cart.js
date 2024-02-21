let addToCartButtons = null;
let miniCartQty = null;
let miniCartSubtotal = null;
let miniCartTitleQty = null;
let miniCartTitleQtyValue = null;
let decrementButton = null;
let incrementButton = null;
let quantityInput = null;

const Toast = Swal.mixin({
    toast: true,
    position: 'bottom-right',
    iconColor: 'white',
    customClass: {
        popup: 'colored-toast'
    },
    showConfirmButton: true,
    timer: 3500,
    timerProgressBar: true,
    confirmButtonText: 'Ver carrito',
    confirmButtonColor: '#197900',
});
const addToCartButtonObserver = new MutationObserver(async (mutations) => {
    for (const mutation of mutations) {
        if (mutation.type === 'attributes') {
            const button = mutation.target;
            const itemName = button.getAttribute('aria-label');
            let name = '';

            if (itemName) {
                name = itemName.split('“')[1].split('”')[0];
            }

            // Dar una vuelta para usar el quantity input en la página de producto simple y variable

            if (button.classList.contains('added')) {
                const productId = button.dataset['product_id'];

                //document.querySelector('.added_to_cart.wc-forward')?.remove();

                const result = await fetch(`https://liligrow.es/wp-json/wc/store/v1/products/${productId}`, {
                    method: 'GET'
                })

                if (result.status === 200 && result.ok) {
                    const headerRight = document.querySelector('.header-right');
                    const topHeaderMiniCartQtyElement = headerRight.querySelector('.top-header-cart #mini-cart-products-qty');
                    const topHeaderMiniCartQtyTotal = parseInt(topHeaderMiniCartQtyElement.textContent);
                    const miniCartContentCountElement = headerRight.querySelector('.mini-cart-contents .count');
                    const miniCartContentCountTotal = parseInt(miniCartContentCountElement.textContent);
                    const miniCartContentAmountElement = headerRight.querySelector('.mini-cart-contents .amount-cart bdi');
                    const miniCartContentAmountPrice = parseFloat(parseFloat(miniCartContentAmountElement.textContent.split('€').join('').replace(',', '.')).toFixed(2));
                    const buttonParentNodeBdi = button.parentNode.querySelector('.price >:not(del) bdi');
                    let productSalePrice = null;
                    let newQuantity = 1;

                    if (buttonParentNodeBdi) {
                        productSalePrice = parseFloat(buttonParentNodeBdi.textContent.split('€')[0].split(',').join('.'));
                    } else {
                        const currentPage = window.location.pathname;

                        if (currentPage.includes('producto')) {
                            quantityInput = document.querySelector('#product-qty-container input[type=number]');
                            newQuantity = parseInt(quantityInput.getAttribute('value'));
                            const productPrice = parseFloat(quantityInput.getAttribute('data-product_price')).toFixed(2);
                            productSalePrice = parseFloat(parseFloat(productPrice) * newQuantity).toFixed(2);

                            console.log({ newQuantity, productPrice, productSalePrice, miniCartContentAmountPrice });
                        }
                    }
                    
                    topHeaderMiniCartQtyElement.innerText = topHeaderMiniCartQtyTotal + newQuantity;
                    miniCartContentCountElement.innerText = miniCartContentCountTotal + newQuantity;
                    miniCartContentAmountElement.innerText = `${parseFloat(parseFloat(productSalePrice) + miniCartContentAmountPrice).toFixed(2).replace('.', ',')}€`;
                    quantityInput.setAttribute('value', 1);
                    quantityInput.value = 1;

                    const { isConfirmed } = await Toast.fire({
                        icon: 'success',
                        title: `El producto ${name} ha sido añadido a tu carrito`
                    })
                    
                    if (isConfirmed){
                        window.location = "https://liligrow.es/cart";
                    };
                }

                addToCartButtonObserver.disconnect();
                break;
            }
        }
    }
    
    loopButtons();
});

function loopButtons() {
    addToCartButtons = document.querySelectorAll('a[class*=ajax_add_to_cart]');
    miniCartQty = document.querySelector('.mini-cart-contents .count');
    miniCartSubtotal = document.querySelector('.mini-cart-contents .amount-cart bdi');
    miniCartTitleQty = document.querySelector('.header-right #mini-cart-products-qty');
    quantityInput = document.querySelector('input[type=number]')

    miniCartTitleQtyValue = parseInt(miniCartTitleQty.textContent);

    addToCartButtons.forEach((button) => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
    
            addToCartButtonObserver.observe(button, { attributes: true, attributeFilter: ['class'] });
        });
    });
}

loopButtons();