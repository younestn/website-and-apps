@php
    use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.admin.app')
@section('title', translate('create_Role'))
@section('content')
    @php($direction = Session::get('direction'))
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2 text-capitalize">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
                {{ translate('employee_role_setup') }}
            </h2>
        </div>
        <div class="card">
            <div class="card-body">
                <form id="submit-create-role" method="post" action="{{ route('admin.custom-role.store') }}"
                      class="text-start">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group mb-4">
                                <label for="name" class="form-label">{{ translate('role_name') }}</label>
                                <input type="text" name="name" class="form-control" id="name"
                                       aria-describedby="emailHelp"
                                       placeholder="{{ translate('ex').':'.translate('store') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-4 flex-wrap mb-4">
                        <label for="name" class="form-label mb-0">{{ translate('module_permission') }} </label>
                        <div class="form-group d-flex gap-2">
                            <input type="checkbox" id="select-all-module"
                                   class="form-check-input checkbox--input checkbox--input_lg cursor-pointer">
                            <label class="form-check-label cursor-pointer text-capitalize"
                                   for="select-all-module">
                                {{ translate('select_all') }}
                            </label>
                        </div>
                    </div>

                    <div class="row gy-2 mb-4">
                        @foreach($employeeRolePermission as $employeeRoleKey => $employeeRole)
                            <div class="col-sm-6 col-lg-3">
                                <div class="form-group d-flex gap-2">
                                    <input type="checkbox" name="modules[]" value="{{ $employeeRoleKey }}"
                                           class="form-check-input checkbox--input module-permission"
                                           id="{{ $employeeRoleKey }}-permission">
                                    <label class="" style="{{ $direction === "rtl" ? 'margin-right: 1.25rem;' : '' }};"
                                           for="{{ $employeeRoleKey }}-permission">
                                        {{ translate($employeeRole) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-end ">
                        <button type="submit" class="btn btn-primary">
                            {{ translate('submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between align-items-center flex-grow-1 mb-4">
                    <div class="col-md-4 col-lg-6 mb-2 mb-sm-0">
                        <h3 class="d-flex align-items-center gap-2">
                            {{ translate('employee_Roles') }}
                            <span class="badge badge-info text-bg-info">{{ count($roles) }}</span>
                        </h3>
                    </div>
                    <div class="col-md-8 col-lg-6 d-flex flex-wrap flex-sm-nowrap justify-content-sm-end gap-3">
                        <form action="{{url()->current() }}?search={{ request('searchValue') }}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                       placeholder="{{ translate('search_role') }}"
                                       value="{{ request('searchValue') }}">
                                <div class="input-group-append search-submit">
                                    <button type="submit">
                                        <i class="fi fi-rr-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <a type="button" class="btn btn-outline-primary text-nowrap"
                           href="{{ route('admin.custom-role.export',['searchValue'=>request('searchValue')]) }}">
                            <img width="14" src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}"
                                 class="excel" alt="">
                            <span class="ps-2">{{ translate('export') }}</span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered align-middle card-table">
                        <thead class="thead-light thead-50 text-capitalize table-nowrap">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('role_name') }}</th>
                            <th>{{ translate('modules') }}</th>
                            <th>{{ translate('created_at') }}</th>
                            <th class="text-center">{{ translate('status') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($roles as $key => $role)
                            <tr>
                                <td>{{ $key+1}}</td>
                                <td>{{ ucwords($role['name']) }}</td>
                                <td class="text-capitalize">
                                    @if($role['module_access'] != null)
                                        @foreach(json_decode($role['module_access'], true) as $module)
                                            @if(array_key_exists($module, $employeeRolePermission))
                                                {{ translate($employeeRolePermission[$module]) }}
                                                {!! !$loop->last ? ',<br/>' : '' !!}
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>{{ date('d-M-y', strtotime($role['created_at'])) }}</td>
                                <td>
                                    <form action="{{ route('admin.custom-role.employee-role-status') }}" method="post"
                                          id="employee-role-status-{{ $role['id'] }}-form" class="no-reload-form">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $role['id'] }}">
                                        <label class="switcher mx-auto" for="employee-role-status-{{ $role['id'] }}">
                                            <input
                                                class="switcher_input custom-modal-plugin"
                                                type="checkbox" value="1" name="status"
                                                id="employee-role-status-{{ $role['id'] }}"
                                                {{ $role['status'] == 1?'checked':''}}
                                                data-modal-type="input-change-form"
                                                data-modal-form="#employee-role-status-{{ $role['id'] }}-form"
                                                data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/employee-on.png') }}"
                                                data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/employee-off.png') }}"
                                                data-on-title="{{ translate('want_to_Turn_ON_Employee_role_Status').'?'}}"
                                                data-off-title="{{ translate('want_to_Turn_OFF_Employee_role_Status').'?'}}"
                                                data-on-message="<p>{{ translate('when_the_status_is_enabled_employees_can_access_the_system_to_perform_their_responsibilities') }}</p>"
                                                data-off-message="<p>{{ translate('when_the_status_is_disabled_employees_cannot_access_the_system_to_perform_their_responsibilities') }}</p>"
                                                data-on-button-text="{{ translate('turn_on') }}"
                                                data-off-button-text="{{ translate('turn_off') }}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('admin.custom-role.update',[$role['id']]) }}"
                                           class="btn btn-outline-primary icon-btn"
                                           title="{{ translate('edit') }}">
                                            <i class="fi fi-rr-pencil"></i>
                                        </a>
                                        <a href="javascript:"
                                           class="btn btn-outline-danger icon-btn delete-data-without-form"
                                           data-action="{{ route('admin.custom-role.delete') }}"
                                           title="{{ translate('delete') }}" data-id="{{ $role['id'] }}">
                                            <i class="fi fi-rr-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($roles)==0)
                    @include('layouts.admin.partials._empty-state',['text'=>'no_data_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/user-management/custom-role.js') }}"></script>
@endpush
