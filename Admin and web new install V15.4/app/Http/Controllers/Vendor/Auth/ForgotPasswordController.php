<?php

namespace App\Http\Controllers\Vendor\Auth;

use App\Contracts\Repositories\PasswordResetRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Vendor\ForgotPassword;
use App\Events\PasswordResetEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\PasswordResetRequest;
use App\Http\Requests\Vendor\VendorPasswordRequest;
use App\Services\FirebaseService;
use App\Services\PasswordResetService;
use App\Services\RecaptchaService;
use App\Traits\EmailTemplateTrait;
use App\Traits\SmsGateway;
use App\Utils\SMSModule;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ForgotPasswordController extends BaseController
{
    use SmsGateway, EmailTemplateTrait;

    /**
     * @param VendorRepositoryInterface $vendorRepo
     * @param PasswordResetRepositoryInterface $passwordResetRepo
     * @param PasswordResetService $passwordResetService
     * @param FirebaseService $firebaseService
     */
    public function __construct(
        private readonly VendorRepositoryInterface        $vendorRepo,
        private readonly PasswordResetRepositoryInterface $passwordResetRepo,
        private readonly PasswordResetService             $passwordResetService,
        private readonly FirebaseService                  $firebaseService,
    )
    {
        $this->middleware('guest:seller', ['except' => ['logout']]);
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getForgotPasswordView();
    }

    /**
     * @return View
     */
    public function getForgotPasswordView(): View
    {
        return view(ForgotPassword::INDEX[VIEW]);
    }

    /**
     * @param PasswordResetRequest $request
     * @return JsonResponse|RedirectResponse
     * @throws Exception
     */
    public function getPasswordResetRequest(PasswordResetRequest $request): JsonResponse|RedirectResponse
    {
        session()->put(SessionKey::FORGOT_PASSWORD_IDENTIFY, $request['identity']);
        $verificationBy = getWebConfig('vendor_forgot_password_method') ?? 'phone';

        $result = RecaptchaService::verificationStatus(request: $request, session: 'default_recaptcha_id_vendor_forgot_password', action: 'vendor_forgot_password', firebase: true);
        if ($result && !$result['status']) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => $result['message'],
                ]);
            }
            ToastMagic::error($result['message']);
            return back();
        }

        if ($verificationBy == 'email') {
            $vendor = $this->vendorRepo->getFirstWhere(['identity' => $request['identity']]);
            if (isset($vendor)) {
                $emailServicesSmtp = getWebConfig(name: 'mail_config');
                if ($emailServicesSmtp['status'] == 0) {
                    $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
                }
                if ($emailServicesSmtp['status'] == 1) {
                    $token = Str::random(120);
                    $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['identity'], token: $token, userType: 'seller'));
                    $resetUrl = route('vendor.auth.forgot-password.reset-password', ['token' => $token]);
                    try {
                        $data = [
                            'userType' => 'vendor',
                            'templateName' => 'forgot-password',
                            'vendorName' => $vendor['f_name'],
                            'subject' => translate('password_reset'),
                            'title' => translate('password_reset'),
                            'passwordResetURL' => $resetUrl,
                        ];
                        event(new PasswordResetEvent(email: $vendor['email'], data: $data));
                    } catch (Exception $exception) {
                        if ($request->ajax()) {
                            return response()->json(['error' => translate('email_send_fail') . '!!']);
                        }
                        ToastMagic::error(translate('email_send_fail'));
                        return back();
                    }
                    if ($request->ajax()) {
                        return response()->json([
                            'verificationBy' => 'mail',
                            'success' => translate('otp_has_been_sent_to_your_email_address'),
                        ]);
                    }
                    ToastMagic::success(translate('otp_has_been_sent_to_your_email_address'));
                    return back();
                }
                $smsErrorMsg = translate('something_went_wrong.') . ' ' . translate('please_try_again_after_sometime');
                if ($request->ajax()) {
                    return response()->json(['error' => $smsErrorMsg]);
                }
                ToastMagic::error($smsErrorMsg);
                return back();
            }
        } elseif ($verificationBy == 'phone') {
            $vendor = $this->vendorRepo->getFirstWhere(['identity' => $request['identity']]);
            if (isset($vendor)) {
                $response = "not_found";
                $smsErrorMsg = translate('something_went_wrong.') . ' ' . translate('please_try_again_after_sometime');
                $token = (env('APP_MODE') == 'live') ? rand(1000, 9999) : 1234;

                $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
                if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
                    try {
                        $firebaseResponse = $this->firebaseService->sendOtp($request['identity']);
                        if ($firebaseResponse['status'] == 'success') {
                            $token = $firebaseResponse['sessionInfo'];
                            $response = $firebaseResponse['status'];
                        } else {
                            $smsErrorMsg = translate(strtolower($firebaseResponse['errors']));
                        }
                    } catch (Exception $e) {
                        $response = "not_found";
                        $smsErrorMsg = translate('something_went_wrong.') . ' ' . translate('please_try_again_after_sometime');
                    }
                } else {
                    $response = SMSModule::sendCentralizedSMS($request['identity'], $token);
                    if (env('APP_MODE') == 'dev') {
                        $response = 'success';
                    }
                }

                $this->passwordResetRepo->add($this->passwordResetService->getAddData(identity: $request['identity'], token: $token, userType: 'seller'));

                if (env('APP_MODE') == 'dev') {
                    if ($request->ajax()) {
                        return response()->json([
                            'verificationBy' => 'phone',
                            'redirectRoute' => route('vendor.auth.forgot-password.otp-verification'),
                            'success' => translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'),
                        ]);
                    }
                    ToastMagic::success(translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'));
                    return redirect()->route('vendor.auth.forgot-password.otp-verification');
                }

                if ($response === "not_found") {
                    if ($request->ajax()) {
                        return response()->json([
                            'error' => $smsErrorMsg,
                        ]);
                    }
                    ToastMagic::error($smsErrorMsg);
                    return back();
                }

                if ($request->ajax()) {
                    return response()->json([
                        'verificationBy' => 'phone',
                        'redirectRoute' => route('vendor.auth.forgot-password.otp-verification'),
                        'success' => translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'),
                    ]);
                }
                ToastMagic::success(translate('Check_your_phone') . ', ' . translate('password_reset_otp_sent'));
                return redirect()->route('vendor.auth.forgot-password.otp-verification');
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'error' => translate('no_such_user_found') . '!!',
            ]);
        }
        ToastMagic::error(translate('no_such_user_found'));
        return back();
    }


    public function getOTPVerificationView(): View
    {
        return view('vendor-views.auth.forgot-password.verify-otp-view');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function submitOTPVerificationCode(Request $request): RedirectResponse
    {
        $identity = session(SessionKey::FORGOT_PASSWORD_IDENTIFY);
        $verificationData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'identity' => $identity]);
        $OTPVerificationData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'identity' => $identity, 'token' => $request['token']]);

        $tokenVerifyStatus = false;
        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification') ?? [];
        if ($firebaseOTPVerification && $firebaseOTPVerification['status']) {
            $firebaseVerify = $this->firebaseService->verifyOtp($verificationData['token'], $verificationData['identity'], $request['token']);
            $tokenVerifyStatus = (bool)($firebaseVerify['status'] == 'success');
            if (!$tokenVerifyStatus) {
                $verificationData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'identity' => $identity]);
                $this->passwordResetRepo->updateOrCreate(params: ['user_type' => 'seller', 'identity' => $identity], value: [
                    'otp_hit_count' => ($verificationData['otp_hit_count'] + 1),
                    'updated_at' => now(),
                    'temp_block_time' => null,
                ]);
                ToastMagic::error(translate(strtolower($firebaseVerify['errors'])));
                return redirect()->back();
            }
        } else {
            $tokenVerifyStatus = (bool)$OTPVerificationData;
        }

        if ($tokenVerifyStatus) {
            return redirect()->route('vendor.auth.forgot-password.reset-password', [
                'token' => $verificationData['token']
            ]);
        }
        ToastMagic::error(translate('invalid_otp'));
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function getPasswordResetView(Request $request): View|RedirectResponse
    {
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['token']]);
        if (isset($passwordResetData)) {
            $token = $request['token'];
            return view(ForgotPassword::RESET_PASSWORD[VIEW], compact('token'));
        }
        ToastMagic::error(translate('Invalid_URL'));
        return redirect()->route('vendor.auth.login');
    }

    /**
     * @param VendorPasswordRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function resetPassword(VendorPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $passwordResetData = $this->passwordResetRepo->getFirstWhere(params: ['user_type' => 'seller', 'token' => $request['reset_token']]);
        if ($passwordResetData) {
            $vendor = $this->vendorRepo->getFirstWhere(params: ['identity' => $passwordResetData['identity']]);
            $this->vendorRepo->update(id: $vendor['id'], data: ['password' => bcrypt($request['password'])]);
            $this->passwordResetRepo->delete(params: ['id' => $passwordResetData['id']]);
            if ($request->ajax()) {
                return response()->json([
                    'passwordUpdate' => 1,
                    'success' => translate('Password_reset_successfully'),
                    'redirectRoute' => route('vendor.auth.login')
                ]);
            }
            ToastMagic::success(translate('Password_reset_successfully'));
            return redirect()->route('vendor.auth.login');
        }

        if ($request->ajax()) {
            return response()->json(['error' => translate('invalid_URL')]);
        }
        ToastMagic::error(translate('invalid_URL'));
        return back();
    }
}
