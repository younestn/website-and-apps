<?php

use App\Enums\ViewPaths\Vendor\Cart;
use App\Enums\ViewPaths\Vendor\CategoryShippingCost;
use App\Enums\ViewPaths\Vendor\Chatting;
use App\Enums\ViewPaths\Vendor\ClearanceSale;
use App\Enums\ViewPaths\Vendor\Coupon;
use App\Enums\ViewPaths\Vendor\Customer;
use App\Enums\ViewPaths\Vendor\Dashboard;
use App\Enums\ViewPaths\Vendor\DeliveryMan;
use App\Enums\ViewPaths\Vendor\DeliveryManWallet;
use App\Enums\ViewPaths\Vendor\DeliveryManWithdraw;
use App\Enums\ViewPaths\Vendor\EmergencyContact;
use App\Enums\ViewPaths\Vendor\Notification;
use App\Enums\ViewPaths\Vendor\POS;
use App\Enums\ViewPaths\Vendor\POSOrder;
use App\Enums\ViewPaths\Vendor\Profile;
use App\Enums\ViewPaths\Vendor\Refund;
use App\Enums\ViewPaths\Vendor\Review;
use App\Enums\ViewPaths\Vendor\ShippingMethod;
use App\Enums\ViewPaths\Vendor\ShippingType;
use App\Http\Controllers\Vendor\Auth\ForgotPasswordController;
use App\Http\Controllers\Vendor\Auth\LoginController;
use App\Enums\ViewPaths\Vendor\Order;
use App\Http\Controllers\Vendor\Auth\RegisterController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\ChattingController;
use App\Http\Controllers\Vendor\Coupon\CouponController;
use App\Http\Controllers\Vendor\CustomerController;
use App\Http\Controllers\Vendor\DeliveryMan\DeliveryManController;
use App\Http\Controllers\Vendor\DeliveryMan\DeliveryManWalletController;
use App\Http\Controllers\Vendor\DeliveryMan\DeliveryManWithdrawController;
use App\Http\Controllers\Vendor\DeliveryMan\EmergencyContactController;
use App\Http\Controllers\Vendor\NotificationController;
use App\Http\Controllers\Vendor\POS\CartController;
use App\Http\Controllers\Vendor\POS\POSController;
use App\Http\Controllers\Vendor\POS\POSOrderController;
use App\Http\Controllers\Vendor\Product\ProductController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\Promotion\ClearanceSaleController;
use App\Http\Controllers\Vendor\RefundController;
use App\Http\Controllers\Vendor\ReviewController;
use App\Http\Controllers\Vendor\Shipping\CategoryShippingCostController;
use App\Http\Controllers\Vendor\Shipping\ShippingMethodController;
use App\Http\Controllers\Vendor\Shipping\ShippingTypeController;
use App\Http\Controllers\Vendor\ShopController;
use App\Http\Controllers\Vendor\SystemController;
use App\Http\Controllers\Vendor\WithdrawController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\Order\OrderController;
use App\Http\Controllers\Vendor\TransactionReportController;
use App\Http\Controllers\Vendor\ProductReportController;
use App\Http\Controllers\Vendor\OrderReportController;
use App\Http\Controllers\Vendor\VendorPaymentInfoController;

Route::group(['middleware' => ['maintenance_mode', 'actch:admin_panel']], function () {

    Route::group(['prefix' => 'vendor', 'as' => 'vendor.'], function () {
        /* authentication */
        Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
            Route::controller(LoginController::class)->group(function () {
                Route::get('login', 'getLoginView');
                Route::post('login', 'login')->name('login');
                Route::get('vendor.auth.login', 'logout')->name('logout');
            });
            Route::group(['prefix' => 'forgot-password', 'as' => 'forgot-password.'], function () {
                Route::controller(ForgotPasswordController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::post('index', 'getPasswordResetRequest');
                    Route::get('otp-verification', 'getOTPVerificationView')->name('otp-verification');
                    Route::post('otp-verification', 'submitOTPVerificationCode');
                    Route::get('reset-password', 'getPasswordResetView')->name('reset-password');
                    Route::post('reset-password', 'resetPassword');
                });
            });
            Route::group(['prefix' => 'registration', 'as' => 'registration.'], function () {
                Route::controller(RegisterController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::post('add', 'add')->name('add');
                });
            });
        });
        /* end authentication */
        Route::group(['middleware' => ['seller']], function () {
            Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
                Route::controller(DashboardController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('order-status/{type}', 'getOrderStatus')->name('order-status');
                    Route::get(Dashboard::EARNING_STATISTICS[URI], 'getEarningStatistics')->name('earning-statistics');
                    Route::post(Dashboard::WITHDRAW_REQUEST[URI], 'getWithdrawRequest')->name('withdraw-request');
                    Route::get(Dashboard::WITHDRAW_REQUEST[URI], 'getMethodList')->name('method-list');
                    Route::get(Dashboard::REAL_TIME_ACTIVITIES[URI], 'getRealTimeActivities')->name('real-time-activities');
                });
            });
            Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
                Route::controller(POSController::class)->group(function () {
                    Route::get(POS::INDEX[URI], 'index')->name('index');
                    Route::any(POS::CHANGE_CUSTOMER[URI], 'changeCustomer')->name('change-customer');
                    Route::post(POS::UPDATE_DISCOUNT[URI], 'updateDiscount')->name('update-discount');
                    Route::post(POS::COUPON_DISCOUNT[URI], 'getCouponDiscount')->name('coupon-discount');
                    Route::get(POS::QUICK_VIEW[URI], 'getQuickView')->name('quick-view');
                    Route::get(POS::SEARCH[URI], 'getSearchedProductsView')->name('search-product');
                });
                Route::controller(CartController::class)->group(function () {
                    Route::post(Cart::VARIANT[URI], 'getVariantPrice')->name('get-variant-price');
                    Route::post(Cart::QUANTITY_UPDATE[URI], 'updateQuantity')->name('quantity-update');
                    Route::get(Cart::GET_CART_IDS[URI], 'getCartIds')->name('get-cart-ids');
                    Route::get(Cart::CLEAR_CART_IDS[URI], 'clearSessionCartIds')->name('clear-cart-ids');
                    Route::post(Cart::ADD[URI], 'addToCart')->name('add-to-cart');
                    Route::post(Cart::REMOVE[URI], 'removeCart')->name('cart-remove');
                    Route::any(Cart::CART_EMPTY[URI], 'emptyCart')->name('cart-empty');
                    Route::any(Cart::CHANGE_CART[URI], 'changeCart')->name('change-cart');
                    Route::get(Cart::NEW_CART_ID[URI], 'addNewCartId')->name('new-cart-id');
                });
                Route::controller(POSOrderController::class)->group(function () {
                    Route::post(POSOrder::ORDER_DETAILS[URI] . '/{id}', 'index')->name('order-details');
                    Route::post(POSOrder::ORDER_PLACE[URI], 'placeOrder')->name('order-place');
                    Route::any(POSOrder::CANCEL_ORDER[URI], 'cancelOrder')->name('cancel-order');
                    Route::any(POSOrder::HOLD_ORDERS[URI], 'getAllHoldOrdersView')->name('view-hold-orders');
                });
            });
            Route::group(['prefix' => 'refund', 'as' => 'refund.'], function () {
                Route::controller(RefundController::class)->group(function () {
                    Route::get(Refund::INDEX[URI] . '/{status}', 'index')->name('index');
                    Route::get(Refund::DETAILS[URI] . '/{id}', 'getDetailsView')->name('details');
                    Route::post(Refund::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                    Route::get(Refund::EXPORT[URI] . '/{status}', 'exportList')->name('export');
                });
            });
            /* product */
            Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
                Route::controller(ProductController::class)->group(function () {
                    Route::get('list/{type}', 'index')->name('list');
                    Route::get('add', 'getAddView')->name('add');
                    Route::post('add', 'add');
                    Route::get('get-categories', 'getCategories')->name('get-categories');
                    Route::post('sku-combination', 'getSkuCombinationView')->name('sku-combination');
                    Route::post('digital-variation-combination', 'getDigitalVariationCombinationView')->name('digital-variation-combination');
                    Route::post('digital-variation-file-delete', 'deleteDigitalVariationFile')->name('digital-variation-file-delete');
                    Route::post('status-update', 'updateStatus')->name('status-update');
                    Route::get('export-excel/{type}', 'exportList')->name('export-excel');
                    Route::get('view/{id}', 'getView')->name('view');
                    Route::get('barcode/{id}', 'getBarcodeView')->name('barcode');
                    Route::delete('delete/{id}', 'delete')->name('delete');
                    Route::get('stock-limit-list', 'getStockLimitListView')->name('stock-limit-list');
                    Route::post('update-quantity', 'updateQuantity')->name('update-quantity');
                    Route::get('update/{id}', 'getUpdateView')->name('update');
                    Route::post('update/{id}', 'update');
                    Route::get('delete-image', 'deleteImage')->name('delete-image');
                    Route::get('get-variations', 'getVariations')->name('get-variations');
                    Route::get('bulk-import', 'getBulkImportView')->name('bulk-import');
                    Route::post('bulk-import', 'importBulkProduct');
                    Route::get('search', 'getSearchedProductsView')->name('search-product');
                    Route::get('product-gallery', 'getProductGalleryView')->name('product-gallery');
                    Route::get('stock-limit-status', 'getStockLimitStatus')->name('stock-limit-status');
                    Route::post('delete-preview-file', 'deletePreviewFile')->name('delete-preview-file');
                    Route::get('request-restock-list', 'getRequestRestockListView')->name('request-restock-list');
                    Route::get('export-restock', 'exportRestockList')->name('restock-export');
                    Route::delete('delete-restock/{id}', 'deleteRestock')->name('restock-delete');
                });
            });

            Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
                Route::controller(OrderController::class)->group(function () {
                    Route::get(Order::LIST[URI] . '/{status}', 'index')->name('list');
                    Route::get(Order::CUSTOMERS[URI], 'getCustomers')->name('customers');
                    Route::get(Order::EXPORT_EXCEL[URI] . '/{status}', 'exportList')->name('export-excel');
                    Route::get(Order::GENERATE_INVOICE[URI] . '/{id}', 'generateInvoice')->name('generate-invoice');
                    Route::get(Order::VIEW[URI] . '/{id}', 'getView')->name('details');
                    Route::post(Order::UPDATE_ADDRESS[URI], 'updateAddress')->name('address-update');// update address from order details
                    Route::post(Order::PAYMENT_STATUS[URI], 'updatePaymentStatus')->name('payment-status');
                    Route::post(Order::UPDATE_DELIVERY_INFO[URI], 'updateDeliverInfo')->name('update-deliver-info');
                    Route::get(Order::ADD_DELIVERY_MAN[URI] . '/{order_id}/{d_man_id}', 'addDeliveryMan')->name('add-delivery-man');
                    Route::post(Order::UPDATE_AMOUNT_DATE[URI], 'updateAmountDate')->name('amount-date-update');
                    Route::post(Order::DIGITAL_FILE_UPLOAD_AFTER_SELL[URI], 'uploadDigitalFileAfterSell')->name('digital-file-upload-after-sell');
                    Route::post(Order::UPDATE_STATUS[URI], 'updateStatus')->name('status');
                });
            });

            Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
                Route::controller(CustomerController::class)->group(function () {
                    Route::get(Customer::LIST[URI], 'getList')->name('list');
                    Route::post(Customer::ADD[URI], 'add')->name('add');
                });
            });

            Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
                Route::controller(ReviewController::class)->group(function () {
                    Route::get(Review::INDEX[URI], 'index')->name('index');
                    Route::get(Review::UPDATE_STATUS[URI] . '/{id}/{status}', 'updateStatus')->name('update-status');
                    Route::get(Review::EXPORT[URI], 'exportList')->name('export');
                    Route::post(Review::REVIEW_REPLY[URI], 'addReviewReply')->name('add-review-reply');
                });
            });

            Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
                Route::controller(CouponController::class)->group(function () {
                    Route::get(Coupon::INDEX[URI], 'index')->name('index');
                    Route::post(Coupon::ADD[URI], 'add')->name('add');
                    Route::get(Coupon::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                    Route::post(Coupon::UPDATE[URI] . '/{id}', 'update');
                    Route::get(Coupon::UPDATE_STATUS[URI] . '/{id}/{status}', 'updateStatus')->name('update-status');
                    Route::delete(Coupon::DELETE[URI] . '/{id}', 'delete')->name('delete');
                    Route::get(Coupon::QUICK_VIEW[URI], 'getQuickView')->name('quick-view');
                    Route::get(Coupon::EXPORT[URI], 'exportList')->name('export');
                });
            });

            Route::group(['prefix' => 'clearance-sale', 'as' => 'clearance-sale.'], function () {
                Route::controller(ClearanceSaleController::class)->group(function () {
                    Route::get(ClearanceSale::LIST[URI], 'index')->name('index');
                    Route::post(ClearanceSale::STATUS[URI], 'updateStatus')->name('status-update');
                    Route::post(ClearanceSale::UPDATE_CONFIG[URI], 'updateClearanceConfig')->name('update-config');
                    Route::get(ClearanceSale::SEARCH[URI], 'getSearchedProductsView')->name('search-product-for-clearance');
                    Route::get(ClearanceSale::MULTIPLE_PRODUCT_DETAILS[URI], 'getMultipleProductDetailsView')->name('multiple-clearance-product-details');
                    Route::post(ClearanceSale::ADD_PRODUCT[URI], 'addClearanceProduct')->name('add-product');
                    Route::post(ClearanceSale::PRODUCT_STATUS[URI], 'updateProductStatus')->name('product-status-update');
                    Route::delete(ClearanceSale::CLEARANCE_DELETE[URI] . '/{product_id}', 'deleteClearanceProduct')->name('clearance-delete');
                    Route::delete(ClearanceSale::CLEARANCE_PRODUCTS_DELETE[URI], 'deleteClearanceAllProduct')->name('clearance-delete-all-product');
                    Route::post(ClearanceSale::UPDATE_DISCOUNT[URI], 'updateDiscountAmount')->name('update-discount');
                });
            });

            Route::group(['prefix' => 'messages', 'as' => 'messages.'], function () {
                Route::controller(ChattingController::class)->group(function () {
                    Route::get(Chatting::INDEX[URI] . '/{type}', 'index')->name('index');
                    Route::get(Chatting::MESSAGE[URI], 'getMessageByUser')->name('message');
                    Route::post(Chatting::MESSAGE[URI], 'addVendorMessage');
                    Route::get(Chatting::NEW_NOTIFICATION[URI], 'getNewNotification')->name('new-notification');
                });
            });

            Route::group(['prefix' => 'notification', 'as' => 'notification.'], function () {
                Route::post(Notification::INDEX[URI], [NotificationController::class, 'getNotificationModalView'])->name('index');
            });

            /* DeliveryMan */
            Route::group(['prefix' => 'delivery-man', 'as' => 'delivery-man.'], function () {
                Route::controller(DeliveryManController::class)->group(function () {
                    Route::get(DeliveryMan::INDEX[URI], 'index')->name('index');
                    Route::post(DeliveryMan::INDEX[URI], 'add');
                    Route::get(DeliveryMan::LIST[URI], 'getListView')->name('list');
                    Route::get(DeliveryMan::EXPORT[URI], 'exportList')->name('export');
                    Route::get(DeliveryMan::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                    Route::post(DeliveryMan::UPDATE[URI] . '/{id}', 'update');
                    Route::post(DeliveryMan::UPDATE_STATUS[URI] . '/{id}', 'updateStatus')->name('update-status');
                    Route::delete(DeliveryMan::DELETE[URI] . '/{id}', 'delete')->name('delete');
                    Route::get(DeliveryMan::RATING[URI] . '/{id}', 'getRatingView')->name('rating');
                });

                Route::group(['prefix' => 'wallet', 'as' => 'wallet.'], function () {
                    Route::controller(DeliveryManWalletController::class)->group(function () {
                        Route::get(DeliveryManWallet::INDEX[URI] . '/{id}', 'index')->name('index');
                        Route::get(DeliveryManWallet::ORDER_HISTORY[URI] . '/{id}', 'getOrderHistory')->name('order-history');
                        Route::get(DeliveryManWallet::ORDER_STATUS_HISTORY[URI] . '/{order}', 'getOrderStatusHistory')->name('order-status-history');
                        Route::get(DeliveryManWallet::EARNING[URI] . '/{id}', 'getEarningListView')->name('earning');
                        Route::get(DeliveryManWallet::CASH_COLLECT[URI] . '/{id}', 'getCashCollectView')->name('cash-collect');
                        Route::post(DeliveryManWallet::CASH_COLLECT[URI] . '/{id}', 'collectCash');
                    });
                });

                Route::group(['prefix' => 'withdraw', 'as' => 'withdraw.'], function () {
                    Route::controller(DeliveryManWithdrawController::class)->group(function () {
                        Route::get(DeliveryManWithdraw::INDEX[URI], 'index')->name('index');
                        Route::post(DeliveryManWithdraw::INDEX[URI], 'getFiltered');
                        Route::get(DeliveryManWithdraw::DETAILS[URI] . '/{withdrawId}', 'getDetails')->name('details');
                        Route::post(DeliveryManWithdraw::UPDATE_STATUS[URI] . '/{withdrawId}', 'updateStatus')->name('update-status');
                        Route::any(DeliveryManWithdraw::EXPORT[URI], 'exportList')->name('export');
                    });
                });

                Route::group(['prefix' => 'emergency-contact', 'as' => 'emergency-contact.'], function () {
                    Route::controller(EmergencyContactController::class)->group(function () {
                        Route::get('index', 'index')->name('index');
                        Route::post('index', 'add');
                        Route::get('update/{id}', 'getUpdateView')->name('update');
                        Route::post('update/{id}', 'update');
                        Route::patch('index', 'updateStatus');
                        Route::delete('index', 'delete');
                    });
                });
            });

            Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
                Route::controller(ProfileController::class)->group(function () {
                    Route::get(Profile::INDEX[URI], 'index')->name('index');
                    Route::get(Profile::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                    Route::post(Profile::UPDATE[URI] . '/{id}', 'update');
                    Route::patch(Profile::UPDATE[URI] . '/{id}', 'updatePassword');
                    Route::get(Profile::BANK_INFO_UPDATE[URI] . '/{id}', 'getBankInfoUpdateView')->name('update-bank-info');
                    Route::post(Profile::BANK_INFO_UPDATE[URI] . '/{id}', 'updateBankInfo');
                });
            });

            Route::group(['prefix' => 'shop', 'as' => 'shop.'], function () {
                Route::controller(ShopController::class)->group(function () {
                    Route::get('index', 'index')->name('index');
                    Route::get('update/{id}', 'getUpdateView')->name('update');
                    Route::post('update/{id}', 'update');
                    Route::post('add-vacation', 'updateVacation')->name('update-vacation');
                    Route::post('close-shop-temporary', 'closeShopTemporary')->name('close-shop-temporary');
                    Route::post('update-other-settings', 'updateOtherSettings')->name('update-other-settings');

                    Route::get('other-setup', 'getOtherSetupView')->name('other-setup');
                });

                Route::group(['prefix' => 'payment-information', 'as' => 'payment-information.'], function () {
                    Route::controller(VendorPaymentInfoController::class)->group(function () {
                        Route::get('', 'index')->name('index');
                        Route::post('add', 'add')->name('add');
                        Route::post('update', 'update')->name('update');
                        Route::get('edit/{id?}', 'getUpdateView')->name('update-view');
                        Route::get('delete/{id?}', 'delete')->name('delete');
                        Route::post('default', 'updateDefault')->name('default');
                        Route::post('status', 'updateStatus')->name('update-status');
                        Route::get('dynamic-fields', 'getDynamicPaymentInformationView')->name('dynamic-fields');
                    });
                });
            });

            Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
                Route::group(['prefix' => 'shipping-method', 'as' => 'shipping-method.'], function () {
                    Route::controller(ShippingMethodController::class)->group(function () {
                        Route::get(ShippingMethod::INDEX[URI], 'index')->name('index');
                        Route::post(ShippingMethod::INDEX[URI], 'add');
                        Route::get(ShippingMethod::UPDATE[URI] . '/{id}', 'getUpdateView')->name('update');
                        Route::post(ShippingMethod::UPDATE[URI] . '/{id}', 'update');
                        Route::post(ShippingMethod::UPDATE_STATUS[URI], 'updateStatus')->name('update-status');
                        Route::post(ShippingMethod::DELETE[URI], 'delete')->name('delete');
                    });
                });

                Route::group(['prefix' => 'shipping-type', 'as' => 'shipping-type.'], function () {
                    Route::post(ShippingType::INDEX[URI], [ShippingTypeController::class, 'addOrUpdate'])->name('index');
                });

                Route::group(['prefix' => 'category-wise-shipping-cost', 'as' => 'category-wise-shipping-cost.'], function () {
                    Route::post(CategoryShippingCost::INDEX[URI], [CategoryShippingCostController::class, 'index'])->name('index');
                });

                Route::group(['prefix' => 'withdraw', 'as' => 'withdraw.'], function () {
                    Route::controller(WithdrawController::class)->group(function () {
                        Route::get('index', 'index')->name('index');
                        Route::post('index', 'getListByStatus');
                        Route::get('close/{id}', 'closeWithdrawRequest')->name('close');
                        Route::get('export', 'exportList')->name('export-withdraw-list');
                        Route::post('render-withdraw-method-infos', 'renderInfosView')->name('render-withdraw-method-infos');
                    });
                });
            });

            Route::controller(SystemController::class)->group(function () {
                Route::get('/get-order-data', 'getOrderData')->name('get-order-data');
            });

            Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
                Route::controller(ProductReportController::class)->group(function () {
                    Route::get('all-product', 'all_product')->name('all-product');
                    Route::get('all-product-excel', 'allProductExportExcel')->name('all-product-excel');

                    Route::get('stock-product-report', 'stock_product_report')->name('stock-product-report');
                    Route::get('product-stock-export', 'productStockExport')->name('product-stock-export');
                });

                Route::controller(OrderReportController::class)->group(function () {
                    Route::get('order-report', 'order_report')->name('order-report');
                    Route::get('order-report-excel', 'orderReportExportExcel')->name('order-report-excel');
                    Route::get('order-report-pdf', 'exportOrderReportInPDF')->name('order-report-pdf');
                });

                Route::any('set-date', 'App\Http\Controllers\Vendor\ReportController@set_date')->name('set-date');
            });

            Route::group(['prefix' => 'transaction', 'as' => 'transaction.'], function () {
                Route::controller(TransactionReportController::class)->group(function () {
                    Route::get('order-list', 'order_transaction_list')->name('order-list');
                    Route::get('pdf-order-wise-transaction', 'pdf_order_wise_transaction')->name('pdf-order-wise-transaction');
                    Route::get('order-transaction-export-excel', 'orderTransactionExportExcel')->name('order-transaction-export-excel');
                    Route::get('order-transaction-summary-pdf', 'order_transaction_summary_pdf')->name('order-transaction-summary-pdf');
                    Route::get('expense-list', 'getExpenseTransactionList')->name('expense-list');
                    Route::get('pdf-order-wise-expense-transaction', 'pdf_order_wise_expense_transaction')->name('pdf-order-wise-expense-transaction');
                    Route::get('expense-transaction-summary-pdf', 'expense_transaction_summary_pdf')->name('expense-transaction-summary-pdf');
                    Route::get('expense-transaction-export-excel', 'expenseTransactionExportExcel')->name('expense-transaction-export-excel');
                });
            });
        });
    });

});
