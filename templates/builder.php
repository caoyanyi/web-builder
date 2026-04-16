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
                        <span class="status-pill">Ctrl/Cmd + Z 撤销</span>
                        <span v-if="selectedElement" class="status-pill status-pill-active">
                            已选中 {{ elementLabels[selectedElement.type] || selectedElement.type }}
                        </span>
                    </div>
                </div>

                <div class="toolbar-actions">
                    <div v-if="statusMessage" :class="['builder-alert', `builder-alert-${statusVariant}`]">
                        {{ statusMessage }}
                    </div>

                    <div class="toolbar-action-groups">
                        <div class="btn-group" role="group">
                            <button @click="undoHistory" class="btn btn-outline-secondary btn-sm" :disabled="!canUndo">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                撤销
                            </button>
                            <button @click="redoHistory" class="btn btn-outline-secondary btn-sm" :disabled="!canRedo">
                                <i class="bi bi-arrow-clockwise"></i>
                                重做
                            </button>
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

                        <div class="btn-group" role="group">
                            <button @click="exportProjectBundle('h5')" class="btn btn-outline-success btn-sm" :disabled="isExportingH5">
                                <span v-if="isExportingH5" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-file-earmark-zip"></i>
                                导出 H5 ZIP
                            </button>
                            <button @click="exportProjectBundle('wechat')" class="btn btn-outline-success btn-sm" :disabled="isExportingWechat">
                                <span v-if="isExportingWechat" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-file-earmark-zip"></i>
                                导出微信 ZIP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0 flex-grow-1 builder-workspace">
            <div class="col-xl-3 col-lg-4 sidebar">
                <div class="workspace-panel">
                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">项目管理</h6>
                            <span v-if="projectId" class="status-pill">ID {{ projectId }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">项目名称</label>
                            <input
                                v-model.trim="projectName"
                                type="text"
                                class="form-control"
                                placeholder="例如：春季活动专题"
                                @input="queueHistoryCapture"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">目标类型</label>
                            <select v-model="projectType" class="form-select" @change="queueHistoryCapture">
                                <option value="h5">H5</option>
                                <option value="wechat">微信小程序</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button @click="saveProject" class="btn btn-primary btn-sm" :disabled="isSavingProject">
                                <span v-if="isSavingProject" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-save"></i>
                                {{ projectId ? '更新项目' : '保存项目' }}
                            </button>
                            <button @click="createNewProject" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-file-earmark-plus"></i>
                                新建项目
                            </button>
                        </div>

                        <div class="builder-mini-actions">
                            <button @click="exportProjectJson" type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-box-arrow-down"></i>
                                导出 JSON
                            </button>
                            <button @click="triggerImportFile" type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-box-arrow-in-down"></i>
                                导入 JSON
                            </button>
                        </div>

                        <input
                            ref="projectImportInput"
                            type="file"
                            class="d-none"
                            accept="application/json,.json"
                            @change="importProjectFile"
                        >
                    </div>

                    <div class="panel-section">
                        <h6 class="section-title">组件库</h6>
                        <p class="section-desc">拖到中间画布，或拖进容器组件中继续嵌套。组件支持复制、排序和撤销重做。</p>
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
                        <h6 class="section-subtitle">表单组件</h6>
                        <div
                            v-for="component in formComponents"
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

                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">已保存项目</h6>
                            <button @click="fetchProjects" type="button" class="btn btn-outline-secondary btn-sm" :disabled="isProjectListLoading">
                                <span v-if="isProjectListLoading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <i v-else class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>

                        <div v-if="savedProjects.length === 0" class="saved-empty-state">
                            暂无已保存项目，保存后会显示在这里。
                        </div>

                        <div v-else class="saved-projects">
                            <div
                                v-for="project in savedProjects"
                                :key="project.id"
                                :class="['saved-project-card', { active: project.id === projectId }]"
                            >
                                <div class="saved-project-head">
                                    <div>
                                        <h6 class="mb-1">{{ project.name }}</h6>
                                        <p class="mb-0 text-muted small">{{ formatProjectMeta(project) }}</p>
                                    </div>
                                    <span class="status-pill">{{ project.type === 'wechat' ? '微信' : 'H5' }}</span>
                                </div>

                                <div class="saved-project-actions">
                                    <button @click="loadProject(project.id)" type="button" class="btn btn-outline-primary btn-sm" :disabled="isProjectLoading">
                                        加载
                                    </button>
                                    <button @click="removeProject(project.id)" type="button" class="btn btn-outline-danger btn-sm" :disabled="isProjectLoading">
                                        删除
                                    </button>
                                </div>
                            </div>
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
                            <p class="mb-0 text-muted">当前页面：{{ currentPage.title }}，点击组件即可编辑属性，支持嵌套拖拽、复制、上下排序。</p>
                        </div>
                        <div class="canvas-headline-actions">
                            <div class="btn-group btn-group-sm" role="group">
                                <button @click="setViewportMode('desktop')" :class="['btn', viewportMode === 'desktop' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-display"></i>
                                    桌面
                                </button>
                                <button @click="setViewportMode('tablet')" :class="['btn', viewportMode === 'tablet' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-tablet"></i>
                                    平板
                                </button>
                                <button @click="setViewportMode('mobile')" :class="['btn', viewportMode === 'mobile' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-phone"></i>
                                    手机
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm" role="group">
                                <button @click="duplicateCurrentPage" class="btn btn-outline-secondary">
                                    <i class="bi bi-copy"></i>
                                    复制页面
                                </button>
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
                    </div>

                    <div :class="['canvas-frame', `canvas-frame-${viewportMode}`]">
                        <div
                            class="canvas"
                            :class="{
                                'drag-over': isCanvasDragOver,
                                'drag-root': activeDropTarget && activeDropTarget.mode === 'root'
                            }"
                            @dragover="onCanvasDragOver"
                            @dragleave="onCanvasDragLeave"
                            @drop="onRootDrop"
                            @click="clearSelection"
                        >
                            <div v-if="currentPage.elements.length === 0" class="canvas-empty-state">
                                <i class="bi bi-inboxes display-5"></i>
                                <h5>从左侧拖拽组件开始构建</h5>
                                <p>现在支持表单组件、项目导入导出、历史撤销重做、跨容器拖拽移动和 ZIP 导出。</p>
                            </div>

                            <component-renderer
                                v-for="element in currentPage.elements"
                                :key="element.id"
                                :element="element"
                                :selected-element-id="selectedElementId"
                                :drop-state="getDropStateForElement(element.id)"
                                @select-element="selectElement"
                                @remove-element="removeElement"
                                @duplicate-element="duplicateElement"
                                @move-element="moveElement"
                                @insert-drop="onInsertDrop"
                                @container-drop="onContainerDrop"
                                @preview-drop-target="setDropTarget"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 properties-panel">
                <div class="workspace-panel">
                    <div class="panel-section">
                        <h6 class="section-title">页面信息</h6>
                        <div class="mb-3">
                            <label class="form-label">页面标题</label>
                            <input
                                v-model.trim="currentPage.title"
                                type="text"
                                class="form-control"
                                placeholder="请输入页面标题"
                                @input="queueHistoryCapture"
                            >
                        </div>
                        <div class="mb-2">
                            <label class="form-label">页面标识</label>
                            <input
                                v-model.trim="currentPage.name"
                                type="text"
                                class="form-control"
                                placeholder="index"
                                @input="queueHistoryCapture"
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

                        <div v-if="selectedElementType === 'button'" class="property-shortcuts">
                            <label class="form-label">按钮快捷样式</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-sm btn-primary" @click="applyButtonPreset('btn btn-primary')">主按钮</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="applyButtonPreset('btn btn-outline-primary')">描边按钮</button>
                                <button type="button" class="btn btn-sm btn-success" @click="applyButtonPreset('btn btn-success')">成功按钮</button>
                                <button type="button" class="btn btn-sm btn-dark" @click="applyButtonPreset('btn btn-dark')">深色按钮</button>
                            </div>
                            <div class="shortcut-grid shortcut-grid-tight mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('')">自动宽度</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('100%')">整行按钮</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'image'" class="property-shortcuts">
                            <label class="form-label">图片快捷尺寸</label>
                            <div class="shortcut-grid shortcut-grid-tight">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('160px')">160px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('320px')">320px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('100%')">铺满</button>
                            </div>
                            <div class="shortcut-grid shortcut-grid-tight mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyImageStylePreset('border-radius: 16px;')">圆角</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyImageStylePreset('object-fit: cover; aspect-ratio: 16 / 9;')">横幅</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'input' || selectedElementType === 'textarea'" class="property-shortcuts">
                            <label class="form-label">表单快捷配置</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldPreset('form-control')">标准输入</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldPreset('form-control form-control-sm')">紧凑输入</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('50%')">半宽</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('100%')">整行</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'spacer'" class="property-shortcuts">
                            <label class="form-label">间距快捷配置</label>
                            <div class="shortcut-grid shortcut-grid-tight">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySpacerPreset('16px')">16px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySpacerPreset('32px')">32px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySpacerPreset('64px')">64px</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'div'" class="property-shortcuts">
                            <label class="form-label">容器快捷布局</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyContainerPreset('padding: 16px; border-radius: 16px; background: #ffffff;')">卡片容器</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyContainerPreset('padding: 24px; background: linear-gradient(180deg, #f8fbf7 0%, #ffffff 100%); border-radius: 20px;')">分组区块</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'row'" class="property-shortcuts">
                            <label class="form-label">行布局快捷排版</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyRowPreset('gap: 12px; align-items: center;')">紧凑横排</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyRowPreset('gap: 20px; flex-wrap: wrap; align-items: stretch;')">卡片栅格</button>
                            </div>
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
                                @input="updateElementProp(field.key, $event.target.value, field)"
                            ></textarea>
                            <div v-else-if="field.control === 'checkbox'" class="form-check form-switch builder-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :checked="Boolean(selectedElement.props[field.key])"
                                    @change="updateElementProp(field.key, $event.target.checked, field)"
                                >
                            </div>
                            <input
                                v-else
                                :type="field.type"
                                class="form-control"
                                :value="selectedElement.props[field.key]"
                                @input="updateElementProp(field.key, $event.target.value, field)"
                            >
                        </div>
                    </div>

                    <div v-else class="panel-section panel-empty">
                        <h6 class="section-title">组件属性</h6>
                        <p>画布中点击任意组件后，这里会显示可编辑属性。</p>
                        <p class="mb-2 text-muted">支持在组件卡片右上角直接完成复制、上移、下移和删除，也可以直接拖到别的容器里。</p>
                        <p class="mb-0 text-muted">导入 JSON 后会按当前构建器结构恢复页面和组件，并可直接导出 ZIP。</p>
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
                        <div class="preview-header-actions">
                            <div class="btn-group btn-group-sm" role="group">
                                <button @click="setViewportMode('desktop')" :class="['btn', viewportMode === 'desktop' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-display"></i>
                                </button>
                                <button @click="setViewportMode('tablet')" :class="['btn', viewportMode === 'tablet' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-tablet"></i>
                                </button>
                                <button @click="setViewportMode('mobile')" :class="['btn', viewportMode === 'mobile' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-phone"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div :class="['preview-shell', `preview-shell-${viewportMode}`]">
                            <div class="preview-stage" v-html="previewHtml"></div>
                        </div>
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
