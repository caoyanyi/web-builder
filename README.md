# 可视化拖拽生成器

一个基于 PHP 和 Vue 3 的可视化页面构建工具，支持通过拖拽方式搭建页面，并生成 H5 页面与微信小程序代码。

## 当前能力

- 拖拽文本、图片、按钮、输入框、文本域、下拉选择、单选组、多选组、间距块、容器、行布局组件到画布
- 支持容器嵌套，适合搭建卡片区、分栏区和内容模块
- 支持多页面编辑、页面切换和页面标识管理
- 支持组件复制、上下排序、撤销和重做
- 支持组件在根画布和容器之间拖拽移动，也支持拖拽插入排序
- 支持桌面 / 平板 / 手机尺寸切换预览
- 属性面板会按组件类型提供快捷配置
- 支持常用区块模板一键插入，适合快速起稿
- 模板区块支持核心文案快捷改写
- 支持页面结构导航，便于在复杂嵌套中快速定位组件
- 支持浏览器本地草稿自动保存与恢复
- 按钮支持配置提示消息和跳转链接动作
- 支持项目级主题配置，可影响预览和导出样式
- 按钮支持提交表单动作，导出后可校验当前页面必填字段
- 输入框支持手机号 / 邮箱 / 数字等字段类型与正则校验配置
- 下拉选择、单选组、多选组支持选项配置、默认值和横向 / 纵向排布
- 提交表单动作支持提交后清空和提交后跳转配置
- 支持项目 JSON 导入与导出
- 支持直接导出 H5 / 微信小程序 ZIP 压缩包
- 支持实时预览 H5 结构
- 支持同时生成 H5 和微信小程序代码
- 支持项目保存、加载、更新和删除
- 项目数据默认保存在 `database/projects.json`

## 技术栈

### 后端

- PHP 7.4+
- Slim Framework 4
- PHP-DI
- `slim/php-view`

### 前端

- Vue.js 3
- Bootstrap 5
- HTML5 Drag and Drop API

## 项目结构

```text
├── database/              # 项目数据存储目录，运行后会生成 projects.json
├── public/                # Web 入口、静态资源、前端脚本
├── src/
│   ├── Controllers/       # 页面与 API 控制器
│   ├── Models/            # 文件存储模型
│   └── Services/          # H5 / 微信代码生成服务
├── templates/             # PHP 页面模板
├── composer.json          # Composer 依赖与启动脚本
└── README.md
```

## 环境要求

- PHP 7.4 或更高版本
- Composer

## 安装与启动

1. 克隆项目

```bash
git clone https://github.com/caoyanyi/web-builder.git
cd web-builder
```

2. 安装依赖

```bash
composer install
```

3. 配置环境

项目使用了 `phpdotenv` 的 `safeLoad()`，因此没有 `.env` 也可以启动。  
如果你需要自定义环境变量，可以复制示例文件：

```bash
cp .env.example .env
```

4. 启动开发服务器

```bash
composer start
```

也可以直接运行：

```bash
php -S localhost:8000 -t public
```

5. 访问应用

打开浏览器访问 `http://localhost:8000`

## 使用说明

### 1. 进入构建器

- 访问首页并进入 `/builder`
- 在左侧选择组件并拖到中间画布

### 2. 编辑页面

- 顶部和中部区域可以查看当前页面数量与组件数量
- 中部支持新增页面、切换页面、删除页面、清空页面
- 中部空白画布支持一键插入 Hero、功能卡片和表单模板
- 右侧可以编辑页面标题、页面标识和组件属性
- 右侧提供页面结构树，可快速选中嵌套组件

### 3. 管理项目

- 左侧输入项目名称并点击“保存项目”
- 保存后会出现在“已保存项目”列表
- 可以从列表中直接加载或删除项目
- 本地未保存内容会自动保存到浏览器，可随时恢复草稿

### 4. 预览与生成代码

- 点击“预览”可查看当前项目的 H5 预览结构
- 点击“生成代码”可同时查看 H5 与微信小程序代码输出
- 点击顶部导出按钮可直接下载 H5 或微信小程序 ZIP 压缩包

## 当前支持的组件

### 基础组件

- 文本 `text`
- 图片 `image`
- 按钮 `button`

### 表单组件

- 输入框 `input`
- 文本域 `textarea`
- 下拉选择 `select`
- 单选组 `radio-group`
- 多选组 `checkbox-group`
- 间距块 `spacer`

### 布局组件

- 容器 `div`
- 行布局 `row`

## API

### 项目管理

- `GET /api/projects` 获取项目列表
- `POST /api/projects` 创建项目
- `GET /api/projects/{id}` 获取项目详情
- `PUT /api/projects/{id}` 更新项目
- `DELETE /api/projects/{id}` 删除项目

### 代码生成

- `POST /api/generate/wechat` 生成微信小程序代码
- `POST /api/generate/h5` 生成 H5 代码
- `POST /api/preview` 生成 H5 预览内容

## 开发说明

### 添加新组件

1. 在 [`public/js/builder.js`](public/js/builder.js) 中补充组件定义、默认属性和前端渲染逻辑
2. 在 `src/Services/H5CodeGenerator.php` 中补充 H5 输出逻辑
3. 在 `src/Services/WechatCodeGenerator.php` 中补充微信小程序输出逻辑

### 项目存储

- 当前项目存储为本地 JSON 文件，不依赖数据库服务
- 默认文件路径为 `database/projects.json`
- 如需接入数据库，可从 `src/Models/Project.php` 开始替换存储实现

## 当前已知限制

- 生成结果目前主要用于结构化输出和开发起点，尚未包含完整工程打包下载能力
- 组件事件和复杂交互还比较基础，适合先完成页面结构搭建
- 项目保存依赖后端接口，因此首次运行前必须先执行 `composer install`

## 许可证

本项目采用 MIT 许可证，详见 [LICENSE](LICENSE)。
