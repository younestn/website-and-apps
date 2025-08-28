@extends('layouts.admin.app')

@section('title',translate('emergency_Contact'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2 text-capitalize">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-delivery-man.png')}}" alt="">
                {{translate('emergency_contact')}}
            </h2>
        </div>
        <div class="row">
            <div class="col-12">
                <form action="{{route('admin.delivery-man.emergency-contact.add')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3 text-capitalize">
                                <i class="fi fi-sr-user"></i>
                                {{translate('add_new_contact_information')}}
                            </h4>
                            <div class="row gy-4 mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label d-flex"
                                               for="f_name">{{translate('contact_name')}}</label>
                                        <input type="text" name="name" class="form-control"
                                               placeholder="{{translate('contact_name')}}"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label d-flex" for="exampleFormControlInput1">{{translate('phone')}}</label>
                                        <div class="input-group mb-3">
                                            <input value="{{old('phone')}}" type="tel" name="phone" class="form-control" placeholder="{{translate('ex').':'.'017********'}}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-3 justify-content-end">
                                <button type="reset" id="reset"
                                        class="btn btn-secondary px-4">{{translate('reset')}}</button>
                                <button type="submit"
                                        class="btn btn-primary px-4">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card mt-3">
                    <div class="p-3">
                        <div class="row gy-1 align-items-center justify-content-between">
                            <div class="col-auto">
                                <h4 class="text-capitalize">
                                    {{translate('contact_information_table')}}
                                    <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $contacts->count() }}</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover table-borderless align-middle">
                            <thead class="text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th class="text-center">{{translate('name')}}</th>
                                <th class="text-center">{{translate('phone')}}</th>
                                <th class="text-center">{{translate('status')}}</th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($contacts as $contact)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td class="text-center text-capitalize">{{ $contact['name'] }}</td>
                                <td class="text-center">
                                    <a class="text-dark text-hover-primary" href="tel:{{ $contact['phone'] }}">
                                        {{ $contact['phone'] }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <form action="{{ route('admin.delivery-man.emergency-contact.ajax-status-change') }}" method="post" id="emergency-contact-status{{$contact->id}}-form" class="no-reload-form">
                                            @csrf
                                            <input name="id" value="{{$contact['id']}}" hidden>
                                            <label class="switcher mx-auto" for="emergency-contact-status{{$contact->id}}">
                                                <input
                                                    class="switcher_input custom-modal-plugin"
                                                    type="checkbox" value="1" name="status"
                                                    id="emergency-contact-status{{$contact->id}}"
                                                    {{ $contact['status'] == 1 ? 'checked' : '' }}
                                                    data-modal-type="input-change-form"
                                                    data-modal-form="#emergency-contact-status{{$contact->id}}-form"
                                                    data-on-image="{{ dynamicAsset(path: 'public/assets/backend/media/toggle-modal-icons/turn-on.svg') }}"
                                                    data-off-image="{{ dynamicAsset(path: 'public/assets/backend/media/toggle-modal-icons/turn-off.svg') }}"
                                                    data-on-title = "{{translate('turn_on_the_status').'?'}}"
                                                    data-off-title = "{{translate('turn_off_the_status').'?'}}"
                                                    data-on-message="<p>{{translate('are_you_sure,_do_you_want_to_turn_on_this_emergency_contact_status_from_your_system.')}}</p>"
                                                    data-off-message="<p>{{translate('are_you_sure,_do_you_want_to_turn_off_this_emergency_contact_status_from_your_system.')}}</p>"
                                                    data-on-button-text="{{ translate('yes') }}"
                                                    data-off-button-text="{{ translate('no') }}">
                                                <span class="switcher_control"></span>
                                            </label>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button  class="btn btn-outline-primary icon-btn emergency-contact-update-view"
                                            title="{{translate('edit')}}"
                                            data-action="{{route('admin.delivery-man.emergency-contact.update',['id'=>$contact->id])}}">
                                            <i class="fi fi-rr-pencil"></i>
                                        </button>
                                        <a class="btn btn-outline-danger icon-btn delete delete-data" href="javascript:"
                                           data-id="delete-contact-{{$contact->id}}"
                                           title="{{ translate('delete')}}">
                                            <i class="fi fi-rr-trash"></i>
                                        </a>
                                    </div>
                                    <form action="{{route('admin.delivery-man.emergency-contact.destroy')}}"
                                          method="post" id="delete-contact-{{$contact->id}}">
                                        @csrf @method('delete')
                                        <input type="hidden" name="id" value="{{ $contact->id }}">
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            {{ $contacts->links() }}
                        </div>
                    </div>
                    @if(count($contacts)==0)
                        @include('layouts.admin.partials._empty-state',['text'=>'no_contact_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade emergency-contact-update-modal" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
    </div>
@endsection
@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/emergency-contact.js')}}"></script>
@endpush
