@extends('theme-views.layouts.app')

@section('title', translate('customer_Verify') . ' | ' . $web_config['company_name'] . ' ' . translate('ecommerce'))
@php($recaptcha = getWebConfig(name: 'recaptcha'))
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-30">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-0 box-shadow max-w-500 mx-auto p-3">
                        <div class="card-body">
                            <div>
                                <div class="text-center">
                                    <div class="py-3">
                                        <img src="{{ theme_asset('assets/img/svg/otp-login-icon.svg') }}" alt=""
                                            width="50">
                                    </div>
                                    <div class="my-3">
                                        <p class="text-muted">
                                            {{ translate('just_one_step_away') }}!
                                            {{ translate('_this_will_help_make_your_profile_more_personalized') }}
                                        </p>
                                    </div>
                                </div>
                                <form class="" id="sign-up-form"
                                    @if ($updateType == 'otp') action="{{ route('customer.auth.login.update-info') }}"
                                      @elseif($updateType == 'social')
                                          action="{{ route('customer.auth.social-login-confirmation.update') }}" @endif
                                    method="post">
                                    @csrf
                                    <div class="col-sm-12">
                                        <div class="form-group mb-2">
                                            <label for="user-name">
                                                {{ translate('Name') }}
                                            </label>
                                            <input class="form-control" type="text" name="name" id="user-name"
                                                required placeholder="{{ translate('Enter_your_name') }}"
                                                value="{{ request('fullName') ? base64_decode(request('fullName')) : '' }}">
                                        </div>

                                        @if ($updateType == 'otp')
                                            <div class="form-group mb-2">
                                                <label for="user-email">
                                                    {{ translate('Email') }}
                                                </label>
                                                <input class="form-control" type="text" name="email" id="user-email"
                                                    placeholder="{{ translate('Enter_your_email') }}">
                                            </div>
                                        @elseif($updateType == 'social')
                                            <div class="form-group mb-2">
                                                <label
                                                    class="form-label font-semibold">{{ translate('phone_number') }}</label>
                                                <input
                                                    class="form-control text-align-direction phone-input-with-country-picker"
                                                    type="tel" value="{{ old('phone') }}" name="phone"
                                                    placeholder="{{ translate('enter_phone_number') }}" required>
                                            </div>
                                        @endif
                                        <input type="hidden" name="identity" value="{{ $identity }}">
                                        @if ($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                                            <div class="generate-firebase-auth-recaptcha" id="firebase-auth-recaptcha-{{ rand(111, 999) }}"></div>
                                        @elseif(isset($recaptcha) && $recaptcha['status'] == 1)
                                            <div class="dynamic-default-and-recaptcha-section">
                                                <input type="hidden" name="g-recaptcha-response"
                                                    class="render-grecaptcha-response"
                                                    data-input="#login-default-captcha-section"
                                                    data-default-captcha="#login-default-captcha-section"
                                                    data-action="customer_auth">

                                                <div class="default-captcha-container d-none"
                                                    id="login-default-captcha-section"
                                                    data-placeholder="{{ translate('enter_captcha_value') }}"
                                                    data-base-url="{{ route('g-recaptcha-session-store') }}"
                                                    data-session="{{ 'default_recaptcha_id_customer_auth' }}">
                                                </div>
                                            </div>
                                        @else
                                            <div class="default-captcha-container"
                                                data-placeholder="{{ translate('enter_captcha_value') }}"
                                                data-base-url="{{ route('g-recaptcha-session-store') }}"
                                                data-session="{{ 'default_recaptcha_id_customer_auth' }}">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-sm-12">
                                        <button type="submit" id="customerInfoUpdateBtn"
                                            class="btn btn-primary">{{ translate('Update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @if ($updateType == 'social')
        @include(VIEW_FILE_NAMES['modal_for_social_media_user_view'])
    @endif
@endsection

@push('script')
    <script src="{{ theme_asset('assets/js/auth.js') }}"></script>
    @if ($user)
        <script>
            $(document).ready(function() {
                $('#social-media-user-modal').modal('show');
            })
        </script>
    @endif

@endpush
