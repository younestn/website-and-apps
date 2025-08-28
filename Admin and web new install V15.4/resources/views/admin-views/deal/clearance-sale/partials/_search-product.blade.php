@if (count($products) > 0)
    @foreach ($products as $key => $product)
        <div class="col-lg-12 select-clearance-product-item">
            <div>
                <div
                    class="media gap-3 p-3 rounded cursor-pointer justify-content-between align-items-center flex-wrap flex-xl-nowrap"
                    data-id="1">
                    <div class="d-flex align-items-center gap-3">
                        <img class="avatar avatar-xl border rounded border" width="75"
                             src="{{ getStorageImages(path:$product->thumbnail_full_url , type: 'backend-basic') }}"
                             alt="">
                        <div class="media-body d-flex flex-column gap-1">
                            <h6 class="product-id" hidden>{{$product['id']}}</h6>
                            <h4 class="mb-1 product-name line-1 text-start">
                                {{ $product['name'] }}
                            </h4>
                            <div class="fs-12 text-dark gap-1">
                                <div class="border-between wrap">
                                    <span class="parent">
                                        <span class="opacity-75">
                                            {{ translate('Price') }}:
                                        </span>
                                        <strong>{{ webCurrencyConverter(amount:
                                            $product->unit_price-(getProductDiscount(product: $product, price: $product->unit_price))) }}
                                        </strong>
                                         @if($product->discount > 0)
                                            <del class="opacity-75">
                                            {{ webCurrencyConverter(amount: $product->unit_price) }}
                                        </del>
                                        @endif
                                    </span>
                                    @if($product->product_type != 'digital')
                                        <span class="parent">
                                            <span class="opacity-75">
                                                {{ translate('Stock') }}:
                                            </span>
                                            {{ $product->current_stock }}
                                        </span>
                                    @endif
                                </div>
                                <div class="border-between wrap">
                                    <span class="parent"><span class="opacity-75">
                                        {{ translate('Category') }} :
                                    </span>
                                        {{ isset($product->category) ? $product->category->name : translate('category_not_found') }}
                                    </span>
                                    <span class="parent">
                                        @if($product?->product_type !== 'digital')
                                            <span class="opacity-75"> {{ translate('Brand') }}:</span>
                                                {{ isset($product->brand) ? $product?->brand?->name : translate('brands_not_found') }}
                                            </span>
                                        @endif

                                        <span class="opacity-75">
                                            {{ translate('shop') }}:
                                        </span><span class="parent text-primary">
                                        {{ isset($product->seller) ? (($product->added_by === 'admin') ? getInHouseShopConfig(key: 'name') : $product->seller->shop->name) : translate('shop_not_found') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="text-center p-4">
        <img class="mb-3 w-60" src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-state-icon/default.png')}}"
             alt="{{ translate('image_description')}}">
        <p class="mb-0">{{ translate('no_product_found')}}</p>
    </div>
@endif
