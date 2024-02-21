const themeMenu = document.body.querySelector('#theme-menu');
const dropdownItemAnchor = themeMenu.querySelectorAll('.nav-item a.dropdown-item:not(.nav-item-link)');
const siteMenuDropdown = document.querySelectorAll('.site-menu li li ul.dropdown-menu');
const siteMenuDropdownMatch = window.matchMedia('(min-width: 768px)');
const nav = new hcOffcanvasNav('#theme-menu', {
    disabledAt: 1024,
    customToggle: '.toggle',
    navTitle: 'MENU',
    levelOpen: 'expand',
    disableBody: true,
    ariaLabels: {
        open: 'Open Menu',
        close: 'Close Menu',
        submenu: 'Submenu'
    }
});

onAttributeObserver(document.body, 'class', (observer) => {
    const themeMenu = document.body.querySelector('#theme-menu');
    const hcNav1 = body.querySelector('nav#hc-nav-1');
    const hcNav2 = body.querySelector('nav#hc-nav-2');

    if (hcNav1) {
        const hcNavTitle = hcNav1.querySelector('.hc-offcanvas-nav.nav-close-button-empty .nav-title');
        const closeButton = hcNav1.querySelector('.hc-offcanvas-nav .nav-title+.nav-close a:not(.has-label)');
        closeButton.innerHTML = '<i class="las la-times la-lg"></i>';

        const itemsWithChildrens = hcNav1.querySelectorAll('.hc-offcanvas-nav .menu-item-has-children.nav-parent .nav-item-wrapper a.nav-next');

        for (const item of itemsWithChildrens) {
            const parent = item.parentNode;
            const separateElement = document.createElement('span');
            const expandButton = parent.querySelector('.nav-next');
            separateElement.classList.add('dropdown-item-divider');

            parent.insertBefore(separateElement, expandButton);
        }

        if (!hcNavTitle.classList.contains('gradient') && !hcNavTitle.classList.contains('title')) {
            hcNavTitle.classList.add('gradient', 'title');
        }
    }

    if (themeMenu.classList.contains('hc-nav-1')) {
        themeMenu.classList.remove('hc-nav-1');
    }

    if (hcNav2) {
        hcNav2.remove();
        observer.disconnect();
    }
});

if (siteMenuDropdownMatch.matches) {
    for (const item of siteMenuDropdown) {
        if (item.hasAttribute('style')) {
            item.removeAttribute('style');
        }
    }
}

for (const anchor of dropdownItemAnchor) {
    anchor.addEventListener('mouseover', function () {
        const parent = anchor.parentNode;
        const dropdownMenu = parent.querySelector('.dropdown-menu');

        if (dropdownMenu) {
            const dropdownWidth = dropdownMenu.getBoundingClientRect().width;
            const dropdownRight = dropdownMenu.getBoundingClientRect().right;
            const windowWidth = window.innerWidth;

            if (dropdownRight > windowWidth) {
                dropdownMenu.style.left = `-${dropdownWidth}px`;
            }
        }
    });
}