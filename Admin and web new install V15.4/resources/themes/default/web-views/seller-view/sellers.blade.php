@extends('layouts.front-end.app')

@section('title', (request('filter') && request('filter') == 'top-vendors' ? translate('top_Stores') : translate('all_Stores')))

@push('css_or_js')
    <meta property="og:image" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="og:title" content="Brands of {{$web_config['company_name']}} "/>
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="twitter:title" content="Brands of {{$web_config['company_name']}}"/>
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')

    <div class="container mb-md-4 {{Session::get('direction') === "rtl" ? 'rtl' : ''}} __inline-65">
        <div class="bg-primary-light rounded-10 my-4 p-3 p-sm-4" data-bg-img="{{ theme_asset(path: 'public/assets/front-end/img/media/bg.png') }}">
            <div class="row g-2 align-items-center">
                <div class="col-lg-8 col-md-6">
                    <div class="d-flex flex-column gap-1 text-primary">
                        <h4 class="mb-0 text-start fw-bold text-primary text-uppercase">
                            {{ (request('filter') && request('filter') == 'top-vendors' ? translate('top_Stores') : translate('all_Stores')) }}
                        </h4>
                        <p class="fs-14 fw-semibold mb-0">{{translate('Find_your_desired_stores_and_shop_your_favourite_products')}}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <form action="{{ route('vendors') }}" method="get">
                        @if(request('filter'))
                            <input type="hidden" name="filter" value="{{ request('filter') }}">
                        @endif
                        <div class="input-group rtl-input-group">
                            <input type="text" class="form-control rounded-10" value="{{request('shop_name')}}"  placeholder="{{translate('Search_Store')}}" name="shop_name">
                            <div class="input-group-append">
                                <button class="btn btn--primary rounded-10" type="submit">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <section class="col-lg-12">
                @if(count($vendorsList) > 0)
                    <div class="row mx-n2 __min-h-200px">
                        @foreach ($vendorsList as $vendorItem)
                            <div class="col-lg-3 col-md-6 col-sm-12 px-2 pb-4 text-center">
                                <a href="{{route('shopView',['slug' => $vendorItem['slug']])}}" class="others-store-card text-capitalize">
                                    <div class="overflow-hidden other-store-banner">
                                        <img class="w-100 h-100 object-cover" alt="" src="{{ getStorageImages(path: $vendorItem->banner_full_url, type: 'shop-banner') }}">
                                    </div>
                                    <div class="name-area">
                                        <div class="position-relative">
                                            <div class="overflow-hidden other-store-logo rounded-full">
                                                <img class="rounded-full" alt="{{ translate('store') }}"
                                                     src="{{ getStorageImages(path: $vendorItem->image_full_url, type: 'shop') }}">
                                            </div>
                                            @php($vendorItemType = $vendorItem['id'] == 0 ? 'inhouse' : 'vendor')
                                            @if(checkVendorAbility(type: $vendorItemType, status: 'temporary_close', vendor: $vendorItem))
                                                <span class="temporary-closed position-absolute text-center rounded-full p-2">
                                                    <span>{{translate('Temporary_OFF')}}</span>
                                                </span>
                                            @elseif(checkVendorAbility(type: $vendorItemType, status: 'vacation_status', vendor: $vendorItem))
                                                <span class="temporary-closed position-absolute text-center rounded-full p-2">
                                                    <span>{{translate('closed_now')}}</span>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="info pt-2">
                                            <h5 class="text-start">{{ $vendorItem['name'] }}</h5>
                                            <div class="d-flex align-items-center">
                                                <h6 class="web-text-primary">
                                                    {{ number_format($vendorItem['average_rating'],1) }}
                                                </h6>
                                                <i class="tio-star text-star mx-1"></i>
                                                <small>{{ translate('rating') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-area">
                                        <div class="info-item">
                                            <h6 class="web-text-primary">{{$vendorItem['review_count'] < 1000 ? $vendorItem['review_count'] : number_format($vendorItem['review_count']/1000 , 1).'K'}}</h6>
                                            <span>{{ translate('reviews') }}</span>
                                        </div>
                                        <div class="info-item">
                                            <h6 class="web-text-primary">{{$vendorItem['products_count'] < 1000 ? $vendorItem['products_count'] : number_format($vendorItem['products_count']/1000 , 1).'K'}}</h6>
                                            <span>{{ translate('products') }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="row mx-n2">
                        <div class="col-md-12">
                            <div class="text-center">
                                {{ $vendorsList->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mb-5 text-center text-muted">
                        <div class="d-flex justify-content-center my-2">
                            <img alt="" src="{{ theme_asset(path: 'public/assets/front-end/img/media/seller.svg') }}">
                        </div>
                        <h4 class="text-muted">{{ translate('vendor_not_available') }}</h4>
                        <p>{{ translate('Sorry_no_data_found_related_to_your_search') }}</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
