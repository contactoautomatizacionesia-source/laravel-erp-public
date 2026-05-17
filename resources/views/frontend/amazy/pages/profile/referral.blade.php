@extends('frontend.amazy.pages.profile.layouts._profile_layout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('/public/css/customer_profile.css') }}">
@endpush

@section('profile_content')
    <div class="dashboard_white_box style2 bg-white mb_25">
        @if(isset($myCode))
        <div class="dashboard_white_box_header d-flex align-items-center">
            <h4 class="font_24 f_w_700 mb_20">{{__('defaultTheme.my_referral_code')}}</h4>
        </div>
        <div id="coupon" class="mb_40 mt-3">
            <div class="row g-4 align-items-center bg-white rounded-3 p-4 theme_border" style="border: 1px solid var(--border_color, #e0e0e0); box-shadow: 0 4px 12px rgba(0,0,0,0.02);">

                <div class="col-xl-4 col-12 text-center coupon-col border-end-xl">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" 
                    style="width: 65px; height: 65px; border: 2px solid var(--base_color); background-color: rgba(255, 193, 7, 0.1); color: var(--base_color);">
                        <svg width="35" height="35" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    </div>
                    <h4 class="font_18 f_w_700 text-dark m-0">{{__('marketing.exclusive_code')}}</h4>
                    <span class="font_14 f_w_400 text-muted">{{__('marketing.exclusive_code_description')}}</span>
                </div>

                <div class="col-xl-4 col-12 text-center coupon-col border-end-xl">
                    <div class="d-flex flex-column align-items-start justify-content-center gap-2 mb-3">
                        <span class="text-muted text-left text-uppercase font_12 f_w_700">{{__('marketing.referral_code')}}</span>
                        <div class="coupon-code-row">
                            <div class="code-dashed">
                                <h3 class="font_16 f_w_500 m-0" style="color: var(--base_color);">{{getNumberTranslate($myCode->referral_code)}}</h3>
                            </div>
                            <input type="hidden" id="code" value="{{getNumberTranslate($myCode->referral_code)}}">
                            
                            <button id="copyBtn" class="amaz_primary_btn d-flex align-items-center justify-content-center" title="{{__('defaultTheme.copy_code')}}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                <span>{{__('defaultTheme.copy_code')}}</span>
                            </button>
                        </div>
                    </div>

                    @php
                        $ref_url = url('/register') . '?referral_code=' . $myCode->referral_code;
                    @endphp
                    <div class="d-flex flex-column align-items-start input-group input-group-sm">
                        <span class="text-muted text-left text-uppercase font_12 f_w_700">{{__('marketing.referral_url')}}</span>
                        <div class="coupon-url-row">
                            <input name="referral_url" id="referral_url" value="{{ $ref_url }}" class="form-control text-center text-muted" readonly type="text" style="font-size: 12px; background-color: #f8f9fa;">
                            <button id="copyUrl" class="btn btn-outline-secondary" type="button" title="{{__('defaultTheme.copy_url')}}">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-12 text-center coupon-col">
                    <p class="font_14 f_w_500 text-muted mb-3 lh-base">
                        {{__('marketing.share_directly')}}
                    </p>
                    <div class="share-buttons">
                        <button type="button" class="amaz_primary_btn btn-sm text-nowrap d-inline-flex align-items-center gap-1 share_btn" style="background-color: #25D366; color: #fff; border-color: #25D366; padding: 6px 15px;"
                                data-method="whatsapp">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            {{__('common.whatsapp')}}
                            </button>
                        <button type="button" class="btn btn-outline-secondary text-center btn-sm text-nowrap d-inline-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#emailShareModal" style="padding: 6px 15px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            {{__('common.email')}}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="dashboard_white_box_header d-flex align-items-center justify-content-between">
            <h4 class="font_20 f_w_700 mb_20">{{__('defaultTheme.user_list')}}</h4>
            <div>
                <span>Filtrar por:</span>
                <select name="" id="" class="select-status">
                    <option value="all">Todos los estados</option>
                </select>
            </div>
        </div>
        <div class="dashboard_white_box_body">
            <div class="table_border_whiteBox mb_30">
                <div class="table-responsive">
                    <table class="table amazy_table style4 mb-0">
                        <thead>
                            <tr>
                            <th class="font_14 text-muted" scope="col">{{__('common.sl')}}</th>
                            <th class="font_14 text-muted border-start-0 border-end-0" scope="col">{{__('common.user')}}</th>
                            <th class="font_14 text-muted border-start-0 border-end-0" scope="col">{{__('common.date')}}</th>
                            <th class="font_14 text-muted border-start-0 border-end-0" scope="col">{{__('common.status')}}</th>
                            <th class="font_14 text-muted border-start-0 border-end-0" scope="col">{{__('defaultTheme.discount_amount')}}</th>
                            <th class="font_14 text-muted border-start-0 border-end-0" scope="col">{{__('common.action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referList as $key => $referral)
                            <tr>
                                <td>
                                    <span class="font_14 f_w_500 mute_text">{{getNumberTranslate($key +1)}}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if(@$referral->user->avatar)
                                            <img 
                                                src="{{ showImage($referral->user->avatar) }}" 
                                                alt="{{ @$referral->user->first_name }}" 
                                                class="ref-avatar"
                                            >
                                        @else
                                            @php
                                                $firstName = @$referral->user->first_name ?: 'U';
                                                $lastName = @$referral->user->last_name ?: '';
                                                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                                
                                                $colors = ['#F44336', '#E91E63', '#9C27B0', '#673AB7', '#3F51B5', '#2196F3', '#00BCD4', '#009688', '#4CAF50', '#FF9800', '#FF5722', '#795548'];
                                                $colorIndex = array_rand($colors);
                                                $baseColor = $colors[$colorIndex];
                                            @endphp
                                            
                                            <div class="ref-avatar d-flex align-items-center justify-content-center" style="color: {{ $baseColor }}; background-color: {{ $baseColor }}26; font-size: 16px; font-weight: bold;">
                                                {{ $initials }}
                                            </div>
                                        @endif

                                        <div>
                                            <span class="font_16 f_w_700">{{textLimit(@$referral->user->first_name. ' ' . @$referral->user->last_name, 20)}}</span><br>
                                            <span class="font_12 f_w_400 mute_text">{{@$referral->user->email ? @$referral->user->email : @$referral->user->username}}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font_14 f_w_500 mute_text">{{dateConvert($referral->created_at)}} </span>
                                </td>
                                <td>
                                    <a href="#" id="referral_used_{{$referral->id}}" 
                                        class="badge_status table_badge_btn {{$referral->is_use == 1?'style4':'style3'}} text-nowrap">
                                        <div class="badge_status_point {{$referral->is_use == 1?'style4':'style3'}}">.</div>
                                        {{$referral->is_use == 1?__('defaultTheme.already_use'):__('defaultTheme.not_used')}}
                                    </a>
                                </td>
                                <td>
                                    <span class="font_14 {{$key%2 == 0 ? 'f_w_600':'mute_text'}} ">{{single_price($referral->discount_amount)}} </span>
                                </td>
                                <td>
                                <button id="referral_used{{$referral->id}}" class="referral_used {{$referral->is_use == 1?'style4 amaz_primary_btn gray_bg_btn':'style3 amaz_primary_btn'}} text-nowrap" {{$referral->is_use == 1 ? 'disabled' : '' }} data-id="{{$referral->id}}">{{$referral->is_use == 1?__('common.already_claimed'):__('common.claim')}}</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($referList->lastPage() > 1)
                <x-pagination-component :items="$referList" type=""/>
            @elseif(!$referList->count())
                <p class="empty_p">{{ __('common.empty_list') }}.</p>
            @endif
        </div>
        @else
            <div class="text-center py-5 px-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 90px; height: 90px; background: rgba(0,0,0,0.03); color: var(--base_color);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                
                <h4 class="font_20 f_w_700 mb-3 text-dark">
                    {{__('defaultTheme.you_will_get_referral_after')}}
                </h4>
                
                <p class="font_14 f_w_400 mute_text mx-auto mb-0" style="max-width: 450px; line-height: 1.6;">
                    {{__('defaultTheme.referral_message')}}
                </p>
            </div>
        @endif
    </div>
@include('frontend.amazy.pages.profile.partials._email_share_modal')
@endsection

@push('scripts')
<script src="{{ asset('/public/js/copy.js') }}"></script>
    <script>
        (function($){
            "use strict";

            $(document).ready(function(){
                // Validación simple de formato de correo
                function validateEmail(email) {
                    var re = /\S+@\S+\.\S+/;
                    return re.test(email);
                }

                // Evento al hacer clic en "Enviar" dentro del modal
                $(document).on('click', '#sendEmailShareBtn', function(e) {
                    e.preventDefault();
                    
                    let targetEmail = $('#targetFriendEmail').val();
                    let errorSpan = $('#emailShareError');

                    // Limpiar errores previos
                    errorSpan.addClass('d-none');

                    if (targetEmail && validateEmail(targetEmail)) {
                        // Ocultar modal y mostrar loader
                        $('#emailShareModal').modal('hide');
                        $('#pre-loader').show(); // o .removeClass('d-none') dependiendo de cómo lo tengas

                        $.post('{{ route("customer_panel.referral.share") }}', {
                            _token: '{{ csrf_token() }}',
                            method: 'email',
                            email: targetEmail
                        }, function(res) {
                            $('#pre-loader').hide();
                            if (res.status) {
                                toastr.success(res.message, "{{__('common.success')}}");
                                $('#targetFriendEmail').val(''); // Limpiar el input para la próxima vez
                            }
                        }).fail(function(xhr) {
                            $('#pre-loader').hide();
                            toastr.error("{{__('common.error_message')}}");
                        });
                    } else {
                        // Mostrar mensaje de error en el modal si el correo es inválido
                        errorSpan.removeClass('d-none');
                    }
                });
                $(document).on('click', '.share_btn', function(e) {
                    e.preventDefault();
                    let method = $(this).data('method');
                    let btn = $(this);

                    if (method === 'whatsapp' || method === 'copy') {
                        $('#pre-loader').show();
                        $.post('{{ route("customer_panel.referral.share") }}', {
                            _token: '{{ csrf_token() }}',
                            method: method
                        },
                        function(res) {
                            $('#pre-loader').hide();
                            if (res.status) {
                                if (method === 'whatsapp') {
                                    let text = res.message + "\n\n" + res.url;
                                    let waUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(text);
                                    window.open(waUrl, '_blank');
                                } else {
                                    // Lógica de copiado usando el mensaje procesado o solo la URL
                                    copyToClipboard(res.url);
                                    toastr.success("{{__('defaultTheme.code_copied_successfully')}}");
                                }
                            }
                        });
                    }
                });

                $(document).on('click', '#copyBtn', function () {
                    copyToClipboard('code');
                });

                $(document).on('click', '#copyUrl', function () {
                    copyToClipboard('referral_url');
                });
                $(document).on('click', '.referral_used', function(event){
                    var id = $(this).data('id');
                    $('#pre-loader').show();
                    $.post('{{ route('customer_panel.referral.used') }}',{_token:'{{ csrf_token() }}', referral_id:id}, function(data){
                        var balance = $('#total_balance').text();
                        var total = balance.split(" ");
                        var total_bal =total[1].split(',') ;
                        var total_balance = parseFloat(total_bal[0]+total_bal[1]);
                        var amount = parseFloat(data.amount + total_balance);
                        $('#total_balance').text(currency_format(amount));
                        $('#referral_used'+id).text('{{__('common.already_claimed')}}');
                        $('#referral_used_'+id).text('{{__('defaultTheme.already_use')}}');
                        $('#referral_used'+id).addClass("gray_bg_btn");
                        $('#referral_used'+id).prop("disabled", true);
                        $('#pre-loader').hide();
                    });
                });
            });

        })(jQuery);
    </script>
@endpush