@extends('layouts.front-end.app')

@section('title', $product['name'])

@push('css_or_js')
    @include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}"/>
@endpush

@section('content')
    <div class="__inline-23">
        <div class="container mt-4 rtl text-align-direction">
            <div class="row {{Session::get('direction') === "rtl" ? '__dir-rtl' : ''}}">
                <div class="col-lg-9">
                    <div class="row">
                        <div class="col-lg-5 col-md-4">
                            <div class="cz-product-gallery">
                                <div class="cz-preview">
                                    <div id="sync1" class="owl-carousel owl-theme product-thumbnail-slider">
                                        @if($product->images!=null && json_decode($product->images)>0)
                                            @if(json_decode($product->colors) && count($product->color_images_full_url)>0)
                                                @foreach ($product->color_images_full_url as $key => $photo)
                                                    @if($photo['color'] != null)
                                                        <div
                                                            class="product-preview-item d-flex align-items-center justify-content-center {{ $key==0?'active':''}}"
                                                            id="image{{ $photo['color'] }}">
                                                            <img class="cz-image-zoom img-responsive w-100"
                                                                 src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                                 data-zoom="{{ getStorageImages(path: $photo['image_name'], type: 'product')  }}"
                                                                 alt="{{ translate('product') }}" width="">
                                                            <div class="cz-image-zoom-pane"></div>
                                                        </div>
                                                    @else
                                                        <div
                                                            class="product-preview-item d-flex align-items-center justify-content-center {{ $key==0?'active':''}}"
                                                            id="image{{ $key}}">
                                                            <img class="cz-image-zoom img-responsive w-100"
                                                                 src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                                 data-zoom="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                                 alt="{{ translate('product') }}" width="">
                                                            <div class="cz-image-zoom-pane"></div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach ($product->images_full_url as $key => $photo)
                                                    <div
                                                        class="product-preview-item d-flex align-items-center justify-content-center {{ $key==0?'active':''}}"
                                                        id="image{{ $key}}">
                                                        <img class="cz-image-zoom img-responsive w-100"
                                                             src="{{ getStorageImages($photo, type: 'product') }}"
                                                             data-zoom="{{ getStorageImages(path: $photo, type: 'product') }}"
                                                             alt="{{ translate('product') }}" width="">
                                                        <div class="cz-image-zoom-pane"></div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @endif
                                    </div>

                                    @if($product?->preview_file_full_url['path'])
                                        <div>
                                            <div class="product-preview-modal-text"
                                                 data-toggle="modal"
                                                 data-target="#product-preview-modal">
                                                <span class="text-primary fw-bold py-2 user-select-none fs-14">
                                                    {{ translate('See_Preview') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex flex-column gap-3">
                                    <button type="button" data-product-id="{{ $product['id'] }}"
                                            class="btn __text-18px border wishList-pos-btn d-sm-none product-action-add-wishlist">
                                        <i class="fa {{($wishlistStatus == 1?'fa-heart':'fa-heart-o') }} wishlist_icon_{{ $product['id'] }} web-text-primary"
                                           aria-hidden="true"></i>
                                        <div class="wishlist-tooltip" x-placement="top">
                                            <div class="arrow"></div>
                                            <div class="inner">
                                                <span class="add">{{ translate('added_to_wishlist') }}</span>
                                                <span class="remove">{{ translate('removed_from_wishlist') }}</span>
                                            </div>
                                        </div>
                                    </button>

                                    <div class="sharethis-inline-share-buttons share--icons text-align-direction">
                                    </div>
                                </div>

                                <div class="cz">
                                    <div class="table-responsive __max-h-515px" data-simplebar>
                                        <div class="d-flex">
                                            <div id="sync2" class="owl-carousel owl-theme product-thumb-slider">
                                                @if($product->images!=null && json_decode($product->images)>0)
                                                    @if(json_decode($product->colors) && count($product->color_images_full_url)>0)
                                                        @foreach ($product->color_images_full_url as $key => $photo)
                                                            @if($photo['color'] != null)
                                                                <div class="">
                                                                    <a class="product-preview-thumb color-variants-preview-box-{{ $photo['color'] }} {{ $key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                       id="preview-img{{ $photo['color'] }}"
                                                                       href="#image{{ $photo['color'] }}">
                                                                        <img alt="{{ translate('product') }}"
                                                                             src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}">
                                                                    </a>
                                                                </div>
                                                            @else
                                                                <div class="">
                                                                    <a class="product-preview-thumb {{ $key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                       id="preview-img{{ $key}}" href="#image{{ $key}}">
                                                                        <img alt="{{ translate('product') }}"
                                                                             src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}">
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        @foreach ($product->images_full_url as $key => $photo)
                                                            <div class="">
                                                                <a class="product-preview-thumb {{ $key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                                   id="preview-img{{ $key}}" href="#image{{ $key}}">
                                                                    <img alt="{{ translate('product') }}"
                                                                         src="{{ getStorageImages(path: $photo, type: 'product') }}">
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 col-md-8 mt-md-0 mt-sm-3 web-direction">
                            <div class="details __h-100 product-cart-option-container">
                                <h2 class="mb-2 __inline-24">{{ $product->name }}</h2>
                                <div class="d-flex flex-wrap align-items-center mb-2 pro">
                                    <div class="star-rating me-2">
                                        @for($inc=1;$inc<=5;$inc++)
                                            @if ($inc <= (int)$overallRating[0])
                                                <i class="tio-star text-warning"></i>
                                            @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                <i class="tio-star-half text-warning"></i>
                                            @else
                                                <i class="tio-star-outlined text-warning"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span
                                        class="d-inline-block  align-middle mt-1 {{Session::get('direction') === "rtl" ? 'ml-md-2 ml-sm-0' : 'mr-md-2 mr-sm-0'}} fs-14 text-muted">({{ $overallRating[0] }})</span>
                                    <span
                                        class="font-regular font-for-tab d-inline-block font-size-sm text-body align-middle mt-1 {{Session::get('direction') === "rtl" ? 'mr-1 ml-md-2 ml-1 pr-md-2 pr-sm-1 pl-md-2 pl-sm-1' : 'ml-1 mr-md-2 mr-1 pl-md-2 pl-sm-1 pr-md-2 pr-sm-1'}}"><span
                                            class="web-text-primary">{{ $overallRating[1] }}</span> {{ translate('reviews') }}</span>
                                    <span class="__inline-25"></span>
                                    <span
                                        class="font-regular font-for-tab d-inline-block font-size-sm text-body align-middle mt-1 {{Session::get('direction') === "rtl" ? 'mr-1 ml-md-2 ml-1 pr-md-2 pr-sm-1 pl-md-2 pl-sm-1' : 'ml-1 mr-md-2 mr-1 pl-md-2 pl-sm-1 pr-md-2 pr-sm-1'}}"><span
                                            class="web-text-primary">{{ $countOrder}}</span> {{ translate('orders') }}   </span>
                                    <span class="__inline-25">    </span>
                                    <span
                                        class="font-regular font-for-tab d-inline-block font-size-sm text-body align-middle mt-1 {{Session::get('direction') === "rtl" ? 'mr-1 ml-md-2 ml-0 pr-md-2 pr-sm-1 pl-md-2 pl-sm-1' : 'ml-1 mr-md-2 mr-0 pl-md-2 pl-sm-1 pr-md-2 pr-sm-1'}} text-capitalize"> <span
                                            class="web-text-primary countWishlist-{{ $product->id }}"> {{ $countWishlist}}</span> {{ translate('wish_listed') }} </span>
                                </div>

                                @if($product['product_type'] == 'digital')
                                    <div class="digital-product-authors mb-2">
                                        @if(count($productPublishingHouseInfo['data']) > 0)
                                            <div class="d-flex align-items-center g-2 me-2">
                                                <span class="text-capitalize digital-product-author-title">{{ translate('Publishing_House') }} :</span>
                                                <div class="item-list">
                                                    @foreach($productPublishingHouseInfo['data'] as $publishingHouseName)
                                                        <a href="{{ route('products', ['publishing_house_id' => $publishingHouseName['id'], 'product_type' => 'digital', 'page'=>1]) }}"
                                                           class="text-base">
                                                            {{ $publishingHouseName['name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if(count($productAuthorsInfo['data']) > 0)
                                            <div class="d-flex align-items-center g-2 me-2">
                                                <span class="text-capitalize digital-product-author-title">{{ translate('Author') }} :</span>
                                                <div class="item-list">
                                                    @foreach($productAuthorsInfo['data'] as $productAuthor)
                                                        <a href="{{ route('products',['author_id' => $productAuthor['id'], 'product_type' => 'digital', 'page' => 1]) }}"
                                                           class="text-base">
                                                            {{ $productAuthor['name'] }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <form class="mb-2 addToCartDynamicForm add-to-cart-details-form">

                                    <div class="mb-3">
                                        <h3 class="font-weight-normal text-accent d-flex align-items-end gap-2 pt-1">
                                            <span class="discounted-unit-price fs-24 font-bold">
                                                {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                            </span>
                                            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                                <del
                                                    class="product-total-unit-price align-middle text-muted fs-18 font-semibold">
                                                    {{ webCurrencyConverter(amount: $product->unit_price) }}
                                                </del>
                                            @endif
                                        </h3>
                                    </div>

                                    @csrf
                                    <input type="hidden" name="id" value="{{ $product->id }}">
                                    <div
                                        class="position-relative {{Session::get('direction') === "rtl" ? 'ml-n4' : 'mr-n4'}} mb-2">
                                        @if (count(json_decode($product->colors)) > 0)
                                            <div class="flex-start align-items-center mb-2 gap-2">
                                                <div class="product-description-label m-0 text-dark font-bold">
                                                    {{ translate('color') }}:
                                                </div>
                                                <div>
                                                    <ul class="list-inline checkbox-color mb-0 flex-start ms-2 ps-0">
                                                        @foreach (json_decode($product->colors) as $key => $color)
                                                            <li>
                                                                <input type="radio"
                                                                       id="{{ str_replace(' ', '', ($product->id. '-color-'. str_replace('#','',$color))) }}"
                                                                       name="color" value="{{ $color }}"
                                                                       @if($key == 0) checked @endif>
                                                                <label style="background: {{ $color }};"
                                                                       class="focus-preview-image-by-color shadow-border"
                                                                       for="{{ str_replace(' ', '', ($product->id. '-color-'. str_replace('#','',$color))) }}"
                                                                       data-toggle="tooltip"
                                                                       data-key="{{ str_replace('#','',$color) }}"
                                                                       data-colorid="preview-box-{{ str_replace('#','',$color) }}"
                                                                       data-title="{{ getColorNameByCode(code: $color) }}">
                                                                    <span class="outline"></span></label>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif
                                        @php
                                            $qty = 0;
                                            if(!empty($product->variation)){
                                            foreach (json_decode($product->variation) as $key => $variation) {
                                                    $qty += $variation->qty;
                                                }
                                            }
                                        @endphp
                                    </div>

                                    @php($extensionIndex=0)
                                    @if($product['product_type'] == 'digital' && $product['digital_product_file_types'] && count($product['digital_product_file_types']) > 0 && $product['digital_product_extensions'])
                                        @foreach($product['digital_product_extensions'] as $extensionKey => $extensionGroup)
                                            <div class="row flex-start mx-0 align-items-center mb-1">
                                                <div
                                                    class="product-description-label text-dark font-bold {{Session::get('direction') === "rtl" ? 'pl-2' : 'pr-2'}} text-capitalize mb-2">
                                                    {{ translate($extensionKey) }} :
                                                </div>
                                                <div>
                                                    @if(count($extensionGroup) > 0)
                                                        <div
                                                            class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-0 mx-1 flex-start ps-0">
                                                            @foreach($extensionGroup as $index => $extension)
                                                                <div class="user-select-none">
                                                                    <div class="for-mobile-capacity">
                                                                        <input type="radio" hidden
                                                                               id="extension_{{ str_replace(' ', '-', $extension) }}"
                                                                               name="variant_key"
                                                                               value="{{ $extensionKey.'-'.preg_replace('/\s+/', '-', $extension) }}"
                                                                            {{ $extensionIndex == 0 ? 'checked' : ''}}>
                                                                        <label
                                                                            for="extension_{{ str_replace(' ', '-', $extension) }}"
                                                                            class="__text-12px">
                                                                            {{ $extension }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                @php($extensionIndex++)
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @foreach (json_decode($product->choice_options) as $key => $choice)
                                        <div class="row flex-start mx-0 align-items-center">
                                            <div
                                                class="product-description-label text-dark font-bold {{Session::get('direction') === "rtl" ? 'pl-2' : 'pr-2'}} text-capitalize mb-2">{{ $choice->title }}
                                                :
                                            </div>
                                            <div>
                                                <div
                                                    class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-0 mx-1 flex-start row ps-0">
                                                    @foreach ($choice->options as $index => $option)
                                                        <div class="user-select-none">
                                                            <div class="for-mobile-capacity">
                                                                <input type="radio"
                                                                       id="{{ str_replace(' ', '', ($choice->name. '-'. $option)) }}"
                                                                       name="{{ $choice->name }}" value="{{ $option }}"
                                                                       @if($index == 0) checked @endif >
                                                                <label class="__text-12px"
                                                                       for="{{ str_replace(' ', '', ($choice->name. '-'. $option)) }}">{{ $option }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-3">
                                        <div class="product-quantity d-flex flex-column __gap-15">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="product-description-label text-dark font-bold mt-0">
                                                    {{ translate('quantity') }} :
                                                </div>
                                                <div
                                                    class="d-flex justify-content-center align-items-center quantity-box border rounded border-base web-text-primary">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-number __p-10 web-text-primary"
                                                                type="button"
                                                                data-type="minus" data-field="quantity"
                                                                disabled="disabled">
                                                            -
                                                        </button>
                                                    </span>
                                                    <input type="text" name="quantity"
                                                           class="form-control input-number text-center product-details-cart-qty __inline-29 border-0 "
                                                           placeholder="{{ translate('1') }}"
                                                           value="{{ $product->minimum_order_qty ?? 1 }}"
                                                           data-producttype="{{ $product->product_type }}"
                                                           min="{{ $product->minimum_order_qty ?? 1 }}"
                                                           max="{{ $product['product_type'] == 'physical' ? $product->current_stock : 100}}">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-number __p-10 web-text-primary"
                                                                type="button"
                                                                data-producttype="{{ $product->product_type }}"
                                                                data-type="plus" data-field="quantity">
                                                                +
                                                        </button>
                                                    </span>
                                                </div>
                                                <input type="hidden" class="product-generated-variation-code"
                                                       name="product_variation_code"
                                                       data-product-id="{{ $product['id'] }}">
                                                <input type="hidden" value=""
                                                       class="product-exist-in-cart-list form-control w-50" name="key">
                                            </div>
                                            <div class="product-details-chosen-price-section">
                                                <div
                                                    class="d-none d-sm-flex justify-content-start align-items-center me-2">
                                                    <div
                                                        class="product-description-label text-dark font-bold text-capitalize">
                                                        <strong>{{ translate('total_price') }}</strong> :
                                                    </div>
                                                    &nbsp; <strong
                                                        class="text-base product-details-chosen-price-amount"></strong>
                                                    <small
                                                        class="ms-2 font-regular product-details-tax-amount-container">
                                                        (<small>{{ translate('tax') }} : </small>
                                                        <small class="product-details-tax-amount"></small>)
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="__btn-grp mt-2 mb-3 product-add-and-buy-section-parent">

                                        <?php


                                        $isTemporaryClose = false;
                                        $isVacationMode = false;
                                        $authorType = $product->added_by == 'admin' ? 'inhouse' : ($product->added_by == 'seller' ? 'vendor' : '');

                                        if ($authorType === 'vendor') {
                                            $isTemporaryClose = checkVendorAbility(type: $authorType, status: 'temporary_close', vendor: $product->seller->shop);
                                            $isVacationMode = !$isTemporaryClose ? checkVendorAbility(type: $authorType, status: 'vacation_status', vendor: $product->seller->shop) : false;
                                        } else {
                                            $isTemporaryClose = checkVendorAbility(type: $authorType, status: 'temporary_close');
                                            $isVacationMode = !$isTemporaryClose ? checkVendorAbility(type: $authorType, status: 'vacation_status') : false;
                                        }
                                        ?>

                                        <div
                                            class="product-add-and-buy-section gap-2 {!! $firstVariationQuantity <= 0 ? '' : 'd-flex' !!}"
                                            {!! $firstVariationQuantity <= 0 ? 'style="display: none;"' : '' !!}>

                                            @if($isTemporaryClose || $isVacationMode)
                                                <button class="btn btn-secondary" type="button" disabled>
                                                    {{ translate('buy_now') }}
                                                </button>
                                                <button class="btn btn--primary string-limit" type="button" disabled>
                                                    {{ translate('add_to_cart') }}
                                                </button>
                                            @else
                                                <button type="button"
                                                        class="btn btn-secondary element-center btn-gap-{{ Session::get('direction') === 'rtl' ? 'left' : 'right' }} product-buy-now-button"
                                                        data-form=".add-to-cart-details-form"
                                                        data-auth="{{ (getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check()) ? 'true' : 'false' }}"
                                                        data-route="{{ route('shop-cart') }}"
                                                >
                                                    <span class="string-limit">{{ translate('buy_now') }}</span>
                                                </button>
                                                <button
                                                    class="btn btn--primary element-center product-add-to-cart-button"
                                                    type="button"
                                                    data-form=".add-to-cart-details-form"
                                                    data-update="{{ translate('update_cart') }}"
                                                    data-add="{{ translate('add_to_cart') }}"
                                                >
                                                    <span class="string-limit">{{ translate('add_to_cart') }}</span>
                                                </button>
                                            @endif
                                        </div>


                                        @if(($product['product_type'] == 'physical'))
                                            <div
                                                class="product-restock-request-section collapse" {!! $firstVariationQuantity <= 0 ? 'style="display: block;"' : '' !!}>
                                                <button type="button"
                                                        class="btn request-restock-btn btn-outline-primary fw-semibold product-restock-request-button"
                                                        data-auth="{{ auth('customer')->check() }}"
                                                        data-form=".addToCartDynamicForm"
                                                        data-default="{{ translate('Request_Restock') }}"
                                                        data-requested="{{ translate('Request_Sent') }}"
                                                >
                                                    {{ translate('Request_Restock') }}
                                                </button>
                                            </div>
                                        @endif
                                        <button type="button" data-product-id="{{ $product['id'] }}"
                                                class="btn __text-18px border product-action-add-wishlist">
                                            <i class="fa {{($wishlistStatus == 1?'fa-heart':'fa-heart-o') }} wishlist_icon_{{ $product['id'] }} web-text-primary"
                                               aria-hidden="true"></i>
                                            <span
                                                class="fs-14 text-muted align-bottom countWishlist-{{ $product['id'] }}">{{ $countWishlist}}</span>
                                            <div class="wishlist-tooltip" x-placement="top">
                                                <div class="arrow"></div>
                                                <div class="inner">
                                                    <span class="add">{{ translate('added_to_wishlist') }}</span>
                                                    <span class="remove">{{ translate('removed_from_wishlist') }}</span>
                                                </div>
                                            </div>
                                        </button>

                                        @if($product->added_by == 'admin')
                                            @if(checkVendorAbility(type: 'inhouse', status: 'temporary_close') || checkVendorAbility(type: 'inhouse', status: 'vacation_status'))
                                                <div class="alert alert-danger mt-2" role="alert">
                                                    {{ translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now') }}
                                                </div>
                                            @endif
                                        @elseif($product->added_by == 'seller')
                                            @if(checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $product->seller->shop) || checkVendorAbility(type: 'vendor', status: 'vacation_status', vendor: $product->seller->shop))
                                                <div class="alert alert-danger mt-2" role="alert">
                                                    {{ translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now') }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rtl text-align-direction">
                        <div class="px-4 pb-3 mb-3 mr-0 mr-md-2 bg-white __review-overview __rounded-10 pt-3">
                            <ul class="nav nav-tabs nav--tabs d-flex justify-content-center mt-3"
                                role="tablist">
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link active" href="#overview"
                                        data-toggle="tab" role="tab">
                                        {{ translate('overview') }}
                                    </h2>
                                </li>
                                <li class="nav-item">
                                    <h2 class="nav-link __inline-27 tab_link" href="#reviews" data-toggle="tab"
                                        role="tab">
                                        {{ translate('reviews') }}
                                    </h2>
                                </li>
                            </ul>
                            <div class="tab-content px-lg-3">
                                <div class="tab-pane fade show active text-justify" id="overview"
                                     role="tabpanel">
                                    <div class="row pt-2 specification">

                                        @if($product->video_url != null && (str_contains($product->video_url, "youtube.com/embed/")))
                                            <div class="col-12 mb-4">
                                                <iframe width="420" height="315"
                                                        src="{{ $product->video_url}}">
                                                </iframe>
                                            </div>
                                        @endif
                                        @if ($product['details'])
                                            <div
                                                class="text-body col-lg-12 col-md-12 overflow-y-auto fs-13 text-justify details-text-justify rich-editor-html-content">
                                                {!! $product['details'] !!}
                                            </div>
                                        @endif

                                    </div>
                                    @if (!$product['details'] && ($product->video_url == null || !(str_contains($product->video_url, "youtube.com/embed/"))))
                                        <div>
                                            <div class="text-center text-capitalize py-5">
                                                <img class="mw-90"
                                                     src="{{ theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg') }}"
                                                     alt="">
                                                <p class="text-capitalize mt-2">
                                                    <small>{{ translate('product_details_not_found') }}
                                                        !</small>
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    @if(count($product->reviews)==0 && $productReviews->total() == 0)
                                        <div>
                                            <div class="text-center text-capitalize">
                                                <img class="mw-100"
                                                     src="{{ theme_asset(path: 'public/assets/front-end/img/icons/empty-review.svg') }}"
                                                     alt="">
                                                <p class="text-capitalize">
                                                    <small>{{ translate('No_review_given_yet') }}!</small>
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row pt-2 pb-3">
                                            <div class="col-lg-4 col-md-5 ">
                                                <div
                                                    class=" row d-flex justify-content-center align-items-center">
                                                    <div
                                                        class="col-12 d-flex justify-content-center align-items-center">
                                                        <h2 class="overall_review mb-2 __inline-28">
                                                            {{ $overallRating[0] }}
                                                        </h2>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-center align-items-center star-rating ">
                                                        @for($inc=1;$inc<=5;$inc++)
                                                            @if ($inc <= (int)$overallRating[0])
                                                                <i class="tio-star text-warning"></i>
                                                            @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                                <i class="tio-star-half text-warning"></i>
                                                            @else
                                                                <i class="tio-star-outlined text-warning"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <div
                                                        class="col-12 d-flex justify-content-center align-items-center mt-2">
                                                        <span class="text-center">
                                                            {{ $productReviews->total() }} {{ translate('ratings') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-8 col-md-7 pt-sm-3 pt-md-0">
                                                <div
                                                    class="d-flex align-items-center mb-2 font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle text-body">{{ translate('excellent') }}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress text-body __h-5px">
                                                            <div class="progress-bar web--bg-primary"
                                                                 role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[0] != 0) ? ($rating[0] / $overallRating[1]) * 100 : (0); ?>%;"
                                                                 aria-valuenow="60" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1 text-body">
                                                        <span
                                                            class=" {{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}} ">
                                                            {{ $rating[0] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle ">{{ translate('good') }}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[1] != 0) ? ($rating[1] / $overallRating[1]) * 100 : (0); ?>%; background-color: #a7e453;"
                                                                 aria-valuenow="27" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                                {{ $rating[1] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle ">{{ translate('average') }}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[2] != 0) ? ($rating[2] / $overallRating[1]) * 100 : (0); ?>%; background-color: #ffda75;"
                                                                 aria-valuenow="17" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                            {{ $rating[2] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center mb-2 text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt "><span
                                                            class="d-inline-block align-middle">{{ translate('below_Average') }}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[3] != 0) ? ($rating[3] / $overallRating[1]) * 100 : (0); ?>%; background-color: #fea569;"
                                                                 aria-valuenow="9" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                            {{ $rating[3] }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="d-flex align-items-center text-body font-size-sm">
                                                    <div
                                                        class="__rev-txt"><span
                                                            class="d-inline-block align-middle ">{{ translate('poor') }}</span>
                                                    </div>
                                                    <div class="w-0 flex-grow">
                                                        <div class="progress __h-5px">
                                                            <div class="progress-bar web--bg-primary" role="progressbar"
                                                                 style="width: <?php echo $widthRating = ($rating[4] != 0) ? ($rating[4] / $overallRating[1]) * 100 : (0); ?>%;"
                                                                 aria-valuenow="4" aria-valuemin="0"
                                                                 aria-valuemax="100"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <span
                                                            class="{{Session::get('direction') === "rtl" ? 'me-3 float-left' : 'ml-3 float-right'}}">
                                                                {{ $rating[4] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row pb-4 mb-3">
                                            <div class="__inline-30">
                                                <span
                                                    class="text-capitalize">{{ translate('Product_review') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row pb-4">
                                        <div class="col-12" id="product-review-list">
                                            @include('web-views.partials._product-reviews')
                                        </div>

                                        @if(count($product->reviews) > 2)
                                            <div class="col-12">
                                                <div
                                                    class="card-footer d-flex justify-content-center align-items-center">
                                                    <button class="btn text-white view_more_button web--bg-primary">
                                                        {{ translate('view_more') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    @php($companyReliability = getWebConfig('company_reliability'))
                    @if($companyReliability != null)
                        <div class="product-details-shipping-details">
                            @foreach ($companyReliability as $key=>$value)
                                @if ($value['status'] == 1 && !empty($value['title']))
                                    <div class="shipping-details-bottom-border">
                                        <div class="px-3 py-3">
                                            <img
                                                class="{{Session::get('direction') === "rtl" ? 'float-right ml-2' : 'mr-2'}} __img-20"
                                                src="{{ getStorageImages(path: imagePathProcessing(imageData: $value['image'],path: 'company-reliability'), type: 'source', source: 'public/assets/front-end/img'.'/'.$value['item'].'.png') }}"
                                                alt="">
                                            <span>{{ translate($value['title']) }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if(getWebConfig(name: 'business_mode')=='multi')
                        <div class="__inline-31">

                            @if($product->added_by=='seller')
                                @if(isset($product->seller->shop))
                                    <div class="row position-relative">
                                        <div class="col-12 position-relative">
                                            <a href="{{route('shopView',['slug'=> $product?->seller?->shop->slug]) }}"
                                               class="d-block">
                                                <div class="d-flex __seller-author align-items-center">
                                                    <div>
                                                        <img class="__img-60 img-circle" alt=""
                                                             src="{{ getStorageImages(path: $product?->seller?->shop->image_full_url, type: 'shop') }}">
                                                    </div>
                                                    <div
                                                        class="ms-2 w-0 flex-grow">
                                                        <h2 class="fs-15 mb-2">
                                                            {{ $product->seller->shop->name }}
                                                        </h2>
                                                        @if(checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $product->seller->shop))
                                                            <span class="badge-danger fs-12 px-3 rounded-full small">
                                                                <span>{{ translate('Temporary_OFF') }}</span>
                                                            </span>
                                                        @elseif(checkVendorAbility(type: 'vendor', status: 'vacation_status', vendor: $product->seller->shop))
                                                            <span class="badge-danger fs-12 px-3 rounded-full small">
                                                                <span>{{ translate('closed_Now') }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <div class="row d-flex justify-content-between">
                                                <div class="col-6 ">
                                                    <div
                                                        class="d-flex justify-content-center align-items-center rounded __h-79px hr-right-before">
                                                        <div class="text-center">
                                                            <img
                                                                src="{{ theme_asset(path: 'public/assets/front-end/img/rating.svg') }}"
                                                                class="mb-2" alt="">
                                                            <div class="__text-12px text-base">
                                                                <strong>{{ $totalReviews }}</strong>
                                                                {{ translate('reviews') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div
                                                        class="d-flex justify-content-center align-items-center rounded __h-79px">
                                                        <div class="text-center">
                                                            <img
                                                                src="{{ theme_asset(path: 'public/assets/front-end/img/products.svg') }}"
                                                                class="mb-2" alt="">
                                                            <div class="__text-12px text-base">
                                                                <strong>{{ $productsForReview->total() }}</strong>
                                                                {{ translate('products') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 position-static mt-3">
                                            <div class="chat_with_seller-buttons">
                                                @if (auth('customer')->id())
                                                    <button
                                                        class="btn w-100 d-block text-center web--bg-primary text-white"
                                                        data-toggle="modal"
                                                        data-target="#chatting_modal"
                                                        @if(checkVendorAbility(type: 'vendor', status: 'temporary_close', vendor: $product->seller->shop))
                                                            disabled
                                                        @endif
                                                    >
                                                        <img class="mb-1" alt=""
                                                             src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png') }}">
                                                        <span class="d-none d-sm-inline-block text-capitalize">
                                                        {{ translate('chat_with_vendor') }}
                                                    </span>
                                                    </button>
                                                @else
                                                    <a href="{{ route('customer.auth.login') }}"
                                                       class="btn w-100 d-block text-center web--bg-primary text-white">
                                                        <img
                                                            src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png') }}"
                                                            class="mb-1" alt="">
                                                        <span class="d-none d-sm-inline-block text-capitalize">
                                                        {{ translate('chat_with_vendor') }}
                                                    </span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="row position-relative d-flex justify-content-between">
                                    <div class="col-9">
                                        <a href="{{route('shopView',['slug' => getInHouseShopConfig(key:'slug')]) }}" class="row d-flex align-items-center">
                                            <div>
                                                <img class="__inline-32" alt=""
                                                     src="{{ getStorageImages(path: getInHouseShopConfig(key:'image_full_url'), type: 'shop') }}">
                                            </div>
                                            <div
                                                class="{{ session('direction') === "rtl" ? 'right' : 'ml-2' }} get-view-by-onclick"
                                                data-link="{{ route('shopView',['slug' => getInHouseShopConfig(key:'slug')]) }}">
                                                <h2 class="font-bold __text-16px mb-1">
                                                    {{ getInHouseShopConfig(key: 'name') }}
                                                </h2>
                                                @if(checkVendorAbility(type: 'inhouse', status: 'temporary_close'))
                                                    <span class="badge-danger fs-12 px-3 rounded-full small">
                                                        <span>{{ translate('Temporary_OFF') }}</span>
                                                    </span>
                                                @elseif(checkVendorAbility(type: 'inhouse', status: 'vacation_status'))
                                                    <span class="badge-danger fs-12 px-3 rounded-full small">
                                                        <span>{{ translate('closed_Now') }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="row d-flex justify-content-between">
                                            <div class="col-6 ">
                                                <div
                                                    class="d-flex justify-content-center align-items-center rounded __h-79px hr-right-before">
                                                    <div class="text-center">
                                                        <img
                                                            src="{{ theme_asset(path: 'public/assets/front-end/img/rating.svg') }}"
                                                            class="mb-2" alt="">
                                                        <div class="__text-12px text-base">
                                                            <strong>{{ $totalReviews}}</strong>
                                                            {{ translate('reviews') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div
                                                    class="d-flex justify-content-center align-items-center rounded __h-79px">
                                                    <div class="text-center">
                                                        <img
                                                            src="{{ theme_asset(path: 'public/assets/front-end/img/products.svg') }}"
                                                            class="mb-2" alt="">
                                                        <div class="__text-12px text-base">
                                                            <strong>{{ $productsForReview->total() }}</strong>
                                                            {{ translate('products') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 position-static mt-3">
                                        <div class="chat_with_seller-buttons">
                                            @if (auth('customer')->id())
                                                <button class="btn w-100 d-block text-center web--bg-primary text-white"
                                                        data-toggle="modal" data-target="#chatting_modal"
                                                    {{ checkVendorAbility(type: 'inhouse', status: 'temporary_close') ? 'disabled' : '' }}
                                                >
                                                    <img class="mb-1" alt=""
                                                         src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png') }}">
                                                    <span class="d-none d-sm-inline-block text-capitalize">
                                                        {{ translate('chat_with_vendor') }}
                                                </span>
                                                </button>
                                            @else
                                                <a href="{{ route('shopView', ['slug' => getInHouseShopConfig(key:'slug')]) }}"
                                                   class="btn w-100 d-block text-center web--bg-primary text-white">
                                                    <img class="mb-1" alt=""
                                                         src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png') }}">
                                                    <span class="d-none d-sm-inline-block text-capitalize">
                                                        {{ translate('chat_with_vendor') }}
                                                    </span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="pt-4 pb-3">
                        <h2 class=" __text-16px font-bold text-capitalize">
                            @if(getWebConfig(name: 'business_mode') == 'multi')
                                {{ translate('more_from_the_store') }}
                            @else
                                {{ translate('you_may_also_like') }}
                            @endif
                        </h2>
                    </div>
                    <div>
                        @foreach($moreProductFromSeller as $item)
                            @include('web-views.partials._seller-products-product-details',['product' => $item, 'decimal_point_settings' => $decimalPointSettings])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if (count($relatedProducts)>0)
            <div class="container rtl text-align-direction">
                <div class="card __card border-0 mb-4">
                    <div class="card-body">
                        <div class="row flex-between align-items-center mb-2">
                            <div class="ms-1">
                                <h2 class="text-capitalize font-bold fs-16 mb-0">{{ translate('similar_products') }}</h2>
                            </div>
                            <div class="view_all d-flex justify-content-center align-items-center">
                                <div>
                                    @if($product['category_id'])
                                        <a class="text-capitalize view-all-text web-text-primary me-1"
                                           href="{{route('products',['category_id'=> $product['category_id'],'data_from'=>'category','page'=>1]) }}">{{ translate('view_all') }}
                                            <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 ' : 'right ml-1 mr-n1'}}"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            @foreach($relatedProducts as $key => $relatedProduct)
                                <div class="col-xl-2 col-sm-3 col-6">
                                    @include('web-views.partials._inline-single-product-without-eye',['product'=>$relatedProduct,'decimal_point_settings'=>$decimalPointSettings])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade rtl text-align-direction" id="show-modal-view" tabindex="-1" role="dialog"
             aria-labelledby="show-modal-image"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body flex justify-content-center">
                        <button class="btn btn-default __inline-33 dir-end-minus-7px"
                                data-dismiss="modal">
                            <i class="fa fa-close"></i>
                        </button>
                        <img class="element-center" id="attachment-view" src="" alt="">
                    </div>
                </div>
            </div>
        </div>

    </div>

    @include("web-views.products._product-details-sticky", ['productDetails' => $product])

    @if($product?->preview_file_full_url['path'])
        @include('web-views.partials._product-preview-modal', ['previewFileInfo' => $previewFileInfo])
    @endif

    @include('layouts.front-end.partials.modal._chatting',['seller'=>$product->seller, 'user_type'=>$product->added_by])

    <span id="route-review-list-product" data-url="{{ route('review-list-product') }}"></span>
    <span id="products-details-page-data" data-id="{{ $product['id'] }}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/product-details.js') }}"></script>
    <script type="text/javascript" async="async"
            src="https://platform-api.sharethis.com/js/sharethis.js#property=5f55f75bde227f0012147049&product=sticky-share-buttons"></script>
@endpush
