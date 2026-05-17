<div class="modal fade novelty-modal" id="noveltyModal_{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-20 text-white">
                    {{ __('costcenter::inventory.novelty_for') }} {{ $item->productSku->sku ?? 'N/A' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body text-left">
                @if($transfer->status === 'dispatched')
                    <div class="alert alert-warning p-2 text-black">
                        <i class="ti-info-alt"></i> {{ __('costcenter::inventory.novelty_instructions') }}
                    </div>
                    <div class="row mx-1">
                        <div class="form-card col-12">
                            <h3>{{ __('common.details') }}</h3>
                            <label class="text-black">{{ __('costcenter::inventory.novelty_type') }} *</label>
                            <select name="items[{{ $index }}][novelty_id]" class="primary_select form-control novelty-select" id="novelty_select_{{ $item->id }}">
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($novelties as $nov)
                                    <option value="{{ $nov->id }}">{{ $nov->name }}</option>
                                @endforeach
                            </select>
                            <div class="">
                                <label class="text-black">{{ __('costcenter::inventory.evidence_pdf') }} *</label>
                                <input type="file" name="items[{{ $index }}][evidence_file]" class="form-control-file novelty-file" id="novelty_file_{{ $item->id }}" accept="application/pdf">
                            </div>
                        </div>

                    </div>
                    <div class="row mx-1">
                        <div class="col-12 mb-3 mt-3 form-card">
                            <h3>{{ __('common.description') }}</h3>
                            <textarea name="items[{{ $index }}][description]" class="primary_textarea form-control novelty-desc" id="novelty_desc_{{ $item->id }}" rows="3"></textarea>
                        </div>
                    </div>
                @else
                    @foreach($item->discrepancies as $discrepancy)
                        <div class="row mx-1">
                            <div class="form-card col-12 mb-3">
                                <h3>{{ __('common.details') }}</h3>
                                <div class="row">
                                    <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                        <strong class="text-black">{{ __('costcenter::inventory.novelty_type') }}:</strong> 
                                        <span class="badge_2 d-table mt-2">{{ $discrepancy->novelty->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <strong class="text-black">{{ __('costcenter::inventory.evidence') }}:</strong><br>
                                        @if($discrepancy->evidence_path)
                                            <a href="{{ asset('public/' . $discrepancy->evidence_path) }}" target="_blank" class="badge_1 mt-2 d-inline-block">
                                                <i class="ti-file"></i> {{ __('costcenter::inventory.view_pdf') }}
                                            </a>
                                        @else
                                            <span class="text-black mt-2 d-inline-block">N/A</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mx-1">
                            <div class="col-12 form-card mb-3">
                                <h3>{{ __('common.description') }}</h3>
                                <p class="text-black line-normal mb-0">{{ $discrepancy->description }}</p>
                            </div>
                        </div>

                        @if(!$loop->last) <hr class="my-3"> @endif
                    @endforeach
                @endif
            </div>
            <div class="modal-footer">
                @if($transfer->status === 'dispatched')
                    <button type="button" class="btn-toolkit btn-primary" data-dismiss="modal">{{ __('common.save') }}</button>
                @else
                    <button type="button" class="btn-toolkit btn-primary" data-dismiss="modal">{{ __('common.close') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>