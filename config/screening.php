<?php
return [
    'denominator'=>'assets',
    'caps'=>[
        'debt'=>0.33,
        'interest'=>0.05,
        'nonsh'=>0.05,
        'liquid'=>0.90
    ],
    'weights'=>[
        'financial'=>0.6,
        'activity'=>0.4,
        'behaviour'=>0.0
    ],
    'review'=>'quarterly',
    'run_ratios_only_for'=>'permissible'
];
