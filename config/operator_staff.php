<?php

return [
    'roles' => [
        'Admin',
        'Head of Department',
        'Reservation Manager',
        'Operational Manager',
        'Finance Manager',
        'Marketing Manager',
        'Support Manager',
        'Content Manager',
    ],

    'actions' => [
        'read' => 'Read',
        'create' => 'Create',
        'update' => 'Update',
        'approve' => 'Approve',
        'publish' => 'Publish',
    ],

    'modules' => [
        'Accommodation' => ['accommodations', 'accommodation-bookings', 'places'],
        'Transport' => ['transport', 'transport-bookings', 'transport-service-route-pairs'],
        'Activity' => ['activities', 'activity-bookings'],
        'shared' => [
            'account',
            'profile',
            'compliance',
            'users',
            'capacity-permissions',
            'collaboration-agreements',
            'reservation',
            'accounting',
            'operations',
            'marketing',
            'content',
            'support',
            'feedback',
            'trips',
            'operators',
            'packages',
            'groups',
            'payment-transactions',
            'regions',
        ],
    ],
];
