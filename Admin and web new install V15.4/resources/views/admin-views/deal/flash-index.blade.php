@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.admin.app')

@section('title', translate('flash_Deal'))

@section('content')
    @php($direction = Session::get('direction'))
    <div class="content container-fluid">
        <div class="d-flex justify-content-between gap-2 mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/flash_deal.png') }}" alt="">
                {{ translate('flash_deals') }}
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#prioritySetModal">
                <span data-bs-toggle="tooltip"
                    data-bs-title="Now you can set priority of products.">{{ translate('product_priority_Setup') }}</span>
            </button>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.deal.flash') }}" method="post"
                            class="text-start onsubmit-disable-action-button" enctype="multipart/form-data">
                            @csrf
                            @php($language = getWebConfig(name: 'pnc_language'))
                            @php($defaultLanguage = $language[0])
                            <div class="position-relative nav--tab-wrapper mb-4">
                                <ul class="nav nav-pills nav--tab lang_tab" id="pills-tab" role="tablist">
                                    @foreach ($language as $lang)
                                        <li class="nav-item px-0" role="presentation">
                                            <a class="nav-link px-2  text-capitalize {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                                id="{{ $lang }}-link" data-bs-toggle="pill"
                                                href="#{{ $lang }}-form" role="tab" aria-selected="true">
                                                {{ ucfirst(getLanguageName($lang)) . '(' . strtoupper($lang) . ')' }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="nav--tab__prev">
                                    <button class="btn btn-circle border-0 bg-white text-primary">
                                        <i class="fi fi-sr-angle-left"></i>
                                    </button>
                                </div>
                                <div class="nav--tab__next">
                                    <button class="btn btn-circle border-0 bg-white text-primary">
                                        <i class="fi fi-sr-angle-right"></i>
                                    </button>
                                </div>

                            </div>
                            <div class="row g-4 mb-4">
                                <div class="col-lg-6">
                                    <div class="h-100">
                                        <div class="tab-content mb-4" id="pills-tabContent">
                                            @foreach ($language as $lang)
                                                <div class="tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}"
                                                    id="{{ $lang }}-form" role="tabpanel"
                                                    aria-labelledby="{{ $lang }}-form-tab">
                                                    <input type="text" name="deal_type" value="flash_deal"
                                                        class="d-none">
                                                    <div class="form-group">
                                                        <label for="name" class="form-label">{{ translate('title') }}
                                                            ({{ strtoupper($lang) }})
                                                        </label>
                                                        <input type="text" name="title[]" class="form-control"
                                                            id="title"
                                                            placeholder="{{ translate('ex') . ':' . translate('LUX') }}"
                                                            {{ $lang == $defaultLanguage ? 'required' : '' }} data-maxlength="100">
                                                        <div class="d-flex justify-content-end">
                                                            <span class="text-body-light">{{ '0/100' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}"
                                                    id="lang">
                                            @endforeach

                                        </div>
                                        <div class="form-group mb-4">
                                            <label for="name" class="form-label">{{ translate('start_date') }}</label>
                                            <input type="date" name="start_date" id="start-date-time" min="{{ date('Y-m-d') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="name" class="form-label">{{ translate('end_date') }}</label>
                                            <input type="date" name="end_date" id="end-date-time" min="{{ date('Y-m-d') }}" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div
                                        class="d-flex justify-content-center align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                        <div class="d-flex flex-column gap-30 w-100">
                                            <div class="text-center">
                                                <label for="" class="form-label fw-semibold mb-0">
                                                    {{ translate('upload_image') }}
                                                    <span class="text-info-dark">( {{ translate('ratio') . ' ' . '5:1' }}
                                                        )</span>
                                                </label>
                                            </div>
                                            <div class="upload-file">
                                                <input type="file" name="image" id="custom-file-upload"
                                                    class="upload-file__input single_file_input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                    value="" required>
                                                <label class="upload-file__wrapper ratio-5-1">
                                                    <div class="upload-file-textbox text-center">
                                                        <img width="34" height="34" class="svg"
                                                            src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}"
                                                            alt="image upload">
                                                        <h6 class="mt-1 fw-medium lh-base text-center">
                                                            <span
                                                                class="text-info">{{ translate('Click to upload') }}</span>
                                                            <br>
                                                            {{ translate('or drag and drop') }}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy" src=""
                                                        data-default-src="" alt="{{ translate('banner_image') }}">
                                                </label>
                                                <div class="overlay">
                                                    <div
                                                        class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                        <button type="button"
                                                            class="btn btn-outline-info icon-btn view_btn">
                                                            <i class="fi fi-sr-eye"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-outline-info icon-btn edit_btn">
                                                            <i class="fi fi-rr-camera"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" id="reset"
                                    class="btn btn-secondary px-4">{{ translate('reset') }}</button>
                                <button type="submit" class="btn btn-primary px-4">{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4 d-flex justify-content-between align-items-center gap-20 flex-wrap">
                        <h3 class="mb-0 text-capitalize d-flex gap-2">
                            {{ translate('flash_deal_table') }}
                            <span
                                class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $flashDeals->total() }}</span>
                        </h3>
                        <div class="flex-grow-1 max-w-280">
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group">
                                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                        placeholder="{{ translate('search_by_Title') }}" aria-label="Search orders"
                                        value="{{ request('searchValue') }}">
                                    <span id="clearBtn"
                                        style="position: absolute; top: 50%; right: 3rem; transform: translateY(-50%);  cursor: pointer;  font-size: 1.25rem; font-weight: bold; color: #555;">
                                        &times;
                                    </span>

                                    <div class="input-group-append search-submit">
                                        <button type="submit">
                                            <i class="fi fi-rr-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable" style="text-align: {{ $direction === 'rtl' ? 'right' : 'left' }};"
                            class="table table-hover table-borderless table-thead-bordered align-middle">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('title') }}</th>
                                    <th>{{ translate('duration') }}</th>
                                    <th>{{ translate('status') }}</th>
                                    <th class="text-center">{{ translate('active_products') }}</th>
                                    <th class="text-center">{{ translate('publish') }}</th>
                                    <th class="text-center">{{ translate('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($flashDeals as $key => $deal)
                                    <tr>
                                        <td>{{ $flashDeals->firstItem() + $key }}</td>
                                        <td><span class="font-weight-semibold">{{ $deal['title'] }}</span></td>
                                        <td>{{ date('d-M-y', strtotime($deal['start_date'])) . '-' . ' ' }}
                                            {{ date('d-M-y', strtotime($deal['end_date'])) }}</td>
                                        <td>
                                            @if (Carbon::parse($deal['end_date'])->endOfDay()->isPast())
                                                <span class="badge text-bg-danger badge-danger">{{ translate('expired') }}
                                                </span>
                                            @else
                                                <span class="badge text-bg-success badge-success">
                                                    {{ translate('active') }} </span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $deal->products_count }}</td>
                                            <?php
                                            $isExpired = \Carbon\Carbon::parse($deal['end_date'])->endOfDay()->isPast();
                                            ?>
                                        <td>
                                            <form action="{{ route('admin.deal.status-update') }}" method="post"
                                                id="flash-deal-status{{ $deal['id'] }}-form" >
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $deal['id'] }}">
                                                <label class="switcher mx-auto"
                                                    for="flash-deal-status{{ $deal['id'] }}"  @if($isExpired) data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="{{ translate('This_deal_has_expired_and_cannot_be_updated.') }}"
                                                    @endif>
                                                    <input class="switcher_input custom-modal-plugin" type="checkbox"
                                                        value="1" name="status"
                                                        id="flash-deal-status{{ $deal['id'] }}"
                                                        {{ $deal['status'] == 1 ? 'checked' : '' }}
                                                           {{ $isExpired ? 'disabled' : '' }}
                                                        data-modal-type="input-change-form"
                                                        data-modal-form="#flash-deal-status{{ $deal['id'] }}-form"
                                                        data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/flash-deal-status-on.png') }}"
                                                        data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/flash-deal-status-off.png') }}"
                                                        data-on-title = "{{ translate('Want_to_Turn_ON_Flash_Deal_Status') . '?' }}"
                                                        data-off-title = "{{ translate('Want_to_Turn_OFF_Flash_Deal_Status') . '?' }}"
                                                        data-on-message = "<p>{{ translate('if_enabled_this_flash_sale_will_be_available_on_the_website_and_customer_app') }}</p>"
                                                        data-off-message = "<p>{{ translate('if_disabled_this_flash_sale_will_be_hidden_from_the_user_website_and_customer_app') }}</p>"
                                                        data-on-button-text="{{ translate('turn_on') }}"
                                                        data-off-button-text="{{ translate('turn_off') }}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-10">
                                                <a class="h-30 d-flex gap-2 text-capitalize align-items-center btn btn-outline-info"
                                                    href="{{ route('admin.deal.add-product', [$deal['id']]) }}">
                                                    <i class="fi fi-sr-plus fs-10"></i>
                                                    {{ translate('add_product') }}
                                                </a>
                                                <a title="{{ translate('edit') }}"
                                                    href="{{ route('admin.deal.update', [$deal['id']]) }}"
                                                    class="btn btn-outline-primary icon-btn edit">
                                                    <i class="fi fi-sr-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $flashDeals->links() }}
                        </div>
                    </div>

                    @if (count($flashDeals) == 0)
                        @include(
                            'layouts.admin.partials._empty-state',
                            ['text' => 'no_data_found'],
                            ['image' => 'default']
                        )
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="prioritySetModal" tabindex="-1" aria-labelledby="prioritySetModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.business-settings.priority-setup.update-by-type') }}" method="post">
                    @csrf
                    <input type="hidden" name="type" value="flash_deal_priority">
                    <div class="modal-body px-sm-4 mb-sm-3">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h4 class="modal-title flex-grow-1 text-center text-capitalize" id="prioritySetModalLabel">
                                {{ translate('priority_settings') }}</h4>
                            <button type="button" class="btn-close border-0 btn-circle bg-section2 shadow-none"
                                data-bs-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="d-flex gap-4 flex-column flash-deal">
                            <div class="d-flex gap-2 justify-content-between pb-3 border-bottom">
                                <div class="d-flex flex-column">
                                    <h3 class="text-capitalize">{{ translate('use_default_sorting_list') }}</h3>
                                    <div class="d-flex gap-2 align-items-center">
                                        <img width="14"
                                            src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}"
                                            alt="">
                                        <span
                                            class="text-dark fs-12">{{ translate('currently_sorting_this_section_based_on_first_created_products') }}</span>
                                    </div>
                                </div>
                                <label class="switcher">
                                    <input type="checkbox" class="switcher_input switcher-input-js"
                                        data-parent-class="flash-deal" data-from="default-sorting"
                                        {{ $flashDealPriority?->custom_sorting_status == 1 ? '' : 'checked' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                            <div class="">
                                <div class="d-flex gap-2 justify-content-between">
                                    <div class="d-flex flex-column">
                                        <h4 class="text-capitalize">{{ translate('use_custom_sorting_list') }}</h4>
                                        <div class="d-flex gap-2 align-items-center">
                                            <img width="14"
                                                src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}"
                                                alt="">
                                            <span
                                                class="text-dark fs-12">{{ translate('you_can_sorting_this_section_by_others_way') }}</span>
                                        </div>
                                    </div>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input switcher-input-js"
                                            name="custom_sorting_status" value="1" data-parent-class="flash-deal"
                                            data-from="custom-sorting"
                                            {{ isset($flashDealPriority?->custom_sorting_status) && $flashDealPriority?->custom_sorting_status == 1 ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>

                                <div
                                    class="custom-sorting-radio-list {{ isset($flashDealPriority?->custom_sorting_status) && $flashDealPriority?->custom_sorting_status == 1 ? '' : 'd--none' }}">
                                    <div class="border rounded p-3 d-flex flex-column gap-2 mt-4">

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" value="latest_created"
                                                id="flash-deal-sort-by-latest-created"
                                                {{ isset($flashDealPriority?->sort_by) && $flashDealPriority?->sort_by == 'latest_created' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-sort-by-latest-created">
                                                {{ translate('sort_by_latest_created') }}
                                            </label>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" value="first_created"
                                                id="flash-deal-sort-by-first-created"
                                                {{ isset($flashDealPriority?->sort_by) && $flashDealPriority?->sort_by == 'first_created' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-sort-by-first-created">
                                                {{ translate('sort_by_first_created') }}
                                            </label>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" value="most_order" id="flash-deal-sort-by-most-order"
                                                {{ isset($flashDealPriority?->sort_by) ? ($flashDealPriority?->sort_by == 'most_order' ? 'checked' : '') : 'checked' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-sort-by-most-order">
                                                {{ translate('sort_by_most_order') }}
                                            </label>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" id="flash-deal-sort-by-reviews-count"
                                                value="reviews_count"
                                                {{ isset($flashDealPriority?->sort_by) && $flashDealPriority?->sort_by == 'reviews_count' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-sort-by-reviews-count">
                                                {{ translate('sort_by_reviews_count') }}
                                            </label>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" id="flash-deal-sort-by-ratings" value="rating"
                                                {{ isset($flashDealPriority?->sort_by) && $flashDealPriority?->sort_by == 'rating' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-sort-by-ratings">
                                                {{ translate('sort_by_average_ratings') }}
                                            </label>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" value="a_to_z" id="flash-deal-alphabetic-order"
                                                {{ isset($flashDealPriority?->sort_by) && $flashDealPriority?->sort_by == 'a_to_z' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer text-capitalize"
                                                for="flash-deal-alphabetic-order">
                                                {{ translate('sort_by_Alphabetical') }}
                                                ({{ 'A ' . translate('to') . ' Z' }})
                                            </label>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" class="show form-check-input radio--input"
                                                name="sort_by" value="z_to_a" id="flash-deal-alphabetic-order-reverse"
                                                {{ isset($flashDealPriority?->sort_by) && $flashDealPriority?->sort_by == 'z_to_a' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer text-capitalize"
                                                for="flash-deal-alphabetic-order-reverse">
                                                {{ translate('sort_by_Alphabetical') }}
                                                ({{ 'Z ' . translate('to') . ' A' }})
                                            </label>
                                        </div>

                                    </div>

                                    <div class="border rounded p-3 d-flex flex-column gap-2 mt-3">
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" name="out_of_stock_product" value="desc"
                                                class="check-box form-check-input radio--input"
                                                data-parent-class="flash-deal" id="show-in-last"
                                                {{ isset($flashDealPriority?->out_of_stock_product) && $flashDealPriority?->out_of_stock_product == 'desc' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="show-in-last">
                                                {{ translate('show_stock_out_products_in_the_last') }}
                                            </label>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" name="out_of_stock_product" value="hide"
                                                class="check-box form-check-input radio--input"
                                                data-parent-class="flash-deal" id="remove-product"
                                                {{ isset($flashDealPriority?->out_of_stock_product) && $flashDealPriority?->out_of_stock_product == 'hide' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="remove-product">
                                                {{ translate('remove_stock_out_products_from_the_list') }}
                                            </label>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" name="out_of_stock_product" value="default"
                                                data-parent-class="flash-deal" id="default"
                                                class="form-check-input radio--input"
                                                {{ isset($flashDealPriority?->out_of_stock_product) ? ($flashDealPriority?->out_of_stock_product == 'default' ? 'checked' : '') : 'checked' }}>
                                            <label class="mb-0 cursor-pointer" for="default">
                                                {{ translate('none') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="border rounded p-3 d-flex flex-column gap-2 mt-3">
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" name="temporary_close_sorting" value="desc"
                                                data-parent-class="flash-deal" id="flash-deal-temporary-close-last"
                                                class="form-check-input radio--input"
                                                {{ isset($flashDealPriority?->temporary_close_sorting) && $flashDealPriority?->temporary_close_sorting == 'desc' ? 'checked' : '' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-temporary-close-last">
                                                {{ translate('show_product_in_the_last_is_store_is_temporarily_off') }}
                                            </label>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" name="temporary_close_sorting" value="hide"
                                                data-parent-class="flash-deal" id="flash-deal-temporary-close-remove"
                                                class="form-check-input radio--input"
                                                {{ isset($flashDealPriority?->temporary_close_sorting) ? ($flashDealPriority?->temporary_close_sorting == 'hide' ? 'checked' : '') : 'checked' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-temporary-close-remove">
                                                {{ translate('remove_product_from_the_list_if_store_is_temporarily_off') }}
                                            </label>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="radio" name="temporary_close_sorting" value="default"
                                                data-parent-class="flash-deal" id="flash-deal-temporary-close-default"
                                                class="form-check-input radio--input"
                                                {{ isset($flashDealPriority?->temporary_close_sorting) ? ($flashDealPriority?->temporary_close_sorting == 'default' ? 'checked' : '') : 'checked' }}>
                                            <label class="mb-0 cursor-pointer" for="flash-deal-temporary-close-default">
                                                {{ translate('none') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-5">{{ translate('save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        $("#clearBtn").on("click", function() {
            window.location.href = '{{ url('admin/deal/flash') }}';
        });
    </script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/deal.js') }}"></script>
@endpush
