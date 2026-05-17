<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('payment_gatways.epayco') }} - {{ __('common.payment') }}</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .loading-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #f5a623;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <div class="spinner"></div>
        <h3>{{ __('payment_gatways.epayco_processing') }}</h3>
        <p>{{ __('payment_gatways.epayco_redirecting') }}</p>
    </div>

    <script data-cfasync="false" src="https://checkout.epayco.co/checkout.js"></script>
    <script data-cfasync="false">
        window.addEventListener('load', function() {
            if (typeof ePayco === 'undefined') {
                console.error('ePayco SDK no cargó correctamente');
                document.querySelector('.spinner').style.display = 'none';
                document.querySelector('h3').textContent = '{{ __("payment_gatways.epayco_sdk_error") }}';
                const pElement = document.querySelector('p');
                pElement.innerHTML = '{{ __("payment_gatways.epayco_sdk_error_description") }}<br><br>';
                const backLink = document.createElement('a');
                const referrerUrl = (document.referrer && !document.referrer.toLowerCase().startsWith('javascript:')) ? document.referrer : '{{ url("/checkout") }}';
                backLink.href = referrerUrl;
                backLink.textContent = '{{ __("payment_gatways.epayco_back_to_checkout") }}';
                backLink.setAttribute('style', 'display:inline-block;padding:10px 20px;background:#f5a623;color:#fff;border-radius:5px;text-decoration:none;');
                pElement.appendChild(backLink);
                return;
            }

            var handler = ePayco.checkout.configure({
                key: '{{ $epayco_data["public_key"] }}',
                test: {{ $epayco_data["test"] }}
            });

            handler.open({
                external: 'true',
                name: 'Order Payment',
                description: 'Payment Invoice #{{ $epayco_data["invoice"] }}',
                invoice: '{{ $epayco_data["invoice"] }}',
                currency: '{{ strtolower($epayco_data["currency"]) }}',
                amount: '{{ $epayco_data["amount"] }}',
                tax_base: '0',
                tax: '0',
                country: '{{ strtolower(app("general_setting")->default_country_code ?? "co") }}',
                lang: '{{ app()->getLocale() }}',
                response: '{{ $epayco_data["response_url"] }}',
                confirmation: '{{ $epayco_data["confirmation_url"] }}',
                name_billing: '{{ $epayco_data["name"] }}',
                address_billing: '',
                type_doc_billing: 'cc',
                mobilephone_billing: '{{ $epayco_data["phone"] }}',
                number_doc_billing: '',
                email_billing: '{{ $epayco_data["email"] }}',
                extra1: '{{ $epayco_data["extra1"] ?? "" }}',
                extra2: '{{ $epayco_data["extra2"] ?? "" }}',
                extra3: '{{ $epayco_data["extra3"] ?? "" }}'
            });
        });
    </script>
</body>
</html>
