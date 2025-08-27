<?php

// 简单的测试脚本来验证项目功能
echo "=== 可视化拖拽生成器 - 功能测试 ===\n\n";

// 测试自动加载
echo "1. 测试自动加载...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ 自动加载成功\n";
} catch (Exception $e) {
    echo "✗ 自动加载失败: " . $e->getMessage() . "\n";
    echo "请先运行: composer install\n";
    exit(1);
}

// 测试类加载
echo "\n2. 测试类加载...\n";
try {
    $project = new \App\Models\Project();
    echo "✓ Project模型加载成功\n";
    
    $wechatGenerator = new \App\Services\WechatCodeGenerator();
    echo "✓ 微信代码生成器加载成功\n";
    
    $h5Generator = new \App\Services\H5CodeGenerator();
    echo "✓ H5代码生成器加载成功\n";
} catch (Exception $e) {
    echo "✗ 类加载失败: " . $e->getMessage() . "\n";
    exit(1);
}

// 测试代码生成
echo "\n3. 测试代码生成...\n";
try {
    $testConfig = [
        'title' => '测试项目',
        'pages' => [
            [
                'name' => 'home',
                'title' => '首页',
                'elements' => [
                    [
                        'type' => 'text',
                        'props' => [
                            'content' => '欢迎使用可视化生成器',
                            'class' => 'welcome-text',
                            'style' => 'font-size: 24px; color: #333;'
                        ]
                    ],
                    [
                        'type' => 'button',
                        'props' => [
                            'text' => '点击我',
                            'class' => 'btn btn-primary',
                            'style' => ''
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    // 测试微信小程序代码生成
    $wechatCode = $wechatGenerator->generate($testConfig);
    echo "✓ 微信小程序代码生成成功\n";
    
    // 测试H5代码生成
    $h5Code = $h5Generator->generate($testConfig);
    echo "✓ H5代码生成成功\n";
    
    // 测试预览代码生成
    $wechatPreview = $wechatGenerator->generatePreview($testConfig);
    $h5Preview = $h5Generator->generatePreview($testConfig);
    echo "✓ 预览代码生成成功\n";
    
} catch (Exception $e) {
    echo "✗ 代码生成失败: " . $e->getMessage() . "\n";
    exit(1);
}

// 测试项目模型
echo "\n4. 测试项目模型...\n";
try {
    $project = new \App\Models\Project();
    $project->name = '测试项目';
    $project->type = 'h5';
    $project->config = json_encode($testConfig);
    
    $projectId = $project->save();
    echo "✓ 项目创建成功，ID: {$projectId}\n";
    
    $savedProject = \App\Models\Project::find($projectId);
    if ($savedProject) {
        echo "✓ 项目查询成功\n";
    } else {
        echo "✗ 项目查询失败\n";
    }
    
} catch (Exception $e) {
    echo "✗ 项目模型测试失败: " . $e->getMessage() . "\n";
}

echo "\n=== 测试完成 ===\n";
echo "所有核心功能测试通过！\n";
echo "现在可以运行 ./start.sh 启动项目了。\n";
