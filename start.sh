#!/bin/bash

echo "启动可视化拖拽生成器..."
echo "正在检查依赖..."

# 检查PHP是否安装
if ! command -v php &> /dev/null; then
    echo "错误: PHP未安装，请先安装PHP 7.4或更高版本"
    exit 1
fi

# 检查PHP版本
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP版本: $PHP_VERSION"

# 检查Composer是否安装
if ! command -v composer &> /dev/null; then
    echo "错误: Composer未安装，请先安装Composer"
    exit 1
fi

echo "安装PHP依赖..."
composer install

echo "启动开发服务器..."
echo "访问地址: http://localhost:8000"
echo "按 Ctrl+C 停止服务器"

php -S localhost:8000 -t public
