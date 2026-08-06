(() => {
    'use strict';

    const header = document.querySelector('[data-site-header]');
    const menuButton = document.querySelector('.mobile-menu-toggle');
    const navigation = document.querySelector('.main-navigation');
    const backToTop = document.querySelector('.back-to-top');

    const updateScrollState = () => {
        const scrolled = window.scrollY > 18;
        header?.classList.toggle('scrolled', scrolled);
        backToTop?.classList.toggle('visible', window.scrollY > 520);
    };

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, { passive: true });

    menuButton?.addEventListener('click', () => {
        const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!isOpen));
        navigation?.classList.toggle('open', !isOpen);
    });

    navigation?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            navigation.classList.remove('open');
            menuButton?.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('click', (event) => {
        if (!navigation?.classList.contains('open')) return;
        if (navigation.contains(event.target) || menuButton?.contains(event.target)) return;
        navigation.classList.remove('open');
        menuButton?.setAttribute('aria-expanded', 'false');
    });

    document.querySelectorAll('.faq-question').forEach((button) => {
        button.addEventListener('click', () => {
            const item = button.closest('.faq-item');
            if (!item) return;

            const willOpen = !item.classList.contains('open');
            item.parentElement?.querySelectorAll('.faq-item').forEach((sibling) => {
                sibling.classList.remove('open');
                sibling.querySelector('.faq-question')?.setAttribute('aria-expanded', 'false');
            });

            item.classList.toggle('open', willOpen);
            button.setAttribute('aria-expanded', String(willOpen));
        });
    });

    document.querySelectorAll('.flash-close').forEach((button) => {
        button.addEventListener('click', () => {
            const flash = button.closest('.flash-wrap');
            if (flash) flash.remove();
        });
    });

    backToTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    const revealItems = document.querySelectorAll('.reveal-on-scroll');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('revealed');
                instance.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -45px' });

        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('revealed'));
    }

    const filterButtons = document.querySelectorAll('[data-filter]');
    const galleryItems = document.querySelectorAll('[data-category]');

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const selected = button.dataset.filter || 'all';
            filterButtons.forEach((item) => item.classList.remove('active'));
            button.classList.add('active');

            galleryItems.forEach((item) => {
                const categories = (item.dataset.category || '').split(',').map((value) => value.trim());
                item.classList.toggle('hidden', selected !== 'all' && !categories.includes(selected));
            });
        });
    });
})();
