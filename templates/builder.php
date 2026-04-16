<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拖拽构建器 - 可视化生成器</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/builder.css" rel="stylesheet">
    <script src="vendor/vue/vue.global.prod.js"></script>
</head>
<body>
    <div id="app" class="builder-container">
        <div class="toolbar">
            <div class="container-fluid toolbar-inner">
                <div class="toolbar-copy">
                    <h5 class="toolbar-title mb-0">
                        <i class="bi bi-grid-1x2-fill"></i>
                        拖拽构建器
                    </h5>
                    <div class="toolbar-meta">
                        <span class="status-pill">{{ pages.length }} 个页面</span>
                        <span class="status-pill">{{ currentPage.elements.length }} 个组件</span>
                        <span v-if="selectedElement" class="status-pill status-pill-active">
                            已选中 {{ elementLabels[selectedElement.type] || selectedElement.type }}
                        </span>
                    </div>
                </div>

                <div class="toolbar-actions">
                    <div v-if="statusMessage" :class="['builder-alert', `builder-alert-${statusVariant}`]">
                        {{ statusMessage }}
                    </div>
                    <div class="btn-group" role="group">
                        <button @click="previewProject" class="btn btn-outline-primary btn-sm" :disabled="isPreviewLoading">
                            <span v-if="isPreviewLoading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                            <i v-else class="bi bi-eye"></i>
                            预览
                        </button>
                        <button @click="generateCode" class="btn btn-primary btn-sm" :disabled="isGenerating">
                            <span v-if="isGenerating" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                            <i v-else class="bi bi-download"></i>
                            生成代码
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0 flex-grow-1 builder-workspace">
            <div class="col-xl-3 col-lg-4 sidebar">
                <div class="workspace-panel">
                    <div class="panel-section">
                        <h6 class="section-title">组件库</h6>
                        <p class="section-desc">拖到中间画布，或拖进容器组件中继续嵌套。</p>
                    </div>

                    <div class="panel-section">
                        <h6 class="section-subtitle">基础组件</h6>
                        <div
                            v-for="component in basicComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <div class="component-item-title">
                                <i :class="component.icon"></i>
                                <span>{{ component.name }}</span>
                            </div>
                            <small>{{ component.description }}</small>
                        </div>
                    </div>

                    <div class="panel-section">
                        <h6 class="section-subtitle">布局组件</h6>
                        <div
                            v-for="component in layoutComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <div class="component-item-title">
                                <i :class="component.icon"></i>
                                <span>{{ component.name }}</span>
                            </div>
                            <small>{{ component.description }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-8 main-content">
                <div class="canvas-shell">
                    <div class="page-toolbar">
                        <div>
                            <h6 class="section-title mb-2">页面管理</h6>
                            <div class="page-chip-group">
                                <button
                                    v-for="page in pages"
                                    :key="page.id"
                                    type="button"
                                    :class="['page-chip', { active: currentPageId === page.id }]"
                                    @click="switchPage(page.id)"
                                >
                                    <span>{{ page.title }}</span>
                                    <small>{{ page.name }}</small>
                                </button>
                            </div>
                        </div>

                        <div class="page-create-box">
                            <label class="form-label">新增页面</label>
                            <div class="input-group input-group-sm">
                                <input
                                    v-model.trim="newPageTitle"
                                    type="text"
                                    class="form-control"
                                    placeholder="例如：活动页"
                                    @keyup.enter="addPage"
                                >
                                <button @click="addPage" class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="canvas-headline">
                        <div>
                            <h6 class="mb-1">页面画布</h6>
                            <p class="mb-0 text-muted">当前页面：{{ currentPage.title }}，点击组件即可编辑属性，支持嵌套拖拽。</p>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button @click="clearCanvas" class="btn btn-outline-danger">
                                <i class="bi bi-trash3"></i>
                                清空页面
                            </button>
                            <button @click="deleteCurrentPage" class="btn btn-outline-secondary">
                                <i class="bi bi-folder-minus"></i>
                                删除页面
                            </button>
                        </div>
                    </div>

                    <div
                        class="canvas"
                        :class="{ 'drag-over': isCanvasDragOver }"
                        @dragover="onCanvasDragOver"
                        @dragleave="onCanvasDragLeave"
                        @drop="onRootDrop"
                        @click="clearSelection"
                    >
                        <div v-if="currentPage.elements.length === 0" class="canvas-empty-state">
                            <i class="bi bi-inboxes display-5"></i>
                            <h5>从左侧拖拽组件开始构建</h5>
                            <p>布局组件可以继续承载子组件，适合做卡片区、横向栅格和内容分组。</p>
                        </div>

                        <component-renderer
                            v-for="element in currentPage.elements"
                            :key="element.id"
                            :element="element"
                            :selected-element-id="selectedElementId"
                            @select-element="selectElement"
                            @remove-element="removeElement"
                            @container-drop="onContainerDrop"
                        />
                    </div>
                </div>
            </div>

            <div class="col-xl-3 properties-panel">
                <div class="workspace-panel">
                    <div class="panel-section">
                        <h6 class="section-title">页面信息</h6>
                        <div class="mb-3">
                            <label class="form-label">页面标题</label>
                            <input v-model.trim="currentPage.title" type="text" class="form-control" placeholder="请输入页面标题">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">页面标识</label>
                            <input
                                v-model.trim="currentPage.name"
                                type="text"
                                class="form-control"
                                placeholder="index"
                                @blur="normalizeCurrentPageName"
                            >
                        </div>
                        <div class="form-text">用于生成代码的文件名和路由，建议使用英文、数字和连字符。</div>
                    </div>

                    <div v-if="selectedElement" class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">组件属性</h6>
                            <button type="button" class="btn btn-outline-danger btn-sm" @click="removeElement(selectedElement.id)">
                                <i class="bi bi-trash3"></i>
                                删除
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">组件类型</label>
                            <input type="text" class="form-control" :value="elementLabels[selectedElement.type] || selectedElement.type" readonly>
                        </div>

                        <div class="mb-3" v-if="selectedElement.type === 'image'">
                            <label class="form-label">图片上传</label>
                            <input type="file" class="form-control" accept="image/*" @change="onImageUpload($event)">
                            <div class="form-text">图片会以 Base64 形式内联，便于离线预览与导出。</div>
                        </div>

                        <div v-for="field in editablePropFields" :key="field.key" class="mb-3">
                            <label class="form-label">{{ field.label }}</label>
                            <textarea
                                v-if="field.control === 'textarea'"
                                class="form-control"
                                rows="4"
                                :value="selectedElement.props[field.key]"
                                @input="updateElementProp(field.key, $event.target.value)"
                            ></textarea>
                            <input
                                v-else
                                :type="field.type"
                                class="form-control"
                                :value="selectedElement.props[field.key]"
                                @input="updateElementProp(field.key, $event.target.value)"
                            >
                        </div>
                    </div>

                    <div v-else class="panel-section panel-empty">
                        <h6 class="section-title">组件属性</h6>
                        <p>画布中点击任意组件后，这里会显示可编辑属性。</p>
                        <p class="mb-0 text-muted">容器组件支持继续拖拽内容到内部，适合做分栏和卡片布局。</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">项目预览（H5）</h5>
                            <p class="text-muted small mb-0">预览内容根据当前全部页面实时生成。</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="preview-stage" v-html="previewHtml"></div>
                        <div v-if="!hasPreview" class="text-muted small mt-3">暂无内容，请先在画布中添加组件。</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="codeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">生成代码</h5>
                            <p class="text-muted small mb-0">当前展示的是完整项目结构，可直接用于后续整理导出。</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#wechatTab">微信小程序</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#h5Tab">H5</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="wechatTab">
                                <pre class="code-preview"><code v-text="wechatCode"></code></pre>
                            </div>
                            <div class="tab-pane fade" id="h5Tab">
                                <pre class="code-preview"><code v-text="h5Code"></code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/builder.js"></script>
</body>
</html>
