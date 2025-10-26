<?php
return [
    'hierarchy' => ['guest', 'user', 'mufti', 'admin', 'superadmin'],
    'abilities' => [
        'guest' => [
            'read.public',
        ],
        'user' => [
            'read.public',
            'suggest.submit',
        ],
        'mufti' => [
            'read.public',
            'suggest.submit',
            'filings.review',
            'filings.approve',
            'comment.discussion',
        ],
        'admin' => [
            'read.public',
            'suggest.submit',
            'filings.review',
            'filings.approve',
            'comment.discussion',
            'manage.companies',
            'manage.filings',
            'manage.users',
        ],
        'superadmin' => ['*'],
    ],
];
