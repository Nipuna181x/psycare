const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

const revealSelector = [
    '[data-scroll-reveal]',
    'main > section',
    'main > article',
    'main > header',
    'main > div:not([data-no-scroll-reveal])',
    'main section > header',
    'main section > article',
].join(', ');

const prepareScrollReveals = () => {
    if (reducedMotionQuery.matches || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.08,
    });

    const observeNewElements = (root = document) => {
        root.querySelectorAll(revealSelector).forEach((element, index) => {
            if (element.dataset.scrollRevealReady !== undefined || element.closest('[data-no-scroll-reveal]')) {
                return;
            }

            element.dataset.scrollRevealReady = '';
            element.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 70}ms`);
            observer.observe(element);
        });
    };

    observeNewElements();
    document.documentElement.classList.add('scroll-reveal-enabled');

    const mutationObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node instanceof Element) {
                    if (node.matches(revealSelector) && !node.closest('[data-no-scroll-reveal]')) {
                        node.dataset.scrollRevealReady = '';
                        observer.observe(node);
                    }

                    observeNewElements(node);
                }
            });
        });
    });

    mutationObserver.observe(document.body, { childList: true, subtree: true });
};

const startAnimations = () => {
    document.documentElement.classList.add('page-is-ready');
    prepareScrollReveals();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startAnimations, { once: true });
} else {
    startAnimations();
}
