<?php

namespace App\Http\Controllers\Admin\POS;

use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\CouponRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DeliveryZipCodeRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Admin\POS;
use App\Http\Controllers\BaseController;
use App\Services\CartService;
use App\Services\POSService;
use App\Services\ProductService;
use App\Traits\CalculatorTrait;
use App\Traits\CommonTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class POSController extends BaseController
{
    use CalculatorTrait, CommonTrait;

    /**
     * @param CategoryRepositoryInterface $categoryRepo
     * @param ProductRepositoryInterface $productRepo
     * @param CustomerRepositoryInterface $customerRepo
     * @param OrderRepositoryInterface $orderRepo
     * @param CouponRepositoryInterface $couponRepo
     * @param CartService $cartService
     * @param POSService $POSService
     * @param DeliveryZipCodeRepositoryInterface $deliveryZipCodeRepo
     * @param AuthorRepositoryInterface $authorRepo
     * @param PublishingHouseRepositoryInterface $publishingHouseRepo
     * @param ProductService $productService
     */
    public function __construct(
        private readonly AuthorRepositoryInterface          $authorRepo,
        private readonly CategoryRepositoryInterface        $categoryRepo,
        private readonly ProductRepositoryInterface         $productRepo,
        private readonly CustomerRepositoryInterface        $customerRepo,
        private readonly OrderRepositoryInterface           $orderRepo,
        private readonly CouponRepositoryInterface          $couponRepo,
        private readonly CartService                        $cartService,
        private readonly POSService                         $POSService,
        private readonly ProductService                     $productService,
        private readonly DeliveryZipCodeRepositoryInterface $deliveryZipCodeRepo,
        private readonly PublishingHouseRepositoryInterface $publishingHouseRepo,
    ) {}

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $categoryId = $request['category_id'];
        $categories = $this->categoryRepo->getListWhere(orderBy: ['id' => 'desc'], filters: ['position' => 0], dataLimit: 'all');
        $searchValue = $request['searchValue'] ?? null;
        $products = $this->productRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            filters: [
                'added_by' => 'in_house',
                'category_id' => $categoryId,
                'code' => $searchValue,
                'status' => 1,
            ],
            relations: ['clearanceSale' => function ($query) {
                return $query->active();
            }],
            dataLimit: getWebConfig('pagination_limit'),
        );
        $cartId = 'walk-in-customer-' . rand(10, 1000);
        $this->cartService->getNewCartSession(cartId: $cartId);
        $customers = $this->customerRepo->getListWhereNotIn(ids: [0]);
        $getCurrentCustomerData = $this->getCustomerDataFromSessionForPOS();
        $summaryData = array_merge($this->POSService->getSummaryData(), $getCurrentCustomerData);
        $cartItems = $this->getCartData(cartName: session(SessionKey::CURRENT_USER));
        $order = $this->orderRepo->getFirstWhere(params: ['id' => session(SessionKey::LAST_ORDER)]);
        $totalHoldOrder = $summaryData['totalHoldOrders'];

        $countries = getWebConfig(name: 'delivery_country_restriction') ? $this->get_delivery_country_array() : COUNTRIES;
        $zipCodes = getWebConfig(name: 'delivery_zip_code_area_restriction') ? $this->deliveryZipCodeRepo->getListWhere(dataLimit: 'all') : 0;
        return view('admin-views.pos.index', compact(
            'categories',
            'categoryId',
            'products',
            'cartId',
            'customers',
            'searchValue',
            'summaryData',
            'cartItems',
            'order',
            'totalHoldOrder',
            'countries',
            'zipCodes'
        ));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeCustomer(Request $request): JsonResponse
    {
        $cartId = ($request['user_id'] != 0 ? 'saved-customer-' . $request['user_id'] : 'walk-in-customer-' . rand(10, 1000));
        $this->POSService->UpdateSessionWhenCustomerChange(cartId: $cartId);
        $getCurrentCustomerData = $this->getCustomerDataFromSessionForPOS();
        $summaryData = array_merge($this->POSService->getSummaryData(), $getCurrentCustomerData);
        $cartItems = $this->getCartData(cartName: $cartId);
        return response()->json([
            'view' => view('admin-views.pos.partials._cart-summary', compact('summaryData', 'cartItems'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDiscount(Request $request): JsonResponse
    {
        $cartId = session(SessionKey::CURRENT_USER);
        if ($request['type'] == 'percent' && ($request['discount'] < 0 || $request['discount'] > 100)) {
            $cartItems = $this->getCartData(cartName: $cartId);
            $text = $request['discount'] > 0 ? 'Extra_discount_can_not_be_less_than_0_percent' :
                'Extra_discount_can_not_be_more_than_100_percent';
            ToastMagic::error(translate($text));
            return response()->json([
                'extraDiscount' => "amount_low",
                'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
            ]);
        }
        $cart = session($cartId, collect());
        if ($cart) {
            $totalProductPrice = 0;
            $productDiscount = 0;
            $productTax = 0;
            $couponDiscount = $cart['coupon_discount'] ?? 0;
            $includeTax = 0;

            foreach ($cart as $item) {
                if (is_array($item)) {
                    $product = $this->productRepo->getFirstWhere(params: ['id' => $item['id']], relations: ['clearanceSale' => function ($query) {
                        return $query->active();
                    }]);
                    $totalProductPrice += $item['price'] * $item['quantity'];
                    $productDiscount += $item['discount'] * $item['quantity'];
                    $productTax += $this->getTaxAmount($item['price'], $product['tax']) * $item['quantity'];
                    if ($product['tax_model'] == 'include') {
                        $includeTax += $productTax;
                    }
                }
            }
            if ($request['type'] == 'percent') {
                $extraDiscount = (($totalProductPrice - $includeTax) / 100) * $request['discount'];
            } else {
                $extraDiscount = currencyConverter(amount: $request['discount']);
            }

            $total = $totalProductPrice - $productDiscount + $productTax - $couponDiscount - $extraDiscount - $includeTax;
            if ($total < 0) {
                $cartItems = $this->getCartData(cartName: $cartId);
                return response()->json([
                    'extraDiscount' => "amount_low",
                    'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
                ]);
            } else {
                $cart['ext_discount'] = $request['type'] == 'percent' ? $request['discount'] : currencyConverter(amount: $request['discount']);
                $cart['ext_discount_type'] = $request['type'];
                session()->put($cartId, $cart);
                $cartItems = $this->getCartData(cartName: $cartId);
                return response()->json([
                    'extraDiscount' => "success",
                    'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
                ]);
            }
        } else {
            $cartItems = $this->getCartData(cartName: $cartId);
            return response()->json([
                'extraDiscount' => "empty",
                'cart' => "empty",
                'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCouponDiscount(Request $request): JsonResponse
    {
        $cartId = session(SessionKey::CURRENT_USER);
        $userId = $this->cartService->getUserId();
        if ($userId != 0) {
            $usedCoupon = $this->orderRepo->getListWhere(filters: ['customer_type' => 'customer', 'coupon_code' => $request['coupon_code']])->count();
            $coupon = $this->couponRepo->getFirstWhereFilters(
                filters: [
                    'code' => $request['coupon_code'],
                    'added_by' => 'admin',
                    'limit' => $usedCoupon,
                    'start_date' => now(),
                    'expire_date' => now(),
                    'status' => 1
                ]
            );
        } else {
            $coupon = $this->couponRepo->getFirstWhereFilters(
                filters: [
                    'code' => $request['coupon_code'],
                    'added_by' => 'admin',
                    'start_date' => now(),
                    'expire_date' => now(),
                    'status' => 1
                ]
            );
        }
        $carts = session($cartId);
        if(empty($carts)) {
            $cartItems = $this->getCartData(cartName: $cartId);
            return response()->json([
                'coupon' => 'cart_empty',
                'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
            ]);
        }
        if (!$coupon || $coupon['coupon_type'] == 'free_delivery' || $coupon['coupon_type'] == 'first_order') {
            $cartItems = $this->getCartData(cartName: $cartId);
            return response()->json([
                'coupon' => 'coupon_invalid',
                'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
            ]);
        }
        $totalProductPrice = 0;
        $productDiscount = 0;
        $productTax = 0;
        $includeTax = 0;
        if (($coupon['customer_id'] == '0' || $coupon['customer_id'] == $userId)) {
            if ($carts != null) {
                foreach ($carts as $cart) {
                    if (is_array($cart)) {
                        $product = $this->productRepo->getFirstWhere(params: ['id' => $cart['id']], relations: ['clearanceSale' => function ($query) {
                            return $query->active();
                        }]);
                        $totalProductPrice += $cart['price'] * $cart['quantity'];
                        $productDiscount += $cart['discount'] * $cart['quantity'];
                        $productTax += ($this->getTaxAmount($cart['price'], $product['tax'])) * $cart['quantity'];
                        if ($product['tax_model'] == 'include') {
                            $includeTax += $productTax;
                        }
                    }
                }

                if ($totalProductPrice >= $coupon['min_purchase']) {
                    $calculation = $this->POSService->getCouponCalculation(coupon: $coupon, totalProductPrice: $totalProductPrice, productDiscount: $productDiscount, productTax: $productTax);
                    $couponDiscount = $calculation['discount'];

                    $extraDiscount = 0;
                    if (isset($carts['ext_discount_type']) && isset($carts['ext_discount'])) {
                        $extraDiscountType = $carts['ext_discount_type'];
                        if ($extraDiscountType == 'percent') {
                            $extraDiscount = (($totalProductPrice - $includeTax) / 100) * $carts['ext_discount'];
                        } else {
                            $extraDiscount = $carts['ext_discount'];
                        }
                    }

                    $total = $totalProductPrice - $productDiscount + $productTax - $couponDiscount - $extraDiscount - $includeTax;
                    if ($total < 0) {
                        $cartItems = $this->getCartData(cartName: $cartId);
                        return response()->json([
                            'coupon' => "amount_low",
                            'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
                        ]);
                    }

                    $this->POSService->putCouponDataOnSession(
                        cartId: $cartId,
                        discount: $couponDiscount,
                        couponTitle: $coupon['title'],
                        couponBearer: $coupon['coupon_bearer'],
                        couponCode: $request['coupon_code'],
                    );

                    $cartItems = $this->getCartData(cartName: $cartId);
                    return response()->json([
                        'coupon' => 'success',
                        'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
                    ]);
                }
            } else {
                $cartItems = $this->getCartData(cartName: $cartId);
                return response()->json([
                    'coupon' => 'cart_empty',
                    'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
                ]);
            }
        }
        $cartItems = $this->getCartData(cartName: $cartId);
        return response()->json([
            'coupon' => 'coupon_invalid',
            'view' => view('admin-views.pos.partials._cart', compact('cartId', 'cartItems'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getQuickView(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhereWithCount(
            params: ['id' => $request['product_id']],
            withCount: ['reviews'],
            relations: ['brand', 'category', 'rating', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
                return $query->active();
            }],
        );
        $productAuthorIds = $this->productService->getProductAuthorsInfo(product: $product)['ids'];
        $digitalProductAuthors = $this->authorRepo->getListWhere(dataLimit: 'all');
        $productPublishingHouseIds = $this->productService->getProductPublishingHouseInfo(product: $product)['ids'];
        $publishingHouseRepo = $this->publishingHouseRepo->getListWhere(dataLimit: 'all');
        return response()->json([
            'success' => 1,
            'view' => view('admin-views.pos.partials._quick-view', compact('product','digitalProductAuthors','productAuthorIds','productPublishingHouseIds','publishingHouseRepo'))->render(),
        ]);
    }

    /**
     * @return array
     */
    protected function getCustomerDataFromSessionForPOS(): array
    {
        if (Str::contains(session(SessionKey::CURRENT_USER), 'walk-in-customer')) {
            $currentCustomerInfo = ['customerName' => 'Walk-In Customer'];
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => '0']);
        } else {
            $userId = explode('-', session(SessionKey::CURRENT_USER))[2];
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => $userId]);
            $currentCustomerInfo = $this->cartService->getCustomerInfo(currentCustomerData: $currentCustomerData, customerId: $userId);
        }
        return [
            'currentCustomer' => $currentCustomerInfo['customerName'],
            'currentCustomerData' => $currentCustomerData
        ];
    }

    /**
     * @param string $cartName
     * @return array
     */
    protected function getCustomerCartData(string $cartName): array
    {
        $customerCartData = [];
        if (Str::contains($cartName, 'walk-in-customer')) {
            $currentCustomerInfo = [
                'customerName' => 'Walk-In Customer',
                'customerPhone' => "",
            ];
            $customerId = 0;
        } else {
            $customerId = explode('-', $cartName)[2];
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => $customerId]);
            $currentCustomerInfo = $this->cartService->getCustomerInfo(currentCustomerData: $currentCustomerData, customerId: $customerId);
        }
        $customerCartData[$cartName] = [
            'customerName' => $currentCustomerInfo['customerName'],
            'customerPhone' => $currentCustomerInfo['customerPhone'],
            'customerId' => $customerId,
        ];
        return $customerCartData;
    }

    protected function calculateCartItemsData(string $cartName, array $customerCartData): array
    {
        $cartItemValue = [];
        $subTotalCalculation = [
            'countItem' => 0,
            'totalQuantity' => 0,
            'taxCalculate' => 0,
            'totalTaxShow' => 0,
            'totalTax' => 0,
            'totalIncludeTax' => 0,
            'subtotal' => 0,
            'discountOnProduct' => 0,
            'productSubtotal' => 0,
        ];
        if (session()->get($cartName)) {
            foreach (session()->get($cartName) as $cartItem) {
                if (is_array($cartItem)) {
                    $product = $this->productRepo->getFirstWhere(params: ['id' => $cartItem['id']], relations: ['clearanceSale' => function ($query) {
                        return $query->active();
                    }]);
                    if ($product) {
                        $cartSubTotalCalculation = $this->cartService->getCartSubtotalCalculation(
                            product: $product,
                            cartItem: $cartItem,
                            calculation: $subTotalCalculation
                        );
                        if ($cartItem['customerId'] == $customerCartData[$cartName]['customerId']) {
                            $cartItem['productSubtotal'] = $cartSubTotalCalculation['productSubtotal'];
                            $cartItemValue[] = $cartItem;
                            $subTotalCalculation['customerOnHold'] = $cartItem['customerOnHold'];

                            $subTotalCalculation['countItem'] += $cartSubTotalCalculation['countItem'];
                            $subTotalCalculation['totalQuantity'] += $cartSubTotalCalculation['totalQuantity'];
                            $subTotalCalculation['taxCalculate'] += $cartSubTotalCalculation['taxCalculate'];
                            $subTotalCalculation['totalTaxShow'] += $cartSubTotalCalculation['totalTaxShow'];
                            $subTotalCalculation['totalTax'] += $cartSubTotalCalculation['totalTax'];
                            $subTotalCalculation['totalIncludeTax'] += $cartSubTotalCalculation['totalIncludeTax'];
                            $subTotalCalculation['productSubtotal'] += $cartSubTotalCalculation['productSubtotal'];
                            $subTotalCalculation['subtotal'] += $cartSubTotalCalculation['subtotal'];
                            $subTotalCalculation['discountOnProduct'] += $cartSubTotalCalculation['discountOnProduct'];
                        }
                    }
                }
            }
        }
        $totalCalculation = $this->cartService->getTotalCalculation(
            subTotalCalculation: $subTotalCalculation,
            cartName: $cartName
        );
        return [
            'countItem' => $subTotalCalculation['countItem'],
            'total' => $totalCalculation['total'],
            'subtotal' => $subTotalCalculation['subtotal'],
            'taxCalculate' => $subTotalCalculation['taxCalculate'],
            'totalTaxShow' => $subTotalCalculation['totalTaxShow'],
            'totalTax' => $subTotalCalculation['totalTax'],
            'discountOnProduct' => $subTotalCalculation['discountOnProduct'],
            'productSubtotal' => $subTotalCalculation['productSubtotal'],
            'cartItemValue' => $cartItemValue,
            'customerOnHold' => $subTotalCalculation['customerOnHold'] ?? false,
            'couponDiscount' => $totalCalculation['couponDiscount'],
            'extraDiscount' => $totalCalculation['extraDiscount'],
        ];
    }

    protected function getCartData(string $cartName): array
    {
        $customerCartData = $this->getCustomerCartData(cartName: $cartName);
        $cartItemData = $this->calculateCartItemsData(cartName: $cartName, customerCartData: $customerCartData);
        return array_merge($customerCartData[$cartName], $cartItemData);
    }

    public function getSearchedProductsView(Request $request): JsonResponse
    {
        $products = $this->productRepo->getListWithScope(
            scope: 'active',
            filters: [
                'added_by' => 'in_house',
                'keywords' => $request['name'],
                'search_from' => 'pos'
            ],
            dataLimit: 'all'
        );
        $data = [
            'count' => $products->count(),
            'result' => view('admin-views.pos.partials._search-product', compact('products'))->render()
        ];
        if ($products->count() > 0) {
            $data += ['id' => $products[0]->id];
        }

        return response()->json($data);
    }
}
