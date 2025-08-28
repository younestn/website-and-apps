<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $id
 * @property string $url
 * @property string $image
 * @property int $status
 */
class BannerAddRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => 'required_if:resource_type,custom|nullable|url',
            'image' => 'required|image|mimes:webp,jpg,jpeg,png,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'url.required_if' => translate('the_url_field_is_required'),
            'image.required' => translate('the_image_is_required'),
            'image.max' => translate('the_image_size_max_2_mb'),
            'image.mimes' => translate('only_webp_jpg_jpeg_png_allowed'),
        ];
    }

}
