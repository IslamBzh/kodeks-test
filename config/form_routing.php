<?php

return [
    /*
     * Правила маршрутизации.
     * Обрабатываются ПО ПОРЯДКУ, первое совпавшее — побеждает.
     */
    'primary'             => [

        // profession = "студент" → student@example.com
        [
            'field'     => 'profession',
            'equals'    => 'студент',
            'recipient' => 'student@example.com',
        ],

        // region in ["Санкт-Петербург", "Москва"] → center@example.com
        [
            'field'     => 'region',
            'equals'    => ['Санкт-Петербург', 'Москва'],
            'recipient' => 'center@example.com',
        ],

        // product = "promo" → promo@example.com
        [
            'field'     => 'product',
            'equals'    => 'promo',
            'recipient' => 'promo@example.com',
        ],
    ],

    /*
      * EXTRA RULES:
      * Правила, которые добавляют дополнительных получателей,
      * но НЕ заменяют primary.
      */
    'extra'       => [
        [
            'field'     => 'product',
            'equals'    => 'special',
            'recipient' => 'special@example.com',
        ],
    ],

    /*
     * если ни одно правило не сработало.
     */
    'default' => 'all@example.com',
];
