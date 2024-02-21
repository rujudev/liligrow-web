document.addEventListener('DOMContentLoaded', function() {
    const iFrameContainer = this.querySelector('div.css-175oi2r.r-150rngu.r-eqz5dr.r-16y2uox.r-1wbh5a2.r-11yh6sk.r-1rnoaur.r-agouwx');
    const taxLabels = this.querySelectorAll('.order-totals #tax strong');

    if (taxLabels.length > 0) {
        taxLabels.forEach((taxLabel) => {
            taxLabel.innerText += ' Incluido';
        })
    }

    if (iFrameContainer) {
        iFrameContainer.setAttribute('style', 'max-width: 80mm;');
    }
})