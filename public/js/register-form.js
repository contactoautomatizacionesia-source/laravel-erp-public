document.addEventListener('click', function(e) {
    if (e.target.matches('.upload-btn')) {
        e.target.closest('.custom-file-upload-img')
            .querySelector('.file-input')
            .click();
    }
});

document.addEventListener('change', function(e) {

    if (e.target.matches('.file-input')) {

        const wrapper = e.target.closest('.custom-file-upload-img');
        const img = wrapper.querySelector('.image-preview img');
        const errorEl = e.target.closest('.reg-group')?.querySelector('.text-danger');

        const file = e.target.files[0];
        const allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (file) {
            const ext = file.name.split('.').pop().toLowerCase();

            if (!allowed.includes(ext)) {
                // Limpiar selección y preview
                e.target.value = '';
                img.src = img.dataset.default || img.src;
                wrapper.classList.add('upload-error');

                if (errorEl) {
                    const msg = (typeof registerTranslations !== 'undefined' && registerTranslations.fileType)
                        ? registerTranslations.fileType
                        : 'Only JPG, JPEG, PNG or PDF files are allowed';
                    errorEl.innerText = msg;
                }
                return;
            }

            // Archivo válido: limpiar error si había
            wrapper.classList.remove('upload-error');
            if (errorEl) errorEl.innerText = '';

            const reader = new FileReader();
            reader.onload = function(event) {
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    if (e.target.matches('.custom-file-input')) {

        const wrapper = e.target.closest('.custom-file');
        const fileNameContainer = wrapper.querySelector('.custom-file-name');

        const fileName = e.target.files.length > 0
            ? e.target.files[0].name
            : 'No se ha seleccionado ningún archivo';

        fileNameContainer.textContent = fileName;
    }

});

// // Mascara para campos de moneda
// document.addEventListener('DOMContentLoaded', () => {

//     document.querySelectorAll('.currency-mask').forEach(input => {

//         input.addEventListener('input', function () {
//             formatCurrency(this);
//         });

//         input.addEventListener('blur', function () {
//             formatCurrency(this);
//         });

//         input.addEventListener('focus', function () {
//             // quitar formato para editar
//             this.value = this.value.replace(/\./g, '').replace('$', '').trim();
//         });

//     });

// });

// function formatCurrency(input) {

//     let value = input.value.replace(/\D/g, ''); // solo números

//     if (!value) {
//         input.value = '';
//         return;
//     }

//     const formatter = new Intl.NumberFormat('es-CO', {
//         style: 'currency',
//         currency: 'COP',
//         minimumFractionDigits: 0
//     });

//     input.value = formatter.format(value);
// }