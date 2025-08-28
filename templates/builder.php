<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拖拽构建器 - 可视化生成器</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="vendor/vue/vue.global.prod.js"></script>
    <style>
        .builder-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }

        .sidebar {
            background: #fff;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .canvas-container {
            flex: 1;
            background: #f8f9fa;
            overflow: auto;
            padding: 20px;
        }

        .canvas {
            background: #fff;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            min-height: 600px;
            padding: 20px;
            position: relative;
        }

        .canvas.drag-over {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .component-item {
            cursor: grab;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: #fff;
            transition: all 0.2s;
        }

        .component-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .component-item:active {
            cursor: grabbing;
        }

        .canvas-element {
            position: relative;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background: #fff;
            cursor: move;
        }

        .canvas-element:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.2);
        }

        .canvas-element.selected {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.3);
        }

        .element-controls {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
        }

        .properties-panel {
            background: #fff;
            border-left: 1px solid #dee2e6;
            padding: 20px;
            overflow-y: auto;
        }
    </style>
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

    </div> <!-- 关闭 #app 容器 -->

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        const { createApp } = Vue;

        // 组件渲染器 - 修复递归渲染回显问题
        const ComponentRenderer = {
            name: 'ComponentRenderer', // 明确指定组件名称以支持递归
            props: ['element', 'onElementClick'],
            template: `
                <template v-if="element.type === 'text'">
                    <div :key="element.id + '-text'"
                         :class="element.props.class || ''"
                         :style="elementStyle"
                         :data-element-id="element.id"
                         @click="handleElementClick">
                        {{ element.props.content || '文本内容' }}
                    </div>
                </template>
                <template v-else-if="element.type === 'image'">
                    <img :key="element.id + '-img'"
                         :src="element.props.src || 'images/placeholder-image.svg'"
                         :alt="element.props.alt || '请上传图片'"
                         :class="element.props.class || ''"
                         :style="elementStyle"
                         :data-element-id="element.id"
                         style="max-width: 100%; height: auto;"
                         @click="handleElementClick">
                </template>
                <template v-else-if="element.type === 'row'">
                    <div :key="element.id + '-row'"
                         class="w-100 d-flex flex-wrap p-3 border relative"
                         :class="element.props.class || 'border-info bg-white'"
                         :style="element.props.style || 'min-height: 60px; gap: 8px;'"
                         :data-element-id="element.id"
                         data-container="1"
                         @click.stop="handleElementClick"
                         @dragover.stop.prevent="onDragOver"
                         @drop.stop.prevent="onDrop">
                        <template v-if="element.children && element.children.length === 0">
                            <div class="text-muted text-sm w-100 text-center py-2">拖拽组件到此处</div>
                        </template>
                        <template v-else-if="element.children">
                            <div v-for="child in element.children" :key="child.id + '-' + child.type">
                                <component :is="ComponentRenderer"
                                           :element="child"
                                           :on-element-click="onElementClick"
                                           @container-drop="$emit('container-drop', $event)"/>
                            </div>
                        </template>
                    </div>
                </template>
                <template v-else-if="element.type === 'button'">
                    <button :key="element.id + '-btn'"
                            :class="element.props.class || 'btn btn-primary'"
                            :style="elementStyle"
                            :data-element-id="element.id"
                            @click="handleElementClick">
                        {{ element.props.text || '按钮' }}
                    </button>
                </template>
                <template v-else-if="element.type === 'div'">
                    <div :key="element.id + '-div'"
                         class="w-100 p-3 border relative"
                         :class="element.props.class || 'border-primary bg-white'"
                         :style="element.props.style || 'min-height: 60px;'"
                         :data-element-id="element.id"
                         data-container="1"
                         @click.stop="handleElementClick"
                         @dragover.stop.prevent="onDragOver"
                         @drop.stop.prevent="onDrop">
                        <template v-if="element.children && element.children.length === 0">
                            <div class="text-muted text-sm w-100 text-center py-2">拖拽组件到此处</div>
                        </template>
                        <template v-else-if="element.children">
                            <div v-for="child in element.children" :key="child.id + '-' + child.type">
                                <component :is="ComponentRenderer"
                                           :element="child"
                                           :on-element-click="onElementClick"
                                           @container-drop="$emit('container-drop', $event)"/>
                            </div>
                        </template>
                    </div>
                </template>
                <template v-else>
                    <div :key="element.id + '-unknown'"
                         :data-element-id="element.id"
                         class="p-2 border border-warning"
                         @click="handleElementClick">
                        未知组件类型: {{ element.type }}
                    </div>
                </template>
            `,
            data() {
                return {
                    // 将自身作为数据属性，以便在模板中引用
                    ComponentRenderer: ComponentRenderer
                }
            },
            computed: {
                elementStyle() {
                    const width = this.element.props.width ? `width: ${this.element.props.width};` : '';
                    const style = this.element.props.style || '';
                    return `${width}${style}`;
                }
            },
            methods: {
                handleElementClick(event) {
                    if (this.onElementClick) {
                        this.onElementClick(this.element, event);
                    }
                },
                onDragOver(e) {
                    // 允许拖拽到容器组件
                    e.dataTransfer.dropEffect = 'copy';
                },
                onDrop(e) {
                    // 阻止默认行为和冒泡
                    e.preventDefault();
                    e.stopPropagation();

                    // 确保拖拽数据能够正确传递
                    const dataTransfer = {
                        getData: function(type) {
                            return e.dataTransfer.getData(type);
                        },
                        setData: function(type, value) {
                            e.dataTransfer.setData(type, value);
                        }
                    };

                    // 触发容器拖拽事件
                    this.$emit('container-drop', { target: this.element, dataTransfer: dataTransfer, originalEvent: e });

                    console.log('[ComponentRenderer] 容器内拖放事件触发:', this.element.type);
                }
            },
            watch: {
                // 监听element属性变化，确保组件重新渲染
                'element': {
                    handler: function(newVal, oldVal) {
                        console.log('[ComponentRenderer] Element updated:', newVal.type);
                    },
                    deep: true
                }
            }
        };

        createApp({
            components: {
                ComponentRenderer
            },
            data() {
                return {
                    currentPage: {
                        name: 'index',
                        title: '首页',
                        elements: []
                    },
                    selectedElement: null,
                    basicComponents: [
                         { type: 'text', name: '文本', icon: 'bi bi-type' },
                         { type: 'image', name: '图片', icon: 'bi bi-image' },
                         { type: 'button', name: '按钮', icon: 'bi bi-box' }
                     ],
                     layoutComponents: [
                        { type: 'row', name: '行布局', icon: 'bi bi-layout-three-columns' },
                        { type: 'div', name: '容器', icon: 'bi bi-square' }
                     ],
                    wechatCode: '',
                    h5Code: '',
                    previewHtml: '',
                    hasPreview: false,
                    formName: {
                        content: '内容',
                        class: 'CSS类名',
                        src: '图片路径',
                        alt: '图片描述',
                        text: '按钮文本',
                        width: '宽度',
                        height: '高度',
                        style: '样式'
                    }
                }
            },
            methods: {
                handleElementClick(element, event) {
                    // 选择点击的元素
                    this.selectElement(element);
                    // 阻止冒泡，防止点击子元素时触发父元素的点击事件
                    if (event) {
                        event.stopPropagation();
                    }
                },
                handleDragStart(e, component) {
                    // 设置拖拽数据
                    e.dataTransfer.setData('application/json', JSON.stringify(component));
                    // 设置拖拽时的视觉效果
                    e.dataTransfer.effectAllowed = 'copy';
                },
                onDragOver(e) {
                    // 允许拖拽
                    e.preventDefault();
                    e.stopPropagation();
                    e.dataTransfer.dropEffect = 'copy';
                },
                onDragLeave(e) {
                    // 移除拖拽效果
                    e.preventDefault();
                    e.stopPropagation();
                },
                onDrop(e) {
                    try {
                        // 阻止默认行为和冒泡
                        e.preventDefault();
                        e.stopPropagation();

                        // 获取拖拽的数据
                        const componentData = JSON.parse(e.dataTransfer.getData('application/json'));

                        // 创建新元素
                        const element = {
                            id: Date.now() + Math.random(),
                            type: componentData.type,
                            props: this.getDefaultProps(componentData.type),
                            children: []
                        };

                        // 添加到根元素
                        const originalElements = this.currentPage.elements;
                        // 使用push替代展开运算符，确保响应式更新
                        originalElements.push(element);

                        // 强制重新渲染 - 使用Vue 3兼容方式
                        const originalId = this.currentPage.id;
                        this.currentPage.id = 'temp_' + Date.now();
                        setTimeout(() => {
                            this.currentPage.id = originalId;
                        }, 0);

                        // 选中新添加的元素
                        this.selectElement(element);

                        console.log('[onDrop] 添加新元素到根，类型：', element.type);
                    } catch (e) {
                        console.error('[onDrop] 错误:', e);
                    }
                },
                onContainerDrop(data) {
                    try {
                        // 获取目标容器
                        const target = data.target;
                        // 获取拖拽的数据
                        const componentData = JSON.parse(data.dataTransfer.getData('application/json'));

                        console.log('[onContainerDrop] 开始处理拖放，目标容器：', target.type);

                        // 确保目标是一个有效的容器
                        if (target && (target.type === 'div' || target.type === 'row')) {
                            // 创建新元素
                            const element = {
                                id: Date.now() + Math.random(),
                                type: componentData.type,
                                props: this.getDefaultProps(componentData.type),
                                children: []
                            };

                            // 确保children数组存在
                            if (!Array.isArray(target.children)) {
                                target.children = [];
                            }

                            // 使用push替代展开运算符，确保响应式更新
                            target.children.push(element);

                            // 强制重新渲染 - 使用Vue 3兼容方式
                            const originalId = target.id;
                            target.id = 'temp_' + Date.now();
                            setTimeout(() => {
                                target.id = originalId;
                            }, 0);

                            // 选中新添加的元素
                            this.selectElement(element);

                            console.log('[onContainerDrop] 成功添加子元素类型=%s到%s (子元素数量: %d)',
                                element.type, target.type, target.children.length);
                        } else {
                            console.warn('[onContainerDrop] 目标不是有效的容器', target);
                        }
                    } catch (e) {
                        console.error('[onContainerDrop] Error:', e.message);
                        console.error(e.stack);
                    }
                },

                addElement(componentData) {
                    const element = {
                        id: Date.now() + Math.random(),
                        type: componentData.type,
                        props: this.getDefaultProps(componentData.type),
                        children: []
                    };

                    if (this.selectedElement && (this.selectedElement.type === 'div' || this.selectedElement.type === 'row')) {
                        // 确保children数组是响应式的
                        if (!Array.isArray(this.selectedElement.children)) {
                            // Vue 3响应式更新
                            this.selectedElement.children = [element];
                        } else {
                            // Vue 3响应式更新
                            this.selectedElement.children = [...this.selectedElement.children, element];
                        }
                        console.log('[addElement] appended to selected container=%s, children=%d', this.selectedElement.type, this.selectedElement.children.length);
                    } else {
                        // 确保elements数组是响应式的
                        this.currentPage.elements = [...this.currentPage.elements, element];
                        console.log('[addElement] appended to root, total=%d', this.currentPage.elements.length);
                    }
                    this.selectElement(element);
                },
                // 递归查找元素
                findElementById(list, id) {
                    if (!list) return null;
                    for (const item of list) {
                        if (String(item.id) === String(id)) return item;
                        const inChildren = this.findElementById(item.children, id);
                        if (inChildren) return inChildren;
                    }
                    return null;
                },

                getDefaultProps(type) {
                    switch (type) {
                        case 'text':
                            return { content: '文本内容', class: '', style: 'display:inline-block;', width: '' };
                        case 'image':
                            return { src: '', alt: '图片', class: '', style: '', width: '' };
                        case 'button':
                            return { text: '按钮', class: 'btn btn-primary', style: '', width: '' };
                        case 'row':
                            return { class: '', style: 'gap:12px;', width: '' };
                        case 'div':
                            return { class: 'container', style: 'min-height: 100px;', width: '' };
                        default:
                            return { class: '', style: '' };
                    }
                },

                selectElement(element) {
                    this.selectedElement = element;
                },

                updateElementProp(key, value) {
                    if (this.selectedElement) {
                        // Vue 3响应式更新 - 确保props对象存在
                        if (!this.selectedElement.props) {
                            this.selectedElement.props = {};
                        }

                        // 直接更新属性值
                        this.selectedElement.props[key] = value;

                        // 强制重新渲染组件 - 使用Vue 3兼容方式
                        const elementKey = this.selectedElement.id;
                        setTimeout(() => {
                            this.selectedElement.id = elementKey + '_update';
                            setTimeout(() => {
                                this.selectedElement.id = elementKey;
                            }, 0);
                        }, 0);
                    }
                },
                onImageUpload(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = () => {
                        if (this.selectedElement && this.selectedElement.type === 'image') {
                            this.selectedElement.props.src = reader.result;
                        }
                    };
                    reader.readAsDataURL(file);
                },

                removeElement(index) {
                    // 确保响应式更新
                    const newElements = [...this.currentPage.elements];
                    newElements.splice(index, 1);
                    this.currentPage.elements = newElements;
                    this.selectedElement = null;
                },

                addPage() {
                    const pageName = prompt('请输入页面名称:');
                    if (pageName) {
                        this.currentPage = {
                            name: pageName.toLowerCase(),
                            title: pageName,
                            elements: []
                        };
                    }
                },

                clearCanvas() {
                    if (confirm('确定要清空画布吗？')) {
                        this.currentPage.elements = [];
                        this.selectedElement = null;
                    }
                },

                async previewProject() {
                    try {
                        const res = await fetch('/api/preview', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ type: 'h5', config: { pages: [this.currentPage] } })
                        });
                        const json = await res.json();
                        if (json.success) {
                            this.previewHtml = json.code || '';
                            this.hasPreview = /<([A-Za-z][\w\-]*)\b|\S/.test(this.previewHtml);
                            new bootstrap.Modal(document.getElementById('previewModal')).show();
                        } else {
                            alert(json.message || '预览失败');
                        }
                    } catch (e) {
                        console.error(e); alert('预览失败');
                    }
                },
                async generateCode() {
                    try {
                        const payload = { config: { pages: [this.currentPage] } };
                        const [wechatRes, h5Res] = await Promise.all([
                            fetch('/api/generate/wechat', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }),
                            fetch('/api/generate/h5', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
                        ]);
                        const wechatJson = await wechatRes.json();
                        const h5Json = await h5Res.json();
                        this.wechatCode = wechatJson.success ? JSON.stringify(wechatJson.code, null, 2) : (wechatJson.message || '生成失败');
                        this.h5Code = h5Json.success ? JSON.stringify(h5Json.code, null, 2) : (h5Json.message || '生成失败');
                        new bootstrap.Modal(document.getElementById('codeModal')).show();
                    } catch (e) {
                        console.error(e); alert('生成失败');
                    }
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
