<?php

namespace App\Http\Controllers\Admin\Shipping;

use App\Contracts\Repositories\ShippingTypeRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ShippingTypeRequest;
use App\Services\ShippingTypeService;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ShippingTypeController extends BaseController
{
    /**
     * @param ShippingTypeRepositoryInterface $shippingTypeRepo
     * @param ShippingTypeService $shippingTypeService
     */
    public function __construct(
        private readonly ShippingTypeRepositoryInterface $shippingTypeRepo,
        private readonly ShippingTypeService             $shippingTypeService
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return null;
    }

    /**
     * @param ShippingTypeRequest $request
     * @return JsonResponse
     */
    public function addOrUpdate(ShippingTypeRequest $request): JsonResponse
    {
        $shippingType = $this->shippingTypeRepo->getFirstWhere(['seller_id' => 0]);
        if (!empty($shippingType)) {
            $this->shippingTypeRepo->update(
                id: $shippingType['id'],
                data: $this->shippingTypeService->getShippingTypeDataForUpdate(request: $request)
            );
        } else {
            $this->shippingTypeRepo->add(data: $this->shippingTypeService->getShippingTypeDataForAdd(request: $request, id: 0));
        }

        cacheRemoveByType(type: 'business_settings');
        updateSetupGuideCacheKey(key: 'shipping_method', panel: 'admin');
        return response()->json([
            'status' => 1,
            'message' => translate('Shipping_type_update_successfully'),
        ]);
    }
}
