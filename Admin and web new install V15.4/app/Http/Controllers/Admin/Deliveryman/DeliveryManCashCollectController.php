<?php

namespace App\Http\Controllers\Admin\Deliveryman;

use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\DeliveryManTransactionRepositoryInterface;
use App\Contracts\Repositories\DeliveryManWalletRepositoryInterface;
use App\Enums\ViewPaths\Admin\DeliveryManCash;
use App\Enums\WebConfigKey;
use App\Events\CashCollectEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\DeliveryManCashCollectRequest;
use App\Services\DeliveryManCashCollectService;
use App\Traits\PushNotificationTrait;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeliveryManCashCollectController extends BaseController
{
    use PushNotificationTrait;

    /**
     * @param DeliveryManRepositoryInterface $deliveryManRepo
     * @param DeliveryManWalletRepositoryInterface $deliveryManWalletRepo
     * @param DeliveryManTransactionRepositoryInterface $deliveryManTransactionRepo
     */
    public function __construct(
        private readonly DeliveryManRepositoryInterface       $deliveryManRepo,
        private readonly DeliveryManWalletRepositoryInterface $deliveryManWalletRepo,
        private readonly DeliveryManTransactionRepositoryInterface $deliveryManTransactionRepo,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): \Illuminate\View\View
    {
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id'=>$request['id']], relations: ['wallet']);
        $transactions = $this->deliveryManTransactionRepo->getListWhere(
            orderBy: ['id'=>'desc'],
            filters: ['delivery_man_id'=>$request['id']],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );

        return view('admin-views.delivery-man.earning-statement.collect-cash', compact('deliveryMan', 'transactions'));
    }


    public function getCashReceive(DeliveryManCashCollectRequest $request, $id, DeliveryManCashCollectService $deliveryManCashCollectService): RedirectResponse
    {
        $wallet = $this->deliveryManWalletRepo->getFirstWhere(params: ['delivery_man_id' => $id]);
        if (empty($wallet) || currencyConverter(amount: $request['amount']) > $wallet['cash_in_hand']) {
            ToastMagic::warning(translate('receive_amount_can_not_be_more_than_cash_in_hand'));
            return back();
        }
        $deliveryMan = $this->deliveryManRepo->getFirstWhere(params: ['id' => $id]);
        $dataArray = $deliveryManCashCollectService->getIdentityImages(request: $request, deliveryMan: $deliveryMan);
        $this->deliveryManTransactionRepo->add(data: $dataArray);
        $amount = $wallet['cash_in_hand'] - currencyConverter(amount: $request['amount']);
        $this->deliveryManWalletRepo->update(id: $wallet['id'], data: ['cash_in_hand' => $amount]);
        if (!empty($deliveryMan['fcm_token'])) {
            CashCollectEvent::dispatch('cash_collect_by_admin_message', 'delivery_man', $deliveryMan['app_language'] ?? getDefaultLanguage(), currencyConverter(amount: $request['amount']), $deliveryMan['fcm_token']);
        }
        ToastMagic::success(translate('amount_receive_successfully'));
        return back();
    }

}
