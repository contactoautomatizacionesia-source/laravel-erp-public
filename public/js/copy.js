function copyToClipboard(inputId) {
    const element = document.getElementById(inputId);
    if (!element) return;
    navigator.clipboard.writeText(element.value).then(() => {
        toastr.success("Copiado");
    });
}