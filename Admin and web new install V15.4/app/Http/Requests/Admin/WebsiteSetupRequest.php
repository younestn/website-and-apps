<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Traits\ResponseHandler;
use Illuminate\Validation\Rule;

class WebsiteSetupRequest extends Request
{
    use ResponseHandler;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_web_logo' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'company_footer_logo' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'company_fav_icon' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'loader_gif' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'company_mobile_logo' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'app_store_download_url' => [
                Rule::requiredIf($this->has('app_store_download_status')),
                'nullable',
                'string',
            ],
            'play_store_download_url' => [
                Rule::requiredIf($this->has('play_store_download_status')),
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company_web_logo.mimes' => translate('the_company_web_logo_must_be_a_file_of_type_jpg_jpeg_png_gif'),
            'company_web_logo.max' => translate('the_company_web_logo_may_not_be_greater_than_1024_kilobytes'),
            'company_web_logo.dimensions' => translate('the_company_web_logo_must_be_at_least_325x100_pixels'),

            'company_footer_logo.mimes' => translate('the_company_footer_logo_must_be_a_file_of_type_jpg_jpeg_png_gif'),
            'company_footer_logo.max' => translate('the_company_footer_logo_may_not_be_greater_than_1024_kilobytes'),
            'company_footer_logo.dimensions' => translate('the_company_footer_logo_must_be_at_least_325x100_pixels'),

            'company_fav_icon.mimes' => translate('the_company_fav_icon_must_be_a_file_of_type_jpg_jpeg_png_gif'),
            'company_fav_icon.max' => translate('the_company_fav_icon_may_not_be_greater_than_1024_kilobytes'),

            'loader_gif.mimes' => translate('the_loader_gif_must_be_a_file_of_type_jpg_jpeg_png_gif'),
            'loader_gif.max' => translate('the_loader_gif_may_not_be_greater_than_1024_kilobytes'),

            'company_mobile_logo.mimes' => translate('the_company_mobile_logo_must_be_a_file_of_type_jpg_jpeg_png_gif'),
            'company_mobile_logo.max' => translate('the_company_mobile_logo_may_not_be_greater_than_1024_kilobytes'),
            'company_mobile_logo.dimensions' => translate('the_company_mobile_logo_must_be_at_least_100x60_pixels'),

            'app_store_download_url.required' => translate('the_app_store_download_url_field_is_required'),
            'app_store_download_url.string' => translate('the_app_store_download_url_must_be_a_string'),

            'play_store_download_url.required' => translate('the_play_store_download_url_field_is_required'),
            'play_store_download_url.string' => translate('the_play_store_download_url_must_be_a_string'),
        ];
    }

}
