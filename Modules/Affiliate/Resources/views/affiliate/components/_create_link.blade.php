<div class="row">
    <div class="col-lg-12">
        <div class="main-title">
            <h3 class="mb-30">
                @if(isset($row))
                    {{__('affiliate.Update Affiliate Link')}}
                @else
                    {{__('affiliate.Create Affiliate Link')}}
                @endif
            </h3>
        </div>
        @if(isset($row))
            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => array('affiliate.my_affiliate.update',$row->id), 'method' => 'PUT']) }}
        @else

            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.my_affiliate.store',
            'method' => 'POST']) }}

        @endif
        <div class="white-box">
            <div class="add-visitor">
                <div class="row">

                    <div class="col-lg-12">
                        <div class="primary_input mb-15">
                            <label class="primary_input_label" for="user_name"> {{__('affiliate.Email_or_Username')}} </label>
                            <input readonly  class="primary_input_field" name="user_name" id="user_name" placeholder="{{__('affiliate.Email_or_Username')}}" type="text" value="{{!empty($user->email)?$user->email:$user->username}}">
                            <span class="text-danger">{{$errors->first('user_name')}}</span>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="primary_input mb-15">
                            <label class="primary_input_label" for="url"> {{__('affiliate.Enter URL')}} <span class="required_mark_theme">*</span> </label>
                            <input autocomplete="off"  class="primary_input_field" name="url" id="url" placeholder="{{__('affiliate.Enter URL')}}" type="text" value="{{isset($row)? $row->url : old('url') }}">
                            <span class="text-danger">{{$errors->first('url')}}</span>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="primary_input mb-15">
                            <label class="primary_input_label" for="affiliate_link"> {{__('affiliate.Affiliate Link')}} </label>
                            <input readonly  class="primary_input_field" name="affiliate_link" id="affiliate_link" placeholder="{{__('affiliate.Affiliate Link')}}" type="text" value="{{isset($row)? $row->affiliate_link : old('affiliate_link') }}">
                            <span class="text-danger">{{$errors->first('affiliate_link')}}</span>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-lg-12 text-center">
                        <button class="primary-btn fix-gr-bg submit">

                            <span class="ti-check"></span>
                            @if(isset($row))
                                {{__('common.update')}}
                            @else
                                {{__('common.save')}}
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
