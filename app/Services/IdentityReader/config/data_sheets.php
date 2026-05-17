<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Python binary
    |--------------------------------------------------------------------------
    | Leave empty to auto-detect: the package will first look for the venv
    | created during `composer install`, then fall back to system `python3`.
    | Set an absolute path here to override (e.g. '/usr/bin/python3').
    */
    'python_bin' => env('DATA_SHEETS_PYTHON_BIN', ''),

    /*
    |--------------------------------------------------------------------------
    | Runner script
    |--------------------------------------------------------------------------
    | Absolute path to ocr_runner.py. Leave empty to use the default path
    | inside the vendor package directory.
    */
    'runner_script' => env('DATA_SHEETS_RUNNER_SCRIPT', ''),

    /*
    |--------------------------------------------------------------------------
    | Process timeout (seconds)
    |--------------------------------------------------------------------------
    | Maximum time allowed for the OCR Python process to complete.
    */
    'timeout' => env('DATA_SHEETS_TIMEOUT', 120),

    /*
    |--------------------------------------------------------------------------
    | Max image size (bytes)
    |--------------------------------------------------------------------------
    | Maximum allowed size for uploaded images. Default: 10 MB.
    */
    'max_image_size' => env('DATA_SHEETS_MAX_IMAGE_SIZE', 10 * 1024 * 1024),

];
