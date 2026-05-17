function ajax_error(data) {
    "use strict";
    if (data.status === 404) {
        toastr.error("Lo que buscas no fue encontrado", '¡Ups!');
        return;
    } else if (data.status === 500) {
        toastr.error('Algo salió mal. Si ve este mensaje varias veces, contacte al administrador.', '¡Ups!');
        return;
    } else if (data.status === 200) {
        toastr.error('Algo no está bien', 'Error');
        return;
    }
    let jsonValue = $.parseJSON(data.responseText);
    let errors = jsonValue.errors;
    if (errors) {
        let i = 0;
        $.each(errors, function(key, value) {
            let first_item = Object.keys(errors)[i];
            let error_el_id = $('#' + first_item);
            if (error_el_id.length > 0) {
                error_el_id.parsley().addError('ajax', {
                    message: value,
                    updateClass: true
                });
            }
            toastr.error(value, 'Error de validación');
            i++;
        });
    } else {
        toastr.error(jsonValue.message, 'Opps!');
    }
}

function jsUcfirst(string) {
    "use strict";
    return string.charAt(0).toUpperCase() + string.slice(1);
}


function _formValidation(form_id = 'content_form', modal = false, modal_id = 'content_modal', ajax_table = null) {

    const form = $('#' + form_id);

    if (!form.length) {
        return;
    }

    form.parsley().on('field:validated', function() {
        $('.parsley-ajax').remove();
        const ok = $('.parsley-error').length === 0;
        $('.bs-callout-info').toggleClass('hidden', !ok);
        $('.bs-callout-warning').toggleClass('hidden', ok);
    });
    form.on('submit', function(e) {
        e.preventDefault();
        $('.parsley-ajax').remove();
        $('.preloader').fadeIn();
        form.find('.submit').hide();
        form.find('.submitting').show();
        const submit_url = form.attr('action');
        const method = form.attr('method');
        //Start Ajax
        const formData = new FormData(form[0]);
        $.ajax({
            url: submit_url,
            type: method,
            data: formData,
            contentType: false, // The content type used when sending data to the server.
            cache: false, // To unable request pages to be cached
            processData: false,
            dataType: 'JSON',
            success: function(data) {
                form.find('input:text').val('');
                form.find("input:text:visible:first").focus();
                toastr.success(data.message, 'Éxito');
                if (ajax_table) {
                    ajax_table.ajax.reload();
                }

                if (data.goto) {
                    window.location.href = data.goto;
                }

                if (data.reload) {
                    window.location.href = '';
                }

                form.find('.submit').show();
                form.find('.submitting').hide();
                $('.preloader').fadeOut();

            },
            error: function(data) {
                ajax_error(data);
                form.find('.submit').show();
                form.find('.submitting').hide();
                $('.preloader').fadeOut();
            }
        });
    });
}
