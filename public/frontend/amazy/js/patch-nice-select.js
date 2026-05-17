/**
 * Nice Select Internationalization Patch
 *
 * Dynamically updates nice-select placeholder text based on the current language.
 *
 * Allows per-select overrides via data-search-text,
 * with fallback to globalThis.AppLang.search.
 */
(function ($) {
    'use strict';


    if (typeof $ === 'undefined') {
        console.warn('jQuery is required for nice-select patch');
        return;
    }

    var originalNiceSelect = $.fn.niceSelect;

    if (typeof originalNiceSelect === 'function') {
        $.fn.niceSelect = function (method) {
            var result = originalNiceSelect.apply(this, arguments);

            if (typeof method === 'undefined' || method === 'update') {
                setTimeout(function () {
                    patchNiceSelectPlaceholders();
                }, 0);
            }

            return result;
        };
    }

    function patchNiceSelectPlaceholders() {
        const defaultSearchText = (typeof window.trans === 'function' ? window.trans('common.search') : '')
            || (globalThis.AppLang && globalThis.AppLang.search)
            || 'Search...';

        $('.nice-select:has(.nice-select-search)').each(function () {
            const $niceSelect = $(this);
            const $originalSelect = $niceSelect.prev('select');

            if ($originalSelect.length === 0) {
                return;
            }

            const searchText = $originalSelect.data('search-text') || defaultSearchText;
            const $searchInput = $niceSelect.find('.nice-select-search');

            if ($searchInput.length > 0) {
                $searchInput.attr('placeholder', searchText);
            }
        });
    }

    $(document).ready(function () {
        setTimeout(function () {
            patchNiceSelectPlaceholders();
        }, 100);
    });

    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function (mutations) {
            let needsPatch = false;

            for (let i = 0; i < mutations.length; i++) {
                const mutation = mutations[i];
                if (mutation.type === 'childList') {
                    for (let j = 0; j < mutation.addedNodes.length; j++) {
                        const node = mutation.addedNodes[j];
                        if (node.nodeType === 1 && ($(node).hasClass('nice-select') || $(node).find('.nice-select').length)) {
                            needsPatch = true;
                            break;
                        }
                    }
                }

                if (needsPatch) {
                    break;
                }
            }

            if (needsPatch) {
                patchNiceSelectPlaceholders();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    globalThis.patchNiceSelectPlaceholders = patchNiceSelectPlaceholders;
})(jQuery);
