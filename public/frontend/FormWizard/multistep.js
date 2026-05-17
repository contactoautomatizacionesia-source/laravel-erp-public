document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('[data-multistep]').forEach(initMultistep);

});

function initMultistep(form) {

    let currentStep = 0;
    const steps = form.querySelectorAll('[data-step]');
    const indicators = form.querySelectorAll('[data-step-indicator]');

    const uniqueValidationCache = new Map();
    const rules = {

        required(input) {
            return input.value.trim() !== '';
        },

        email(input) {
            if(input.value.trim() === '') return true;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
        },
        alphaNumeric(input) {
            if(input.value.trim() === '') return true;
            return /^[a-zA-Z0-9\s\-.&#,\/°ªÁÉÍÓÚáéíóúÑñ]+$/.test(input.value.trim());
        },

        // 🔤 Solo letras y espacios (para nombres)
        alpha(input) {
            if(input.value.trim() === '') return true;
            return /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/.test(input.value.trim());
        },

        // 👤 Nombre coherente (mínimo 2 palabras opcional)
        fullName(input) {
            const value = input.value.trim();
            return /^[A-Za-zÁÉÍÓÚáéíóúÑñ]{2,}(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]{2,})+$/.test(value);
        },

        // 🔢 Solo números enteros
        integer(input) {
            if(input.value.trim() === '') return true;
            return /^\d+$/.test(input.value.trim());
        },

        // 🔢 Solo números
        numeric(input) {
            let value = input.value.trim();
            if(value === '') return true;

            if (!/^\d+([.,]\d+)?$/.test(value)) {
                return false;
            }

            input.value = value.replace(',', '.'); // estandariza
            return true;
        },

        currency(input) {
            let value = input.value.trim();

            if (value === '') return true;

            // 1️⃣ Quitar símbolo peso y espacios
            value = value.replace(/\$/g, '').trim();

            // 2️⃣ Quitar separadores de miles (.)
            value = value.replace(/\./g, '');

            // 3️⃣ Reemplazar coma decimal por punto
            value = value.replace(',', '.');

            // 4️⃣ Validar que sea número válido
            if (!/^\d+(\.\d+)?$/.test(value)) {
                return false;
            }

            return true;
        },

        // 🇨🇴 Cédula colombiana coherente
        document(input) {

            const value = input.value.trim();
            const type = document.querySelector('#document_type_id')?.value;

            if (!value || !type) return false;

            switch (type) {

                // 🇨🇴 CÉDULA DE CIUDADANÍA
                case '1':
                    if (!/^\d+$/.test(value)) return false;
                    if (value.length < 6 || value.length > 10) return false;
                    if (/^(\d)\1+$/.test(value)) return false;
                    return true;

                // 🌎 CÉDULA DE EXTRANJERÍA
                case '2':

                    if (!/^[A-Za-z0-9]+$/.test(value)) return false;
                    if (value.length < 6 || value.length > 20) return false;
                    return true;

                // 🌎 Identificación tributaria
                case '3':

                    if (!/^\d+$/.test(value)) return false;
                    if (value.length < 6 || value.length > 12) return false;
                    return true;


                // 🛂 PASAPORTE
                case '4':

                    // pasaporte puede tener letras y números
                    if (!/^[A-Za-z0-9]{6,12}$/.test(value)) return false;
                    return true;

                // 🆔 Identificacion tributaria
                case '5':

                    // PPT suele ser numérico (puedes ajustar si cambia)
                    if (!/^\d{6,15}$/.test(value)) return false;
                    return true;

                default:
                    return false;
            }
        },
        phone(input) {

            if(input.value.trim() === '') return true;

            const normalized = normalizePhone(input.value);

            if (!/^\d{6,14}$/.test(normalized)) {
                return false;
            }

            input.value = normalized;

            return true;
        },

        // 🏢 RUC (11 dígitos)
        ruc(input) {
            if(input.value.trim() === '') return true;
            return /^\d{11}$/.test(input.value.trim());
        },

        minLength(input, length = 3) {
            if(input.value.trim() === '') return true;
            return input.value.trim().length >= length;
        },

        maxLength(input, length = 50) {
            if(input.value.trim() === '') return true;
            return input.value.trim().length <= length;
        },

        // Validaciones especiales
        required_if(input, fieldName, expectedValue, operator = 'equals') {

            const dependentField = document.querySelector(`[name="${fieldName}"]`);
            if (!dependentField) return true;

            const fieldValue = dependentField.value.trim();

            let conditionMet = false;

            if (operator === 'equals') {
                conditionMet = fieldValue === expectedValue;
            }

            if (operator === 'notEmpty') {
                conditionMet = fieldValue !== '';
            }

            if (!conditionMet) return true;

            return input.value.trim() !== '';
        },
        // 🔞 Mayoría de edad (≥18 años)
        minAge(input) {
            const value = input.value;
            if (!value) return false;
            const [y, m, d] = value.split('-').map(Number);
            const dob = new Date(y, m - 1, d);
            if (isNaN(dob.getTime())) return false;
            const today = new Date();
            const limit = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
            return dob <= limit;
        },

        // 📅 Fecha de expedición: no futura, posterior a nacimiento
        issueDateValid(input) {
            const value = input.value;
            if (!value) return false;
            const [iy, im, id] = value.split('-').map(Number);
            const issue = new Date(iy, im - 1, id);
            if (isNaN(issue.getTime())) return false;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (issue > today) return false;
            const dobInput = document.getElementById('date_of_birth');
            if (dobInput && dobInput.value) {
                const [dy, dm, dd] = dobInput.value.split('-').map(Number);
                const dob = new Date(dy, dm - 1, dd);
                if (!isNaN(dob.getTime()) && issue <= dob) return false;
            }
            return true;
        },

        // 🔐 Confirmación de contraseña
        passwordMatch(input) {
            const password = document.getElementById('password');
            if (!password) return true;
            return input.value === password.value;
        },

        // 📅 Fecha de vencimiento: posterior a la expedición
        expirationDateValid(input) {
            const value = input.value;
            if (!value) return true; // campo opcional
            const [ey, em, ed] = value.split('-').map(Number);
            const expiration = new Date(ey, em - 1, ed);
            if (isNaN(expiration.getTime())) return false;
            const issueInput = document.getElementById('issue_date');
            if (issueInput && issueInput.value) {
                const [iy, im, id] = issueInput.value.split('-').map(Number);
                const issue = new Date(iy, im - 1, id);
                if (!isNaN(issue.getTime()) && expiration <= issue) return false;
            }
            return true;
        },

        // 📎 Tipo de archivo permitido
        fileType(input, ...allowed) {
            if (!input.files || input.files.length === 0) return true;
            const ext = input.files[0].name.split('.').pop().toLowerCase();
            const allowedList = allowed.length ? allowed : ['jpg', 'jpeg', 'png', 'pdf'];
            return allowedList.includes(ext);
        },

        data_unique: async function(input) {

            // tratamos de obtener el id del customer para evitar que se valide contra sí mismo
            const customerId = form.dataset.customer || null;

            const value = input.value.trim();
            if (!value) return true;

            const key = input.name;

            const cached = uniqueValidationCache.get(key);

            // ✅ Si el valor no cambió, devolver resultado anterior
            if (cached && cached.lastValue === value) {
                return cached.lastResult;
            }

            try {

                const params = new URLSearchParams({
                    field: input.name,
                    value: value
                });

                if (customerId) {
                    params.append('customer_id', customerId);
                }


                const response = await fetch(
                    `/customer/check-availability?${params.toString()}`,
                    { headers: { 'Accept': 'application/json' } }
                );

                if (!response.ok) return false;

                const data = await response.json();

                // ✅ Guardamos el resultado
                uniqueValidationCache.set(key, {
                    lastValue: value,
                    lastResult: data.is_available
                });

                return data.is_available;

            } catch {
                return false;
            }
        }

    };

    function normalizePhone(value) {
        return value
            .replace(/\s+/g, '')     // quitar espacios
            .replace(/[-()]/g, '')   // quitar guiones y paréntesis
            .replace(/^\+/, '');     // quitar +
    }

    function showStep(index) {
        steps.forEach(step => step.classList.add('d-none'));
        steps[index].classList.remove('d-none');

        indicators.forEach(i => i.classList.remove('active'));
        indicators[index]?.classList.add('active');

        currentStep = index;
        toggleButtons();
    }

    function toggleButtons() {
        form.querySelector('[data-action="prev"]')?.toggleAttribute('disabled', currentStep === 0);
        form.querySelector('[data-action="next"]')?.classList.toggle('d-none', currentStep === steps.length - 1);
        form.querySelector('[data-action="submit"]')?.classList.toggle('d-none', currentStep !== steps.length - 1);
    }

    async function validateStep(stepIndex) {

        let valid = true;

        form.classList.add('validating');

        const step = steps[stepIndex];
        const inputs = step.querySelectorAll('[data-validate]');

        for (const input of inputs) {

            clearError(input);

            const validations = input.dataset.validate.split('|');

            for (let ruleString of validations) {

                let [ruleName, params] = ruleString.split(':');
                let ruleParams = params ? params.split(',') : [];

                const rule = rules[ruleName];
                if (!rule) continue;

                const result = rule(input, ...ruleParams);

                const isValid = result instanceof Promise
                    ? await result
                    : result;

                if (!isValid) {
                    valid = false;
                    showError(input, ruleName);
                    scrollToFirstError();
                    break;
                }
            }
        }

        form.classList.remove('validating');

        return valid;
    }

    async function  validateAllSteps() {

        for (let i = 0; i < steps.length; i++) {

            const isValid = await validateStep(i);

            if (!isValid) {

                showStep(i);

                return {
                    valid: false,
                    failedStep: i
                };
            }
        }

        return {
            valid: true,
            failedStep: null
        };
    }

    function showError(input, rule) {

        input.classList.add('is-invalid');

        let message = registerTranslations[rule] || 'Error';

        // Buscar el contenedor de error dentro del mismo bloque
        const container = input.closest('.reg-group');
        if (!container) return;

        const errorElement = container.querySelector('.text-danger');

        if (errorElement) {
            errorElement.innerText = message;
        }
    }
    function clearError(input) {

        input.classList.remove('is-invalid');

        const container = input.closest('.reg-group');
        if (!container) return;

        const errorElement = container.querySelector('.text-danger');

        if (errorElement) {
            errorElement.innerText = '';
        }
    }
    function scrollToFirstError() {

        const firstInvalid = document.querySelector('.is-invalid');

        if (!firstInvalid) return;

        firstInvalid.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        firstInvalid.focus();
    }
    // Eventos
    form.addEventListener('click', async e => {
        const button = e.target.closest('[data-action]');
        if (!button || !form.contains(button)) return;

        const action = button.dataset.action;

        if (action === 'next') {
            if (!(await validateStep(currentStep))) return;
            showStep(currentStep + 1);
        }

        if (action === 'prev') {
            showStep(currentStep - 1);
        }
    });
    form.addEventListener('click', async function(e) {

        const stepItem = e.target.closest('[data-step-indicator]');

        if (!stepItem) return;

        const step = parseInt(stepItem.dataset.stepIndicator);

        if (step < currentStep) {
            // Permite retroceder sin validar el paso actual.
            showStep(step);
        } else if (step > currentStep) {
            // Valida el paso actual antes de avanzar.
            if (await validateStep(currentStep)) {
                showStep(step);
            }
        }

    });
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        
        
        const result = await validateAllSteps();

        if (!result.valid) {
            const label = indicators[result.failedStep]
                ?.querySelector('.step-label')
                ?.innerText;

                showStep(result.failedStep);
            toastr.error(
                `${registerTranslations['error_step']}: ${label || result.failedStep + 1}`,
                registerTranslations['incomplete_form']
            );

            return;
        }

        document.querySelectorAll('.currency-mask').forEach(input => {
            input.value = cleanNumber(input.value);
        });

        form.submit();
    });

 

    showStep(0);

}

$(document).on('change', 'select[data-sync-text="true"]', function () {

    const targetId = $(this).data('text-target');
    const text = $(this).find('option:selected').text();

    $('#' + targetId).val(text);

});


