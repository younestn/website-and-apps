<?php

namespace App\Http\Controllers\Admin\Deliveryman;

use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\OrderStatusHistoryRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Enums\ViewPaths\Admin\DeliveryMan;
use App\Enums\WebConfigKey;
use App\Enums\ExportFileNames\Admin\DeliveryMan as DeliveryManExport;
use App\Exports\DeliveryManListExport;
use App\Exports\DeliveryManOrderHistory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeliveryManAddRequest;
use App\Http\Requests\Admin\DeliveryManUpdateRequest;
use App\Services\DeliveryManService;
use App\Traits\CommonTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DeliveryManController extends Controller
{
    use CommonTrait;

    /**
     * @param DeliveryManRepositoryInterface $deliveryManRepo
     * @param OrderRepositoryInterface $orderRepo
     * @param ReviewRepositoryInterface $reviewRepo
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepo
     */
    public function __construct(
        private readonly DeliveryManRepositoryInterface        $deliveryManRepo,
        private readonly OrderRepositoryInterface              $orderRepo,
        private readonly ReviewRepositoryInterface             $reviewRepo,
        private readonly OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepo
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        $deliveryMens = $this->deliveryManRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['seller_id' => 0],
            relations: ['rating'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        return view('admin-views.delivery-man.list', compact('deliveryMens'));
    }

    public function getAddView(Request $request): View
    {
        $telephoneCodes = TELEPHONE_CODES;
        return view('admin-views.delivery-man.index', compact('telephoneCodes'));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $this->deliveryManRepo->update(id: $request['id'], data: ['is_active' => $request->get('status', 0)]);
        return response()->json(['message' => translate("status_updated_successfully")], 200);
    }

    public function exportList(Request $request): BinaryFileResponse
    {
        $deliveryMens = $this->deliveryManRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['seller_id' => 0],
            relations: ['rating'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $active = $deliveryMens->where('is_active', 1)->count();
        $inactive = $deliveryMens->where('is_active', 0)->count();
        return Excel::download(new DeliveryManListExport([
            'delivery_men' => $deliveryMens,
            'search' => $request['searchValue'],
            'active' => $active,
            'inactive' => $inactive,
        ]), DeliveryManExport::EXPORT_XLSX
        );
    }

    public function getUpdateView($id): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id]);
        $telephoneCodes = TELEPHONE_CODES;
        return view('admin-views.delivery-man.edit', compact('deliveryMan', 'telephoneCodes'));
    }


    public function add(DeliveryManAddRequest $request, DeliveryManService $deliveryManService): JsonResponse|RedirectResponse
    {
        $dataArray = $deliveryManService->getDeliveryManAddData(request: $request, addedBy: 'admin');
        $this->deliveryManRepo->add(data: $dataArray);
        return response()->json(['message' => translate('delivery_man_added_successfully')]);
    }


    public function update(DeliveryManUpdateRequest $request, $id, DeliveryManService $deliveryManService): JsonResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id, 'seller_id' => 0]);
        $deliveryManExists = $this->deliveryManRepo->getFirstWhere(params: ['phone' => $request['phone'], 'country_code' => $request['country_code']]);

        if (isset($deliveryManExists) && $deliveryManExists['id'] != $deliveryMan['id']) {
            return response()->json(['errors' => translate('this_phone_number_is_already_taken')]);
        }
        $dataArray = $deliveryManService->getDeliveryManUpdateData(
            request: $request,
            addedBy: 'admin',
            identityImages: $deliveryMan['identity_image'],
            deliveryManImage: $deliveryMan['image']
        );
        $this->deliveryManRepo->update(id: $id, data: $dataArray);
        return response()->json(['message' => translate('delivery_man_updated_successfully')]);
    }


    public function delete(Request $request, DeliveryManService $deliveryManService): RedirectResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $request['id']]);
        $deliveryManService->deleteImages(deliveryMan: $deliveryMan);
        $deliveryMan->delete();
        ToastMagic::success(translate('Delivery_man_removed'));
        return back();
    }

    public function getEarningOverview(Request $request, $id): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id], relations: ['wallet']);
        $withdrawalableBalance = isset($deliveryMan->wallet) ? self::delivery_man_withdrawable_balance($id) : null;
        return view('admin-views.delivery-man.earning-statement.overview', compact('deliveryMan', 'withdrawalableBalance'));
    }

    public function getOrderHistoryList(Request $request, $id): View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(
            params: ['id' => $id],
            relations: ['wallet'],
        );

        $orders = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['delivery_man_id' => $id, 'whereHas_deliveryMan' => 0],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        return view('admin-views.delivery-man.earning-statement.active-log', compact('deliveryMan', 'orders'));
    }

    public function getOrderHistoryListExport(Request $request, $id, DeliveryManService $deliveryManService): BinaryFileResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(
            params: ['id' => $id],
            relations: ['wallet'],
        );
        $totalOrders = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['delivery_man_id' => $id, 'whereHas_deliveryMan' => 0],
            dataLimit: 'all',
        );

        $orders = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['delivery_man_id' => $id,
                'whereHas_deliveryMan' => 0,
                'whereIn_order_status' => $request['order_status'] ? explode(',', $request['order_status']) : 'all',
                'whereIn_payment_status' => $request['payment_status'] ? explode(',', $request['payment_status']) : 'all'
            ],
            dataLimit: 'all',
        );
        $data = $deliveryManService->getOrderHistoryListExportData(request: $request, deliveryMan: $deliveryMan, orders: $orders);
        $data['totalOrders'] = count($totalOrders);

        $fileName = $request['type'] == 'earn' ? DeliveryManExport::EXPORT_EARNING_LIST_XLSX : DeliveryManExport::EXPORT_ORDER_LIST_XLSX;
        return Excel::download(new DeliveryManOrderHistory($data), $fileName);
    }

    public function getOrderWiseEarningView(Request $request, $id): View
    {
        $orders = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['delivery_man_id' => $id, 'whereHas_deliveryMan' => 0],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(
            params: ['id' => $id],
            relations: ['wallet'],
        );
        $totalEarn = self::delivery_man_total_earn($id);
        $withdrawalableBalance = self::delivery_man_withdrawable_balance($id);
        return view('admin-views.delivery-man.earning-statement.earning', compact('deliveryMan', 'totalEarn', 'withdrawalableBalance', 'orders'));
    }

    public function getOrderWiseEarningListByFilter(Request $request, $id): JsonResponse
    {
        $orders = $this->orderRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['delivery_man_id' => $id,
                'whereHas_deliveryMan' => 0,
                'whereIn_order_status' => $request['order_status'] ?? 'all',
                'whereIn_payment_status' => $request['payment_status'] ?? 'all'
            ],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $currentFilters = [
            'order_status' => $request['order_status'] ?? 'all',
            'payment_status' => $request['payment_status'] ?? 'all'
        ];

        return response()->json([
            'view' => view('admin-views.delivery-man.earning-statement._table', compact('orders', 'currentFilters'))->render(),
            'count' => count($orders),
        ]);
    }

    public function getOrderStatusHistory($order): View
    {
        $histories = $this->orderStatusHistoryRepo->getListWhere(filters: ['order_id' => $order], dataLimit: 'all');
        return view('admin-views.delivery-man.earning-statement._order-status-history', compact('histories'));
    }

    public function getRatingView(Request $request, $id): View|RedirectResponse
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id, 'seller_id' => 0], relations: ['review']);
        if (!$deliveryMan) {
            ToastMagic::warning(translate('invaild_review'));
            return redirect(route('admin.delivery-man.list'));
        }

        $reviews_collection = $this->reviewRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: [
                'delivery_man_id' => $id,
                'from' => $request['from_date'],
                'to' => $request['to_date'],
                'rating' => $request['rating'],
            ],
            dataLimit: 'all',
        );

        return view('admin-views.delivery-man.rating', [
            'deliveryMan' => $deliveryMan,
            'reviews' => $reviews_collection->paginate(getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)),
            'total' => $reviews_collection->count(),
            'averageRating' => $reviews_collection->avg('rating'),
            'one' => $reviews_collection->where('rating', 1)->count(),
            'two' => $reviews_collection->where('rating', 2)->count(),
            'three' => $reviews_collection->where('rating', 3)->count(),
            'four' => $reviews_collection->where('rating', 4)->count(),
            'five' => $reviews_collection->where('rating', 5)->count(),
        ]);
    }

}
