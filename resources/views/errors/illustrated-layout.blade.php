<!DOCTYPE html>
<html lang="en" dir="{{isRtl()?'rtl':''}}" class="{{isRtl()?'rtl':''}}">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{showImage(app('general_setting')->favicon)}}" type="image/png" />
    
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <style>
        :root {
            --base_color: #7CB342;
            --accent_color: #ea9466;
            --bg_color: #f6f3ee;
            --text_dark: #1f1f1f;
            --text_muted: #6f6f6f;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Nunito", system-ui, -apple-system, sans-serif;
            background: url("{{ asset('public/images/Background_login.png') }}") no-repeat center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text_dark);
        }

        .lh-error-card {
            background: #ffffff;
            max-width: 680px;
            width: 90%;
            padding: 70px 60px;
            border-radius: 36px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,.12);
            position: relative;
            overflow: hidden; /* Importante para que los blobs no se salgan */
        }

        .lh-logo img {
            max-width: 180px;
            margin-bottom: 35px;
        }

        /* Estilo para el número del error (Code) */
        .lh-code {
            font-size: 140px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 20px;
            letter-spacing: -4px;
            
            /* Color base para el primer y tercer número (Verde) */
            color: var(--base_color); 
        }

        /* Clase específica para el número del medio (Naranja) */
        .lh-code .accent {
            color: var(--accent_color);
        }

        .lh-title {
            font-size: 26px;
            font-weight: 600;
            color: var(--text_dark);
            margin-bottom: 14px;
        }

        .lh-text {
            font-size: 16px;
            line-height: 1.7;
            color: var(--text_muted);
            max-width: 480px;
            margin: 0 auto 35px;
        }

        .lh-actions a, .lh-actions button {
            display: inline-block;
            background: var(--base_color);
            color: #fff;
            padding: 14px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all .25s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(124, 179, 66, 0.2);
        }

        .lh-actions a:hover, .lh-actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0,0,0,.18);
        }

        /* --- ANIMACIÓN LÁMPARA DE LAVA (CORREGIDA) --- */
        .blob {
            position: absolute;
            border-radius: 50%;
            z-index: 0; /* Detrás del contenido */
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }

        .blob.green {
            width: 170px;
            height: 170px;
            background: var(--base_color);
            top: -50px;
            right: -50px;
            animation-name: floatGreen;
            animation-duration: 10s;
        }

        .blob.orange {
            width: 170px;
            height: 170px;
            background: var(--accent_color);
            bottom: -50px;
            left: -50px;
            animation-name: floatOrange;
            animation-duration: 13s;
        }

        /* Definición de movimientos (KEYFRAMES) */
        @keyframes floatGreen {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-30px, 40px) scale(1.1); }
            66% { transform: translate(20px, 20px) scale(0.9); }
            100% { transform: translate(0, 0) scale(1); }
        }

        @keyframes floatOrange {
            0% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.2); }
            66% { transform: translate(20px, -20px) scale(0.8); }
            100% { transform: translate(0, 0) scale(1); }
        }

        /* Soporte para Webkit (Chrome, Safari antiguos) */
        @-webkit-keyframes floatGreen {
            0% { -webkit-transform: translate(0, 0) scale(1); }
            33% { -webkit-transform: translate(-30px, 40px) scale(1.1); }
            66% { -webkit-transform: translate(20px, 20px) scale(0.9); }
            100% { -webkit-transform: translate(0, 0) scale(1); }
        }

        @-webkit-keyframes floatOrange {
            0% { -webkit-transform: translate(0, 0) scale(1); }
            33% { -webkit-transform: translate(30px, -50px) scale(1.2); }
            66% { -webkit-transform: translate(20px, -20px) scale(0.8); }
            100% { -webkit-transform: translate(0, 0) scale(1); }
        }

        .content-relative {
            position: relative;
            z-index: 1; /* El texto va encima de los blobs */
        }

        @media (max-width: 600px) {
            .lh-error-card {
                padding: 50px 30px;
            }
            .lh-code {
                font-size: 100px;
            }
        }
    </style>
</head>
<body>

    <div class="lh-error-card">
        <div class="blob green"></div>
        <div class="blob orange"></div>

        <div class="content-relative">
            {{-- Logo Dinámico --}}
            <div class="lh-logo">
                <img src="{{ showImage(app('general_setting')->logo) }}" alt="Logo">
            </div>

            {{-- Código del Error con Lógica de Color --}}
            <div class="lh-code">
                @php
                    // Capturamos el código (ej: "419", "404")
                    $code = trim($__env->yieldContent('code', 'Error'));
                @endphp

                @if(strlen($code) === 3 && is_numeric($code))
                    {{-- Si son 3 números, coloreamos el del medio --}}
                    <span>{{ $code[0] }}</span><span class="accent">{{ $code[1] }}</span><span>{{ $code[2] }}</span>
                @else
                    {{-- Si es texto u otro formato, lo mostramos normal --}}
                    {{ $code }}
                @endif
            </div>

            {{-- Mensaje del Error --}}
            <div class="lh-text">
                @yield('message')
            </div>

            {{-- Botón de Acción --}}
            <div class="lh-actions">
                <a href="{{ URL::previous() }}">
                    {{ __('defaultTheme.go_back') }}
                </a>
                <a href="{{ url('/') }}">
                    {{ __('defaultTheme.go_home') }}
                </a>
            </div>
        </div>
    </div>

</body>
</html>