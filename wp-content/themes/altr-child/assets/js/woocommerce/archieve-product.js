document.addEventListener('DOMContentLoaded', function() {
    const isProductCategoryPage = window.location.pathname.includes('categoria-producto');

    const subcategoriesContainer = document.createElement('div');    
    subcategoriesContainer.setAttribute('id', 'subcategories-container');
    
    const productsContainer = document.createElement('div');
    productsContainer.setAttribute('id', 'products-container');
    
    const productsList = document.querySelector('ul.products');
    productsList.classList.add('custom-list');
    
    if (isProductCategoryPage) {
        const subcategoriesTitle = document.createElement('h2');
        subcategoriesTitle.innerText = 'Subcategorías';
        subcategoriesContainer.appendChild(subcategoriesTitle);
        
        const productTitle = document.createElement('h2');
        productTitle.innerText = 'Productos';    
        productsContainer.appendChild(productTitle);
    }
    

    const productsElements = productsList.querySelectorAll('.product');
    const productsElementGroups = Object.groupBy(productsElements, (product) => {
        return product.classList.contains('product-category') ? 'subcategories' : 'products';
    });

    const {subcategories, products} = productsElementGroups;

    if (subcategories) {
        const subcategoriesListContainer = document.createElement('div');
        subcategoriesListContainer.setAttribute('id', 'subcategories-list-container');
        
        subcategories.forEach((subCategory) => {
            subcategoriesListContainer.appendChild(subCategory);
        });

        subcategoriesContainer.appendChild(subcategoriesListContainer);
        productsList.append(subcategoriesContainer);
    }

    if (products) {
        const productsListContainer = document.createElement('div');
        productsListContainer.setAttribute('id', 'products-list-container');
        
        products.forEach((product) => {
            productsListContainer.appendChild(product);
        });
        
        productsContainer.appendChild(productsListContainer);
        productsList.append(productsContainer);
    }
});