$(document).ready(function () {
    $('.nice-select-ajax').niceSelect();
    $('.nice-select-regular').niceSelect();
    initNiceSelectAjax();
});

function initNiceSelectAjax() {

    // Carga inicial
    $('.nice-select-ajax').each(function () {
        const $select = $(this);
        const $niceSelect = $select.next('.nice-select');

        if ($select.data('initial')) {
            fetchOptions($select, $niceSelect, buildParams($select));
        }
    });

    // Búsqueda
    $(document).on('keyup', '.nice-select.nice-select-ajax .nice-select-search', function () {
        const $niceSelect = $(this).closest('.nice-select');
        const $select = $niceSelect.prev('select');
        const search = $(this).val().trim();

        if (search.length === 0) {
            debounce(() => {
                fetchOptions($select, $niceSelect, buildParams($select));
            }, 300)();
            return;
        }

        if (search.length < 2) return;

        debounce(() => {
            fetchOptions($select, $niceSelect, buildParams($select, search));
        }, 300)();
    });

    // 🔥 CAMBIO DEL PADRE
    $(document).on('change', '.nice-select-ajax', function () {
        const parentId = this.id;

        $(`select[data-depends-on="${parentId}"]`).each(function () {
            resetDependentSelect($(this));
        });
    });

    $(document).on('keydown keypress', '.nice-select-search', function (e) {
        if (e.key === ' ') {
            e.stopPropagation();
        }
    });
}

function buildParams($select, search = '') {

    const params = {};

    if (search) {
        params.search = search;
    }

    const dependsOn = $select.data('depends-on');
    const paramName = $select.data('param');

    if (dependsOn && paramName) {
        const parentValue = $(`#${dependsOn}`).val();
        if (parentValue) {
            params[paramName] = parentValue;
        }
    }

    return params;
}

function fetchOptions($select, $niceSelect, params) {

    if ($select.data('request')) {
        $select.data('request').abort();
    }

    if (!params) return;

    $niceSelect.addClass('loading');
    const request = $.ajax({
        url: $select.data('url'),
        data: params,
        type: 'GET',

        success(response) {
            updateNiceSelect($select, $niceSelect, response);
        },

        complete() {
            $niceSelect.removeClass('loading');
            $select.removeData('request');
        }
    });

    $select.data('request', request);
}
function resetDependentSelect($select) {

    const dependsOn = $select.data('depends-on');
    const paramName = $select.data('param');

    const parentValue = $(`#${dependsOn}`).val();

    // 🔄 Resetear siempre
    $select.val('');
    $select.find('option:not(:first)').remove();
    $select.niceSelect('update');

    // 🚫 Si no hay padre → deshabilitado
    if (!parentValue) {
        $select.prop('disabled', true);
        $select.next('.nice-select').addClass('disabled');
        return;
    }

    // ✅ AQUÍ VA TU CÓDIGO
    $select.prop('disabled', false);
    $select.next('.nice-select').removeClass('disabled');

    // 🔥 Opcional: cargar automáticamente al cambiar el padre
    fetchOptions(
        $select,
        $select.next('.nice-select'),
        buildParams($select)
    );
}


function updateNiceSelect($select, $niceSelect, items) {

    // Guardar estado antes de tocar el DOM
    const searchVal = $niceSelect.find('.nice-select-search').val();
    const wasOpen = $niceSelect.hasClass('open');
    const $prevSelected = $select.find('option:selected');
    const prevValue = $select.val();
    const prevText  = $prevSelected.text().trim();
    const prevCode  = $prevSelected.attr('data-code') ?? '';

    // Cerrar antes del update para que niceSelect('update') NO haga trigger("click")
    if (wasOpen) {
        $niceSelect.removeClass('open');
    }

    // Limpiar opciones actuales
    $select.find('option:not(:first)').remove();

    if (!items.length) {
        $select.append(`<option value="">Sin resultados</option>`);
    }

    items.forEach(item => {
        const $option = $(`<option value="${item.id}">${item.name}</option>`);
        Object.keys(item).forEach(key => {
            if (key !== 'id' && key !== 'name') {
                $option.attr(`data-${key}`, item[key]);
            }
        });
        $select.append($option);
    });

    // Si había un valor seleccionado y no vino en los nuevos items, reinsertarlo
    // para no perder la selección (sin data-code ya que no vino del servidor)
    if (prevValue && !$select.find(`option[value="${prevValue}"]`).length && prevText) {
        $select.append(`<option value="${prevValue}" data-code="${prevCode}" selected>${prevText}</option>`);
    } else if (prevValue) {
        $select.val(prevValue);
    }

    // Refrescar niceSelect (ahora está cerrado, no hará trigger("click"))
    $select.niceSelect('update');

    // Solo restaurar si el dropdown estaba abierto
    if (wasOpen) {
        setTimeout(() => {
            const $newNiceSelect = $select.next('.nice-select');
            $newNiceSelect.addClass('open');
            $newNiceSelect.find('.nice-select-search').val(searchVal).focus();
        }, 50);
    }
}

function debounce(fn, delay) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}
