<?php

use Slim\Factory\AppFactory;
use DI\Container;
use App\Controllers\BuilderController;
use App\Controllers\ProjectController;
use App\Controllers\CodeGeneratorController;

require_once __DIR__ . '/../vendor/autoload.php';

// 加载环境变量
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 创建容器
$container = new Container();

// 配置容器
$container->set('view', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

// 创建应用
AppFactory::setContainer($container);
$app = AppFactory::create();

// 添加中间件
$app->addMiddleware(new \Slim\Middleware\MethodOverrideMiddleware());
$app->addErrorMiddleware(true, true, true);

// 设置路由
$app->get('/', [BuilderController::class, 'index']);
$app->get('/builder', [BuilderController::class, 'builder']);
$app->post('/api/projects', [ProjectController::class, 'create']);
$app->get('/api/projects/{id}', [ProjectController::class, 'get']);
$app->put('/api/projects/{id}', [ProjectController::class, 'update']);
$app->delete('/api/projects/{id}', [ProjectController::class, 'delete']);
$app->post('/api/generate/wechat', [CodeGeneratorController::class, 'generateWechat']);
$app->post('/api/generate/h5', [CodeGeneratorController::class, 'generateH5']);
$app->post('/api/preview', [CodeGeneratorController::class, 'preview']);

// 运行应用
$app->run();
