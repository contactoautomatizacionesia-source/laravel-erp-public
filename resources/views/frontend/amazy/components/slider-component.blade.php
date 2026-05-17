@php
    $headerSliderSection = $headers->where('type','slider')->first();
@endphp

<div class="bannerUi_active banner-principal-home owl-carousel {{$headerSliderSection->is_enable == 0?'d-none':''}}">
    @php
        $sliders = $headerSliderSection->sliders();
    @endphp
    @if(count($sliders) > 0)
        @foreach($sliders as $key => $slider)
            @php
                $sliderUrl = url('/category');

                if ($slider->data_type == 'url' && filled($slider->url)) {
                    $sliderUrl = $slider->url;
                } elseif ($slider->data_type == 'product' && filled(optional(optional($slider->product)->seller)->slug) && filled(optional($slider->product)->slug)) {
                    $sliderUrl = singleProductURL($slider->product->seller->slug, $slider->product->slug);
                } elseif ($slider->data_type == 'category' && filled(optional($slider->category)->slug)) {
                    $sliderUrl = route('frontend.category-product', ['slug' => $slider->category->slug, 'item' => 'category']);
                } elseif ($slider->data_type == 'brand' && filled(optional($slider->brand)->slug)) {
                    $sliderUrl = route('frontend.category-product', ['slug' => $slider->brand->slug, 'item' => 'brand']);
                } elseif ($slider->data_type == 'tag' && filled(optional($slider->tag)->name)) {
                    $sliderUrl = route('frontend.category-product', ['slug' => $slider->tag->name, 'item' => 'tag']);
                }
            @endphp
            <a class="banner_img" href="{{ $sliderUrl }}" {{$slider->is_newtab == 1?'target="_blank"':''}}>
                <img class="img-fluid" src="{{showImage($slider->slider_image)}}" alt="{{@$slider->name}}" title="{{@$slider->name}}">
            </a>
        @endforeach
    @endif
</div>
