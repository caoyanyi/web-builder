<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <?php
        $indexCssVersion = @filemtime(__DIR__ . '/../public/css/index.css') ?: time();
        $recentProjects = isset($recentProjects) && is_array($recentProjects) ? $recentProjects : [];
        $starterTemplates = [
            [
                'key' => 'hero',
                'eyebrow' => '专题首屏',
                'industry' => '品牌发布',
                'goal' => '提升首屏完成度',
                'module_count' => '3 个核心模块',
                'title' => '快速起一个能看的开场页',
                'description' => '适合活动页、品牌页和专题页，带标题、说明与双按钮结构，省掉从零摆首屏的时间。',
                'notes' => ['双 CTA 结构', '适合主视觉开场', '便于继续叠加卖点区'],
            ],
            [
                'key' => 'features',
                'eyebrow' => '卖点卡片',
                'industry' => '方案说明',
                'goal' => '清晰拆解卖点',
                'module_count' => '4 个信息单元',
                'title' => '把产品优势整理成清晰模块',
                'description' => '适合方案介绍、服务说明和产品对比，把一整段说明拆成更适合阅读和改写的卡片布局。',
                'notes' => ['三列能力区', '便于替换文案', '适合专题中段'],
            ],
            [
                'key' => 'contact',
                'eyebrow' => '表单收集',
                'industry' => '线索转化',
                'goal' => '承接咨询与报名',
                'module_count' => '5 个表单字段',
                'title' => '从展示页自然过渡到线索收集',
                'description' => '适合预约报名、咨询收集和活动登记，能直接作为后续分步表单或条件显隐的起点。',
                'notes' => ['支持继续扩展字段', '适合收口转化', '更接近真实业务页'],
            ],
        ];
        $featuredProject = $recentProjects[0] ?? null;
        $queuedProjects = array_slice($recentProjects, 1);
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>可视化拖拽生成器 - 微信小程序和 H5 页面快速搭建</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/index.css?v=<?php echo $indexCssVersion; ?>" rel="stylesheet">
</head>
<body class="home-page">
    <div class="page-shell">
        <nav class="navbar navbar-expand-lg navbar-dark home-navbar">
            <div class="container">
                <a class="navbar-brand brand-mark" href="/">
                    <span class="brand-mark-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                    <span>
                        <strong>可视化生成器</strong>
                        <small>拖拽搭建 H5 与微信页面</small>
                    </span>
                </a>
                <button
                    class="navbar-toggler border-0 shadow-none"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="切换导航"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link" href="#features">核心能力</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#scenes">适用场景</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">产品说明</a>
                        </li>
                        <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                            <a class="btn btn-sm nav-cta" href="/builder">进入构建器</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            <section class="hero-section">
                <div class="container">
                    <div class="hero-grid">
                        <div class="hero-copy">
                            <span class="section-tag">
                                <i class="bi bi-stars"></i>
                                让页面搭建回到产品工作流里
                            </span>
                            <h1>少一点模板味，多一点真正能落地的构建体验。</h1>
                            <p class="hero-lead">
                                从首屏、表单到多页面流程，你可以直接拖拽组件、配置主题、保存草稿，
                                然后导出 H5 或微信小程序代码。首页不只是介绍工具，更是在告诉用户这套工具究竟能帮他把什么事情做完。
                            </p>
                            <div class="hero-actions">
                                <a href="/builder" class="btn btn-lg btn-hero-primary">
                                    <i class="bi bi-play-circle"></i>
                                    现在开始构建
                                </a>
                                <a href="#features" class="btn btn-lg btn-hero-secondary">
                                    <i class="bi bi-layout-text-window"></i>
                                    先看能力结构
                                </a>
                            </div>
                            <div class="hero-metrics">
                                <div class="metric-pill">
                                    <strong>多页面编辑</strong>
                                    <span>适合活动页、报名流、问卷页</span>
                                </div>
                                <div class="metric-pill">
                                    <strong>条件显隐 + 分步表单</strong>
                                    <span>覆盖更真实的业务收集场景</span>
                                </div>
                                <div class="metric-pill">
                                    <strong>H5 / 微信双端输出</strong>
                                    <span>从搭建到导出一条链闭环</span>
                                </div>
                            </div>
                        </div>

                        <div class="hero-showcase">
                            <div class="showcase-card showcase-main">
                                <div class="showcase-toolbar">
                                    <span class="toolbar-dot"></span>
                                    <span class="toolbar-dot"></span>
                                    <span class="toolbar-dot"></span>
                                    <div class="toolbar-badge">Builder Workspace</div>
                                </div>
                                <div class="showcase-body">
                                    <div class="showcase-sidebar">
                                        <span class="mini-label">项目面板</span>
                                        <div class="sidebar-card active">
                                            <strong>春季活动专题</strong>
                                            <span>H5 / 已保存</span>
                                        </div>
                                        <div class="sidebar-card">
                                            <strong>表单线索收集</strong>
                                            <span>微信 / 草稿中</span>
                                        </div>
                                        <div class="sidebar-list">
                                            <span><i class="bi bi-check2-circle"></i> 主题色快速切换</span>
                                            <span><i class="bi bi-check2-circle"></i> 本地草稿自动恢复</span>
                                            <span><i class="bi bi-check2-circle"></i> ZIP 导出</span>
                                        </div>
                                    </div>
                                    <div class="showcase-canvas">
                                        <span class="mini-label">画布预览</span>
                                        <div class="canvas-frame">
                                            <div class="canvas-hero">
                                                <span class="canvas-kicker">专题主视觉</span>
                                                <strong>活动报名页</strong>
                                                <p>标题、说明、CTA、表单入口都能直接拖拽排布。</p>
                                            </div>
                                            <div class="canvas-grid">
                                                <div class="canvas-block">
                                                    <i class="bi bi-window"></i>
                                                    <span>功能卡片区</span>
                                                </div>
                                                <div class="canvas-block">
                                                    <i class="bi bi-ui-checks-grid"></i>
                                                    <span>分步表单</span>
                                                </div>
                                                <div class="canvas-block">
                                                    <i class="bi bi-columns-gap"></i>
                                                    <span>双栏布局</span>
                                                </div>
                                            </div>
                                            <div class="canvas-footer">
                                                <span>实时预览</span>
                                                <span>代码生成</span>
                                                <span>导出发布</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="showcase-note">
                                <i class="bi bi-lightning-charge-fill"></i>
                                不是“只会摆三张卡片”的演示页，而是围绕真实搭建流程组织内容和能力。
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="workflow-section">
                <div class="container">
                    <div class="section-heading centered">
                        <span class="section-tag">工作方式</span>
                        <h2>从起稿到导出，步骤清晰，而不是把所有卖点堆在一屏里。</h2>
                    </div>
                    <div class="workflow-grid">
                        <article class="workflow-card">
                            <span class="workflow-index">01</span>
                            <h3>先搭结构</h3>
                            <p>拖入文本、图片、按钮、表单和布局组件，把页面骨架先搭起来，适合快速起稿和比稿。</p>
                        </article>
                        <article class="workflow-card">
                            <span class="workflow-index">02</span>
                            <h3>再补业务逻辑</h3>
                            <p>配置字段校验、条件显隐、分步切换、按钮动作，让页面不是静态示意，而是可交互的业务页面。</p>
                        </article>
                        <article class="workflow-card">
                            <span class="workflow-index">03</span>
                            <h3>最后直接交付</h3>
                            <p>支持实时预览、项目保存、草稿恢复以及 H5 / 微信小程序 ZIP 导出，减少二次整理成本。</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="features" class="feature-section">
                <div class="container">
                    <div class="section-heading">
                        <span class="section-tag">核心能力</span>
                        <h2>把“能拖拽”说得更具体一点。</h2>
                        <p>真正有用的不是“零代码”这句口号，而是页面、表单、主题和输出能力能不能连起来。</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-3 col-md-6">
                            <article class="feature-panel h-100">
                                <span class="feature-icon"><i class="bi bi-grid"></i></span>
                                <h3>结构搭建更顺手</h3>
                                <p>支持基础组件、表单组件、容器和行布局，还能在根画布和容器之间拖拽移动与排序。</p>
                            </article>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <article class="feature-panel h-100">
                                <span class="feature-icon"><i class="bi bi-sliders"></i></span>
                                <h3>表单能力更完整</h3>
                                <p>手机号、邮箱、数字、正则校验，配合提交动作、字段标签快照和提交记录分析，适合线索收集。</p>
                            </article>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <article class="feature-panel h-100">
                                <span class="feature-icon"><i class="bi bi-palette2"></i></span>
                                <h3>主题和内容可控</h3>
                                <p>项目级主题配置会作用于预览和导出，搭配模板区块和快捷改写，首页到落地页风格能保持统一。</p>
                            </article>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <article class="feature-panel h-100">
                                <span class="feature-icon"><i class="bi bi-box-arrow-up-right"></i></span>
                                <h3>输出链路闭环</h3>
                                <p>预览、生成、导出在同一工作流内完成，不需要再把页面定义拆成多套工具来回转译。</p>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="scenes" class="scene-section">
                <div class="container">
                    <div class="section-heading centered">
                        <span class="section-tag">适用场景</span>
                        <h2>更像真实业务页面，而不是一个抽象的“万能站点生成器”。</h2>
                    </div>
                    <div class="scene-grid">
                        <article class="scene-card">
                            <span class="scene-label">活动专题</span>
                            <h3>适合快速搭主视觉、卖点区、报名表单和跳转按钮。</h3>
                            <p>页面结构清晰，适合市场活动页、专题页、会务报名页和促销承接页。</p>
                        </article>
                        <article class="scene-card">
                            <span class="scene-label">线索收集</span>
                            <h3>支持条件显隐、字段校验、分步提交这些真正会用到的交互。</h3>
                            <p>比只支持“文本 + 按钮”的演示型工具更接近实际表单业务。</p>
                        </article>
                        <article class="scene-card">
                            <span class="scene-label">小程序起稿</span>
                            <h3>先把页面框架和组件关系跑通，再导出给开发继续深化。</h3>
                            <p>更适合作为高效率起稿器和前期协作界面，而不是停留在展示层。</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="template-section">
                <div class="container">
                    <div class="section-heading">
                        <span class="section-tag">模板参考</span>
                        <h2>给用户一个更具体的起稿入口，而不是只说“你可以自由创作”。</h2>
                        <p>下面这些入口会直接打开构建器，并预插入对应区块。首页因此不再只是介绍能力，而是开始参与实际使用流程。</p>
                    </div>
                    <div class="template-grid">
                        <?php foreach ($starterTemplates as $template): ?>
                            <article class="template-showcase-card">
                                <div class="template-preview template-preview-<?php echo htmlspecialchars($template['key'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="template-preview-top">
                                        <span class="template-preview-chip"><?php echo htmlspecialchars($template['eyebrow'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <div class="template-preview-pills">
                                            <span><?php echo htmlspecialchars($template['industry'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span><?php echo htmlspecialchars($template['goal'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </div>
                                    <div class="template-preview-body">
                                        <?php if ($template['key'] === 'hero'): ?>
                                            <div class="template-preview-kicker">Campaign Launch</div>
                                            <strong>新品发布主视觉</strong>
                                            <p>标题、说明、主次按钮一屏完整起稿。</p>
                                            <div class="template-preview-actions">
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <div class="template-preview-metrics">
                                                <span>主标题</span>
                                                <span>卖点描述</span>
                                                <span>双按钮</span>
                                            </div>
                                        <?php elseif ($template['key'] === 'features'): ?>
                                            <div class="template-preview-kicker">Solution Overview</div>
                                            <strong>功能结构区</strong>
                                            <div class="template-preview-columns">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <p>适合承接卖点与方案亮点。</p>
                                            <div class="template-preview-strip">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="template-preview-kicker">Lead Capture</div>
                                            <strong>预约表单</strong>
                                            <div class="template-preview-contact-shell">
                                                <div class="template-preview-contact-copy">
                                                    <span></span>
                                                    <span></span>
                                                </div>
                                                <div class="template-preview-form">
                                                    <span></span>
                                                    <span></span>
                                                    <span class="wide"></span>
                                                </div>
                                            </div>
                                            <p>把浏览流量往收集动作里推进一步。</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="template-showcase-copy">
                                    <span class="scene-label"><?php echo htmlspecialchars($template['eyebrow'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <div class="template-meta-row">
                                        <span><?php echo htmlspecialchars($template['industry'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span><?php echo htmlspecialchars($template['module_count'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <h3><?php echo htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p><?php echo htmlspecialchars($template['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="template-note-list">
                                        <?php foreach ($template['notes'] as $note): ?>
                                            <span><i class="bi bi-check2"></i> <?php echo htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <a class="template-link" href="/builder?template=<?php echo urlencode($template['key']); ?>">
                                        套用这个起稿
                                        <i class="bi bi-arrow-right-short"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="recent-section">
                <div class="container">
                    <div class="section-heading">
                        <span class="section-tag">最近项目</span>
                        <h2>首页可以把人带回手头工作，而不是只负责“看一眼”。</h2>
                        <p>如果本地已经有保存过的项目，这里会显示最近更新的内容，直接跳回对应构建器继续编辑。</p>
                    </div>

                    <?php if ($featuredProject): ?>
                        <div class="recent-console">
                            <article class="recent-featured-card">
                                <div class="recent-project-accent" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($featuredProject['theme_color'], ENT_QUOTES, 'UTF-8'); ?>, #16302b);"></div>
                                <div class="recent-featured-top">
                                    <div>
                                        <span class="scene-label"><?php echo $featuredProject['type'] === 'wechat' ? '微信小程序' : 'H5 页面'; ?></span>
                                        <h3><?php echo htmlspecialchars($featuredProject['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    </div>
                                    <span class="recent-project-pages"><?php echo (int) $featuredProject['page_count']; ?> 页</span>
                                </div>
                                <p class="recent-project-summary"><?php echo htmlspecialchars($featuredProject['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="recent-featured-grid">
                                    <div class="recent-featured-panel">
                                        <span class="recent-featured-label">当前入口页</span>
                                        <strong><?php echo htmlspecialchars($featuredProject['entry_title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <p>适合从这里继续补内容结构、交互动作和导出配置。</p>
                                    </div>
                                    <div class="recent-featured-panel">
                                        <span class="recent-featured-label">最近更新</span>
                                        <strong><?php echo htmlspecialchars($featuredProject['updated_at'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <p>首页已经可以承担继续上次工作的入口角色，而不只是展示能力。</p>
                                    </div>
                                </div>
                                <div class="recent-featured-actions">
                                    <a class="recent-project-link" href="/builder?project=<?php echo urlencode((string) $featuredProject['id']); ?>">
                                        继续编辑这个项目
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                    <a class="recent-secondary-link" href="/builder">打开空白工作台</a>
                                </div>
                            </article>

                            <aside class="recent-queue">
                                <div class="recent-queue-head">
                                    <span class="section-tag"><i class="bi bi-clock-history"></i> 工作队列</span>
                                    <p>把最近项目做得更像控制台，而不是三张并排宣传卡片。</p>
                                </div>
                                <?php if (!empty($queuedProjects)): ?>
                                    <div class="recent-queue-list">
                                        <?php foreach ($queuedProjects as $project): ?>
                                            <article class="recent-mini-card">
                                                <div class="recent-mini-top">
                                                    <span class="recent-mini-dot" style="background: <?php echo htmlspecialchars($project['theme_color'], ENT_QUOTES, 'UTF-8'); ?>;"></span>
                                                    <span class="recent-mini-type"><?php echo $project['type'] === 'wechat' ? '微信' : 'H5'; ?></span>
                                                </div>
                                                <h4><?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                                <p><?php echo htmlspecialchars($project['entry_title'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo (int) $project['page_count']; ?> 页</p>
                                                <a class="recent-mini-link" href="/builder?project=<?php echo urlencode((string) $project['id']); ?>">继续处理</a>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="recent-mini-empty">
                                        <strong>现在这就是你最近的主项目</strong>
                                        <p>后面保存更多项目后，这里会自动显示成一个轻量工作队列。</p>
                                    </div>
                                <?php endif; ?>
                            </aside>
                        </div>
                    <?php else: ?>
                        <div class="recent-empty">
                            <div>
                                <strong>还没有已保存项目</strong>
                                <p>你可以先从空白页开始，也可以直接套用一个模板区块作为第一页骨架。</p>
                            </div>
                            <div class="recent-empty-actions">
                                <a href="/builder" class="btn btn-lg btn-hero-primary">从空白开始</a>
                                <a href="/builder?template=hero" class="btn btn-lg btn-hero-secondary">插入 Hero 起稿</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="stack-section">
                <div class="container">
                    <div class="stack-shell">
                        <div class="stack-copy">
                            <span class="section-tag">技术与交付</span>
                            <h2>底层保持轻量，前台体验尽量完整。</h2>
                            <p>
                                项目基于 PHP、Slim 和 Vue 3 构建，首页这次优化也延续了构建器里已经出现的绿色系和产品感，
                                让用户从首页进入工作台时不会像切到另一套系统。
                            </p>
                        </div>
                        <div class="stack-grid">
                            <article class="stack-card">
                                <h3><i class="bi bi-server"></i> 后端支撑</h3>
                                <ul class="stack-list">
                                    <li>PHP 7.4+ 与 Slim Framework 4</li>
                                    <li>JSON 项目存储，部署门槛更低</li>
                                    <li>项目、表单、代码生成 API 一体化</li>
                                </ul>
                            </article>
                            <article class="stack-card">
                                <h3><i class="bi bi-window"></i> 前端体验</h3>
                                <ul class="stack-list">
                                    <li>Vue 3 驱动构建器交互</li>
                                    <li>Bootstrap 作为基础能力层</li>
                                    <li>响应式布局，移动端也能顺畅浏览首页</li>
                                </ul>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="about" class="about-section">
                <div class="container">
                    <div class="about-shell">
                        <div>
                            <span class="section-tag">产品说明</span>
                            <h2>这次首页优化，核心不是更花，而是更像一个有人在认真打磨的产品。</h2>
                        </div>
                        <div class="about-copy">
                            <p>
                                之前的首页信息虽然完整，但视觉和文案都过于平均，容易让人一眼判断为通用模板页。
                                现在这一版把重点收束到构建流程、真实场景和核心能力上，页面会更像一个有明确用户对象的产品入口。
                            </p>
                            <p>
                                如果后面还想继续往前走，我们还可以把首页与构建器再打通一步，比如加入真实案例封面、
                                模板入口和最近项目直达，让首页从好看变成真正有用。
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="container footer-inner">
                <div>
                    <strong>可视化生成器</strong>
                    <p>从拖拽搭建到代码导出，把首页气质和产品能力重新对齐。</p>
                </div>
                <div class="footer-actions">
                    <a href="#features">查看能力</a>
                    <a href="/builder">进入构建器</a>
                </div>
            </div>
            <div class="container footer-bottom">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> 可视化生成器. 保留所有权利。</p>
            </div>
        </footer>
    </div>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
