@extends('layouts.admin.app')

@section('title', translate('employee_Edit'))
@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-employee.png')}}" alt="">
            {{translate('employee_update')}}
        </h2>
    </div>

    <form action="{{route('admin.employee.update',[$employee['id']])}}" method="post" enctype="multipart/form-data"
            class="text-start">
        @csrf
        <div class="card">
            <div class="card-body">
                <h3 class="mb-0 page-header-title d-flex text-capitalize align-items-center gap-2 border-bottom pb-3 mb-3">
                    <i class="fi fi-sr-user"></i>
                    {{translate('general_information')}}
                </h3>
                <div class="row g-4">
                    <div class="col-md-6">
                        <input type="hidden" name="id" value="{{$employee['id']}}">
                        <div class="form-group">
                            <label for="name" class="mb-2">{{translate('full_Name')}}</label>
                            <input type="text" name="name" class="form-control" id="name"
                                placeholder="{{translate('ex')}} : John Doe"
                                value="{{$employee['name']}}" required>
                        </div>
                        <div class="form-group">
                            <label for="phone" class="mb-2">{{translate('phone')}}</label>
                            <input class="form-control form-control-user"
                                   type="tel" value="{{$employee ? $employee->phone  : ''}}"
                                   placeholder="{{ translate('ex').': 017xxxxxxxx' }}" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="role_id" class="mb-2">{{translate('role')}}</label>
                            <div class="select-wrapper">
                                <select class="form-select" name="role_id" id="role_id">
                                    <option value="0" selected disabled>{{'---'.translate('select').'---'}}</option>
                                    @foreach($adminRoles as $adminRole)
                                        <option value="{{$adminRole->id}}" {{$adminRole['id']==$employee['admin_role_id']?'selected':''}}>{{ ucfirst($adminRole->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="identify_type" class="mb-2">{{translate('identify_type')}}</label>
                            <div class="select-wrapper">
                                <select class="form-select" name="identify_type" id="identify_type">
                                    <option value="" selected disabled>{{translate('select_identify_type')}} </option>
                                    <option value="nid" {{$employee->identify_type == 'nid' ?'selected' : ''}}>{{translate('NID')}}</option>
                                    <option value="passport" {{$employee->identify_type == 'passport' ?'selected' : ''}}>{{translate('passport')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="identify_number" class="mb-2">{{translate('identify_number')}}</label>
                            <input type="number" name="identify_number" value="{{$employee->identify_number}}" class="form-control"
                                placeholder="{{translate('ex').':'.'9876123123'}}" id="identify_number">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="text-center mb-3">
                                <img class="upload-img-view" id="viewer"
                                        src="{{ getStorageImages(path: $employee->image_full_url , type: 'backend-profile') }}"
                                    alt=""/>
                            </div>
                            <label for="customFileUpload" class="mb-2">{{translate('employee_image')}}</label>
                            <span class="text-info">( {{translate('ratio').'1:1'}})</span>
                            <div class="form-group">
                                <div class="custom-file text-left">
                                    <input type="file" name="image" id="custom-file-upload" class="custom-file-input image-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" data-image-id="viewer" {{ $employee->image_full_url['path'] ? '' : 'required' }}>
                                    <label class="custom-file-label" for="custom-file-upload">{{translate('choose_file')}}</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="mb-2 d-flex">{{translate('identity_image')}}</label>
                            <div>
                                <div class="row" id="coba">
                                    @if($employee['identify_image'])
                                        @foreach($employee->identify_images_full_url as $img)
                                            <div class="col-md-6 h-100 mb-3">
                                                <img class="img-fit" alt="" src="{{getStorageImages(path:$img,type:'backend-basic')}}">
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h3 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                    <i class="fi fi-sr-user"></i>
                    {{translate('account_information')}}
                </h3>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email" class="mb-2">{{translate('email')}}</label>
                            <input type="email" name="email" value="{{$employee['email']}}" class="form-control"
                                id="email" placeholder="{{translate('ex').':'.'ex@gmail.com'}}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="user_password" class="mb-2 d-flex gap-2 align-items-center">
                                {{translate('password')}}
                                <span class="input-label-secondary cursor-pointer" data-bs-toggle="tooltip" data-bs-title="{{translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.'}}">
                                        <i class="fi fi-rr-info"></i>
                                    </span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="js-toggle-password form-control password-check" name="password"  id="user_password" placeholder="{{ translate('password_minimum_8_characters') }}">
                                <div id="changePassTarget" class="input-group-append changePassTarget">
                                    <a class="text-body-light" href="javascript:">
                                        <i id="changePassIcon" class="fi fi-sr-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <span class="text-danger password-error"></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="confirm_password" class="mb-2">
                                {{translate('confirm_password')}}
                            </label>

                            <div class="input-group">
                                <input type="password" class="js-toggle-password form-control" name="confirm_password"  id="confirm_password" placeholder="{{ translate('confirm_password') }}">
                                <div id="changeConfirmPassTarget" class="input-group-append changePassTarget">
                                    <a class="text-body-light" href="javascript:">
                                        <i id="changeConfirmPassIcon" class="fi fi-sr-eye"></i>
                                    </a>
                                </div>

                            </div>
                            <span class="text-danger confirm-password-error"></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                </div>
            </div>
        </div>
    </form>
</div>

<span id="coba-image" data-url="{{dynamicAsset(path: "public/assets/back-end/img/400x400/img2.jpg")}}"></span>
<span id="extension-error" data-text="{{ translate("please_only_input_png_or_jpg_type_file") }}"></span>
<span id="size-error" data-text="{{ translate("file_size_too_big") }}"></span>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/spartan-multi-image-picker.js')}}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/backend/admin/js/user-management/employee.js')}}"></script>
@endpush

