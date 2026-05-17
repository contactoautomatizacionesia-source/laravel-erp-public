window._ = require('lodash');

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');
    require('bootstrap');
} catch (e) {}

globalThis.toastr = require('toastr');

/**
 * Global Language Translations for JavaScript Components
 * These are set server-side in the layout template
 * Fallback structure if not provided by the server
 */
if (globalThis.AppLang === undefined) {
    globalThis.AppLang = {
        search: 'Search...',
        select: 'Select...'
    };
}


