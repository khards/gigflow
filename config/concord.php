<?php

return [
    'modules' => [
        /*
        Konekt\AppShell\Providers\ModuleServiceProvider::class => [
            'ui' => [
                'name' => 'Vanilo',
                'url' => '/admin/product'
            ]
        ],
*/
        /*
         * Example:
         * VendorA\ModuleX\Providers\ModuleServiceProvider::class,
         * VendorB\ModuleY\Providers\ModuleServiceProvider::class
         *
         */
        Vanilo\Order\Providers\ModuleServiceProvider::class,
        Vanilo\Cart\Providers\ModuleServiceProvider::class,
        Vanilo\Checkout\Providers\ModuleServiceProvider::class,
        Konekt\Address\Providers\ModuleServiceProvider::class,

    ],
];
