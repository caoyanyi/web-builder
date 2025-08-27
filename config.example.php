<?php

return [
    'app' => [
        'name' => '可视化拖拽生成器',
        'env' => 'local',
        'debug' => true,
        'url' => 'http://localhost:8000'
    ],
    
    'database' => [
        'connection' => 'sqlite',
        'database' => 'database/database.sqlite'
    ],
    
    'components' => [
        'basic' => [
            'text' => ['name' => '文本', 'icon' => 'bi bi-type'],
            'image' => ['name' => '图片', 'icon' => 'bi bi-image'],
            'button' => ['name' => '按钮', 'icon' => 'bi bi-box']
        ],
        'layout' => [
            'div' => ['name' => '容器', 'icon' => 'bi bi-square']
        ],
        'form' => [
            'form' => ['name' => '表单', 'icon' => 'bi bi-input-cursor']
        ]
    ]
];
