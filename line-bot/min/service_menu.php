<?php

function createServiceMenu() {
    $bubble = [
        'type' => 'bubble',
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => '寵物攝影服務選單',
                    'weight' => 'bold',
                    'size' => 'xl',
                    'align' => 'center',
                    'color' => '#4A4A4A'
                ],
                [
                    'type' => 'separator',
                    'margin' => 'lg'
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'lg',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '毛孩形象全檔方案',
                                    'size' => 'sm',
                                    'color' => '#555555',
                                    'flex' => 0
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'NT.5980',
                                    'size' => 'sm',
                                    'color' => '#111111',
                                    'align' => 'end'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '毛孩親寫真',
                                    'size' => 'sm',
                                    'color' => '#555555',
                                    'flex' => 0
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'NT.600',
                                    'size' => 'sm',
                                    'color' => '#111111',
                                    'align' => 'end'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '毛孩與你親子寫真',
                                    'size' => 'sm',
                                    'color' => '#555555',
                                    'flex' => 0
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'NT.1200',
                                    'size' => 'sm',
                                    'color' => '#111111',
                                    'align' => 'end'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '毛孩BOOM起來',
                                    'size' => 'sm',
                                    'color' => '#555555',
                                    'flex' => 0
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'NT.800',
                                    'size' => 'sm',
                                    'color' => '#111111',
                                    'align' => 'end'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'styles' => [
            'body' => [
                'backgroundColor' => '#FFFFFF'
            ]
        ]
    ];

    $flexMessage = [
        'type' => 'flex',
        'altText' => '寵物攝影服務選單',
        'contents' => $bubble
    ];

    return $flexMessage;
}