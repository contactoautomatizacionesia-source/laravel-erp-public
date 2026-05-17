$(document).on('change', '.input-file', function (e) {

    let input = $(this);
    let inputId = input.attr('id');

    let placeholder = $('#' + inputId + '_placeholder');
    let listContainer = $('#' + inputId + '_file_list');

    listContainer.html(''); // limpiar lista

    let files = this.files;

    if (!files || files.length === 0) {
        placeholder.val("Ningún archivo seleccionado");
        return;
    }

    // actualizar placeholder
    if (files.length === 1) {
        placeholder.val(files[0].name);
    } else {
        placeholder.val(files.length + " archivos seleccionados");
    }

    // renderizar lista
    $.each(files, function (index, file) {

        let item = `
            <div class="file-item">
                <span class="file-name">${file.name}</span>
            </div>
        `;

        listContainer.append(item);
    });

});