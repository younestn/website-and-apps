<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\ViewPaths\Admin\Product;
use App\Events\RestockProductNotificationEvent;
use App\Models\Color;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Boolean;
use Rap2hpoutre\FastExcel\FastExcel;
use function App\Utils\currency_converter;
use function Aws\map;
use function React\Promise\all;

class ProductService
{
    use FileManagerTrait;

    public function __construct(private readonly Color $color)
    {
    }

    public function getProcessedImages(object $request): array
    {
        $colorImageSerial = [];
        $imageNames = [];
        $storage = config('filesystems.disks.default') ?? 'public';
        if ($request->has('colors_active') && $request->has('colors') && count($request['colors']) > 0) {
            foreach ($request['colors'] as $color) {
                $color_ = Str::replace('#', '', $color);
                $img = 'color_image_' . $color_;
                if ($request->file($img)) {
                    $image = $this->upload(dir: 'product/', format: 'webp', image: $request->file($img));
                    $colorImageSerial[] = [
                        'color' => $color_,
                        'image_name' => $image,
                        'storage' => $storage,
                    ];
                    $imageNames[] = [
                        'image_name' => $image,
                        'storage' => $storage,
                    ];
                } else if ($request->has($img)) {
                    $image = $request->$img[0];
                    $colorImageSerial[] = [
                        'color' => $color_,
                        'image_name' => $image,
                        'storage' => $storage,
                    ];
                    $imageNames[] = [
                        'image_name' => $image,
                        'storage' => $storage,
                    ];
                }
            }
        }
        if ($request->file('images')) {
            foreach ($request->file('images') as $image) {
                $images = $this->upload(dir: 'product/', format: 'webp', image: $image);
                $imageNames[] = [
                    'image_name' => $images,
                    'storage' => $storage,
                ];
                if ($request->has('colors_active') && $request->has('colors') && count($request['colors']) > 0) {
                    $colorImageSerial[] = [
                        'color' => null,
                        'image_name' => $images,
                        'storage' => $storage,
                    ];
                }
            }
        }
        if (!empty($request->existing_images)) {
            foreach ($request->existing_images as $image) {
                $colorImageSerial[] = [
                    'color' => null,
                    'image_name' => $image,
                    'storage' => $storage,
                ];

                $imageNames[] = [
                    'image_name' => $image,
                    'storage' => $storage,
                ];
            }
        }
        return [
            'image_names' => $imageNames ?? [],
            'colored_image_names' => $colorImageSerial ?? []
        ];

    }

    public function getProcessedUpdateImages(object $request, object $product): array
    {
        $productImages = collect(json_decode($product->images, true))
            ->unique('image_name')
            ->values()->toArray();

        $colorImageArray = [];
        $storage = config('filesystems.disks.default') ?? 'public';
        $dbColorImage = $product->color_image ? json_decode($product->color_image, true) : [];
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            if (!$dbColorImage) {
                foreach ($productImages as $image) {
                    $image = is_string($image) ? $image : (array)$image;
                    $dbColorImage[] = [
                        'color' => null,
                        'image_name' => is_array($image) ? $image['image_name'] : $image,
                        'storage' => $image['storage'] ?? $storage,
                    ];
                }
            }

            $dbColorImageFinal = [];
            if ($dbColorImage) {
                foreach ($dbColorImage as $colorImage) {
                    if ($colorImage['color']) {
                        $dbColorImageFinal[] = $colorImage['color'];
                    }
                }
            }

            $inputColors = [];
            foreach ($request->colors as $color) {
                $inputColors[] = str_replace('#', '', $color);
            }
            $colorImageArray = $dbColorImage;

            foreach ($inputColors as $color) {
                $image = 'color_image_' . $color;
                if (!in_array($color, $dbColorImageFinal)) {
                    if ($request->file($image)) {
                        $imageName = $this->upload(dir: 'product/', format: 'webp', image: $request->file($image));
                        $productImages[] = [
                            'image_name' => $imageName,
                            'storage' => $storage,
                        ];
                        $colorImages = [
                            'color' => $color,
                            'image_name' => $imageName,
                            'storage' => $storage,
                        ];
                        $colorImageArray[] = $colorImages;
                    }
                } else if ($dbColorImage && in_array($color, $dbColorImageFinal) && $request->has($image) && $request->file($image)) {
                    $dbColorFilterImages = [];
                    foreach ($dbColorImage as $colorImage) {
                        if ($colorImage['color'] == $color) {
                            $this->delete(filePath: 'product/' . $colorImage['image_name']);
                            $imageName = $this->upload(dir: 'product/', format: 'webp', image: $request->file($image));

                            $productImages = collect($productImages)->filter(function ($productImageItem) use ($colorImage) {
                                if (is_array($productImageItem) && isset($productImageItem['image_name'])) {
                                    return $productImageItem['image_name'] != $colorImage['image_name'];
                                }
                                return $productImageItem != $colorImage['image_name'];
                            })->values()->toArray();


                            $dbColorFilterImages = collect($dbColorImage)->filter(function ($dbColorImageItem) use ($colorImage) {
                                return $dbColorImageItem['image_name'] != $colorImage['image_name'];
                            })->values()->toArray();

                            $productImages[] = [
                                'image_name' => $imageName,
                                'storage' => $storage,
                            ];

                            $colorImageArray = collect($colorImageArray)->filter(function ($colorItem) use ($color, $colorImage) {
                                return $colorItem['color'] != $color && $colorItem['image_name'] != $colorImage['image_name'];
                            })->values()->toArray();

                            $colorImages = [
                                'color' => $color,
                                'image_name' => $imageName,
                                'storage' => $storage,
                            ];
                            $colorImageArray[] = $colorImages;
                        }
                    }
                    $dbColorImage = $dbColorFilterImages;
                }
            }
        }

        foreach ($dbColorImage as $colorImage) {
            $image = is_string($colorImage) ? $colorImage : (array)$colorImage;
            $productImages[] = [
                'image_name' => is_array($image) ? $image['image_name'] : $image,
                'storage' => $image['storage'] ?? $storage,
            ];
        }
        $requestColors = [];
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            foreach ($request['colors'] as $color) {
                $requestColors[] = str_replace('#', '', $color);
            }
        }

        foreach ($colorImageArray as $colorImage) {
            if (!in_array($colorImage['color'], $requestColors)) {
                $productImages[] = [
                    'image_name' => $colorImage['image_name'],
                    'storage' => $colorImage['storage'] ?? $storage,
                ];
            }
        }

        $colorImageArray = collect($colorImageArray)->map(function ($colorImage) use ($requestColors) {
            if (!in_array($colorImage['color'], $requestColors)) {
                $colorImage['color'] = null;
            }
            return $colorImage;
        })->sortByDesc(function ($colorImage) {
            return !is_null($colorImage['color']);
        })->values()->toArray();

        if ($request->file('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = $this->upload(dir: 'product/', format: 'webp', image: $image);
                $productImages[] = [
                    'image_name' => $imageName,
                    'storage' => $storage,
                ];
                if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
                    $colorImageArray[] = [
                        'color' => null,
                        'image_name' => $imageName,
                        'storage' => $storage,
                    ];
                }
            }
        }
        $productImages = collect($productImages)->unique('image_name')->values()->toArray();

        return [
            'image_names' => $productImages ?? [],
            'colored_image_names' => $colorImageArray ?? []
        ];
    }

    public function getCategoriesArray(object $request): array
    {
        $category = [];
        if ($request['category_id'] != null) {
            $category[] = [
                'id' => $request['category_id'],
                'position' => 1,
            ];
        }
        if ($request['sub_category_id'] != null) {
            $category[] = [
                'id' => $request['sub_category_id'],
                'position' => 2,
            ];
        }
        if ($request['sub_sub_category_id'] != null) {
            $category[] = [
                'id' => $request['sub_sub_category_id'],
                'position' => 3,
            ];
        }
        return $category;
    }

    public function getColorsObject(object $request): bool|string
    {
        if ($request->has('colors_active') && $request->has('colors') && count($request['colors']) > 0) {
            $colors = $request['product_type'] == 'physical' ? json_encode($request['colors']) : json_encode([]);
        } else {
            $colors = json_encode([]);
        }
        return $colors;
    }

    public function getSlug(object $request): string
    {
        return Str::slug($request['name'][array_search('en', $request['lang'])], '-') . '-' . Str::random(6);
    }

    public function getChoiceOptions(object $request): array
    {
        $choice_options = [];
        if ($request->has('choice')) {
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['name'] = 'choice_' . $no;
                $item['title'] = $request->choice[$key];
                $item['options'] = explode(',', implode('|', $request[$str]));
                $choice_options[] = $item;
            }
        }
        return $choice_options;
    }

    public function getOptions(object $request): array
    {
        $options = [];
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $options[] = $request->colors;
        }
        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $no) {
                $name = 'choice_options_' . $no;
                $myString = implode('|', $request[$name]);
                $optionArray = array_filter(explode(',', $myString), function ($value) {
                    return $value !== '';
                });
                $options[] = $optionArray;
            }
        }
        return $options;
    }

    public function getCombinations(array $arrays): array
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public function getSkuCombinationView(object $request, object $product = null): string
    {
        $colorsActive = ($request->has('colors_active') && $request->has('colors') && count($request['colors']) > 0) ? 1 : 0;
        $unitPrice = $request['unit_price'];
        $productName = $request['name'][array_search('en', $request['lang'])];
        $options = $this->getOptions(request: $request);
        $combinations = $this->getCombinations(arrays: $options);
        $combinations = $this->generatePhysicalVariationCombination(request: $request, options: $options, combinations: $combinations, product: $product);

        if ($product) {
            return view(Product::SKU_EDIT_COMBINATION[VIEW], compact('combinations', 'unitPrice', 'colorsActive', 'productName'))->render();
        } else {
            return view(Product::SKU_COMBINATION[VIEW], compact('combinations', 'unitPrice', 'colorsActive', 'productName'))->render();
        }
    }

    public function getVariations(object $request, array $combinations): array
    {
        $variations = [];
        if (isset($combinations[0]) && count($combinations[0]) > 0) {
            foreach ($combinations as $combination) {
                $str = '';
                foreach ($combination as $combinationKey => $item) {
                    if ($combinationKey > 0) {
                        $str .= '-' . str_replace(' ', '', $item);
                    } else {
                        if ($request->has('colors_active') && $request->has('colors') && count($request['colors']) > 0) {
                            $color_name = $this->color->where('code', $item)->first()->name;
                            $str .= $color_name;
                        } else {
                            $str .= str_replace(' ', '', $item);
                        }
                    }
                }
                $item = [];
                $item['type'] = $str;
                $item['price'] = currencyConverter(abs($request['price_' . str_replace('.', '_', $str)]));
                $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
                $item['qty'] = abs($request['qty_' . str_replace('.', '_', $str)]);
                $variations[] = $item;
            }
        }

        return $variations;
    }

    public function getTotalQuantity(array $variations): int
    {
        $sum = 0;
        foreach ($variations as $item) {
            if (isset($item['qty'])) {
                $sum += $item['qty'];
            }
        }
        return $sum;
    }

    public function getCategoryDropdown(object $request, object $categories): string
    {
        $dropdown = '<option value="' . 0 . '" disabled selected>---' . translate("Select") . '---</option>';
        foreach ($categories as $row) {
            if ($row->id == $request['sub_category']) {
                $dropdown .= '<option value="' . $row->id . '" selected >' . $row->defaultName . '</option>';
            } else {
                $dropdown .= '<option value="' . $row->id . '">' . $row->defaultName . '</option>';
            }
        }

        return $dropdown;
    }

    public function deleteImages(object $product): bool
    {
        foreach (json_decode($product['images'], true) as $image) {
            $this->delete(filePath: '/product/' . (isset($image['image_name']) ? $image['image_name'] : $image));
        }
        $this->delete(filePath: '/product/thumbnail/' . $product['thumbnail']);

        return true;
    }

    public function deletePreviewFile(object $product): bool
    {
        if ($product['preview_file']) {
            $this->delete(filePath: '/product/preview/' . $product['preview_file']);
        }
        return true;
    }

    public function deleteImage(object $request, object $product): array
    {
        $colors = json_decode($product['colors']);
        $color_image = json_decode($product['color_image']);
        $images = [];
        $imageNames = [];
        $color_images = [];
        if ($colors && $color_image) {
            foreach ($color_image as $img) {
                if ($img->color != $request['color'] && $img?->image_name != $request['name']) {
                    $imageNames[] = $img->image_name;
                    $color_images[] = [
                        'color' => $img->color != null ? $img->color : null,
                        'image_name' => $img->image_name,
                        'storage' => $img?->storage ?? 'public',
                    ];
                }
            }

            foreach (json_decode($product['images']) as $image) {
                $imageName = $image?->image_name ?? $image;
                if ($imageName != $request['name'] && !in_array($imageName, $imageNames)) {
                    $color_images[] = [
                        'color' => null,
                        'image_name' => $imageName,
                        'storage' => $image?->storage ?? 'public',
                    ];
                }
            }
        }

        foreach (json_decode($product['images']) as $image) {
            $imageName = $image?->image_name ?? $image;
            if ($imageName != $request['name']) {
                $images[] = $image;
            }
        }

        return [
            'images' => $images,
            'color_images' => $color_images
        ];
    }

    public function getAddProductData(object $request, string $addedBy): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $processedImages = $this->getProcessedImages(request: $request); //once the images are processed do not call this function again just use the variable
        $combinations = $this->getCombinations($this->getOptions(request: $request));
        $variations = $this->getVariations(request: $request, combinations: $combinations);
        $stockCount = isset($combinations[0]) && count($combinations[0]) > 0 ? $this->getTotalQuantity(variations: $variations) : (integer)$request['current_stock'];

        $digitalFile = '';
        if ($request['product_type'] == 'digital' && $request['digital_product_type'] == 'ready_product' && $request['digital_file_ready']) {
            $digitalFile = $this->fileUpload(dir: 'product/digital-product/', format: $request['digital_file_ready']->getClientOriginalExtension(), file: $request['digital_file_ready']);
        }

        $previewFile = $request['product_type'] == 'digital' && $request->existing_preview_file ? $request->existing_preview_file : '';
        if ($request['product_type'] == 'digital' && $request->has('preview_file') && $request['preview_file']) {
            $previewFile = $this->fileUpload(dir: 'product/preview/', format: $request['preview_file']->getClientOriginalExtension(), file: $request['preview_file']);
        }

        $digitalFileOptions = $this->getDigitalVariationOptions(request: $request);
        $digitalFileCombinations = $this->getDigitalVariationCombinations(arrays: $digitalFileOptions);

        return [
            'added_by' => $addedBy,
            'user_id' => $addedBy == 'admin' ? auth('admin')->id() : auth('seller')->id(),
            'name' => $request['name'][array_search('en', $request['lang'])],
            'code' => $request['code'],
            'slug' => $this->getSlug($request),
            'category_ids' => json_encode($this->getCategoriesArray(request: $request)),
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
            'brand_id' => $request['product_type'] == "physical" ? ( $request['brand_id'] ?? null) : null,
            'unit' => $request['product_type'] == 'physical' ? $request['unit'] : null,
            'digital_product_type' => $request['product_type'] == 'digital' ? $request['digital_product_type'] : null,
            'digital_file_ready' => $digitalFile,
            'digital_file_ready_storage_type' => $digitalFile ? $storage : null,
            'product_type' => $request['product_type'],
            'details' => $request['description'][array_search('en', $request['lang'])],
            'colors' => $this->getColorsObject(request: $request),
            'choice_options' => $request['product_type'] == 'physical' ? json_encode($this->getChoiceOptions(request: $request)) : json_encode([]),
            'variation' => $request['product_type'] == 'physical' ? json_encode($variations) : json_encode([]),
            'digital_product_file_types' => $request->has('extensions_type') ? $request->get('extensions_type') : [],
            'digital_product_extensions' => $digitalFileCombinations,
            'unit_price' => currencyConverter(amount: $request['unit_price']),
            'purchase_price' => 0,
            'tax' => $request['tax_type'] == 'flat' ? currencyConverter(amount: $request['tax']) : $request['tax'],
            'tax_type' => $request->get('tax_type', 'percent'),
            'tax_model' => $request['tax_model'],
            'discount' => $request['discount_type'] == 'flat' ? currencyConverter(amount: $request['discount']) : $request['discount'],
            'discount_type' => $request['discount_type'],
            'attributes' => $request['product_type'] == 'physical' ? json_encode($request['choice_attributes']) : json_encode([]),
            'current_stock' => $request['product_type'] == 'physical' ? abs($stockCount) : 999999999,
            'minimum_order_qty' => $request['minimum_order_qty'],
            'video_provider' => 'youtube',
            'video_url' => $request['video_url'],
            'status' => $addedBy == 'admin' ? 1 : 0,
            'request_status' => $addedBy == 'admin' ? 1 : (getWebConfig(name: 'new_product_approval') == 1 ? 0 : 1),
            'shipping_cost' => $request['product_type'] == 'physical' ? currencyConverter(amount: $request['shipping_cost']) : 0,
            'multiply_qty' => ($request['product_type'] == 'physical') ? ($request['multiply_qty'] == 'on' ? 1 : 0) : 0, //to be changed in form multiply_qty
            'color_image' => json_encode($processedImages['colored_image_names']),
            'images' => json_encode($processedImages['image_names']),
            'thumbnail' => $request->has('image') ? $this->upload(dir: 'product/thumbnail/', format: 'webp', image: $request['image']) : $request->existing_thumbnail,
            'thumbnail_storage_type' => $request->has('image') ? $storage : null,
            'preview_file' => $previewFile,
            'preview_file_storage_type' => $request->has('image') ? $storage : $request->get('existing_preview_file_storage_type', null),
            'meta_title' => $request['meta_title'],
            'meta_description' => $request['meta_description'],
            'meta_image' => $request->has('meta_image') ? $this->upload(dir: 'product/meta/', format: 'webp', image: $request['meta_image']) : $request->existing_meta_image,
        ];
    }

    public function getUpdateProductData(object $request, object $product, string $updateBy): array
    {
        $storage = config('filesystems.disks.default') ?? 'public';
        $processedImages = $this->getProcessedUpdateImages(request: $request, product: $product);
        $combinations = $this->getCombinations($this->getOptions(request: $request));
        $variations = $this->getVariations(request: $request, combinations: $combinations);
        $stockCount = isset($combinations[0]) && count($combinations[0]) > 0 ? $this->getTotalQuantity(variations: $variations) : (integer)$request['current_stock'];

        if ($request->has('extensions_type') && $request->has('digital_product_variant_key')) {
            $digitalFile = null;
        } else {
            $digitalFile = $product['digital_file_ready'];
        }
        if ($request['product_type'] == 'digital') {
            if ($request['digital_product_type'] == 'ready_product' && $request->hasFile('digital_file_ready')) {
                $digitalFile = $this->update(dir: 'product/digital-product/', oldImage: $product['digital_file_ready'], format: $request['digital_file_ready']->getClientOriginalExtension(), image: $request['digital_file_ready'], fileType: 'file');
            } elseif (($request['digital_product_type'] == 'ready_after_sell') && $product['digital_file_ready']) {
                $digitalFile = null;
                // $this->delete(filePath: 'product/digital-product/' . $product['digital_file_ready']);
            }
        } elseif ($request['product_type'] == 'physical' && $product['digital_file_ready']) {
            $digitalFile = null;
            // $this->delete(filePath: 'product/digital-product/' . $product['digital_file_ready']);
        }

        $digitalFileOptions = $this->getDigitalVariationOptions(request: $request);
        $digitalFileCombinations = $this->getDigitalVariationCombinations(arrays: $digitalFileOptions);

        $dataArray = [
            'name' => $request['name'][array_search('en', $request['lang'])],
            'code' => $request['code'],
            'product_type' => $request['product_type'],
            'category_ids' => json_encode($this->getCategoriesArray(request: $request)),
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
            'brand_id' => $request['product_type'] == "physical" ? ($request['brand_id'] ?? null) : null,
            'unit' => $request['product_type'] == 'physical' ? $request['unit'] : null,
            'digital_product_type' => $request['product_type'] == 'digital' ? $request['digital_product_type'] : null,
            'details' => $request['description'][array_search('en', $request['lang'])],
            'colors' => $this->getColorsObject(request: $request),
            'choice_options' => $request['product_type'] == 'physical' ? json_encode($this->getChoiceOptions(request: $request)) : json_encode([]),
            'variation' => $request['product_type'] == 'physical' ? json_encode($variations) : json_encode([]),
            'digital_product_file_types' => $request->has('extensions_type') ? $request->get('extensions_type') : [],
            'digital_product_extensions' => $digitalFileCombinations,
            'unit_price' => currencyConverter(amount: $request['unit_price']),
            'purchase_price' => 0,
            'tax' => $request['tax_type'] == 'flat' ? currencyConverter(amount: $request['tax']) : $request['tax'],
            'tax_type' => $request['tax_type'],
            'tax_model' => $request['tax_model'],
            'discount' => $request['discount_type'] == 'flat' ? currencyConverter(amount: $request['discount']) : $request['discount'],
            'discount_type' => $request['discount_type'],
            'attributes' => $request['product_type'] == 'physical' ? json_encode($request['choice_attributes']) : json_encode([]),
            'current_stock' => $request['product_type'] == 'physical' ? abs($stockCount) : 999999999,
            'minimum_order_qty' => $request['minimum_order_qty'],
            'video_provider' => 'youtube',
            'video_url' => $request['video_url'],
            'multiply_qty' => ($request['product_type'] == 'physical') ? ($request['multiply_qty'] == 'on' ? 1 : 0) : 0,
            'color_image' => json_encode($processedImages['colored_image_names']),
            'images' => json_encode($processedImages['image_names']),
            'digital_file_ready' => $digitalFile,
            'digital_file_ready_storage_type' => $request->has('digital_file_ready') ? $storage : $product['digital_file_ready_storage_type'],
            'meta_title' => $request['meta_title'],
            'meta_description' => $request['meta_description'],
            'meta_image' => $request->file('meta_image') ? $this->update(dir: 'product/meta/', oldImage: $product['meta_image'], format: 'png', image: $request['meta_image']) : $product['meta_image'],
        ];

        if ($request->file('image')) {
            $dataArray += [
                'thumbnail' => $this->update(dir: 'product/thumbnail/', oldImage: $product['thumbnail'], format: 'webp', image: $request['image'], fileType: 'image'),
                'thumbnail_storage_type' => $storage
            ];
        }
        if ($request->file('preview_file')) {
            $dataArray += [
                'preview_file' => $this->update(dir: 'product/preview/', oldImage: $product['preview_file'], format: $request['preview_file']->getClientOriginalExtension(), image: $request['preview_file'], fileType: 'file'),
                'preview_file_storage_type' => $storage
            ];
        }
        if ($request['product_type'] == 'physical' && $product['preview_file']) {
            $this->delete(filePath: '/product/preview/' . $product['preview_file']);
            $dataArray += [
                'preview_file' => null,
            ];
        }

        if ($updateBy == 'seller' && getWebConfig(name: 'product_wise_shipping_cost_approval') == 1 && $product->shipping_cost != currencyConverter($request->shipping_cost)) {
            $dataArray += [
                'temp_shipping_cost' => currencyConverter($request->shipping_cost),
                'is_shipping_cost_updated' => 0,
                'shipping_cost' => $product->shipping_cost,
            ];
        } else {
            $dataArray += [
                'shipping_cost' => $request['product_type'] == 'physical' ? currencyConverter(amount: $request['shipping_cost']) : 0,
            ];
        }
        if ($updateBy == 'seller' && $product->request_status == 2) {
            $dataArray += [
                'request_status' => 0
            ];
        }
        if ($updateBy == 'admin' && $product->added_by == 'seller' && ($product->request_status == 2 || $product->request_status == 0)) {
            $dataArray += [
                'request_status' => 1
            ];
        }

        return $dataArray;
    }

    public function getUniqueProductSKUCode(): string
    {
        $code = strtoupper(Str::random('6'));
        if (\App\Models\Product::where('code', $code)->exists()) {
            return self::getUniqueProductSKUCode();
        }
        return $code;
    }

    public function getImportBulkProductData(object $request, string $addedBy): array
    {
        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => translate('you_have_uploaded_a_wrong_format_file') . ',' . translate('please_upload_the_right_file'),
                'products' => []
            ];
        }

        $columnKey = [
            'name',
            'category_id',
            'sub_category_id',
            'sub_sub_category_id',
            'brand_id', 'unit',
            'minimum_order_qty',
            'status',
            'refundable',
            'youtube_video_url',
            'unit_price',
            'tax',
            'discount',
            'discount_type',
            'current_stock',
            'details',
            'thumbnail'
        ];
        $skip = ['sub_category_id', 'sub_sub_category_id', 'brand_id', 'youtube_video_url', 'details', 'thumbnail'];

        if (count($collections) <= 0) {
            return [
                'status' => false,
                'message' => translate('you_need_to_upload_with_proper_data'),
                'products' => []
            ];
        }

        $products = [];
        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                if ($key != "" && !in_array($key, $columnKey)) {
                    return [
                        'status' => false,
                        'message' => translate('Please_upload_the_correct_format_file'),
                        'products' => []
                    ];
                }

                if ($key != "" && $value === "" && !in_array($key, $skip)) {
                    return [
                        'status' => false,
                        'message' => translate('Please fill ' . $key . ' fields'),
                        'products' => []
                    ];
                }
            }
            $thumbnail = explode('/', $collection['thumbnail']);

            $products[] = [
                'name' => $collection['name'],
                'slug' => Str::slug($collection['name'], '-') . '-' . Str::random(6),
                'category_ids' => json_encode([['id' => (string)$collection['category_id'], 'position' => 1], ['id' => (string)$collection['sub_category_id'], 'position' => 2], ['id' => (string)$collection['sub_sub_category_id'], 'position' => 3]]),
                'category_id' => $collection['category_id'],
                'sub_category_id' => $collection['sub_category_id'],
                'sub_sub_category_id' => $collection['sub_sub_category_id'],
                'brand_id' => $collection['brand_id'],
                'unit' => $collection['unit'],
                'minimum_order_qty' => $collection['minimum_order_qty'],
                'refundable' => $collection['refundable'],
                'unit_price' => currencyConverter(amount: $collection['unit_price']),
                'purchase_price' => 0,
                'tax' => currencyConverter(amount: $collection['tax']),
                'discount' => $collection['discount_type'] == 'flat' ? currencyConverter(amount: $collection['discount']) : $collection['discount'],
                'discount_type' => $collection['discount_type'],
                'shipping_cost' => 0,
                'current_stock' => $collection['current_stock'],
                'details' => $collection['details'],
                'video_provider' => 'youtube',
                'video_url' => $collection['youtube_video_url'],
                'images' => json_encode(['def.png']),
                'thumbnail' => $thumbnail[1] ?? $thumbnail[0],
                'status' => $addedBy == 'admin' && $collection['status'] == 1 ? 1 : 0,
                'request_status' => $addedBy == 'admin' ? 1 : (getWebConfig(name: 'new_product_approval') == 1 ? 0 : 1),
                'colors' => json_encode([]),
                'attributes' => json_encode([]),
                'choice_options' => json_encode([]),
                'variation' => json_encode([]),
                'featured_status' => 0,
                'added_by' => $addedBy,
                'user_id' => $addedBy == 'admin' ? auth('admin')->id() : auth('seller')->id(),
                'code' => self::getUniqueProductSKUCode(),
                'created_at' => now(),
            ];
        }

        return [
            'status' => true,
            'message' => count($products) . ' - ' . translate('products_imported_successfully'),
            'products' => $products
        ];
    }

    public function getAddProductDigitalVariationData(object $request, object|array $product): array
    {
        $digitalFileOptions = $this->getDigitalVariationOptions(request: $request);
        $digitalFileCombinations = $this->getDigitalVariationCombinations(arrays: $digitalFileOptions);

        $digitalFiles = [];
        foreach ($digitalFileCombinations as $combinationKey => $combination) {
            foreach ($combination as $item) {
                $string = $combinationKey . '-' . str_replace(' ', '', $item);
                $uniqueKey = strtolower(str_replace('-', '_', $string));
                $fileItem = $request->file('digital_files.' . $uniqueKey);
                $uploadedFile = '';
                if ($fileItem) {
                    $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                }
                $digitalFiles[] = [
                    'product_id' => $product->id,
                    'variant_key' => $request->input('digital_product_variant_key.' . $uniqueKey),
                    'sku' => $request->input('digital_product_sku.' . $uniqueKey),
                    'price' => currencyConverter(amount: $request->input('digital_product_price.' . $uniqueKey)),
                    'file' => $uploadedFile,
                ];
            }
        }
        return $digitalFiles;
    }

    public function getDigitalVariationCombinationView(object $request, object $product = null): string
    {
        $productName = $request['name'][array_search('en', $request['lang'])];
        $unitPrice = $request['unit_price'];
        $options = $this->getDigitalVariationOptions(request: $request);
        $combinations = $this->getDigitalVariationCombinations(arrays: $options);
        $digitalProductType = $request['digital_product_type'];
        $generateCombination = $this->generateDigitalVariationCombination(request: $request, combinations: $combinations, product: $product);
        return view(Product::DIGITAL_VARIATION_COMBINATION[VIEW], compact('generateCombination', 'unitPrice', 'productName', 'digitalProductType', 'request'))->render();
    }

    public function generatePhysicalVariationCombination(object|array $request, object|array $options, object|array $combinations, object|array|null $product): array
    {
        $productName = $request['name'][array_search('en', $request['lang'])];
        $unitPrice = $request['unit_price'];

        $generateCombination = [];
        $existingType = [];

        if ($product && $product->variation && count(json_decode($product->variation, true)) > 0) {
            foreach (json_decode($product->variation, true) as $digitalVariation) {
                $existingType[] = $digitalVariation['type'];
            }
        }

        $existingType = array_unique($existingType);

        $combinations = array_filter($combinations, function ($value) {
            return !empty($value);
        });

        foreach ($combinations as $combination) {
            $type = '';
            foreach ($combination as $combinationKey => $item) {
                if ($combinationKey > 0) {
                    $type .= '-' . str_replace(' ', '', $item);
                } else {
                    if ($request->has('colors_active') && $request->has('colors') && count($request['colors']) > 0) {
                        $color_name = $this->color->where('code', $item)->first()->name;
                        $type .= $color_name;
                    } else {
                        $type .= str_replace(' ', '', $item);
                    }
                }
            }

            $sku = '';
            foreach (explode(' ', $productName) as $value) {
                $sku .= substr($value, 0, 1);
            }
            $sku .= '-' . $type;
            if (in_array($type, $existingType)) {
                if ($product && $product->variation && count(json_decode($product->variation, true)) > 0) {
                    foreach (json_decode($product->variation, true) as $digitalVariation) {
                        if ($digitalVariation['type'] == $type) {
                            $digitalVariation['price'] = $digitalVariation['price'];
                            $digitalVariation['sku'] = str_replace(' ', '', $digitalVariation['sku']);
                            $generateCombination[] = $digitalVariation;
                        }
                    }
                }
            } else {
                $generateCombination[] = [
                    'type' => $type,
                    'price' => currencyConverter(amount: $unitPrice),
                    'sku' => str_replace(' ', '', $sku),
                    'qty' => 1,
                ];
            }
        }

        return $generateCombination;
    }


    public function generateDigitalVariationCombination(object|array $request, object|array $combinations, object|array|null $product): array
    {
        $productName = $request['name'][array_search('en', $request['lang'])];
        $unitPrice = $request['unit_price'];

        $generateCombination = [];
        foreach ($combinations as $combinationKey => $combination) {
            foreach ($combination as $item) {
                $sku = '';
                foreach (explode(' ', $productName) as $value) {
                    $sku .= substr($value, 0, 1);
                }
                $string = $combinationKey . '-' . preg_replace('/\s+/', '-', $item);
                $sku .= '-' . $combinationKey . '-' . str_replace(' ', '', $item);
                $uniqueKey = strtolower(str_replace('-', '_', $string));
                if ($product && $product->digitalVariation && count($product->digitalVariation) > 0) {
                    $productDigitalVariationArray = [];
                    foreach ($product->digitalVariation->toArray() as $variationKey => $digitalVariation) {
                        $productDigitalVariationArray[$digitalVariation['variant_key']] = $digitalVariation;
                    }
                    if (key_exists($string, $productDigitalVariationArray)) {
                        $generateCombination[] = [
                            'product_id' => $product['id'],
                            'unique_key' => $uniqueKey,
                            'variant_key' => $productDigitalVariationArray[$string]['variant_key'],
                            'sku' => $productDigitalVariationArray[$string]['sku'],
                            'price' => $productDigitalVariationArray[$string]['price'],
                            'file' => $productDigitalVariationArray[$string]['file'],
                        ];
                    } else {
                        $generateCombination[] = [
                            'product_id' => $product['id'],
                            'unique_key' => $uniqueKey,
                            'variant_key' => $string,
                            'sku' => $sku,
                            'price' => currencyConverter(amount: $unitPrice),
                            'file' => '',
                        ];
                    }
                } else {
                    $generateCombination[] = [
                        'product_id' => '',
                        'unique_key' => $uniqueKey,
                        'variant_key' => $string,
                        'sku' => $sku,
                        'price' => currencyConverter(amount: $unitPrice),
                        'file' => '',
                    ];
                }
            }
        }
        return $generateCombination;
    }

    public function getDigitalVariationOptions(object $request): array
    {
        $options = [];
        if ($request->has('extensions_type')) {
            foreach ($request->extensions_type as $type) {
                $name = 'extensions_options_' . $type;
                $my_str = implode('|', $request[$name]);
                $optionsArray = [];
                foreach (explode(',', $my_str) as $option) {
                    $optionsArray[] = str_replace('.', '_', removeSpecialCharacters($option));
                }
                $options[$type] = $optionsArray;
            }
        }
        return $options;
    }

    public function getDigitalVariationCombinations(array $arrays): array
    {
        $result = [];
        foreach ($arrays as $arrayKey => $array) {
            foreach ($array as $key => $value) {
                if ($value) {
                    $result[$arrayKey][] = $value;
                }
            }
        }
        return $result;
    }

    public function getProductSEOData(object $request, object|null $product = null, string $action = null): array
    {
        if ($product) {
            if ($request->file('meta_image')) {
                $metaImage = $this->update(dir: 'product/meta/', oldImage: $product['meta_image'], format: 'png', image: $request['meta_image']);
            } elseif (!$request->file('meta_image') && $request->file('image') && $action == 'add') {
                $metaImage = $this->upload(dir: 'product/meta/', format: 'webp', image: $request['image']);
            } else {
                $metaImage = $product?->seoInfo?->image ?? $product['meta_image'];
            }
        } else {
            if ($request->file('meta_image')) {
                $metaImage = $this->upload(dir: 'product/meta/', format: 'webp', image: $request['meta_image']);
            } elseif (!$request->file('meta_image') && $request->file('image') && $action == 'add') {
                $metaImage = $this->upload(dir: 'product/meta/', format: 'webp', image: $request['image']);
            }
        }
        return [
            "product_id" => $product['id'],
            "title" => $request['meta_title'] ?? ($product ? $product['meta_title'] : null),
            "description" => $request['meta_description'] ?? ($product ? $product['meta_description'] : null),
            "index" => $request['meta_index'] == 'index' ? '' : 'noindex',
            "no_follow" => $request['meta_no_follow'] ? 'nofollow' : '',
            "no_image_index" => $request['meta_no_image_index'] ? 'noimageindex' : '',
            "no_archive" => $request['meta_no_archive'] ? 'noarchive' : '',
            "no_snippet" => $request['meta_no_snippet'] ?? 0,
            "max_snippet" => $request['meta_max_snippet'] ?? 0,
            "max_snippet_value" => $request['meta_max_snippet_value'] ?? 0,
            "max_video_preview" => $request['meta_max_video_preview'] ?? 0,
            "max_video_preview_value" => $request['meta_max_video_preview_value'] ?? 0,
            "max_image_preview" => $request['meta_max_image_preview'] ?? 0,
            "max_image_preview_value" => $request['meta_max_image_preview_value'] ?? 0,
            "image" => $metaImage ?? ($product ? $product['meta_image'] : null),
            "created_at" => now(),
            "updated_at" => now(),
        ];
    }

    public function getProductAuthorsInfo(object|array $product): array
    {
        $productAuthorIds = [];
        $productAuthorNames = [];
        $productAuthors = [];
        if ($product?->digitalProductAuthors && count($product?->digitalProductAuthors) > 0) {
            foreach ($product?->digitalProductAuthors as $author) {
                $productAuthorIds[] = $author['author_id'];
                $productAuthors[] = $author?->author;
                if ($author?->author?->name) {
                    $productAuthorNames[] = $author?->author?->name;
                }
            }
        }
        return [
            'ids' => $productAuthorIds,
            'names' => $productAuthorNames,
            'data' => $productAuthors,
        ];
    }

    public function getProductPublishingHouseInfo(object|array $product): array
    {
        $productPublishingHouseIds = [];
        $productPublishingHouseNames = [];
        $productPublishingHouses = [];
        if ($product?->digitalProductPublishingHouse && count($product?->digitalProductPublishingHouse) > 0) {
            foreach ($product?->digitalProductPublishingHouse as $publishingHouse) {
                $productPublishingHouseIds[] = $publishingHouse['publishing_house_id'];
                $productPublishingHouses[] = $publishingHouse?->publishingHouse;
                if ($publishingHouse?->publishingHouse?->name) {
                    $productPublishingHouseNames[] = $publishingHouse?->publishingHouse?->name;
                }
            }
        }
        return [
            'ids' => $productPublishingHouseIds,
            'names' => $productPublishingHouseNames,
            'data' => $productPublishingHouses,
        ];
    }

    public function sendRestockProductNotification(object|array $restockRequest, string $type = null): void
    {
        // Send Notification to customer
        $data = [
            'topic' => getRestockProductFCMTopic(restockRequest: $restockRequest),
            'title' => $restockRequest?->product?->name,
            'product_id' => $restockRequest?->product?->id,
            'slug' => $restockRequest?->product?->slug,
            'description' => $type == 'restocked' ? translate('This_product_has_restocked') : translate('Your_requested_restock_product_has_been_updated'),
            'image' => getStorageImages(path: $restockRequest?->product?->thumbnail_full_url ?? '', type: 'product'),
            'route' => route('product', $restockRequest?->product?->slug),
            'type' => 'product_restock_update',
            'status' => $type == 'restocked' ? 'product_restocked' : 'product_update',
        ];
        event(new RestockProductNotificationEvent(data: $data));
    }

    public function validateStockClearanceProductDiscount($stockClearanceProduct): bool
    {
        if ($stockClearanceProduct && $stockClearanceProduct['discount_type'] == 'flat' && $stockClearanceProduct?->setup && $stockClearanceProduct?->setup?->discount_type == 'product_wise') {
            $minimumPrice = $stockClearanceProduct?->product?->unit_price;
            foreach ((json_decode($stockClearanceProduct?->product?->variation, true) ?? []) as $variation) {
                if ($variation['price'] < $minimumPrice) {
                    $minimumPrice = $variation['price'];
                }
            }

            if ($minimumPrice < $stockClearanceProduct['discount_amount']) {
                return false;
            }
        }
        return true;
    }
}
