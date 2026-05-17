<script type="text/javascript">
window.plansUtils = window.plansUtils || {};

window.plansUtils.userLang = '{{ auth()->user()->lang_code ?? "en" }}';

window.plansUtils.getTranslatedValue = function (value, fallbackValue) {
    if (value && typeof value === 'object') {
        return value[window.plansUtils.userLang] || value['en'] || Object.values(value)[0] || fallbackValue;
    }
    return value || fallbackValue;
};

// Traduce el valor guardado de un answer buscando en las opciones del campo.
// Si el campo es un select con options [{en,es,...}], devuelve la traducción de la opción cuyo
// valor 'en' coincide con el answer almacenado. Si no aplica, devuelve el answer crudo.
// Aplica máscara de moneda a todos los inputs.currency-mask dentro de un contenedor.
// El valor se guarda internamente como número plano (sin símbolo ni separadores).
window.applyCurrencyMask = function (container) {
    var inputs = (container || document).querySelectorAll('.currency-mask');
    inputs.forEach(function (input) {
        if (input._currencyMaskApplied) return;
        input._currencyMaskApplied = true;

        function format(raw) {
            var num = parseFloat(raw.replace(/[^0-9.]/g, ''));
            if (isNaN(num)) return '';
            return '$ ' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function getRaw(input) {
            return input.value.replace(/[^0-9.]/g, '');
        }

        input.addEventListener('focus', function () {
            this.value = getRaw(this);
        });

        input.addEventListener('blur', function () {
            if (this.value !== '') this.value = format(this.value);
        });

        input.addEventListener('input', function () {
            // Solo permitir dígitos y un punto decimal
            this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        });

        // Formatear si ya tiene valor (modo editar)
        if (input.value !== '') input.value = format(input.value);
    });
};

// Al enviar un form que contiene currency-mask, normalizar valores a número plano
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('submit', 'form', function () {
            jQuery(this).find('.currency-mask').each(function () {
                this.value = this.value.replace(/[^0-9.]/g, '');
            });
        });
    }
});

window.plansUtils.resolveAnswerDisplay = function (answer, field) {
    if (!field || !field.validation_rules) return answer;
    var opts = field.validation_rules.options;
    if (!Array.isArray(opts)) return answer;
    var getT = window.plansUtils.getTranslatedValue;
    for (var i = 0; i < opts.length; i++) {
        var opt = opts[i];
        if (opt && typeof opt === 'object' && opt.id !== undefined) {
            // New format: option from form_options table — compare by numeric id
            if (String(opt.id) === String(answer)) {
                return getT(opt.option_label, opt.option_key);
            }
        } else if (opt && typeof opt === 'object') {
            // Legacy inline format: {"en":"Fixed","es":"Fijo"}
            var storedVal = opt['en'] || Object.values(opt)[0];
            if (String(storedVal) === String(answer)) {
                return getT(opt, answer);
            }
        } else if (String(opt) === String(answer)) {
            return answer;
        }
    }
    return answer;
};
</script>
