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
        <!-- 工具栏 -->
        <div class="toolbar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="bi bi-code-slash"></i> 拖拽构建器
                        </h5>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <button @click="previewProject" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> 预览
                            </button>
                            <button @click="generateCode" class="btn btn-primary btn-sm">
                                <i class="bi bi-download"></i> 生成代码
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0 flex-grow-1">
            <!-- 左侧组件库 -->
            <div class="col-md-3 sidebar">
                <div class="p-3">
                    <h6 class="mb-3">组件库</h6>

                    <div class="mb-3">
                        <h6 class="text-muted small">基础组件</h6>
                        <div
                            v-for="component in basicComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <i :class="component.icon"></i> {{ component.name }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-muted small">布局组件</h6>
                        <div
                            v-for="component in layoutComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <i :class="component.icon"></i> {{ component.name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 主画布区域 -->
            <div class="col-md-6 main-content">
                <div class="canvas-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>页面画布</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button @click="addPage" class="btn btn-outline-secondary">
                                <i class="bi bi-plus"></i> 添加页面
                            </button>
                            <button @click="clearCanvas" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> 清空
                            </button>
                        </div>
                    </div>

                    <div class="canvas"
                         @dragover="onDragOver"
                         @drop="onDrop"
                         @dragleave="onDragLeave">

                        <div v-if="currentPage.elements.length === 0"
                             class="text-center text-muted py-5">
                            <i class="bi bi-arrow-down-circle display-4"></i>
                            <p class="mt-3">拖拽组件到这里开始构建页面</p>
                        </div>

                        <div v-for="(element, index) in currentPage.elements"
                             :key="element.id"
                             :class="['canvas-element', { selected: selectedElement === element }]"
                             draggable="true">

                            <div class="element-controls" @click.stop="removeElement(index)">
                                <i class="bi bi-x"></i>
                            </div>

                            <component-renderer :element="element" @container-drop="onContainerDrop" :on-element-click="handleElementClick" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右侧属性面板 -->
            <div class="col-md-3 properties-panel">
                <h6 class="mb-3">属性设置</h6>

                <div v-if="selectedElement">
                    <div class="mb-3">
                        <label class="form-label">组件类型</label>
                        <input type="text" class="form-control" :value="selectedElement.type" readonly>
                    </div>

                    <div class="mb-3" v-if="selectedElement.type === 'image'">
                        <label class="form-label">图片上传</label>
                        <input type="file" class="form-control" accept="image/*" @change="onImageUpload($event)">
                        <div class="form-text">支持本地上传，图片将以 Base64 内联，便于离线导出。</div>
                    </div>

                    <div v-for="(value, key) in selectedElement.props" :key="key" class="mb-3" v-if="!['style','width'].includes(key)">
                        <label class="form-label">{{ formName[key] || key }}</label>
                        <input
                            type="text"
                            class="form-control"
                            :value="value"
                            @input="updateElementProp(key, $event.target.value)"
                        >
                    </div>
                </div>

                <div v-else class="text-muted">
                    <p>选择一个组件来编辑其属性</p>
                </div>
            </div>
        </div>

    <!-- 预览模态框 -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">项目预览（H5）</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3" style="max-height:60vh;overflow:auto" v-html="previewHtml"></div>
                    <div v-if="!hasPreview" class="text-muted small mt-2">暂无内容，请在画布添加组件后再试</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 代码生成模态框 -->
    <div class="modal fade" id="codeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">生成代码</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#wechatTab">微信小程序</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#h5Tab">H5</a></li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="wechatTab">
                            <pre class="bg-light p-3 rounded" style="max-height:60vh;overflow:auto"><code v-text="wechatCode"></code></pre>
                        </div>
                        <div class="tab-pane fade" id="h5Tab">
                            <pre class="bg-light p-3 rounded" style="max-height:60vh;overflow:auto"><code v-text="h5Code"></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div> 
    <!-- 关闭 #app 容器 -->

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/builder.js"></script>
</body>
</html>
