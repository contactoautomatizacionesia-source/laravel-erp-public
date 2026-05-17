document.addEventListener('DOMContentLoaded', () => {

    applyCurrencyMask();

    $(document).on('input', '.currency-mask', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.addEventListener('focus', function (e) {
        if (e.target.classList.contains('currency-mask')) {
            e.target.value = cleanNumber(e.target.value);
        }
    }, true);

    document.addEventListener('blur', function (e) {
        if (e.target.classList.contains('currency-mask')) {
            if (e.target.value !== '') {
                e.target.value = formatCurrency(e.target.value);
            }
        }
    }, true);

});

function applyCurrencyMask(context = document) {
    context.querySelectorAll('.currency-mask').forEach(input => {
        if (input.value !== '') {
            input.value = formatCurrency(input.value);
        }
    });
}

function cleanNumber(value) {
    return value
        .replace(/\./g, '')   // quitar miles
        .replace(/\$/g, '')
        .replace(',', '.')
        .trim();
}

function formatCurrency(value) {

    // Normalizar: quitar símbolo de moneda, espacios y separadores de miles (.)
    // luego convertir coma decimal a punto para parseFloat
    value = value
        .replace(/[^\d,]/g, '')   // dejar solo dígitos y coma
        .replace(',', '.');       // convertir coma decimal a punto

    let number = parseFloat(value);

    if (isNaN(number)) return '';

    // Formatear sin decimales: 100000 → $100.000
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(Math.trunc(number));
}