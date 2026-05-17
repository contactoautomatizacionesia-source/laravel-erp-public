<div id="section_documents" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class="section-title text-uppercase">{{ __('common.documents') }}</h3>
        <p>{{ __('common.documents_description') }}</p>
    </div>
    <div class="reg-group col-12 col-md-6 mb_20">
        <div class="custom-file-upload-img">
            <span class="general-lable mb-3">{{ label_case_custom(__('common.front_document')) }} *</span>

            <div class="image-preview">
                <img
                    src="{{ $customerProfile?->front_id_image ? digital_file_url($customerProfile?->front_id_image) : asset('public/images/defaul_front_cedula.png') }}"
                    alt="preview"
                    data-default="{{ $customerProfile?->front_id_image ? digital_file_url($customerProfile?->front_id_image) : asset('public/images/defaul_front_cedula.png') }}"
                >
            </div>

            <input
                type="file"
                name="front_id_image"
                class="file-input"
                accept=".jpg,.jpeg,.png,.pdf"
                @if(!$customerProfile?->front_id_image)
                data-validate="required|fileType:jpg,jpeg,png,pdf"
                @else
                data-validate="fileType:jpg,jpeg,png,pdf"
                @endif
            >

            <input
                type="file"
                class="camera-input d-none"
                accept="image/*"
                capture="environment"
                tabindex="-1"
                aria-hidden="true"
            >

            <div class="document-upload-actions">
                <button type="button" class="upload-btn btn-toolkit btn-primary btn-sm flex-1">
                    {{ __('common.upload_image') }}
                </button>

                <button type="button" class="camera-btn btn-toolkit btn-secondary-outline btn-sm flex-1">
                    {{ __('common.open_camera') }}
                </button>
            </div>

        </div>
        <span class="text-danger" >{{ $errors->first('front_id_image') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 mb_20">
        <div class="custom-file-upload-img">
            <span class="general-lable mb-3">{{ label_case_custom(__('common.back_document')) }} * </span>
            <div class="image-preview">
                <img
                    src="{{ $customerProfile?->back_id_image ? digital_file_url($customerProfile?->back_id_image) : asset('public/images/default_back_cedula.png') }}"
                    alt="preview"
                    data-default="{{ $customerProfile?->back_id_image ? digital_file_url($customerProfile?->back_id_image) : asset('public/images/default_back_cedula.png') }}"
                >
            </div>

            <input
                type="file"
                name="back_id_image"
                class="file-input"
                accept=".jpg,.jpeg,.png,.pdf"
                @if(!$customerProfile?->back_id_image)
                data-validate="required|fileType:jpg,jpeg,png,pdf"
                @else
                data-validate="fileType:jpg,jpeg,png,pdf"
                @endif
            >

            <input
                type="file"
                class="camera-input d-none"
                accept="image/*"
                capture="environment"
                tabindex="-1"
                aria-hidden="true"
            >

            <div class="document-upload-actions">
                <button type="button" class="upload-btn btn-toolkit btn-primary btn-sm flex-1">
                    {{ __('common.upload_image') }}
                </button>

                <button type="button" class="camera-btn btn-toolkit btn-secondary-outline btn-sm flex-1">
                    {{ __('common.open_camera') }}
                </button>
            </div>

        </div>
        <span class="text-danger" >{{ $errors->first('back_id_image') }}</span>
    </div>

    <div class="col-12">
        <div class="document-scan-panel text-center">
            <button
                type="button"
                class="btn-toolkit btn-primary"
                id="scanDocumentBtn"
                data-scan-url="{{ route('register.scan_id') }}"
            >
                {{ __('common.scan_document') }}
            </button>

            <p id="scanDocumentOutput" class="document-scan-output d-none" aria-live="polite"></p>
        </div>
    </div>

</div>

<div class="modal fade" id="documentCameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content document-camera-modal">
            <div class="modal-header border-0">
                <div>
                    <h4 class="modal-title mb-1">Tomar foto del documento</h4>
                    <p class="document-camera-subtitle mb-0">Ajusta el documento dentro del encuadre y toma la foto cuando se vea nítido.</p>
                </div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="document-camera-stage" data-camera-stage="live">
                    <video id="documentCameraVideo" class="document-camera-video" autoplay playsinline muted></video>
                    <img id="documentCameraPreview" class="document-camera-preview d-none" alt="Vista previa de captura">
                    <canvas id="documentCameraCanvas" class="d-none"></canvas>
                </div>
                <p class="document-camera-error text-danger d-none mb-0 mt-3" id="documentCameraError"></p>
            </div>
            <div class="modal-footer border-0 justify-content-between flex-wrap">
                <button type="button" class="btn-toolkit btn-secondary-outline" id="retakeDocumentPhotoBtn" disabled>
                    Repetir foto
                </button>
                <div class="document-camera-footer-actions">
                    <button type="button" class="btn-toolkit btn-secondary" id="captureDocumentPhotoBtn">
                        Capturar
                    </button>
                    <button type="button" class="btn-toolkit btn-primary" id="useDocumentPhotoBtn" disabled>
                        Usar foto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
