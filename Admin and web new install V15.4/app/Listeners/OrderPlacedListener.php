<?php

namespace App\Listeners;

use App\Events\OrderPlacedEvent;
use App\Models\ReferralCustomer;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\Mail;
use App\Traits\PushNotificationTrait;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPlacedListener
{
    use PushNotificationTrait, EmailTemplateTrait;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlacedEvent $event): void
    {
        if ($event->email) {
            $this->sendMail($event);
        }
        if ($event->notification) {
            $this->sendNotification($event);
        }

    }

    private function sendMail(OrderPlacedEvent $event): void
    {
        $email = $event->email;
        $data = $event->data;
        try {
            $this->sendingMail(sendMailTo: $email, userType: $data['userType'], templateName: $data['templateName'], data: $data);
        } catch (\Exception $exception) {

        }
    }

    private function sendNotification(OrderPlacedEvent $event): void
    {
        $key = $event->notification->key;
        $type = $event->notification->type;
        $order = $event->notification->order;
        $this->sendOrderNotification(key: $key, type: $type, order: $order);

        if (!$order['is_guest'] && isset($order?->customer?->id)) {
            $getCustomer = ReferralCustomer::where('user_id', $order->customer->id)->first();
            if ($getCustomer && $getCustomer->ordered_notify != 1) {
                $getCustomer->ordered_notify = 1;
                $getCustomer->save();
            }
        }
    }
}
