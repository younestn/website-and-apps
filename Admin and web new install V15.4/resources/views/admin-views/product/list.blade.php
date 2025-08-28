@extends('layouts.admin.app')

@section('title', translate('product_List'))

@section('content')
    <div class="content container-fluid">

        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/inhouse-product-list.png') }}" alt="">
                @if ($type == 'in_house')
                    {{ translate('in_House_Product_List') }}
                @elseif($type == 'seller')
                    {{ translate('vendor_Product_List') }}
                @endif
                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $products->total() }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="GET">
                    <input type="hidden" value="{{ request('request_status') }}" name="request_status">
                    <input type="hidden" value="{{ request('status') }}" name="status">
                    <div class="row g-2">
                        <div class="col-12">
                            <h3 class="mb-3">{{ translate('filter_Products') }}</h3>
                        </div>
                        @if (request('type') == 'seller')
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label class="form-label" for="store">{{ translate('store') }}</label>
                                    <select name="seller_id" class="custom-select" data-placeholder="Select from dropdown">
                                        <option></option>
                                        <option value="" selected>{{ translate('all_store') }}</option>
                                        @foreach ($sellers as $seller)
                                            <option
                                                value="{{ $seller->id }}"{{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                                {{ $seller->shop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label class="form-label" for="store">{{ translate('brand') }}</label>
                                <select name="brand_id" class="custom-select" data-placeholder="Select from dropdown">
                                    <option></option>
                                    <option value="" selected>{{ translate('all_brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->default_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="form-label">{{ translate('category') }}</label>
                                <select class="custom-select action-get-request-onchange"
                                    data-placeholder="Select from dropdown" name="category_id"
                                    data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                    data-element-id="sub-category-select" data-element-type="select">
                                    <option value="{{ old('category_id') }}" selected disabled>
                                        {{ translate('select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}"
                                            {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                                            {{ $category['defaultName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="form-label">{{ translate('sub_Category') }}</label>
                                <select class="custom-select action-get-request-onchange"
                                    data-placeholder="Select from dropdown" name="sub_category_id" id="sub-category-select"
                                    data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                    data-element-id="sub-sub-category-select" data-element-type="select">
                                    <option disabled {{ request('sub_category_id') ? '' : 'selected' }}>
                                        {{ translate('select_Sub_Category') }}
                                    </option>
                                    @foreach ($subCategories as $subCategoryItem)
                                        <option value="{{ $subCategoryItem['id'] }}"
                                            {{ request('sub_category_id') == $subCategoryItem['id'] ? 'selected' : '' }}>
                                            {{ $subCategoryItem['defaultName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="form-label">{{ translate('sub_Sub_Category') }}</label>
                                <select class="custom-select" data-placeholder="Select from dropdown"
                                    name="sub_sub_category_id" id="sub-sub-category-select">
                                    <option
                                        value="{{ request('sub_sub_category_id') != null ? request('sub_sub_category_id') : null }}"
                                        selected {{ request('sub_sub_category_id') != null ? '' : 'disabled' }}>
                                        {{ request('sub_sub_category_id') != null ? $subSubCategory['defaultName'] : translate('select_Sub_Sub_Category') }}
                                    </option>
                                    @foreach ($subSubCategories as $subSubCategoryItem)
                                        <option value="{{ $subSubCategoryItem['id'] }}"
                                            {{ request('sub_sub_category_id') == $subSubCategoryItem['id'] ? 'selected' : '' }}>
                                            {{ $subSubCategoryItem['defaultName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-end mt-4">
                                <a href="{{ route('admin.products.list', ['type' => request('type')]) }}"
                                    class="btn btn-secondary px-4">
                                    {{ translate('reset') }}
                                </a>
                                <button type="submit" class="btn btn-primary px-4 action-get-element-type">
                                    {{ translate('show_data') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="row align-items-center">
                            <div class="col-lg-4">

                                <div class="flex-grow-1 max-w-280">
                                    <form action="{{ url()->current() }}" method="get">
                                          <input type="hidden" value="{{ request('request_status') }}" name="request_status">
                                        <input type="hidden" value="{{ request('status') }}" name="status">
                                        <div class="input-group">
                                            <input id="datatableSearch_" type="search" name="searchValue"
                                                class="form-control"
                                                placeholder="{{ translate('search_by_Product_Name') }}"
                                                aria-label="Search orders" value="{{ request('searchValue') }}">
                                            <div class="input-group-append search-submit">
                                                <button type="submit">
                                                    <i class="fi fi-rr-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-8 mt-3 mt-lg-0 d-flex flex-wrap gap-3 justify-content-lg-end">
                                <div class="dropdown">
                                    <a type="button" class="btn btn-outline-primary text-nowrap"
                                        href="{{ route('admin.products.export-excel', ['type' => request('type')]) }}?&request_status={{ request('request_status') }}&brand_id={{ request('brand_id') }}&searchValue={{ request('searchValue') }}&category_id={{ request('category_id') }}&sub_category_id={{ request('sub_category_id') }}&sub_sub_category_id={{ request('sub_sub_category_id') }}&seller_id={{ request('seller_id') }}&status={{ request('status') }}">
                                        <img width="14"
                                            src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/excel.png') }}"
                                            class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                                @if ($type == 'in_house')
                                    <a href="{{ route('admin.products.stock-limit-list', ['in_house']) }}"
                                        class="btn btn-info text-white">
                                        <span class="text">{{ translate('limited_Stocks') }}</span>
                                    </a>
                                    <a href="{{ route('admin.products.add') }}" class="btn btn-primary">
                                        <i class="fi fi-sr-add"></i>
                                        <span class="text">{{ translate('add_new_product') }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                            class="table table-hover table-borderless table-thead-bordered align-middle">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('product Name') }}</th>
                                    <th class="text-center">{{ translate('product Type') }}</th>
                                    <th class="text-center">{{ translate('unit_price') }}</th>
                                    <th class="text-center">{{ translate('show_as_featured') }}</th>
                                    <th class="text-center">{{ translate('active_status') }}</th>
                                    <th class="text-center">{{ translate('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $key => $product)
                                    <tr>
                                        <th scope="row">{{ $products->firstItem() + $key }}</th>
                                        <td>
                                            <a href="{{ route('admin.products.view', ['addedBy' => $product['added_by'] == 'seller' ? 'vendor' : 'in-house', 'id' => $product['id']]) }}"
                                                class="media align-items-center gap-2">
                                                <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product') }}"
                                                    class="avatar border object-fit-cover" alt="">
                                                <div>
                                                    <div class="media-body text-dark text-hover-primary">
                                                        {{ Str::limit($product['name'], 20) }}
                                                    </div>
                                                    @if ($product?->clearanceSale)
                                                        <div class="badge text-bg-warning badge-warning user-select-none">
                                                            {{ translate('Clearance_Sale') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ translate(str_replace('_', ' ', $product['product_type'])) }}
                                        </td>
                                        <td class="text-center">
                                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $product['unit_price']), currencyCode: getCurrencyCode()) }}
                                        </td>
                                        <td class="text-center">

                                            @php($productName = str_replace("'", '`', $product['name']))
                                            <form action="{{ route('admin.products.featured-status') }}" method="post"
                                                id="product-featured-{{ $product['id'] }}-form"
                                                class="admin-product-status-form">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product['id'] }}">
                                                <label class="switcher mx-auto"
                                                    for="products-featured-update-{{ $product['id'] }}">
                                                    <input class="switcher_input custom-modal-plugin" type="checkbox"
                                                        value="1" name="status"
                                                        id="products-featured-update-{{ $product['id'] }}"
                                                        {{ $product['featured'] == 1 ? 'checked' : '' }}
                                                        data-modal-type="input-change-form"
                                                        data-modal-form="#product-featured-{{ $product['id'] }}-form"
                                                        data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-on.png') }}"
                                                        data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-off.png') }}"
                                                        data-on-title="{{ translate('Want_to_Add') . ' ' . $productName . ' ' . translate('to_the_featured_section') }}"
                                                        data-off-title="{{ translate('Want_to_Remove') . ' ' . $productName . ' ' . translate('to_the_featured_section') }}"
                                                        data-on-message="<p>{{ translate('if_enabled_this_product_will_be_shown_in_the_featured_product_on_the_website_and_customer_app') }}</p>"
                                                        data-off-message="<p>{{ translate('if_disabled_this_product_will_be_removed_from_the_featured_product_section_of_the_website_and_customer_app') }}</p>">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </form>

                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.products.status-update') }}" method="post"
                                                id="product-status{{ $product['id'] }}-form"
                                                class="admin-product-status-form">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product['id'] }}">
                                                <label class="switcher mx-auto"
                                                    for="products-status-update-{{ $product['id'] }}">
                                                    <input class="switcher_input custom-modal-plugin" type="checkbox"
                                                        value="1" name="status"
                                                        id="products-status-update-{{ $product['id'] }}"
                                                        {{ $product['status'] == 1 ? 'checked' : '' }}
                                                        data-modal-type="input-change-form"
                                                        data-modal-form="#product-status{{ $product['id'] }}-form"
                                                        data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-on.png') }}"
                                                        data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/product-status-off.png') }}"
                                                        data-on-title="{{ translate('Want_to_Turn_ON') . ' ' . $productName . ' ' . translate('status') }}"
                                                        data-off-title="{{ translate('Want_to_Turn_OFF') . ' ' . $productName . ' ' . translate('status') }}"
                                                        data-on-message="<p>{{ translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app') }}</p>"
                                                        data-off-message="<p>{{ translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app') }}</p>">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info icon-btn"
                                                    title="{{ translate('barcode') }}"
                                                    href="{{ route('admin.products.barcode', [$product['id']]) }}">
                                                    <i class="fi fi-sr-barcode"></i>
                                                </a>
                                                <a class="btn btn-outline-info icon-btn" title="View"
                                                    href="{{ route('admin.products.view', ['addedBy' => $product['added_by'] == 'seller' ? 'vendor' : 'in-house', 'id' => $product['id']]) }}">
                                                    <i class="fi fi-sr-eye"></i>
                                                </a>
                                                <a class="btn btn-outline-primary icon-btn"
                                                    title="{{ translate('edit') }}"
                                                    href="{{ route('admin.products.update', [$product['id']]) }}">
                                                    <i class="fi fi-sr-pencil"></i>
                                                </a>
                                                <span class="btn btn-outline-danger icon-btn delete-data"
                                                    title="{{ translate('delete') }}"
                                                    data-id="product-{{ $product['id'] }}">
                                                    <i class="fi fi-rr-trash"></i>
                                                </span>
                                            </div>
                                            <form action="{{ route('admin.products.delete', [$product['id']]) }}"
                                                method="post" id="product-{{ $product['id'] }}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $products->links() }}
                        </div>
                    </div>

                    @if (count($products) == 0)
                        @include(
                            'layouts.admin.partials._empty-state',
                            ['text' => 'no_product_found'],
                            ['image' => 'default']
                        )
                    @endif
                </div>
            </div>
        </div>
    </div>
    <span id="message-select-word" data-text="{{ translate('select') }}"></span>
@endsection
