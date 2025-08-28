<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>可视化拖拽生成器 - 微信小程序和H5网页</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-code-slash"></i> 可视化生成器
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/builder">开始构建</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">功能特性</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">关于我们</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主标题区域 -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">可视化拖拽生成器</h1>
            <p class="lead mb-5">快速创建微信小程序和H5网页，无需编写代码</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/builder" class="btn btn-light btn-lg px-4">
                    <i class="bi bi-play-fill"></i> 开始构建
                </a>
                <a href="#features" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-info-circle"></i> 了解更多
                </a>
            </div>
        </div>
    </section>

    <!-- 功能特性 -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">强大功能</h2>
                <p class="lead text-muted">拖拽式设计，一键生成代码</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="bi bi-mouse feature-icon mb-3"></i>
                            <h5 class="card-title">拖拽设计</h5>
                            <p class="card-text">直观的拖拽界面，轻松设计页面布局和组件</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="bi bi-phone feature-icon mb-3"></i>
                            <h5 class="card-title">多平台支持</h5>
                            <p class="card-text">同时支持微信小程序和H5网页开发</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="bi bi-code-slash feature-icon mb-3"></i>
                            <h5 class="card-title">代码生成</h5>
                            <p class="card-text">自动生成完整的项目代码，可直接使用</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 技术栈 -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">技术栈</h2>
                <p class="lead text-muted">基于现代化的技术架构</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-server text-primary"></i> 后端技术
                            </h5>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> PHP 7.4+</li>
                                <li><i class="bi bi-check-circle text-success"></i> Slim Framework</li>
                                <li><i class="bi bi-check-circle text-success"></i> 依赖注入容器</li>
                                <li><i class="bi bi-check-circle text-success"></i> RESTful API</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-window text-primary"></i> 前端技术
                            </h5>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle text-success"></i> Vue.js 3</li>
                                <li><i class="bi bi-check-circle text-success"></i> HTML5 + CSS3</li>
                                <li><i class="bi bi-check-circle text-success"></i> 响应式设计</li>
                                <li><i class="bi bi-check-circle text-success"></i> 拖拽交互</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 关于我们 -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="display-5 fw-bold mb-4">关于我们</h2>
                    <p class="lead mb-4">
                        我们致力于为开发者提供简单易用的可视化开发工具，让创建微信小程序和H5网页变得简单高效。
                    </p>
                    <p class="text-muted">
                        通过拖拽式设计界面，您可以快速构建出专业的页面，系统会自动生成完整的代码，
                        支持实时预览和代码下载，大大提升开发效率。
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <i class="bi bi-lightbulb display-1 text-warning"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- 页脚 -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> 可视化拖拽生成器. 保留所有权利.</p>
        </div>
    </footer>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
