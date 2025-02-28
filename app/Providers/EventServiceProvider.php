<?php

namespace App\Providers;

use App\Events\UploadBulk\TrackUploadEvent;
use App\Events\UploadBulk\VideoEffectUploadEvent;
use App\Events\UploadBulk\SFXUploadEvent;
use App\Events\Webhook\Billing\SubscriptionEvent;
use App\Events\Webhook\Classic\SubscriptionPaymentFailedEvent;
use App\Events\Webhook\Classic\SubscriptionPaymentRefundedEvent;
use App\Events\Webhook\Classic\SubscriptionPaymentSucceededEvent;
use App\Events\Webhook\Billing\CustomerEvent;
use App\Events\Webhook\Billing\ProductEvent;
use App\Events\Webhook\Billing\PriceEvent;
use App\Events\Webhook\Billing\TransactionEvent;
use App\Events\AttachTagEvent;
use App\Events\CreateArchiveForTracksEvent;
use App\Events\Webhook\Billing\AdjustmentEvent;
use App\Events\Webhook\Classic\SubscriptionCanceledEvent;
use App\Events\Webhook\Classic\SubscriptionUpdatedEvent;
use App\Listeners\Webhook\Billing\TransactionListener;
use App\Models\Order;
use App\Listeners\UploadBulk\TrackUploadListener;
use App\Listeners\UploadBulk\VideoEffectUploadListener;
use App\Listeners\UploadBulk\SFXUploadListener;
use App\Listeners\Webhook\Classic\SubscriptionPaymentFailedListener;
use App\Listeners\Webhook\Classic\SubscriptionPaymentRefundedListener;
use App\Listeners\Webhook\Classic\SubscriptionPaymentSucceededListener;
use App\Listeners\Webhook\Billing\CustomerListener;
use App\Listeners\Webhook\Billing\ProductListener;
use App\Listeners\Webhook\Billing\SubscriptionListener;
use App\Listeners\AttachTagListener;
use App\Listeners\CreateArchiveForTracksListener;
use App\Listeners\Webhook\Billing\PriceListener;
use App\Listeners\Webhook\Billing\AdjustmentListener;
use App\Listeners\Webhook\Classic\SubscriptionCanceledListener;
use App\Listeners\Webhook\Classic\SubscriptionUpdatedListener;
use App\Observers\OrderObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Order::observe(OrderObserver::class);

        Event::listen(
            TrackUploadEvent::class,
            TrackUploadListener::class
        );

        Event::listen(
            VideoEffectUploadEvent::class,
            VideoEffectUploadListener::class
        );

        Event::listen(
            SFXUploadEvent::class,
            SFXUploadListener::class
        );

        Event::listen(
            AttachTagEvent::class,
            AttachTagListener::class,
        );

        Event::listen(
            CreateArchiveForTracksEvent::class,
            CreateArchiveForTracksListener::class,
        );

        Event::listen(
            CustomerEvent::class,
            CustomerListener::class
        );

        Event::listen(
            ProductEvent::class,
            ProductListener::class
        );

        Event::listen(
            ProductEvent::class,
            ProductListener::class
        );

        Event::listen(
            PriceEvent::class,
            PriceListener::class
        );

        Event::listen(
            TransactionEvent::class,
            TransactionListener::class
        );

        Event::listen(
            SubscriptionEvent::class,
            SubscriptionListener::class
        );

        Event::listen(
            SubscriptionPaymentSucceededEvent::class,
            SubscriptionPaymentSucceededListener::class
        );

        Event::listen(
            SubscriptionPaymentFailedEvent::class,
            SubscriptionPaymentFailedListener::class
        );

        Event::listen(
            SubscriptionPaymentRefundedEvent::class,
            SubscriptionPaymentRefundedListener::class
        );

        Event::listen(
            AdjustmentEvent::class,
            AdjustmentListener::class
        );

        Event::listen(
            SubscriptionCanceledEvent::class,
            SubscriptionCanceledListener::class
        );

        Event::listen(
            SubscriptionUpdatedEvent::class,
            SubscriptionUpdatedListener::class
        );
    }
}
