@extends('layouts.admin.app')

@section('title', translate('add_new_Vendor'))

@section('content')
    <div class="content container-fluid main-card {{ Session::get('direction') }}">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('add_new_Vendor') }}
            </h2>
        </div>

        <form action="{{ route('admin.vendors.add') }}" method="post" enctype="multipart/form-data" id="add-vendor-form"
            data-message="{{ translate('want_to_add_this_vendor') . '?' }}"
            data-redirect-route="{{ route('admin.vendors.vendor-list') }}">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="fs-18">{{ translate('Vendor_Information') }}</h3>
                    <p class="mb-0 fs-12">
                        {{ translate('Provide_all_mandatory_details_to_create_a_new_vendor_profile.') }}
                    </p>

                </div>
                <div class="card-body">
                    <input type="hidden" name="status" value="approved">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="bg-section rounded-8 p-20 h-100">
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="form-group mb-0">
                                            <label for="exampleFirstName"
                                                class="form-label mb-2">{{ translate('first_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-user"
                                                id="exampleFirstName" name="f_name" value="{{ old('f_name') }}"
                                                placeholder="{{ translate('ex') }}: Jhone" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="exampleLastName"
                                                class="form-label mb-2">{{ translate('last_name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-user"
                                                id="exampleLastName" name="l_name" value="{{ old('l_name') }}"
                                                placeholder="{{ translate('ex') }}: {{ 'Doe' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label for="phone_number" class="form-label mb-2">{{ translate('phone') }}
                                                <span class="text-danger">*</span></label>
                                            <input class="form-control form-control-user" type="tel" value=""
                                                placeholder="{{ translate('ex') . ': 017xxxxxxxx' }}" name="phone"
                                                required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                <div class="d-flex flex-column gap-20 w-100">
                                    <div>
                                        <label for="" class="form-label fw-semibold mb-1">
                                            {{ translate('Vendor_Image') }}
                                        </label>
                                        <p class="fs-12 mb-0">{{ translate('JPG,_JPEG_or_PNG._image_size_:_max_2_MB') }}
                                            <span class="text-dark fw-semibold">(1:1)</span>
                                        </p>
                                    </div>
                                    <div class="upload-file">
                                        <input type="file" name="image" id="custom-file-upload"
                                            class="upload-file__input single_file_input"
                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                            value="" required>
                                        <label class="upload-file__wrapper">
                                            <div class="upload-file-textbox text-center">
                                                <img width="34" height="34" class="svg"
                                                    src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}"
                                                    alt="image upload">
                                                <h6 class="mt-1 fw-medium lh-base text-center text-body">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file-img" loading="lazy" src="" data-default-src=""
                                                alt="">
                                        </label>
                                        <div class="overlay">
                                            <div class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                    <i class="fi fi-sr-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                    <i class="fi fi-rr-camera"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="fs-18 text-capitalize">{{ translate('Account_Information') }}</h3>
                   <p class="mb-0 fs-12">
                        {{ translate('Provide_the_account_details_required_for_vendor_operations.') }}
                    </p>

                </div>
                <div class="card-body">
                    <input type="hidden" name="status" value="approved">
                    <div class="row g-4">
                        <div class="col-lg-4 ">
                            <div class="form-group">
                                <label for="exampleInputEmail" class="form-label mb-2">{{ translate('email') }} <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-user" id="exampleInputEmail"
                                    name="email" value="{{ old('email') }}"
                                    placeholder="{{ translate('ex') . ':' . 'Jhone@company.com' }}" required>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="user_password" class="form-label mb-2">
                                    {{ translate('password') }} <span class="text-danger">*</span>
                                    <span class="tooltip-icon cursor-pointer" data-bs-toggle="tooltip"
                                        data-bs-title="{{ translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter') . ',' . translate('_one_lowercase_letter') . ',' . translate('_one_digit_') . ',' . translate('_one_special_character') . ',' . translate('_and_no_spaces') . '.' }}">
                                        <i class="fi fi-sr-info"></i>
                                    </span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="js-toggle-password form-control password-check"
                                        name="password" required id="user_password" minlength="8"
                                        placeholder="{{ translate('password_minimum_8_characters') }}">
                                    <div id="changePassTarget" class="input-group-append changePassTarget">
                                        <a class="text-body-light" href="javascript:">
                                            <i id="changePassIcon" class="fi fi-rr-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <span class="text-danger mx-1 password-error"></span>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="confirm_password" class="form-label mb-2">{{ translate('confirm_password') }}
                                    <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="js-toggle-password form-control"
                                        name="confirm_password" required id="confirm_password"
                                        placeholder="{{ translate('confirm_password') }}">
                                    <div id="changeConfirmPassTarget" class="input-group-append changePassTarget">
                                        <a class="text-body-light" href="javascript:">
                                            <i id="changeConfirmPassIcon" class="fi fi-rr-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="pass invalid-feedback">{{ translate('repeat_password_not_match') . '.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="fs-18">{{ translate('Shop_Information') }}</h3>
                    <p class="mb-0 fs-12">
                         {{ translate('Basic_shop_details_provide_below') }}.
                    </p>
                </div>
                <div class="card-body">
                    <div class="bg-section rounded-8 p-20 mb-4">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="form-group mb-0">
                                    <label for="shop_name" class="form-label mb-2">{{ translate('shop_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-user" id="shop_name"
                                        name="shop_name" placeholder="{{ translate('ex') . ':' . translate('Jhon') }}"
                                        value="{{ old('shop_name') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-0">
                                    <label for="shop_address"
                                        class="form-label mb-2">{{ translate('shop_address') }}</label>
                                    <textarea name="shop_address" class="form-control text-area-max" id="shop_address" rows="1"
                                        data-maxlength="100" placeholder="{{ translate('ex') . ':' . translate('doe') }}">{{ old('shop_address') }}</textarea>
                                    <div class="d-flex justify-content-end">
                                        <span class="text-body-light">0/100</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                <div class="d-flex flex-column gap-20 w-100">
                                    <div>
                                        <label for="" class="form-label fw-semibold mb-1">
                                            {{ translate('Shop_logo') }}
                                        </label>
                                        <p class="fs-12 mb-0">{{ translate('JPG,_JPEG_or_PNG._image_size_:_max_2_MB') }}
                                            <span class="text-dark fw-semibold">(1:1)</span>
                                        </p>
                                    </div>
                                    <div class="upload-file">
                                        <input type="file" name="logo" id="logo-upload"
                                            class="upload-file__input single_file_input"
                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                            value="" required>
                                        <label class="upload-file__wrapper">
                                            <div class="upload-file-textbox text-center">
                                                <img width="34" height="34" class="svg"
                                                    src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}"
                                                    alt="image upload">
                                                <h6 class="mt-1 fw-medium lh-base text-center text-body">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file-img" loading="lazy" src=""
                                                data-default-src="" alt="">
                                        </label>
                                        <div class="overlay">
                                            <div class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                    <i class="fi fi-sr-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                    <i class="fi fi-rr-camera"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                <div class="d-flex flex-column gap-20 w-100">
                                    <div>
                                        <label for="" class="form-label fw-semibold mb-1">
                                              {{ translate('Shop_cover_image') }}
                                        </label>
                                        <p class="fs-12 mb-0">
                                            {{ translate('JPG,_JPEG_or_PNG._image:_2000_x_500_px') }}
                                            <span class="text-dark fw-semibold">(1:1),</span>
                                            {{ translate('max_2_MB') }}
                                        </p>
                                    </div>
                                    <div class="upload-file">
                                        <input type="file" name="banner" id="banner-upload"
                                            class="upload-file__input single_file_input"
                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                            value="" required>
                                        <label class="upload-file__wrapper ratio-4-1">
                                            <div class="upload-file-textbox text-center">
                                                <img width="34" height="34" class="svg"
                                                    src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}"
                                                    alt="image upload">
                                                <h6 class="mt-1 fw-medium lh-base text-center text-body">
                                                    <span class="text-info">{{ translate('Click to upload') }}</span>
                                                    <br>
                                                    {{ translate('or drag and drop') }}
                                                </h6>
                                            </div>
                                            <img class="upload-file-img" loading="lazy" src=""
                                                data-default-src="" alt="">
                                        </label>
                                        <div class="overlay">
                                            <div class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                    <i class="fi fi-sr-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                    <i class="fi fi-rr-camera"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (theme_root_path() == 'theme_aster')
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                    <div class="d-flex flex-column gap-20 w-100">
                                        <div>
                                            <label for="" class="form-label fw-semibold mb-1">
                                               {{ translate('secondary_banner') }}
                                            </label>
                                            <p class="fs-12 mb-0">
                                                {{ translate('JPG,_JPEG_or_PNG._image:_2000_x_377_px') }}
                                                <span class="text-dark fw-semibold">(1:1),</span>
                                                {{ translate('max_2_MB') }}
                                            </p>
                                        </div>
                                        <div class="upload-file">
                                            <input type="file" name="bottom_banner" id="bottom-banner-upload"
                                                class="upload-file__input single_file_input"
                                                accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                value="" required>
                                            <label class="upload-file__wrapper ratio-4-1">
                                                <div class="upload-file-textbox text-center">
                                                    <img width="34" height="34" class="svg"
                                                        src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}"
                                                        alt="image upload">
                                                    <h6 class="mt-1 fw-medium lh-base text-center text-body">
                                                        <span class="text-info">{{ translate('Click to upload') }}</span>
                                                        <br>
                                                        {{ translate('or drag and drop') }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file-img" loading="lazy" src=""
                                                    data-default-src="" alt="">
                                            </label>
                                            <div class="overlay">
                                                <div class="d-flex gap-10 justify-content-center align-items-center h-100">
                                                    <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="fi fi-sr-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="fi fi-rr-camera"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="fs-18">{{ translate('Business_TIN') }}</h3>
                    <p class="mb-0 fs-12">
                        {{ translate('provide_vendor_business_tin_and_related_information_for_taxpayer_verification') }}.
                    </p>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="bg-section rounded-8 p-20 h-100">
                                <div class="form-group">
                                    <label class="form-label mb-2" for="">
                                        {{ translate('taxpayer_identification_number(TIN)') }}
                                    </label>
                                    <input class="form-control" type="text" name="tax_identification_number"
                                        placeholder="{{ translate('type_your_user_name') }}">
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label mb-2" for="">
                                        {{ translate('Expire_Date') }}
                                    </label>
                                    <div class="position-relative">
                                        <span class="fi fi-sr-calendar icon-absolute-on-right"></span>
                                        <input type="text"
                                            class="js-daterangepicker_single-date-with-placeholder-add-new-vendor form-control"
                                            placeholder="{{ translate('click_to_add_date') }}" name="tin_expire_date"
                                            value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-section rounded-8 p-20 w-100 h-100">
                                <div class="d-flex flex-column gap-20 w-100">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <div>
                                            <label for="" class="form-label fw-semibold mb-1">
                                                {{ translate('TIN_Certificate') }}
                                            </label>
                                            <p class="fs-12 mb-0">{{ translate('pdf,_doc,_jpg._file_size_:_max_2_MB') }}
                                            </p>
                                        </div>
                                        <div class="d-flex gap-3 align-items-center">
                                            <button type="button" id="doc_edit_btn" class="btn btn-primary icon-btn">
                                                <i class="fi fi-sr-pencil"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <div id="file-assets"
                                            data-picture-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/picture.svg') }}"
                                            data-document-icon="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/document.svg') }}"
                                            data-blank-thumbnail="{{ dynamicAsset(path: 'public/assets/back-end/img/blank.png') }}">
                                        </div>
                                        <!-- Upload box -->
                                        <div class="d-flex justify-content-center" id="pdf-container">
                                            <div class="document-upload-wrapper mw-100" id="doc-upload-wrapper">
                                                <input type="file" name="tin_certificate" class="document_input"
                                                    accept=".pdf,.doc,.jpg,.jpeg">
                                                <div class="textbox">
                                                    <img class="svg"
                                                        src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/doc-upload-icon.svg') }}"
                                                        alt="">
                                                    <p class="fs-12 mb-0">
                                                        {{ translate('Select_a_file_or') }}
                                                        <span class="font-weight-semibold">
                                                            {{ translate('Drag_and_Drop_here') }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end trans3 mt-4">
                <div
                    class="d-flex justify-content-sm-end justify-content-center gap-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                    <input type="hidden" name="from_submit" value="admin">
                    <button type="reset" class="btn btn-secondary px-3 px-sm-4 w-120">{{ translate('reset') }}</button>
                    <button type="submit" class="btn btn-primary px-3 px-sm-4">
                        <i class="fi fi-sr-disk"></i>
                        {{ translate('save_information') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/admin/vendor.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/file-upload/pdf.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/file-upload/pdf-worker.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/file-upload/multiple-document-upload.js') }}"></script>
@endpush
