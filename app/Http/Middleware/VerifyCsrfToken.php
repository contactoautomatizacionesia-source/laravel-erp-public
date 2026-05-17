<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * CSRF protection is intentionally disabled for these routes.
     * Payment gateway callbacks (paytm, jazzcash, payumoney, midtrans, ssl-commerz, epayco)
     * are server-to-server POST requests initiated by external providers — they never carry
     * a CSRF token because they do not originate from a browser session.
     * The page-builder wildcard is excluded because its POST endpoints are already protected
     * by auth + admin middleware, making CSRF a redundant layer there.
     * The installer routes are excluded to allow first-run setup before any session exists.
     *
     * @var array
     */
    protected $except = [
        // Admin page builder — protected by auth + admin middleware
        'page-builder/*',

        // Payment gateway callbacks — server-to-server POSTs from external providers
        '/paytm-payment/status',
        '/jazzcash-payment-status',
        '/payumoney-payment-success',
        '/payumoney-payment-failed',
        '/midtrans-payment-success',
        '/midtrans-payment-failed',

        // SSL Commerz callbacks
        '/ssl-commerz/success',
        '/ssl-commerz/cancel',
        '/ssl-commerz/fail',
        '/ssl-commerz/ipn',

        // Epayco callback
        '/epayco/callback',

        // Installer — runs before any authenticated session exists
        'install',
        'install/*',
    ];
}
