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
                            @dragstart="onDragStart($event, component)"
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
                            @dragstart="onDragStart($event, component)"
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
                             @click="selectElement(element)"
                             draggable="true">
                            
                            <div class="element-controls" @click.stop="removeElement(index)">
                                <i class="bi bi-x"></i>
                            </div>
                            
                            <component-renderer :element="element"></component-renderer>
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
                    
                    <div v-for="(value, key) in selectedElement.props" :key="key" class="mb-3">
                        <label class="form-label">{{ key }}</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            :value="value"
                            @input="updateElementProp(key, $event.target.value)"
                        >
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">样式</label>
                        <textarea 
                            class="form-control" 
                            rows="3"
                            :value="selectedElement.props.style || ''"
                            @input="updateElementProp('style', $event.target.value)"
                            placeholder="CSS样式，如: color: red; font-size: 16px;"
                        ></textarea>
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

        // 组件渲染器
        const ComponentRenderer = {
            props: ['element'],
            template: `
                <div :class="element.props.class || ''" :style="element.props.style || ''">
                    <template v-if="element.type === 'text'">
                        {{ element.props.content || '文本内容' }}
                    </template>
                    <template v-else-if="element.type === 'image'">
                        <img :src="element.props.src || 'https://via.placeholder.com/200x150'" 
                             :alt="element.props.alt || '图片'" 
                             style="max-width: 100%; height: auto;">
                    </template>
                    <template v-else-if="element.type === 'button'">
                        <button class="btn btn-primary">{{ element.props.text || '按钮' }}</button>
                    </template>
                    <template v-else-if="element.type === 'div'">
                        <div class="p-3 border border-dashed text-muted text-center">
                            容器组件
                        </div>
                    </template>
                </div>
            `
        };

        createApp({
            components: {
                ComponentRenderer
            },
            data() {
                return {
                    currentPage: {
                        name: 'home',
                        title: '首页',
                        elements: [
                            { id: Date.now(), type: 'text', props: { content: '欢迎使用可视化生成器', class: '', style: 'font-size:16px;color:#333;' }, children: [] }
                        ]
                    },
                    selectedElement: null,
                    basicComponents: [
                        { type: 'text', name: '文本', icon: 'bi bi-type' },
                        { type: 'image', name: '图片', icon: 'bi bi-image' },
                        { type: 'button', name: '按钮', icon: 'bi bi-box' }
                    ],
                    layoutComponents: [
                        { type: 'div', name: '容器', icon: 'bi bi-square' }
                    ],
                    previewHtml: '',
                    wechatCode: '',
                    h5Code: '',
                    hasPreview: false
                };
            },
            methods: {
                onDragStart(event, component) {
                    event.dataTransfer.setData('text/plain', JSON.stringify(component));
                },
                
                onDragOver(event) {
                    event.preventDefault();
                    event.currentTarget.classList.add('drag-over');
                },
                
                onDragLeave(event) {
                    event.currentTarget.classList.remove('drag-over');
                },
                
                onDrop(event) {
                    event.preventDefault();
                    event.currentTarget.classList.remove('drag-over');
                    
                    const componentData = JSON.parse(event.dataTransfer.getData('text/plain'));
                    this.addElement(componentData);
                },
                
                addElement(componentData) {
                    const element = {
                        id: Date.now() + Math.random(),
                        type: componentData.type,
                        props: this.getDefaultProps(componentData.type),
                        children: []
                    };
                    
                    this.currentPage.elements.push(element);
                    this.selectElement(element);
                },
                
                getDefaultProps(type) {
                    switch (type) {
                        case 'text':
                            return { content: '文本内容', class: '', style: '' };
                        case 'image':
                            return { src: 'https://via.placeholder.com/200x150', alt: '图片', class: '', style: '' };
                        case 'button':
                            return { text: '按钮', class: 'btn btn-primary', style: '' };
                        case 'div':
                            return { class: 'container', style: 'min-height: 100px;' };
                        default:
                            return { class: '', style: '' };
                    }
                },
                
                selectElement(element) {
                    this.selectedElement = element;
                },
                
                updateElementProp(key, value) {
                    if (this.selectedElement) {
                        this.selectedElement.props[key] = value;
                    }
                },
                
                removeElement(index) {
                    this.currentPage.elements.splice(index, 1);
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
