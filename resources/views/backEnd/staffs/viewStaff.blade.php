@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('backend/css/backend_page_css/staff_view.css'))}}" />
<style>
   .single-meta .name,.single-info .name{color: var(--text_black)}
   .nav-tabs .nav-link.active {
      border-color: #fff #fff var(--base_color);
      color: var(--base_color);
   }
   .nav-tabs .nav-link{color:var(--text_black)}
</style>

@endsection
@section('mainContent')

<x-admin.section class="ign-detail-staf">
   <div class="row">
         @if(session()->has('message-success'))
            <div class="alert alert-success">
               {{ session()->get('message-success') }}
            </div>
         @elseif(session()->has('message-danger'))
            <div class="alert alert-danger">
               {{ session()->get('message-danger') }}
            </div>
         @endif
         <div class="col-12">
            <div class="box_header common_table_header ">
               <div class="main-title d-md-flex align-items-center mb-3">
                  <x-backEnd.back-button :text="false" />
                  <h3 class="mb-0">{{__('common.staff_info')}}</h3>
               </div>
            </div>
         </div>
         <div class="col-lg-4">
            <div class="form-card">
                  <div class="">
                     <div class="avatar_div d-flex justify-content-between align-items-start">
                        <img class="img-100"
                           src="{{ asset( (@$staffDetails->user->avatar !=null)?asset_path(@$staffDetails->user->avatar) : asset_path('backend/img/avatar.png')) }}"
                           alt="">
                        
                        <div class="d-flex gap-2">
                           @if(isModuleActive('DigitalFolder') && $staffUserFolder)
                           <a href="{{ route('admin.file-explorer.index', ['folder' => $staffUserFolder->id]) }}" class="btn-toolkit btn-primary btn-icon btn-sm">
                              <i class="ti-folder"></i>{{ __('common.user_folder') }}
                           </a>
                           @endif
                           <a href="{{ route('staffs.edit', $staffDetails->id) }}" class="btn-toolkit btn-secondary btn-icon btn-sm"><i class="ti-pencil"></i>{{
                              __('common.edit') }}
                           </a>
                        </div>
                     </div>
                     <h3 class="mt-3">
                        @if(isset($staffDetails)){{ucwords(@$staffDetails->user->getFullNameAttribute())}}@endif
                     </h3>
                     
                     @if ($staffDetails->user->role_id != 1)
                     <div class="single-meta">
                        <div class="d-flex justify-content-between">
                           <div class="name">
                              {{ __('common.employee_id') }}
                           </div>
                           <div class="value">
                              @if(isset($staffDetails)){{getNumberTranslate($staffDetails->employee_id)}}@endif
                           </div>
                        </div>
                     </div>
                     <div class="single-meta">
                        <div class="d-flex justify-content-between">
                           <div class="name">
                              {{ __('common.opening_balance') }}
                           </div>
                           <div class="value">
                              @if(isset($staffDetails)){{single_price($staffDetails->opening_balance)}}@endif
                           </div>
                        </div>
                     </div>
                     @endif
                     <div class="single-meta">
                        <div class="d-flex justify-content-between">
                           <div class="name">
                              {{ __('common.username') }}
                           </div>
                           <div class="value">
                              @if(isset($staffDetails)){{getNumberTranslate(@$staffDetails->user->username)}}@endif
                           </div>
                        </div>
                     </div>
                     <div class="single-meta">
                        <div class="d-flex justify-content-between">
                           <div class="name">
                              {{ __('hr.role') }}
                           </div>
                           <div class="value">
                              @if(isset($staffDetails)){{@$staffDetails->user->role->name}}@endif
                           </div>
                        </div>
                     </div>
                     <div class="single-meta">
                        <div class="d-flex justify-content-between">
                           <div class="name">
                              {{ __('hr.department') }}
                           </div>
                           <div class="value">
                              @if(isset($staffDetails)){{ !empty($staffDetails->department != null)?
                              $staffDetails->department->name:''}}@endif
                           </div>
                        </div>
                     </div>

                     @if ($staffDetails->user->role_id != 1)
                     <div class="single-meta">
                        <div class="d-flex justify-content-between">
                           <div class="name">
                              {{ __('hr.date_of_joining') }}
                           </div>
                           <div class="value">
                              @if(isset($staffDetails))
                              {{ dateConvert($staffDetails->date_of_joining) }}
                              @endif
                           </div>
                        </div>
                     </div>
                     @endif
                  </div>
            </div>
         </div>
         <div class="col-lg-8">
            <div class="form-card">
               <ul class="nav nav-tabs tabs_scroll_nav border-0 mb-3">
                  <li class="nav-item">
                     <a class="nav-link active" href="#studentProfile" role="tab" data-toggle="tab">{{ __('common.profile')
                        }}</a>
                  </li>

                  <li class="nav-item">
                     <a class="nav-link" href="#staffDocuments" role="tab" data-toggle="tab">{{ __('common.documents')
                        }}</a>
                  </li>

                  
               </ul>
               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane fade show active" id="studentProfile">
                     <div class="form-card">
                        <h3 class="">{{ __('common.personal_info') }}</h3>
                        <div class="single-info">
                           <div class="row">
                              <div class="col-lg-5 col-md-5">
                                 <div class="name">
                                    {{ __('common.document_type') }}
                                 </div>
                              </div>
                              <div class="col-lg-7 col-md-6">
                                 <div class="value">
                                    @if(isset($staffDetails)){{ !empty($staffDetails->typeDocument != null)?
                                 $staffDetails->typeDocument->name:''}}@endif
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="single-info">
                           <div class="row">
                              <div class="col-lg-5 col-md-5">
                                 <div class="name">
                                    {{ __('common.document_number') }}
                                 </div>
                              </div>
                              <div class="col-lg-7 col-md-6">
                                 <div class="value">
                                    @if(isset($staffDetails)){{getNumberTranslate($staffDetails->document_number)}}@endif
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="single-info">
                           <div class="row">
                              <div class="col-lg-5 col-md-5">
                                 <div class="name">
                                    {{ __('hr.cost_center') }}
                                 </div>
                              </div>
                              <div class="col-lg-7 col-md-6">
                                 <div class="value">
                                    @if(isset($staffDetails)){{getNumberTranslate($staffDetails->cost_center)}}@endif
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="single-info">
                           <div class="row">
                              <div class="col-lg-5 col-md-5">
                                 <div class="name">
                                    {{ __('common.phone') }}
                                 </div>
                              </div>
                              <div class="col-lg-7 col-md-6">
                                 <div class="value">
                                    @if(isset($staffDetails)){{getNumberTranslate($staffDetails->phone)}}@endif
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="single-info">
                           <div class="row">
                              <div class="col-lg-5 col-md-6">
                                 <div class="name">
                                    {{ __('common.email') }}
                                 </div>
                              </div>
                              <div class="col-lg-7 col-md-7">
                                 <div class="value">
                                    @if(isset($staffDetails)){{@$staffDetails->user->email}}@endif
                                 </div>
                              </div>
                           </div>
                        </div>
                        @if ($staffDetails->user->role_id != 1)
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-6">
                                    <div class="name">
                                       {{ __('common.date_of_birth') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-7">
                                    <div class="value">
                                       @if(isset($staffDetails))
                                       {{$staffDetails->date_of_birth != ""? dateConvert($staffDetails->date_of_birth):''}}
                                       @endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                        @endif
                     </div>
                     @if ($staffDetails->user->role_id != 1)
                     <div class="form-card">
                           <!-- Start Parent Part -->
                           <h3 class="">{{ __('common.address') }}</h3>
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('common.address') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if(isset($staffDetails)){{$staffDetails->address}}@endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <!-- End Parent Part -->
                           <!-- Start Transport Part -->
                     </div>
                     <div class="form-card">
                           <h3 class="">{{ __('hr.bank_account_details') }}</h3>
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('common.account_name') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if(isset($staffDetails)){{$staffDetails->bank_account_name}}@endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('hr.bank_account_number') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if(isset($staffDetails)){{getNumberTranslate($staffDetails->bank_account_no)}}@endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('hr.bank_name') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if(isset($staffDetails)){{$staffDetails->bank_name}}@endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('hr.bank_branch_name') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if(isset($staffDetails)){{$staffDetails->bank_branch_name}}@endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                     </div>
                     <div class="form-card">
                           <h3 class="">{{ __('common.status_details') }}</h3>
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('common.status') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if($staffDetails->user->is_active)
                                          <span class="badge_1 ">
                                             {{ __('common.active') }}
                                          </span>
                                       @else
                                          <span class="badge_2">
                                             {{ __('common.inactive') }}
                                          </span>
                                       @endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                           @if(!$staffDetails->user->is_active)
                           <div class="single-info">
                              <div class="row">
                                 <div class="col-lg-5 col-md-5">
                                    <div class="name">
                                       {{ __('hr.reason_for_inactivation') }}
                                    </div>
                                 </div>
                                 <div class="col-lg-7 col-md-6">
                                    <div class="value">
                                       @if(isset($staffDetails)){{$staffDetails->user->inactive_reason}}@endif
                                    </div>
                                 </div>
                              </div>
                           </div>
                           @endif
                           <!-- End Transport Part -->
                     </div>
                     @endif
                  </div>
                  @if(isset($staffDetails))<input type="hidden" name="user_id" id="user_id"
                        value="{{ @$staffDetails->user->id }}">@endif
                  <div role="tabpanel" class="tab-pane fade" id="staffDocuments">
                     <div class="">
                        <div class="text-right mb-3">
                           <button type="button" data-toggle="modal" data-target="#add_document_madal"
                              class="btn-toolkit btn-secondary-outline btn-icon">
                              {{__('common.upload_document')}}
                              <span class="pl ti-upload"></span>
                           </button>
                        </div>
                        <div class="QA_section QA_section_heading_custom check_box_table">
                           <div class="QA_table ">
                              <table class="table Crm_table_active">
                                 <thead>
                                    <tr>
                                       <th scope="col">{{__('common.title')}}</th>
                                       <th scope="col" style="width:140px;text-align:center">{{__('common.action')}}</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    @isset($staffDocuments)
                                       @foreach ($staffDocuments as $key => $staffDocument)
                                       <tr>
                                             <td> <a href="{{asset(asset_path($staffDocument->documents))}}" download
                                                target="_blank">{{ $staffDocument->name }}</a></td>
                                             <td style="text-align:center">
                                             <div class="dropdown CRM_dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                                   id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true"
                                                   aria-expanded="false">
                                                   {{ __('common.select') }}
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right"
                                                   aria-labelledby="dropdownMenu2">
                                                   <a href="{{asset(asset_path($staffDocument->documents))}}"
                                                         class="dropdown-item" download>{{__('common.download')}}</a>
                                                   <a data-value="{{route('staff_document.destroy', $staffDocument->id)}}"
                                                         class="dropdown-item delete_document">{{__('common.delete')}}</a>
                                                </div>
                                             </div>
                                             </td>
                                       </tr>
                                       @endforeach
                                    @endisset
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
   </div>
   <div class="modal fade admin-query" id="add_document_madal">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h4 class="modal-title">{{__('common.upload_document')}}</h4>
               <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
               <div class="container-fluid">
                  <form class="" action="{{ route('staff_document.store') }}" method="post"
                     id="document_create_form" enctype="multipart/form-data">
                     @csrf
                     <div class="row">
                        <input type="hidden" name="staff_id" value="{{$staffDetails->id}}">
                        <div class="col-xl-12">
                           <div class="primary_input mb-25">
                              <label class="primary_input_label" for="">{{ __('common.name') }}</label>
                              <input name="name" class="primary_input_field name" id="create_name"
                                 placeholder="{{ __('common.name') }}" type="text">
                              <span class="text-danger" id="create_name_error"></span>
                           </div>
                        </div>
                        <div class="col-lg-12">
                           <div class="primary_input mb-15">
                              <label class="primary_input_label" for="">{{ __('common.File') }}</label>
                              <div class="primary_file_uploader">
                                 <input class="primary-input" type="text" id="placeholderFileOneName"
                                    placeholder="{{__('common.browse_file')}}" readonly="">
                                 <button class="" type="button">
                                    <label class="primary-btn small fix-gr-bg" for="document_file_1">{{
                                       __('common.browse') }}</label>
                                    <input type="file" class="d-none" name="file" id="document_file_1">
                                 </button>
                                 <span class="text-danger" id="create_file_error"></span>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-12 text-center mt-10">
                           <div class="mt-10 d-flex justify-content-between">
                              <button type="button" class="primary-btn tr-bg" data-dismiss="modal">{{
                                 __('common.cancel') }}</button>
                              <button class="primary-btn fix-gr-bg" type="submit">{{ __('common.save')
                                 }}</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</x-admin.section>

@include('backEnd.partials.delete_modal')
@endsection
@push('scripts')
<script type="text/javascript">
   (function($){
            "use strict";

            $(document).ready(function(){

               // Reinicializar tabla de documentos deshabilitando sorting en columna de acción
               if ($('.Crm_table_active').length) {
                  $('.Crm_table_active').DataTable({
                     bLengthChange: false,
                     bDestroy: true,
                     language: {
                        paginate: {
                           next: "<i class='ti-arrow-right'></i>",
                           previous: "<i class='ti-arrow-left'></i>"
                        }
                     },
                     responsive: true,
                     searching: false,
                     columnDefs: [
                        { orderable: false, targets: -1 }
                     ]
                  });
               }
               $(document).on('submit', '#document_create_form', function(event){
                  $('#create_name_error').text('');
                  $('#create_file_error').text('');
                  let name = $('#create_name').val();
                  let file = $('#document_file_1').val();

                  if(name == ''){
                     $('#create_name_error').text('The Name field is Required.');
                  }
                  if(file == ''){
                     $('#create_file_error').text('The File field is Required.');
                  }

                  if(name == '' || file == ''){
                     event.preventDefault();
                     return false;
                  }

               });

               $(document).on('change', '#document_file_1', function(){
                  getFileName($(this).val(),'#placeholderFileOneName');
               });

               $(document).on('click', '.printDiv', function(){
                  printDiv(divName);
               });

               function printDiv(divName) {

                  var printContents = document.getElementById(divName).innerHTML;
                  var originalContents = document.body.innerHTML;
                  document.body.innerHTML = printContents;
                  window.print();
                  document.body.innerHTML = originalContents;

               }

               $(document).on('click', '.delete_document', function(event){
                  event.preventDefault();
                  let url = $(this).data('value');
                  confirm_modal(url);
               });

            });

         })(jQuery);


</script>
@endpush
