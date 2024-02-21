import('https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.mjs')
    .then(({Swiper}) => {
        new Swiper(".swiper", {
            loop: true,
            autoplay: {
                delay: 5000
            },
            navigation: {
                nextEl: ".home-top-slider .swiper-button-next",
                prevEl: ".home-top-slider .swiper-button-prev",
            }
        });
    });