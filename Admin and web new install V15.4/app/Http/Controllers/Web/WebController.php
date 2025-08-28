<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Models\Admin;
use App\Services\ProductService;
use App\Services\RecaptchaService;
use App\Traits\CacheManagerTrait;
use App\Traits\InHouseTrait;
use App\Models\User;
use App\Traits\MaintenanceModeTrait;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Events\DigitalProductOtpVerificationEvent;
use App\Http\Controllers\Controller;
use App\Models\OfflinePaymentMethod;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\ShippingType;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\DeliveryZipCode;
use App\Models\DigitalProductOtpVerification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCompare;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\Wishlist;
use App\Traits\CommonTrait;
use App\Traits\SmsGateway;
use App\Utils\CartManager;
use App\Utils\Convert;
use App\Utils\CustomerManager;
use App\Utils\OrderManager;
use App\Utils\ProductManager;
use App\Utils\SMSModule;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use function App\Utils\payment_gateways;

class WebController extends Controller
{
    use CommonTrait;
    use InHouseTrait;
    use SmsGateway;
    use MaintenanceModeTrait;
    use CacheManagerTrait;

    public function __construct(
        private OrderDetail                                   $order_details,
        private Product                                       $product,
        private Wishlist                                      $wishlist,
        private Order                                         $order,
        private Category                                      $category,
        private Brand                                         $brand,
        private Seller                                        $seller,
        private ProductCompare                                $compare,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
        private readonly ProductService                       $productService,
    )
    {

    }

    public function maintenance_mode(): View|RedirectResponse
    {
        if ($this->checkMaintenanceMode()) {
            return view(VIEW_FILE_NAMES['maintenance_mode'], [
                'maintenanceMessages' => getWebConfig(name: 'maintenance_message_setup') ?? [],
                'maintenanceTypeAndDuration' => getWebConfig(name: 'maintenance_duration_setup') ?? [],
            ]);
        }
        return redirect()->route('home');
    }

    public function search_shop(Request $request)
    {
        $key = explode(' ', $request['shop_name']);
        $sellers = Shop::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%");
            }
        })->whereHas('seller', function ($query) {
            return $query->where(['status' => 'approved']);
        })->paginate(30);
        return view(VIEW_FILE_NAMES['all_stores_page'], compact('sellers'));
    }

    public function getAllCategoriesView(Request $request): View|RedirectResponse
    {
        if (theme_root_path() == 'theme_fashion') {
            Toastr::warning(translate('Page_not_found'));
            return back();
        }

        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'categories']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $categories = Category::with(['product' => function ($query) {
            return $query->active()->withCount(['orderDetails']);
        }])->when($request['search'], function ($query) use ($request) {
            return $query->where('name', 'like', "%{$request['search']}%");
        })->withCount(['product' => function ($query) {
            $query->active();
        }])->with(['childes' => function ($query) {
            $query->with(['childes' => function ($query) {
                $query->withCount(['subSubCategoryProduct'])->where('position', 2);
            }])->withCount(['subCategoryProduct'])->where('position', 1);
        }, 'childes.childes'])->where('position', 0)->get();

        return view('web-views.products.categories', [
            'categories' => CategoryManager::getPriorityWiseCategorySortQuery(query: $categories),
            'robotsMetaContentData' => $robotsMetaContentData
        ]);
    }

    public function categories_by_category($id)
    {
        $category = Category::with(['childes.childes'])->where('id', $id)->first();
        return response()->json([
            'view' => view('web-views.partials._category-list-ajax', compact('category'))->render(),
        ]);
    }

    public function getAllBrandsView(Request $request): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'brands']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $brandStatus = getWebConfig(name: 'product_brand');
        session()->put('product_brand', $brandStatus);
        if ($brandStatus == 1) {
            $brandList = Brand::active()->with(['brandProducts' => function ($query) {
                return $query->withCount(['orderDetails']);
            }])
                ->withCount('brandProducts')
                ->when($request->has('search'), function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request['search'] . '%');
                });

            return view(VIEW_FILE_NAMES['all_brands'], [
                'brands' => self::getPriorityWiseBrandProductsQuery(request: $request, query: $brandList),
                'robotsMetaContentData' => $robotsMetaContentData
            ]);
        } else {
            return redirect()->route('home');
        }
    }

    function getPriorityWiseBrandProductsQuery($request, $query)
    {
        if (theme_root_path() == 'theme_aster') {
            $paginateLimit = 12;
        } elseif (theme_root_path() == 'theme_fashion') {
            $paginateLimit = 10;
        } else {
            $paginateLimit = 18;
        }
        $brandProductSortBy = getWebConfig(name: 'brand_list_priority');
        $orderBy = $request->get('order_by', 'desc');

        if (empty($request['order_by']) && $brandProductSortBy && ($brandProductSortBy['custom_sorting_status'] == 1)) {
            if ($brandProductSortBy['sort_by'] == 'most_order') {
                $query = $query->get()->map(function ($brand) {
                    $brand['order_count'] = $brand->brandProducts->sum('order_details_count');
                    return $brand;
                })->sortByDesc('order_count');
            } elseif ($brandProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($brandProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($brandProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($brandProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            return $query->paginate($paginateLimit)->appends(['order_by' => $orderBy, 'search' => $request['search']]);
        } else {
            return $query->orderBy('name', $orderBy)->latest()->paginate($paginateLimit)->appends(['order_by' => $orderBy, 'search' => $request['search']]);
        }

    }

    public function getAllVendorsView(Request $request): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'vendors']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $businessMode = getWebConfig(name: 'business_mode');
        if (isset($businessMode) && $businessMode == 'single') {
            Toastr::warning(translate('access_denied') . ' !!');
            return back();
        }

        $vendorsList = Shop::active()->where('author_type', 'vendor')
            ->withCount(['products' => function ($query) {
                $query->active();
            }])
            ->when(isset($request['shop_name']), function ($query) use ($request) {
                $key = explode(' ', $request['shop_name']);
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                    }
                });
            })
            ->with('seller', function ($query) {
                $query->with('product', function ($query) {
                    $query->active()->with('reviews', function ($query) {
                        $query->active();
                    });
                })->withCount(['orders']);
            })
            ->get()
            ->each(function ($shop) {
                $shop->orders_count = $shop->seller->orders_count;

                $productReviews = $shop->seller->product->pluck('reviews')->collapse();
                $productReviews = $productReviews->where('status', 1);
                $shop->average_rating = $productReviews->avg('rating');
                $shop->review_count = $productReviews->count();
                $shop->total_rating = $productReviews->sum('rating');

                $positiveReviewsCount = $productReviews->where('rating', '>=', 4)->count();
                $shop->positive_review = ($shop->review_count !== 0) ? ($positiveReviewsCount * 100) / $shop->review_count : 0;

                $currentDate = date('Y-m-d');
                $startDate = date('Y-m-d', strtotime($shop['vacation_start_date']));
                $endDate = date('Y-m-d', strtotime($shop['vacation_end_date']));
                $shop->is_vacation_mode_now = $shop['vacation_status'] && ($currentDate >= $shop['vacation_start_date']) && ($currentDate <= $shop['vacation_end_date']) ? 1 : 0;
                return $shop;
            });


        $inhouseProducts = Product::active()->with(['reviews', 'rating'])->withCount('reviews')->where(['added_by' => 'admin'])->get();
        $inhouseProductCount = $inhouseProducts->count();

        $inhouseReviewData = Review::active()->whereIn('product_id', $inhouseProducts->pluck('id'));
        $inhouseReviewDataCount = $inhouseReviewData->count();
        $inhouseRattingStatusPositive = 0;
        foreach ($inhouseReviewData->pluck('rating') as $singleRating) {
            ($singleRating >= 4 ? ($inhouseRattingStatusPositive++) : '');
        }

        $inhouseShop = getInHouseShopConfig();

        $inhouseShop->products_count = $inhouseProductCount;

        $inhouseShop->review_count = $inhouseReviewDataCount;
        $inhouseShop->average_rating = $inhouseReviewData->avg('rating');
        $inhouseShop->positive_review = $inhouseReviewDataCount != 0 ? ($inhouseRattingStatusPositive * 100) / $inhouseReviewDataCount : 0;
        $inhouseShop->orders_count = Order::where(['seller_is' => 'admin'])->count();
        $inhouseShop->is_vacation_mode_now = checkVendorAbility(type: 'inhouse', status: 'vacation_status') ? 1 : 0;

        if (!(isset($request['shop_name']) && !str_contains(strtolower(getInHouseShopConfig(key: 'name')), strtolower($request['shop_name'])))) {
            $vendorsList = $vendorsList->reject(function ($shop) use ($inhouseShop) {
                return $shop->seller_id === $inhouseShop->seller_id && $shop->author_type === $inhouseShop->author_type;
            })->prepend($inhouseShop);
        }

        if ($request->has('filter') && $request['filter'] == 'top-vendors') {
            $vendorsList = ProductManager::getPriorityWiseTopVendorQuery($vendorsList);
        } else {
            $vendorsList = ProductManager::getPriorityWiseVendorQuery($vendorsList);
        }

        if ($request->has('order_by')) {
            if ($request['order_by'] == 'asc') {
                $vendorsList = $vendorsList->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } else if ($request['order_by'] == 'desc') {
                $vendorsList = $vendorsList->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            } else if ($request['order_by'] == 'highest-products') {
                $vendorsList = $vendorsList->sortByDesc('products_count');
            } else if ($request['order_by'] == 'lowest-products') {
                $vendorsList = $vendorsList->sortBy('products_count');
            } else if ($request['order_by'] == 'rating-high-to-low') {
                $vendorsList = $vendorsList->sortByDesc('average_rating');
            } else if ($request['order_by'] == 'rating-low-to-high') {
                $vendorsList = $vendorsList->sortBy('average_rating');
            };
        }
        return view(VIEW_FILE_NAMES['all_stores_page'], [
            'vendorsList' => $vendorsList->paginate(12)->appends($request->all()),
            'order_by' => $request['order_by'],
            'robotsMetaContentData' => $robotsMetaContentData,
        ]);
    }

    public function seller_profile($id)
    {
        $seller_info = Seller::find($id);
        return view('web-views.seller-profile', compact('seller_info'));
    }

    public function getSearchedProducts(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Product name is required!',
        ]);

        $result = ProductManager::getSearchProductsForWeb($request['name'], $request['category_id'] ?? 'all');
        $products = $result['products'];
        if ($products == null) {
            $result = ProductManager::getTranslatedProductSearchForWeb($request['name'], $request['category_id'] ?? 'all');
            $products = $result['products'];
        }

        $sellers = Shop::where(function ($query) use ($request) {
            $query->orWhere('name', 'like', "%{$request['name']}%");
        })->whereHas('seller', function ($query) {
            return $query->where(['status' => 'approved']);
        })->with('products', function ($query) {
            return $query->active()->where('added_by', 'seller');
        })->get();

        $product_ids = [];
        foreach ($sellers as $seller) {
            if (isset($seller->product) && $seller->product->count() > 0) {
                $ids = $seller->product->pluck('id');
                array_push($product_ids, ...$ids);
            }
        }

        $companyName = getInHouseShopConfig(key: 'name');
        if (strpos($request['name'], $companyName) !== false) {
            $ids = Product::active()->Where('added_by', 'admin')->pluck('id');
            array_push($product_ids, ...$ids);
        }

        $seller_products = Product::active()->withCount('reviews')->whereIn('id', $product_ids)
            ->orderByRaw("LOCATE('{$request['name']}', name), name")->get();

        return response()->json([
            'result' => view(VIEW_FILE_NAMES['product_search_result'], compact('products', 'seller_products'))->render(),
            'seller_products' => $seller_products->count(),
        ]);
    }

    public function getSearchedProductsForCompareList(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Product name is required!',
        ]);
        $compare_id = $request['compare_id'];
        $result = ProductManager::getSearchProductsForWeb($request['name']);
        $products = $result['products'];
        if ($products == null) {
            $result = ProductManager::getTranslatedProductSearchForWeb($request['name']);
            $products = $result['products'];
        }
        return response()->json([
            'result' => view(VIEW_FILE_NAMES['product_search_result_for_compare_list'], compact('products', 'compare_id'))->render(),
        ]);
    }

    public function checkout_details(Request $request)
    {
        if (
            (!auth('customer')->check() || Cart::where(['customer_id' => auth('customer')->id()])->count() < 1)
            && (!getWebConfig(name: 'guest_checkout') || !session()->has('guest_id') || !session('guest_id'))
        ) {
            Toastr::error(translate('invalid_access'));
            return redirect('/');
        }
        ProductManager::updateProductPriceInCartList(request: $request);

        $response = OrderManager::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return isset($response['redirect']) ? redirect($response['redirect']) : redirect('/');
        }

        $countryRestrictStatus = getWebConfig(name: 'delivery_country_restriction');
        $zipRestrictStatus = getWebConfig(name: 'delivery_zip_code_area_restriction');
        $countries = $countryRestrictStatus ? $this->get_delivery_country_array() : COUNTRIES;
        $zipCodes = $zipRestrictStatus ? DeliveryZipCode::all() : 0;
        $billingInputByCustomer = getWebConfig(name: 'billing_input_by_customer');
        $defaultLocation = getWebConfig(name: 'default_location');

        $user = Helpers::getCustomerInformation($request);
        $shippingAddresses = ShippingAddress::where([
            'customer_id' => $user == 'offline' ? session('guest_id') : auth('customer')->id(),
            'is_guest' => $user == 'offline' ? 1 : '0',
        ])->get();

        $countriesName = [];
        $countriesCode = [];
        foreach ($countries as $country) {
            $countriesName[] = $country['name'];
            $countriesCode[] = $country['code'];
        }

        return view(VIEW_FILE_NAMES['order_shipping'], [
            'physical_product_view' => $response['physical_product_view'],
            'zip_codes' => $zipCodes,
            'country_restrict_status' => $countryRestrictStatus,
            'zip_restrict_status' => $zipRestrictStatus,
            'countries' => $countries,
            'countriesName' => $countriesName,
            'countriesCode' => $countriesCode,
            'billing_input_by_customer' => $billingInputByCustomer,
            'default_location' => $defaultLocation,
            'shipping_addresses' => $shippingAddresses,
            'billing_addresses' => $shippingAddresses
        ]);
    }

    public function checkout_payment(Request $request): View|RedirectResponse
    {
        if (!session('address_id') && !session('billing_address_id')) {
            Toastr::error(translate('Please_update_address_information'));
            return redirect()->route('checkout-details');
        }

        $response = OrderManager::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return $response['redirect'] ? redirect($response['redirect']) : redirect('/');
        }

        $vendorWiseCartList = OrderManager::processOrderGenerateData(data: [
            'coupon_code' => session('coupon_code') ?? '',
            'address_id' => session('address_id'),
            'billing_address_id' => session('billing_address_id'),
            'requestObj' => $request,
        ]);

        $isPhysicalProductExistArray = [];
        foreach ($vendorWiseCartList as $groupId => $vendorWiseCartListItem) {
            $isPhysicalProductExist = false;
            foreach ($vendorWiseCartListItem['cart_list'] as $cart) {
                if ($cart->product_type == 'physical') {
                    $isPhysicalProductExist = true;
                }
            }
            $isPhysicalProductExistArray[$groupId] = $isPhysicalProductExist;
        }
        $cashOnDeliveryBtnShow = !in_array(false, $isPhysicalProductExistArray);

        $vendorWiseCartListCollection = collect($vendorWiseCartList);
        $couponDiscount = $vendorWiseCartListCollection?->sum('coupon_discount') ?? 0;
        $referralDiscount = $vendorWiseCartListCollection?->sum('refer_and_earn_discount') ?? 0;
        $grandTotal = $vendorWiseCartListCollection?->sum('grand_total') ?? 0;
        $freeDeliveryDiscount = $vendorWiseCartListCollection?->sum('free_delivery_discount') ?? 0;
        $amount = $grandTotal - $referralDiscount - $freeDeliveryDiscount;

        $inr = Currency::where(['symbol' => '₹'])->first();
        $usd = Currency::where(['code' => 'USD'])->first();
        $myr = Currency::where(['code' => 'MYR'])->first();

        $offlinePaymentMethods = OfflinePaymentMethod::where('status', 1)->get();
        $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;
        $offlinePaymentStatus = getWebConfig(name: 'offline_payment');

        $availablePaymentMethod = [];

        $cashOnDeliveryStatus = getWebConfig(name: 'cash_on_delivery');
        if ($cashOnDeliveryStatus && $cashOnDeliveryStatus['status'] && $cashOnDeliveryBtnShow) {
            $availablePaymentMethod = ['cash_on_delivery'];
        }

        if (getWebConfig(name: 'digital_payment') && count(payment_gateways()) > 0) {
            $availablePaymentMethod = ['payment_gateways'];
        }

        if ($offlinePaymentStatus && $offlinePaymentStatus['status'] == 1 && count($offlinePaymentMethods) > 0) {
            $availablePaymentMethod = ['offline_payment'];
        }
        if (auth('customer')->check() && getWebConfig(name: 'wallet_status')) {
            $availablePaymentMethod = ['wallet_status'];
        }

        if (session()->has('address_id') && session()->has('billing_address_id')) {
            return view(VIEW_FILE_NAMES['payment_details'], [
                'cashOnDeliveryBtnShow' => $cashOnDeliveryBtnShow,
                'cash_on_delivery' => $cashOnDeliveryStatus,
                'digital_payment' => getWebConfig(name: 'digital_payment'),
                'wallet_status' => getWebConfig(name: 'wallet_status'),
                'offline_payment' => $offlinePaymentStatus,
                'coupon_discount' => $couponDiscount,
                'amount' => $amount,
                'inr' => $inr,
                'usd' => $usd,
                'myr' => $myr,
                'paymentGatewayPublishedStatus' => $paymentGatewayPublishedStatus,
                'payment_gateways_list' => payment_gateways(),
                'offline_payment_methods' => $offlinePaymentMethods,
                'activeMinimumMethods' => count($availablePaymentMethod) > 0,
            ]);
        }

        Toastr::error(translate('incomplete_info'));
        return back();
    }

    public function getCashOnDeliveryCheckoutComplete(Request $request): View|RedirectResponse|JsonResponse
    {
        if ($request['payment_method'] != 'cash_on_delivery') {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0, 'message' => translate('Something_went_wrong'),
                ]);
            }
            return back()->with('error', 'Something_went_wrong');
        }

        $response = OrderManager::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => $response['message'][0] ?? translate('Something went_wrong'),
                ]);
            }
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return isset($response['redirect']) ? redirect($response['redirect']) : redirect('/');
        }

        $cartListQuery = CartManager::getCartListQuery(type: 'checked');
        $productStockCheck = CartManager::product_stock_check($cartListQuery);
        $physicalProductExist = (bool)$cartListQuery->where('product_type', 'physical')->first();

        if (!$physicalProductExist) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('Something_went_wrong'),
                ]);
            }
            return back()->with('error', translate('Something_went_wrong'));
        }

        if (!$productStockCheck) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('the_following_items_in_your_cart_are_currently_out_of_stock'),
                ]);
            }
            Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
            return redirect()->route('shop-cart');
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('check_minimum_order_amount_requirement'),
                    'redirect' => route('shop-cart'),
                ]);
            }

            Toastr::info(translate('check_minimum_order_amount_requirement'));
            return redirect()->route('shop-cart');
        }

        if (session('newCustomerRegister')) {
            $newCustomerRegister = session('newCustomerRegister');
            if (User::where(['email' => $newCustomerRegister['email']])->orWhere(['phone' => $newCustomerRegister['phone']])->first()) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 0,
                        'message' => translate('Already_registered'),
                    ]);
                }
                Toastr::error(translate('Already_registered'));
                return back();
            }

            $addCustomer = User::create([
                'name' => $newCustomerRegister['name'],
                'f_name' => $newCustomerRegister['name'],
                'l_name' => $newCustomerRegister['l_name'],
                'email' => $newCustomerRegister['email'],
                'phone' => $newCustomerRegister['phone'],
                'is_active' => 1,
                'password' => bcrypt($newCustomerRegister['password']),
                'referral_code' => $newCustomerRegister['referral_code'],
            ]);
            session()->put('newRegisterCustomerInfo', $addCustomer);

            $guestID = session()->has('guest_id') ? session('guest_id') : 0;
            OrderManager::updateCustomerShippingAddressForOrder($guestID, $addCustomer['id'], session('address_id'));
            OrderManager::updateCustomerShippingAddressForOrder($guestID, $addCustomer['id'], session('billing_address_id'));
        }

        $orderIds = OrderManager::generateOrder(data: [
            'order_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'unpaid',
            'transaction_ref' => '',
            'coupon_code' => session('coupon_code'),
            'address_id' => session('address_id'),
            'billing_address_id' => session('billing_address_id'),
            'bring_change_amount' => $request['bring_change_amount'] ?? 0,
            'bring_change_amount_currency' => session('currency_code'),
        ]);

        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('newRegisterCustomerInfo');

        if ($request->ajax()) {
            return response()->json([
                'status' => 1,
                'message' => translate('Order_Placed_Successfully'),
                'redirect' => route('order-placed-success', ['orderIds' => json_encode($orderIds)]),
            ]);
        }

        return view(VIEW_FILE_NAMES['order_complete'], [
            'order_ids' => $orderIds,
            'isNewCustomerInSession' => $isNewCustomerInSession,
        ]);
    }

    public function getOrderPlaceView(Request $request): View
    {
        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('newRegisterCustomerInfo');

        $orderIds = json_decode($request['orderIds'] ?? '', true);

        return view(VIEW_FILE_NAMES['order_complete'], [
            'order_ids' => $orderIds,
            'isNewCustomerInSession' => $isNewCustomerInSession,
        ]);
    }

    public function getOfflinePaymentCheckoutComplete(Request $request): View|RedirectResponse
    {
        if ($request['payment_method'] != 'offline_payment') {
            return back()->with('error', 'Something went wrong!');
        }

        if (!session('address_id') && !session('billing_address_id')) {
            Toastr::error(translate('Please_update_address_information'));
            return redirect()->route('checkout-details');
        }

        $response = OrderManager::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return isset($response['redirect']) ? redirect($response['redirect']) : redirect('/');
        }

        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with('product')->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        $productStockCheck = CartManager::product_stock_check($carts);
        if (!$productStockCheck) {
            Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
            return redirect()->route('shop-cart');
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            Toastr::info(translate('check_minimum_order_amount_requirement'));
            return redirect()->route('shop-cart');
        }

        $offlinePaymentInfo = [];
        $method = OfflinePaymentMethod::where(['id' => $request['method_id'], 'status' => 1])->first();

        if (isset($method)) {
            $fields = array_column($method->method_informations, 'customer_input');
            $values = $request->all();

            $offlinePaymentInfo['method_id'] = $request['method_id'];
            $offlinePaymentInfo['method_name'] = $method->method_name;
            foreach ($fields as $field) {
                if (key_exists($field, $values)) {
                    $offlinePaymentInfo[$field] = $values[$field];
                }
            }
        }

        if (session('newCustomerRegister')) {
            $newCustomerRegister = session('newCustomerRegister');
            if (User::where(['email' => $newCustomerRegister['email']])->orWhere(['phone' => $newCustomerRegister['phone']])->first()) {
                Toastr::error(translate('Already_registered'));
                return back();
            }

            $addCustomer = User::create([
                'name' => $newCustomerRegister['name'],
                'f_name' => $newCustomerRegister['name'],
                'l_name' => $newCustomerRegister['l_name'],
                'email' => $newCustomerRegister['email'],
                'phone' => $newCustomerRegister['phone'],
                'is_active' => 1,
                'password' => bcrypt($newCustomerRegister['password']),
                'referral_code' => $newCustomerRegister['referral_code'],
            ]);
            session()->put('newRegisterCustomerInfo', $addCustomer);

            $customerID = session()->has('guest_id') ? session('guest_id') : 0;
            ShippingAddress::where(['customer_id' => $customerID, 'is_guest' => 1, 'id' => session('address_id')])
                ->update(['customer_id' => $addCustomer['id'], 'is_guest' => 0]);
            ShippingAddress::where(['customer_id' => $customerID, 'is_guest' => 1, 'id' => session('billing_address_id')])
                ->update(['customer_id' => $addCustomer['id'], 'is_guest' => 0]);
        }

        $orderIds = OrderManager::generateOrder(data: [
            'order_status' => 'pending',
            'payment_method' => 'offline_payment',
            'payment_status' => 'unpaid',
            'transaction_ref' => '',
            'coupon_code' => session('coupon_code'),
            'address_id' => session('address_id'),
            'billing_address_id' => session('billing_address_id'),
            'payment_note' => $request['payment_note'],
            'offline_payment_info' => $offlinePaymentInfo,
        ]);

        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('newRegisterCustomerInfo');

        return view(VIEW_FILE_NAMES['order_complete'], [
            'order_ids' => $orderIds,
            'isNewCustomerInSession' => $isNewCustomerInSession,
        ]);
    }

    public function checkout_complete_wallet(Request $request): View|RedirectResponse
    {
        if (!session('address_id') && !session('billing_address_id')) {
            Toastr::error(translate('Please_update_address_information'));
            return redirect()->route('checkout-details');
        }

        $response = OrderManager::checkValidationForCheckoutPages($request);
        if ($response['status'] == 0) {
            foreach ($response['message'] as $message) {
                Toastr::error($message);
            }
            return isset($response['redirect']) ? redirect($response['redirect']) : redirect('/');
        }

        $discount = session()->has('coupon_discount') ? session('coupon_discount') : 0;
        $orderWiseShippingDiscount = CartManager::order_wise_shipping_discount();
        $shippingCostSaved = CartManager::getShippingCostSavedForFreeDelivery(type: 'checked');
        $paymentAmount = CartManager::cart_grand_total(type: 'checked') - $discount - $orderWiseShippingDiscount - $shippingCostSaved;

        $user = Helpers::getCustomerInformation($request);
        if ($paymentAmount > $user->wallet_balance) {
            Toastr::warning(translate('Inefficient_balance_in_your_wallet_to_pay_for_this_order') . '!!');
            return back();
        } else {
            $cart_group_ids = CartManager::get_cart_group_ids(type: 'checked');
            $carts = Cart::whereHas('product', function ($query) {
                return $query->active();
            })->with('product')
                ->whereIn('cart_group_id', $cart_group_ids)
                ->where(['is_checked' => 1])->get();

            $productStockCheck = CartManager::product_stock_check($carts);
            if (!$productStockCheck) {
                Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
                return redirect()->route('shop-cart');
            }

            $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
            if ($verifyStatus['status'] == 0) {
                Toastr::info(translate('check_minimum_order_amount_requirement'));
                return redirect()->route('shop-cart');
            }

            $order_ids = OrderManager::generateOrder(data: [
                'order_status' => 'confirmed',
                'payment_method' => 'pay_by_wallet',
                'payment_status' => 'paid',
                'transaction_ref' => '',
                'coupon_code' => session('coupon_code'),
                'address_id' => session('address_id'),
                'billing_address_id' => session('billing_address_id'),
            ]);

            foreach ($order_ids as $order_id) {
                OrderManager::generateReferBonusForFirstOrder(orderId: $order_id);
            }

            CustomerManager::create_wallet_transaction($user->id, Convert::default($paymentAmount), 'order_place', 'order payment');
        }

        if (session()->has('payment_mode') && session('payment_mode') == 'app') {
            return redirect()->route('payment-success');
        }

        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        session()->forget('coupon_discount');
        return view(VIEW_FILE_NAMES['order_complete'], compact('order_ids', 'isNewCustomerInSession'));
    }

    public function order_placed(): View
    {
        $isNewCustomerInSession = session('newCustomerRegister');
        session()->forget('newCustomerRegister');
        return view(VIEW_FILE_NAMES['order_complete'], compact('isNewCustomerInSession'));
    }

    public function shop_cart(Request $request): View|RedirectResponse
    {

        if (
            (auth('customer')->check() && Cart::where(['customer_id' => auth('customer')->id()])->count() > 0)
            || (getWebConfig(name: 'guest_checkout') && session()->has('guest_id') && session('guest_id'))
        ) {
            ProductManager::updateProductPriceInCartList(request: $request);
            $topRatedShops = [];
            $newSellers = [];
            $currentDate = date('Y-m-d H:i:s');
            if (theme_root_path() === "theme_fashion") {

                $sellerList = $this->seller->approved()->with(['shop', 'product.reviews'])
                    ->withCount(['product' => function ($query) {
                        $query->active();
                    }])->get();
                $sellerList?->map(function ($seller) {
                    $rating = 0;
                    $count = 0;
                    foreach ($seller->product as $item) {
                        foreach ($item->reviews as $review) {
                            $rating += $review->rating;
                            $count++;
                        }
                    }
                    $averageRating = $rating / ($count == 0 ? 1 : $count);
                    $ratingCount = $count;
                    $seller['average_rating'] = $averageRating;
                    $seller['rating_count'] = $ratingCount;

                    $productCount = $seller->product->count();
                    $randomProduct = Arr::random($seller->product->toArray(), $productCount < 3 ? $productCount : 3);
                    $seller['product'] = $randomProduct;
                    return $seller;
                });
                $newSellers = $sellerList->sortByDesc('id')->take(12);
                $topRatedShops = $sellerList->where('rating_count', '!=', 0)->sortByDesc('average_rating')->take(12);
            }
            return view(VIEW_FILE_NAMES['cart_list'], compact('topRatedShops', 'newSellers', 'currentDate', 'request'));
        }
        Toastr::warning(translate('please_login_your_account'));
        if (theme_root_path() == 'default') {
            return redirect('customer/auth/login');
        } else {
            return redirect('/');
        }
    }

    public function seller_shop_product(Request $request, $id): View|JsonResponse
    {
        $products = Product::active()->withCount('reviews')->with('shop')->where(['added_by' => 'seller'])
            ->where('user_id', $id)
            ->whereJsonContains('category_ids', [
                ['id' => strval($request->category_id)],
            ])
            ->paginate(12);
        $shop = Shop::where('seller_id', $id)->first();
        if ($request['sort_by'] == null) {
            $request['sort_by'] = 'latest';
        }

        if ($request->ajax()) {
            return response()->json([
                'view' => view(VIEW_FILE_NAMES['products__ajax_partials'], compact('products'))->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['shop_view_page'], compact('products', 'shop'))->with('seller_id', $id);
    }

    public function getQuickView(Request $request): JsonResponse
    {
        $product = ProductManager::get_product($request['product_id']);
        $order_details = OrderDetail::where('product_id', $product->id)->get();
        $wishlists = Wishlist::where('product_id', $product->id)->get();
        $wishlist_status = Wishlist::where(['product_id' => $product->id, 'customer_id' => auth('customer')->id()])->count();
        $countOrder = count($order_details);
        $countWishlist = count($wishlists);
        $relatedProducts = Product::with(['reviews'])->withCount('reviews')->where('category_ids', $product->category_ids)->where('id', '!=', $product->id)->limit(12)->get();
        $currentDate = date('Y-m-d');
        $productAuthorsInfo = $this->productService->getProductAuthorsInfo(product: $product);
        $productPublishingHouseInfo = $this->productService->getProductPublishingHouseInfo(product: $product);

        $temporary_close = getWebConfig(name: 'temporary_close');
        $inhouse_vacation = getWebConfig(name: 'vacation_add');

        // Newly Added From Blade
        $overallRating = getOverallRating($product?->reviews);
        $rating = getRating($product->reviews);
        $reviews_of_product = Review::where('product_id', $product->id)->latest()->paginate(2);
        $decimal_point_settings = getWebConfig(name: 'decimal_point_settings');
        $more_product_from_seller = Product::active()->withCount('reviews')->where('added_by', $product->added_by)->where('id', '!=', $product->id)->where('user_id', $product->user_id)->latest()->take(5)->get();

        $firstVariationQuantity = $product['current_stock'];
        if (count(json_decode($product['variation'], true)) > 0) {
            $firstVariationQuantity = json_decode($product['variation'], true)[0]['qty'];
        }
        $firstVariationQuantity = $product['product_type'] == 'physical' ? $firstVariationQuantity : 999;

        return response()->json([
            'success' => 1,
            'product' => $product,
            'view' => view(VIEW_FILE_NAMES['product_quick_view_partials'], compact('product', 'countWishlist', 'countOrder',
                'relatedProducts', 'currentDate', 'productAuthorsInfo', 'productPublishingHouseInfo', 'wishlist_status', 'overallRating', 'rating', 'firstVariationQuantity'))->render(),
        ]);
    }

    public function discounted_products(Request $request): View|JsonResponse
    {
        $request['sort_by'] == null ? $request['sort_by'] == 'latest' : $request['sort_by'];

        $productData = Product::active()->with(['reviews'])->withCount('reviews');

        $query = $productData;

        if ($request['data_from'] == 'category') {
            $products = $productData->get();
            $product_ids = [];
            foreach ($products as $product) {
                foreach (json_decode($product['category_ids'], true) as $category) {
                    if ($category['id'] == $request['id']) {
                        $product_ids[] = $product['id'];
                    }
                }
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'brand') {
            $query = $productData->where('brand_id', $request['id']);
        }

        if ($request['data_from'] == 'latest') {
            $query = $productData->orderBy('id', 'DESC');
        }

        if ($request['data_from'] == 'top-rated') {
            $reviews = Review::select('product_id', DB::raw('AVG(rating) as count'))
                ->groupBy('product_id')
                ->orderBy("count", 'desc')->get();
            $product_ids = [];
            foreach ($reviews as $review) {
                $product_ids[] = $review['product_id'];
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'best-selling') {
            $details = OrderDetail::with('product')
                ->select('product_id', DB::raw('COUNT(product_id) as count'))
                ->groupBy('product_id')
                ->orderBy("count", 'desc')
                ->get();
            $product_ids = [];
            foreach ($details as $detail) {
                $product_ids[] = $detail['product_id'];
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'most-favorite') {
            $details = Wishlist::with('product')
                ->select('product_id', DB::raw('COUNT(product_id) as count'))
                ->groupBy('product_id')
                ->orderBy("count", 'desc')
                ->get();
            $product_ids = [];
            foreach ($details as $detail) {
                $product_ids[] = $detail['product_id'];
            }
            $query = $productData->whereIn('id', $product_ids);
        }

        if ($request['data_from'] == 'featured') {
            $query = Product::with(['reviews'])->active()->where('featured', 1);
        }

        if ($request['data_from'] == 'search') {
            $key = explode(' ', $request['name']);
            $query = $productData->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        }

        if ($request['data_from'] == 'discounted_products') {
            $query = Product::with(['reviews'])->active()->where('discount', '!=', 0);
        }

        if ($request['sort_by'] == 'latest') {
            $fetched = $query->latest();
        } elseif ($request['sort_by'] == 'low-high') {
            $fetched = $query->orderBy('unit_price', 'ASC');
        } elseif ($request['sort_by'] == 'high-low') {
            $fetched = $query->orderBy('unit_price', 'DESC');
        } elseif ($request['sort_by'] == 'a-z') {
            $fetched = $query->orderBy('name', 'ASC');
        } elseif ($request['sort_by'] == 'z-a') {
            $fetched = $query->orderBy('name', 'DESC');
        } else {
            $fetched = $query;
        }

        if ($request['min_price'] != null || $request['max_price'] != null) {
            $fetched = $fetched->whereBetween('unit_price', [Helpers::convert_currency_to_usd($request['min_price']), Helpers::convert_currency_to_usd($request['max_price'])]);
        }

        $data = [
            'id' => $request['id'],
            'name' => $request['name'],
            'data_from' => $request['data_from'],
            'sort_by' => $request['sort_by'],
            'page_no' => $request['page'],
            'min_price' => $request['min_price'],
            'max_price' => $request['max_price'],
        ];

        $products = $fetched->paginate(5)->appends($data);

        if ($request->ajax()) {
            return response()->json([
                'view' => view(VIEW_FILE_NAMES['products__ajax_partials'], compact('products'))->render()
            ], 200);
        }
        if ($request['data_from'] == 'category') {
            $data['brand_name'] = Category::find((int)$request['id'])->name;
        }
        if ($request['data_from'] == 'brand') {
            $data['brand_name'] = Brand::active()->find((int)$request['id'])->name;
        }

        return view(VIEW_FILE_NAMES['products_view_page'], compact('products', 'data'), $data);

    }

    public function viewWishlist(Request $request): View
    {
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;

        $wishlists = Wishlist::with([
            'productFullInfo' => function ($query) {
                $query->with(['digitalVariation', 'clearanceSale' => function ($query) {
                    $query->active();
                }]);
            },
            'productFullInfo.compareList' => function ($query) {
                return $query->where('user_id', auth('customer')->id() ?? 0);
            }
        ])
            ->whereHas('wishlistProduct', function ($q) use ($request) {
                $q->when($request['search'], function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request['search']}%")
                        ->orWhereHas('category', function ($qq) use ($request) {
                            $qq->where('name', 'like', "%{$request['search']}%");
                        });
                });
            })
            ->where('customer_id', auth('customer')->id())->paginate(15);

        return view(VIEW_FILE_NAMES['account_wishlist'], compact('wishlists', 'brand_setting'));
    }

    public function storeWishlist(Request $request)
    {
        if ($request->ajax()) {
            if (auth('customer')->check()) {
                $wishlist = Wishlist::where('customer_id', auth('customer')->id())->where('product_id', $request->product_id)->first();
                if ($wishlist) {
                    $wishlist->delete();

                    $countWishlist = Wishlist::whereHas('wishlistProduct', function ($q) {
                        return $q;
                    })->where('customer_id', auth('customer')->id())->count();
                    $product_count = Wishlist::where(['product_id' => $request->product_id])->count();

                    session()->forget('wish_list');
                    session()->put('wish_list', Wishlist::whereHas('product', function ($query) {
                        return $query->active();
                    })->where('customer_id', auth('customer')->user()->id)->pluck('product_id')->toArray());

                    return response()->json([
                        'error' => translate("product_removed_from_the_wishlist"),
                        'value' => 2,
                        'count' => $countWishlist,
                        'product_count' => $product_count
                    ]);

                } else {
                    $wishlist = new Wishlist;
                    $wishlist->customer_id = auth('customer')->id();
                    $wishlist->product_id = $request->product_id;
                    $wishlist->save();

                    $countWishlist = Wishlist::whereHas('wishlistProduct', function ($q) {
                        return $q;
                    })->where('customer_id', auth('customer')->id())->count();

                    $product_count = Wishlist::where(['product_id' => $request->product_id])->count();
                    session()->forget('wish_list');
                    session()->put('wish_list', Wishlist::whereHas('product', function ($query) {
                        return $query->active();
                    })->where('customer_id', auth('customer')->user()->id)->pluck('product_id')->toArray());

                    return response()->json([
                        'success' => translate("Product has been added to wishlist"),
                        'value' => 1, 'count' => $countWishlist,
                        'id' => $request->product_id,
                        'product_count' => $product_count
                    ]);
                }

            } else {
                return response()->json(['error' => translate('please_login_your_account'), 'value' => 0]);
            }
        }
    }

    public function deleteWishlist(Request $request): JsonResponse|RedirectResponse
    {
        $this->wishlist->where(['product_id' => $request['id'], 'customer_id' => auth('customer')->id()])->delete();
        $data = translate('product_has_been_remove_from_wishlist') . '!';
        $wishlists = $this->wishlist->where('customer_id', auth('customer')->id())->paginate(15);
        $brand_setting = BusinessSetting::where('type', 'product_brand')->first()->value;
        session()->forget('wish_list');
        session()->put('wish_list', $this->wishlist->whereHas('product', function ($query) {
            return $query->active();
        })->where('customer_id', auth('customer')->id())->pluck('product_id')->toArray());
        if ($request->ajax()) {
            return response()->json([
                'success' => $data,
                'message' => translate('product_has_been_removed_from_wishlist'),
                'count' => count($wishlists),
                'id' => $request->id,
                'wishlist' => view(VIEW_FILE_NAMES['account_wishlist_partials'], compact('wishlists', 'brand_setting'))->render(),
            ]);
        }

        Toastr::success(translate('product_has_been_removed_from_wishlist'));
        return back();
    }

    public function deleteAllWishListItems(): RedirectResponse
    {
        $this->wishlist->where('customer_id', auth('customer')->id())->delete();
        session()->forget('wish_list');
        session()->put('wish_list', $this->wishlist->where('customer_id', auth('customer')->id())->pluck('product_id')->toArray());
        return redirect()->back();
    }

    public function contact_store(Request $request): RedirectResponse
    {
        $result = RecaptchaService::verificationStatus(request: $request, session: 'default_captcha_value_contact', action: 'contact');
        if ($result && !$result['status']) {
            Toastr::error($result['message']);
            return back();
        }
        $request->validate([
            'mobile_number' => 'required|max:20',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'email',
        ], [
            'mobile_number.required' => translate('phone_number_is_required'),
            'mobile_number.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            'subject.required' => translate('Subject_is_Empty'),
            'message.required' => translate('Message_is_Empty'),
        ]);

        $numericPhoneValue = preg_replace('/[^0-9]/', '', $request['mobile_number']);
        $numericLength = strlen($numericPhoneValue);
        if ($numericLength < 4 || $numericLength > 20) {
            $request->validate([
                'mobile_number' => 'min:5|max:20',
            ], [
                'mobile_number.min' => translate('The_phone_number_must_be_at_least_4_characters'),
                'mobile_number.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            ]);
        }

        $contact = new Contact;
        $contact->name = $request['name'];
        $contact->email = $request['email'];
        $contact->mobile_number = $request['mobile_number'];
        $contact->subject = $request['subject'];
        $contact->message = $request['message'];
        $contact->save();
        Toastr::success(translate('Your_Message_Send_Successfully'));
        return back();
    }

    public function captcha($tmp)
    {

        $phrase = new PhraseBuilder;
        $code = $phrase->build(4);
        $builder = new CaptchaBuilder($code, $phrase);
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        $builder->build($width = 100, $height = 40, $font = null);
        $phrase = $builder->getPhrase();

        if (Session::has('default_captcha_code')) {
            Session::forget('default_captcha_code');
        }
        Session::put('default_captcha_code', $phrase);
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }

    public function order_note(Request $request): JsonResponse
    {
        if ($request->has('order_note')) {
            session::put('order_note', $request['order_note']);
        }
        $response = OrderManager::checkValidationForCheckoutPages($request);
        return response()->json($response);
    }


    public function getDigitalProductDownload($id, Request $request): JsonResponse
    {
        $orderDetailsData = OrderDetail::with('order.customer')->find($id);
        if ($orderDetailsData) {
            if ($orderDetailsData->order->payment_status !== "paid") {
                return response()->json([
                    'status' => 0,
                    'message' => translate('Payment_must_be_confirmed_first') . ' !!',
                ]);
            };

            if ($orderDetailsData->order->is_guest) {
                $customerEmail = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->email : ($orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->email : '');

                $customerPhone = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->phone : ($orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->phone : '');

                $customerData = ['email' => $customerEmail, 'phone' => $customerPhone];
                return self::getDigitalProductDownloadProcess(orderDetailsData: $orderDetailsData, customer: $customerData);
            } else {
                if (auth('customer')->check() && auth('customer')->user()->id == $orderDetailsData->order->customer->id) {
                    $fileName = '';
                    $fileExist = false;
                    $productDetails = json_decode($orderDetailsData['product_details'], true);
                    if ($productDetails['digital_product_type'] == 'ready_product' && $productDetails['digital_file_ready']) {
                        $checkFilePath = storageLink('product/digital-product', $productDetails['digital_file_ready'], ($productDetails['storage_path'] ?? 'public'));
                        $filePath = $checkFilePath['path'];
                        $fileExist = $checkFilePath['status'] == 200;
                        $fileName = $productDetails['digital_file_ready'];
                    } else {
                        $checkFilePath = $orderDetailsData->digital_file_after_sell_full_url;
                        $filePath = $checkFilePath['path'];
                        $fileName = $orderDetailsData['digital_file_after_sell'];
                        $fileExist = $checkFilePath['status'] == 200;
                    }

                    if (!is_null($fileName) && $fileExist) {
                        return response()->json([
                            'status' => 1,
                            'file_path' => $filePath,
                            'file_name' => $fileName,
                        ]);
                    } else {
                        return response()->json([
                            'status' => 0,
                            'message' => translate('file_not_found'),
                        ]);
                    }
                } else {
                    $customerData = ['email' => $orderDetailsData->order->customer->email ?? '', 'phone' => $orderDetailsData->order->customer->phone ?? ''];
                    return self::getDigitalProductDownloadProcess(orderDetailsData: $orderDetailsData, customer: $customerData);
                }
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => translate('order_Not_Found') . ' !',
            ]);
        }
    }

    public function getDigitalProductDownloadOtpVerify(Request $request): JsonResponse
    {
        $verification = DigitalProductOtpVerification::where(['token' => $request->otp, 'order_details_id' => $request->order_details_id])->first();
        $orderDetailsData = OrderDetail::with('order.customer')->find($request->order_details_id);

        if ($verification) {
            $fileName = '';
            $fileExist = false;
            if ($orderDetailsData) {
                $productDetails = json_decode($orderDetailsData['product_details'], true);
                if ($productDetails['digital_product_type'] == 'ready_product' && $productDetails['digital_file_ready']) {
                    $checkFilePath = storageLink('product/digital-product', $productDetails['digital_file_ready'], ($productDetails['storage_path'] ?? 'public'));
                    $filePath = $checkFilePath['path'];
                    $fileExist = $checkFilePath['status'] == 200;
                    $fileName = $productDetails['digital_file_ready'];
                } else {
                    $checkFilePath = $orderDetailsData->digital_file_after_sell_full_url;
                    $filePath = $checkFilePath['path'];
                    $fileName = $orderDetailsData['digital_file_after_sell'];
                    $fileExist = $checkFilePath['status'] == 200;
                }
            }

            DigitalProductOtpVerification::where(['token' => $request->otp, 'order_details_id' => $request->order_details_id])->delete();

            if (!is_null($fileName) && $fileExist) {
                return response()->json([
                    'status' => 1,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'message' => translate('successfully_verified'),
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => translate('file_not_found'),
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => translate('the_OTP_is_incorrect') . ' !',
            ]);
        }
    }

    public function getDigitalProductDownloadOtpReset(Request $request): JsonResponse
    {
        $tokenInfo = DigitalProductOtpVerification::where(['order_details_id' => $request->order_details_id])->first();
        $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 1; //minute
        if (isset($tokenInfo) && Carbon::parse($tokenInfo->created_at)->diffInSeconds() < $otpIntervalTime) {
            $timeCount = $otpIntervalTime - Carbon::parse($tokenInfo->created_at)->diffInSeconds();

            return response()->json([
                'status' => 0,
                'time_count' => CarbonInterval::seconds($timeCount)->cascade()->forHumans(),
                'message' => translate('Please_try_again_after') . ' ' . CarbonInterval::seconds($timeCount)->cascade()->forHumans()
            ]);
        } else {
            $guestEmail = '';
            $guestPhone = '';
            $token = rand(1000, 9999);

            $orderDetailsData = OrderDetail::with('order.customer')->find($request['order_details_id']);

            try {
                if ($orderDetailsData->order->is_guest) {
                    if ($orderDetailsData->order->shipping_address_data) {
                        $guestName = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->contact_person_name : null;
                        $guestEmail = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->email : null;
                        $guestPhone = $orderDetailsData->order->shipping_address_data ? $orderDetailsData->order->shipping_address_data->phone : null;
                    } else {
                        $guestName = $orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->contact_person_name : null;
                        $guestEmail = $orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->email : null;
                        $guestPhone = $orderDetailsData->order->billing_address_data ? $orderDetailsData->order->billing_address_data->phone : null;
                    }
                } else {
                    $guestName = $orderDetailsData->order->customer->f_name;
                    $guestEmail = $orderDetailsData->order->customer->email;
                    $guestPhone = $orderDetailsData->order->customer->phone;
                }
            } catch (\Throwable $th) {

            }

            $verifyData = [
                'order_details_id' => $orderDetailsData->id,
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DigitalProductOtpVerification::updateOrInsert(['identity' => $guestEmail, 'order_details_id' => $orderDetailsData->id], $verifyData);
            DigitalProductOtpVerification::updateOrInsert(['identity' => $guestPhone, 'order_details_id' => $orderDetailsData->id], $verifyData);

            $emailServicesSmtp = getWebConfig(name: 'mail_config');
            if ($emailServicesSmtp['status'] == 0) {
                $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
            }

            if ($emailServicesSmtp['status'] == 1) {
                try {
                    $data = [
                        'userName' => $guestName,
                        'userType' => 'customer',
                        'templateName' => 'digital-product-otp',
                        'subject' => translate('verification_Code'),
                        'title' => translate('verification_Code') . '!',
                        'verificationCode' => $token,
                    ];
                    event(new DigitalProductOtpVerificationEvent(email: $guestEmail, data: $data));
                    $mailStatus = 1;
                } catch (\Exception $exception) {
                    $mailStatus = 0;
                }
            } else {
                $mailStatus = 0;
            }

            $response = SMSModule::sendCentralizedSMS($guestPhone, $token);
            $smsStatus = $response == "not_found" ? 0 : 1;

            return response()->json([
                'mail_status' => $mailStatus,
                'sms_status' => $smsStatus,
                'status' => ($mailStatus || $smsStatus) ? 1 : 0,
                'new_time' => $otpIntervalTime,
                'message' => ($mailStatus || $smsStatus) ? translate('OTP_sent_successfully') : translate('OTP_sent_fail'),
            ]);

        }
    }

    public function getDigitalProductDownloadProcess($orderDetailsData, $customer): JsonResponse
    {
        $status = 2;
        $emailServicesSmtp = getWebConfig(name: 'mail_config');
        if ($emailServicesSmtp['status'] == 0) {
            $emailServicesSmtp = getWebConfig(name: 'mail_config_sendgrid');
        }

        $paymentPublishedStatus = config('get_payment_publish_status') ?? 0;

        if ($paymentPublishedStatus == 1) {
            $smsConfigStatus = Setting::where(['settings_type' => 'sms_config', 'is_active' => 1])->count() > 0 ? 1 : 0;
        } else {
            $smsConfigStatus = Setting::where(['settings_type' => 'sms_config', 'is_active' => 1])->whereIn('key_name', Helpers::getDefaultSMSGateways())->count() > 0 ? 1 : 0;
        }

        if ($emailServicesSmtp['status'] || $smsConfigStatus) {
            $token = rand(1000, 9999);
            if ($customer['email'] == '' && $customer['phone'] == '') {
                return response()->json([
                    'status' => $status,
                    'file_path' => '',
                    'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
                ]);
            }

            $verificationData = DigitalProductOtpVerification::where('identity', $customer['email'])->orWhere('identity', $customer['phone'])->where('order_details_id', $orderDetailsData->id)->latest()->first();
            $otpIntervalTime = getWebConfig(name: 'otp_resend_time') ?? 1; //second

            if (isset($verificationData) && Carbon::parse($verificationData->created_at)->diffInSeconds() < $otpIntervalTime) {
                $timeCount = $otpIntervalTime - Carbon::parse($verificationData->created_at)->diffInSeconds();
                return response()->json([
                    'status' => $status,
                    'file_path' => '',
                    'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify'], ['orderDetailID' => $orderDetailsData->id, 'time_count' => $timeCount])->render(),
                ]);
            } else {
                $verifyData = [
                    'order_details_id' => $orderDetailsData->id,
                    'token' => $token,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                DigitalProductOtpVerification::updateOrInsert(['identity' => $customer['email'], 'order_details_id' => $orderDetailsData->id], $verifyData);
                DigitalProductOtpVerification::updateOrInsert(['identity' => $customer['phone'], 'order_details_id' => $orderDetailsData->id], $verifyData);

                $resetData = DigitalProductOtpVerification::where('identity', $customer['email'])->orWhere('identity', $customer['phone'])->where('order_details_id', $orderDetailsData->id)->latest()->first();
                $otpResendTime = getWebConfig(name: 'otp_resend_time') > 0 ? getWebConfig(name: 'otp_resend_time') : 0;
                $tokenTime = Carbon::parse($resetData->created_at);
                $convertTime = $tokenTime->addSeconds($otpResendTime);
                $timeCount = $convertTime > Carbon::now() ? Carbon::now()->diffInSeconds($convertTime) : 0;
                $mailStatus = 0;

                if ($emailServicesSmtp['status'] == 1) {
                    try {
                        $data = [
                            'userName' => $customer['f_name'],
                            'userType' => 'customer',
                            'templateName' => 'digital-product-otp',
                            'subject' => translate('verification_Code'),
                            'title' => translate('verification_Code') . '!',
                            'verificationCode' => $token,
                        ];
                        event(new DigitalProductOtpVerificationEvent(email: $customer['email'], data: $data));
                        $mailStatus = 1;
                    } catch (\Exception $exception) {
                    }
                }

                $response = SMSModule::sendCentralizedSMS($customer['phone'], $token);

                $smsStatus = ($response == "not_found" || $smsConfigStatus == 0) ? 0 : 1;
                if ($mailStatus || $smsStatus) {
                    return response()->json([
                        'status' => $status,
                        'file_path' => '',
                        'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify'], ['orderDetailID' => $orderDetailsData->id, 'time_count' => $timeCount])->render(),
                    ]);
                } else {
                    return response()->json([
                        'status' => $status,
                        'file_path' => '',
                        'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => $status,
                'file_path' => '',
                'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
            ]);
        }
    }


    public function subscription(Request $request): RedirectResponse
    {
        $request->validate([
            'subscription_email' => 'required|email'
        ]);
        $subscriptionEmail = Subscription::where('email', $request['subscription_email'])->first();

        if (isset($subscriptionEmail)) {
            Toastr::info(translate('You_already_subscribed_this_site'));
        } else {
            $newSubscription = new Subscription;
            $newSubscription->email = $request['subscription_email'];
            $newSubscription->save();
            Toastr::success(translate('Your_subscription_successfully_done'));
        }
        if (str_contains(url()->previous(), 'checkout-complete') || str_contains(url()->previous(), 'web-payment')) {
            return redirect()->route('home');
        }
        return back();
    }

    public function review_list_product(Request $request): JsonResponse
    {
        $productReviews = Review::where('product_id', $request['product_id'])->latest()->paginate(2, ['*'], 'page', $request['offset'] + 1);
        $checkReviews = Review::where('product_id', $request['product_id'])->latest()->paginate(2, ['*'], 'page', ($request['offset'] + 1));
        return response()->json([
            'productReview' => view(VIEW_FILE_NAMES['product_reviews_partials'], compact('productReviews'))->render(),
            'not_empty' => $productReviews->count(),
            'checkReviews' => $checkReviews->count(),
        ]);
    }

    public function getShopReviewList(Request $request): JsonResponse
    {
        $sellerId = 0;
        if ($request['shop_id'] != 0) {
            $sellerId = Shop::where('id', $request['shop_id'])->first()->seller_id;
        }
        $getProductIds = Product::active()
            ->when($request['shop_id'] == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['shop_id'] != 0, function ($query) use ($sellerId) {
                return $query->where(['added_by' => 'seller', 'user_id' => $sellerId]);
            })
            ->pluck('id')->toArray();

        $productReviews = Review::active()->whereIn('product_id', $getProductIds)->latest()->paginate(4, ['*'], 'page', $request['offset'] + 1);
        $checkReviews = Review::active()->whereIn('product_id', $getProductIds)->latest()->paginate(4, ['*'], 'page', ($request['offset'] + 1));

        return response()->json([
            'productReview' => view(VIEW_FILE_NAMES['product_reviews_partials'], compact('productReviews'))->render(),
            'not_empty' => $productReviews->count(),
            'checkReviews' => $checkReviews->count(),
        ]);
    }

    public function product_view_style(Request $request): JsonResponse
    {
        Session::put('product_view_style', $request['value']);
        return response()->json([
            'message' => translate('View_style_updated') . "!",
        ]);
    }


    public function pay_offline_method_list(Request $request): JsonResponse
    {
        $method = OfflinePaymentMethod::where(['id' => $request['method_id'], 'status' => 1])->first();

        $vendorWiseCartList = \App\Utils\OrderManager::processOrderGenerateData(data: [
            'coupon_code' => session('coupon_code') ?? '',
            'address_id' => session('address_id'),
            'billing_address_id' => session('billing_address_id'),
        ]);

        $vendorWiseCartListCollection = collect($vendorWiseCartList);
        $referralDiscount = $vendorWiseCartListCollection?->sum('refer_and_earn_discount') ?? 0;
        $grandTotal = $vendorWiseCartListCollection?->sum('grand_total') ?? 0;
        $freeDeliveryDiscount = $vendorWiseCartListCollection?->sum('free_delivery_discount') ?? 0;
        $couponDiscount =  session()->has('coupon_discount') ? session('coupon_discount') : 0;
        $totalOfflineAmount = $grandTotal - $referralDiscount - $freeDeliveryDiscount - $couponDiscount;

        return response()->json([
            'methodHtml' => view(VIEW_FILE_NAMES['pay_offline_method_list_partials'], compact('method', 'totalOfflineAmount'))->render(),
        ]);
    }

}
