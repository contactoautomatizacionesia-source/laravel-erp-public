/**
 * Nice Select Internationalization Patch
 * 
 * Dynamically updates nice-select placeholder text based on the current language
 * This runs AFTER nice-select initialization to inject translated search text
 * 
 * Allows for per-select translation via data-search-text attribute,
 * with fallback to global window.AppLang.search
 */

(function($) {
    'use strict';

    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.warn('jQuery is required for nice-select patch');
        return;
    }

    /**
     * Update all nice-select search placeholders with translated text
     */
    function patchNiceSelectPlaceholders() {
        // Get the default search text from global translations, fallback to "Search..."
        const defaultSearchText = (globalThis.AppLang && globalThis.AppLang.search) || 'Search...';

        // Find all nice-select containers with search functionality
        $('.nice-select:has(.nice-select-search)').each(function() {
            const $niceSelect = $(this);
            
            // Find the associated original select element
            const $originalSelect = $niceSelect.prev('select');
            
            if ($originalSelect.length === 0) {
                return;
            }

            // Check if select has a custom data-search-text attribute (allow per-select override)
            let searchText = $originalSelect.data('search-text') || defaultSearchText;

            // Update the placeholder on the search input
            const $searchInput = $niceSelect.find('.nice-select-search');
            if ($searchInput.length > 0) {
                $searchInput.attr('placeholder', searchText);
            }
        });
    }

    /**
     * Initialize patch when DOM is ready
     */
    $(document).ready(function() {
        // Delay slightly to ensure nice-select has finished initialization
        setTimeout(function() {
            patchNiceSelectPlaceholders();
        }, 100);
    });

    /**
     * Watch for dynamically added nice-select elements
     * This handles cases where nice-select is initialized via AJAX or dynamic DOM manipulation
     */
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
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
                if (needsPatch) break;
            }
            if (needsPatch) patchNiceSelectPlaceholders();
        });

        // Observe the document body for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Expose patch function globally for manual re-patching
     * Useful when language is changed dynamically without page reload
     */
    globalThis.patchNiceSelectPlaceholders = patchNiceSelectPlaceholders;

})(jQuery);
