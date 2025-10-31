<?php

return [
    'customer_details_form' => [
        'name' => 'booking_user',
        'items' => [
            'firstName' =>  ['form_item_name' => 'first-name'],
            'lastName'  =>  ['form_item_name' => 'last-name'],
            'email'     =>  ['form_item_name' => 'email'],
            'password'  =>  ['form_item_name' => 'password'],
        ],
    ],

    'customer_address_details_form' => [
        'name' => 'booking_user',
        'items' => [
            'country_id'    => ['form_item_name' => 'country'],
            'address'       => ['form_item_name' => 'address-street'],
            'city'          => ['form_item_name' => 'address-town'],
            'postalcode'    => ['form_item_name' => 'postcode'],
        ],
    ],

    // vendor/vanilo/order/src/resources/database/migrations/2017_11_27_131854_create_billpayers_table.php
    'customer_billpayer_form' => [
        'name' => 'booking_user',
        'items' => [
            'email'            => ['form_item_name' => 'email'],
            'phone'            => [
                'form_item_name' => 'phone',
                'validation' => 'required|string|min:10|max:22'
            ],
            'firstName'        => ['form_item_name' => 'first-name'],
            'lastName'         => ['form_item_name' => 'last-name'],
        ],
    ],

    'customer_event_form' => [
        'name' => 'event_form',
        'items' => [
            'event-type' => ['form_item_name' => 'event-type'],
        ]
    ]

];
