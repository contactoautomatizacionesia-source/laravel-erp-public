<!doctype html>
<html @if(isRtl()) dir="rtl" class="rtl no-js" @else class="no-js" @endif lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{app('general_setting')->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/x-icon" href="{{showImage(app('general_setting')->favicon)}}">

    @php
        $themeColor = Modules\Appearance\Entities\ThemeColor::where('status',1)->first();
    @endphp
    <style>

        :root {
            --background_color : {{ $themeColor->background_color }};
            --base_color : {{ $themeColor->base_color }};
            --text_color : {{ $themeColor->text_color }};
            --feature_color : {{ $themeColor->feature_color }};
            --footer_background_color : {{ $themeColor->footer_background_color }};
            --footer_text_color : {{ $themeColor->footer_text_color }};
            --navbar_color : {{ $themeColor->navbar_color }};
            --menu_color : {{ $themeColor->menu_color }};
            --border_color : {{ $themeColor->border_color }};
            --success_color : {{ $themeColor->success_color }};
            --warning_color : {{ $themeColor->warning_color }};
            --danger_color : {{ $themeColor->danger_color }};
            --text_white : #ffffff;
            --toolkit_base_color_dark_green: #35431E;
            --toolkit_corporative-red-color: #d54830;
            --toolkit_corporative-orange-color: #e99466;
            --toolkit_corporative-green-color: #8dae58;
            --toolkit_premium-brown-color: #b4935a;
            --toolkit_premium-dark-color: #2b2a29;
            --toolkit_secondary-gray-color: #929496;
            --toolkit_secondary-blue-color: #b3c4d0;
            --toolkit_primary-50: #7eb05152;
        }
    </style>
    <!-- CSS here -->
    @if(isRtl())
    <link rel="stylesheet" href="{{asset(asset_path('frontend/amazy/compile_css/rtl_app.css'))}}" >
    @else
    <link rel="stylesheet"  href="{{asset(asset_path('frontend/amazy/compile_css/app.css'))}}" >
    @endif

    <!-- Igniweb -->
    <link rel="stylesheet" href="{{asset(asset_path('css/ign_custom.css'))}}" />

    @stack('styles')
    <!-- CSS here -->
</head>
