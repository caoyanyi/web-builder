# 可视化拖拽生成器

一个基于PHP和Vue.js的可视化拖拽生成器，支持生成微信小程序和H5网页的完整代码。

## 功能特性

- 🎨 **拖拽式设计界面** - 直观的拖拽操作，轻松设计页面布局
- 📱 **多平台支持** - 同时支持微信小程序和H5网页开发
- 🔧 **组件库** - 丰富的预置组件，包括基础组件、布局组件、表单组件
- 👀 **实时预览** - 支持实时预览设计效果
- 💻 **代码生成** - 自动生成完整的项目代码，可直接使用
- 📁 **项目管理** - 支持保存、编辑、删除项目

## 技术架构

### 后端技术
- PHP 7.4+
- Slim Framework 4
- 依赖注入容器 (PHP-DI)
- RESTful API设计

### 前端技术
- Vue.js 3
- Bootstrap 5
- HTML5 + CSS3
- 拖拽交互 (HTML5 Drag & Drop API)

## 项目结构

```
├── src/                    # PHP源代码
│   ├── Controllers/       # 控制器
│   ├── Models/           # 数据模型
│   └── Services/         # 业务服务
├── templates/             # 页面模板
├── public/               # 公共资源
├── database/             # 数据库文件
├── composer.json         # 依赖配置
└── README.md            # 项目说明
```

## 安装说明

### 环境要求
- PHP 7.4 或更高版本
- Composer

### 安装步骤

1. 克隆项目
```bash
git clone <repository-url>
cd visual-builder
```

2. 安装依赖
```bash
composer install
```

3. 配置环境
```bash
cp .env.example .env
# 编辑 .env 文件配置数据库等信息
```

4. 启动开发服务器
```bash
composer start
# 或者
php -S localhost:8000 -t public
```

5. 访问应用
打开浏览器访问 `http://localhost:8000`

## 使用说明

### 1. 创建项目
- 访问首页，点击"开始构建"
- 进入拖拽构建器界面

### 2. 设计页面
- 从左侧组件库拖拽组件到画布
- 点击组件编辑属性
- 调整样式和布局

### 3. 预览效果
- 点击"预览"按钮查看效果
- 实时查看设计结果

### 4. 生成代码
- 点击"生成代码"按钮
- 选择目标平台（微信小程序或H5）
- 下载生成的代码文件

## 组件说明

### 基础组件
- **文本组件** - 显示文本内容
- **图片组件** - 显示图片
- **按钮组件** - 交互按钮

### 布局组件
- **容器组件** - 布局容器，可包含其他组件

### 表单组件
- **表单组件** - 完整的表单结构

## API接口

### 项目管理
- `POST /api/projects` - 创建项目
- `GET /api/projects/{id}` - 获取项目
- `PUT /api/projects/{id}` - 更新项目
- `DELETE /api/projects/{id}` - 删除项目

### 代码生成
- `POST /api/generate/wechat` - 生成微信小程序代码
- `POST /api/generate/h5` - 生成H5代码
- `POST /api/preview` - 预览项目

## 开发说明

### 添加新组件
1. 在 `src/Services/` 中的代码生成器添加组件支持
2. 在前端模板中添加组件定义
3. 实现组件的渲染逻辑

### 自定义样式
- 编辑 `templates/` 中的CSS样式
- 修改Bootstrap主题变量
- 添加自定义组件样式

## 贡献指南

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开 Pull Request

## 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情

## 联系方式

如有问题或建议，请提交 Issue 或联系开发团队。

---

**注意**: 这是一个开发中的项目，部分功能可能仍在开发中。欢迎提供反馈和建议！
