<?php
/**
 * Contains the OrderStatus enum class.
 *
 * @copyright   Copyright (c) 2017 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2017-11-27
 *
 */

namespace App\Domains\Order;

use Konekt\Enum\Enum;
use Vanilo\Order\Contracts\OrderStatus as OrderStatusContract;
use function __;

class OrderStatus extends Enum implements OrderStatusContract
{
    const __DEFAULT = self::NEW;

    /**
     * New orders are new orders. Payment has not been made.
     */
    const NEW = 'new';

    /**
     * Deposit paid is booked.
     */
    const BOOKED = 'booked';

    /**
     * Ready to dispatch / to supply service (gig prepped). Payment may have been made, agreements have reached,
     */
    const READY = 'ready';

    /**
     * Orders fulfilled completely - service has been provided / goods dispatched.
     */
    const COMPLETED = 'completed';

    /**
     * Order that has been cancelled.
     */
    const CANCELLED = 'cancelled';

    // $labels static property needs to be defined
    static public $labels = [];

    protected static array $openStatuses = [self::NEW, self::BOOKED, self::READY];

    public function isOpen(): bool
    {
        return in_array($this->value, static::$openStatuses);
    }

    public static function getOpenStatuses(): array
    {
        return static::$openStatuses;
    }

    protected static function boot()
    {
        static::$labels = [
            // New booking no payments made or known about
            self::NEW       => __('New'),

            // New booking, Deposit Payment was made.
            self::BOOKED         => __('Booked'),

            // Been paid. Music has ben prepped and spoken to them.
            self::READY         => __('Ready'),

            // Booking has been done
            self::COMPLETED     => __('Completed'),

            // Booking was cancelled.
            self::CANCELLED     => __('Cancelled')
        ];
    }
}
