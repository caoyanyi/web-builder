const { createApp } = Vue;

const CONTAINER_TYPES = ['div', 'row'];
const PROP_ORDER = ['content', 'text', 'src', 'alt', 'class', 'width', 'style'];

function createId(prefix = 'node') {
    return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
}

function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function isContainerType(type) {
    return CONTAINER_TYPES.includes(type);
}

const ComponentRenderer = {
    name: 'ComponentRenderer',
    props: {
        element: {
            type: Object,
            required: true
        },
        selectedElementId: {
            type: [String, Number],
            default: null
        }
    },
    emits: ['select-element', 'remove-element', 'container-drop'],
    computed: {
        isContainer() {
            return isContainerType(this.element.type);
        },
        hasChildren() {
            return Array.isArray(this.element.children) && this.element.children.length > 0;
        },
        wrapperClasses() {
            return [
                'builder-node',
                `builder-node-${this.element.type}`,
                {
                    'is-selected': String(this.selectedElementId) === String(this.element.id)
                }
            ];
        },
        typeLabel() {
            const labels = {
                text: '文本',
                image: '图片',
                button: '按钮',
                row: '行布局',
                div: '容器'
            };

            return labels[this.element.type] || this.element.type;
        },
        elementStyle() {
            const width = this.element.props && this.element.props.width
                ? `width: ${this.element.props.width};`
                : '';
            const style = (this.element.props && this.element.props.style) || '';

            return `${width}${style}`;
        }
    },
    methods: {
        selectElement() {
            this.$emit('select-element', this.element.id);
        },
        removeElement() {
            this.$emit('remove-element', this.element.id);
        },
        onDragOver(event) {
            event.dataTransfer.dropEffect = 'copy';
        },
        onDrop(event) {
            const componentType = event.dataTransfer.getData('text/component-type');

            if (!componentType) {
                return;
            }

            this.$emit('container-drop', {
                targetId: this.element.id,
                componentType
            });
        }
    },
    template: `
        <div :class="wrapperClasses" @click.stop="selectElement">
            <div class="builder-node-toolbar">
                <span class="builder-node-badge">{{ typeLabel }}</span>
                <button type="button" class="builder-node-remove" title="删除组件" @click.stop="removeElement">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

            <div
                v-if="isContainer"
                :class="['builder-render-surface', element.type === 'row' ? 'builder-render-row' : 'builder-render-stack']"
                :style="elementStyle"
                @click.stop="selectElement"
                @dragover.stop.prevent="onDragOver"
                @drop.stop.prevent="onDrop"
            >
                <div v-if="!hasChildren" class="builder-drop-hint">
                    拖拽组件到这个{{ typeLabel }}
                </div>

                <component-renderer
                    v-for="child in element.children"
                    :key="child.id"
                    :element="child"
                    :selected-element-id="selectedElementId"
                    @select-element="$emit('select-element', $event)"
                    @remove-element="$emit('remove-element', $event)"
                    @container-drop="$emit('container-drop', $event)"
                />
            </div>

            <div
                v-else-if="element.type === 'text'"
                :class="['builder-render-text', element.props.class || '']"
                :style="elementStyle"
            >
                {{ element.props.content || '文本内容' }}
            </div>

            <img
                v-else-if="element.type === 'image'"
                :class="['builder-render-image', element.props.class || '']"
                :src="element.props.src || 'images/placeholder-image.svg'"
                :alt="element.props.alt || '图片'"
                :style="elementStyle"
            >

            <button
                v-else-if="element.type === 'button'"
                type="button"
                :class="element.props.class || 'btn btn-primary'"
                :style="elementStyle"
                @click.stop="selectElement"
            >
                {{ element.props.text || '按钮' }}
            </button>

            <div v-else class="builder-render-text text-muted">
                未知组件类型: {{ element.type }}
            </div>
        </div>
    `
};

ComponentRenderer.components = {
    ComponentRenderer
};

createApp({
    components: {
        ComponentRenderer
    },
    data() {
        const firstPageId = createId('page');

        return {
            pages: [
                {
                    id: firstPageId,
                    name: 'index',
                    title: '首页',
                    elements: []
                }
            ],
            currentPageId: firstPageId,
            selectedElementId: null,
            newPageTitle: '',
            isCanvasDragOver: false,
            isPreviewLoading: false,
            isGenerating: false,
            wechatCode: '',
            h5Code: '',
            previewHtml: '',
            hasPreview: false,
            statusMessage: '',
            statusVariant: 'info',
            statusTimer: null,
            basicComponents: [
                { type: 'text', name: '文本', icon: 'bi bi-type', description: '适合标题、段落和说明文案。' },
                { type: 'image', name: '图片', icon: 'bi bi-image', description: '支持本地上传图片并直接预览。' },
                { type: 'button', name: '按钮', icon: 'bi bi-hand-index-thumb', description: '用于按钮区、操作入口和 CTA。' }
            ],
            layoutComponents: [
                { type: 'row', name: '行布局', icon: 'bi bi-layout-three-columns', description: '横向排列多个组件，适合卡片组。' },
                { type: 'div', name: '容器', icon: 'bi bi-square', description: '纵向包裹内容，适合做内容模块。' }
            ],
            elementLabels: {
                text: '文本',
                image: '图片',
                button: '按钮',
                row: '行布局',
                div: '容器'
            },
            formName: {
                content: '内容',
                class: 'CSS 类名',
                src: '图片地址',
                alt: '图片描述',
                text: '按钮文案',
                width: '宽度',
                style: '内联样式'
            },
            propInputTypes: {
                src: 'text',
                alt: 'text',
                width: 'text',
                text: 'text',
                content: 'text',
                class: 'text',
                style: 'text'
            }
        };
    },
    computed: {
        currentPage() {
            return this.pages.find((page) => page.id === this.currentPageId) || this.pages[0];
        },
        selectedElement() {
            if (!this.selectedElementId || !this.currentPage) {
                return null;
            }

            return this.findElementById(this.currentPage.elements, this.selectedElementId);
        },
        editablePropFields() {
            if (!this.selectedElement || !this.selectedElement.props) {
                return [];
            }

            const keys = Object.keys(this.selectedElement.props);
            const orderedKeys = [
                ...PROP_ORDER.filter((key) => keys.includes(key)),
                ...keys.filter((key) => !PROP_ORDER.includes(key))
            ];

            return orderedKeys.map((key) => ({
                key,
                label: this.formName[key] || key,
                control: key === 'style' || key === 'content' ? 'textarea' : 'input',
                type: this.propInputTypes[key] || 'text'
            }));
        }
    },
    methods: {
        setStatus(message, variant = 'info') {
            this.statusMessage = message;
            this.statusVariant = variant;

            if (this.statusTimer) {
                window.clearTimeout(this.statusTimer);
            }

            this.statusTimer = window.setTimeout(() => {
                this.statusMessage = '';
            }, 3200);
        },
        handleDragStart(event, component) {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/component-type', component.type);
        },
        onCanvasDragOver(event) {
            event.preventDefault();
            this.isCanvasDragOver = true;
            event.dataTransfer.dropEffect = 'copy';
        },
        onCanvasDragLeave() {
            this.isCanvasDragOver = false;
        },
        onRootDrop(event) {
            event.preventDefault();
            this.isCanvasDragOver = false;

            const componentType = event.dataTransfer.getData('text/component-type');

            if (!componentType) {
                return;
            }

            this.insertElement(componentType);
        },
        onContainerDrop({ targetId, componentType }) {
            this.insertElement(componentType, targetId);
        },
        clearSelection() {
            this.selectedElementId = null;
        },
        createElement(type) {
            return {
                id: createId('el'),
                type,
                props: this.getDefaultProps(type),
                children: []
            };
        },
        insertElement(componentType, targetId = null) {
            const newElement = this.createElement(componentType);

            if (targetId) {
                const target = this.findElementById(this.currentPage.elements, targetId);

                if (!target || !isContainerType(target.type)) {
                    this.setStatus('只能将组件拖放到容器或行布局中。', 'danger');
                    return;
                }

                if (!Array.isArray(target.children)) {
                    target.children = [];
                }

                target.children.push(newElement);
            } else {
                this.currentPage.elements.push(newElement);
            }

            this.selectedElementId = newElement.id;
            this.setStatus(`已添加${this.elementLabels[componentType] || componentType}组件。`, 'success');
        },
        findElementById(elements, id) {
            for (const element of elements || []) {
                if (String(element.id) === String(id)) {
                    return element;
                }

                const found = this.findElementById(element.children || [], id);
                if (found) {
                    return found;
                }
            }

            return null;
        },
        findElementLocation(elements, id, parent = null) {
            for (let index = 0; index < (elements || []).length; index += 1) {
                const element = elements[index];

                if (String(element.id) === String(id)) {
                    return {
                        list: elements,
                        index,
                        element,
                        parent
                    };
                }

                const found = this.findElementLocation(element.children || [], id, element);
                if (found) {
                    return found;
                }
            }

            return null;
        },
        elementContainsId(element, id) {
            if (!element) {
                return false;
            }

            if (String(element.id) === String(id)) {
                return true;
            }

            return (element.children || []).some((child) => this.elementContainsId(child, id));
        },
        getDefaultProps(type) {
            switch (type) {
                case 'text':
                    return {
                        content: '双击左侧组件后拖到画布，点击这里即可编辑文案。',
                        class: '',
                        width: '',
                        style: 'font-size: 16px; line-height: 1.7;'
                    };
                case 'image':
                    return {
                        src: '',
                        alt: '图片',
                        class: '',
                        width: '100%',
                        style: 'max-width: 320px;'
                    };
                case 'button':
                    return {
                        text: '立即操作',
                        class: 'btn btn-primary',
                        width: '',
                        style: ''
                    };
                case 'row':
                    return {
                        class: '',
                        width: '',
                        style: 'gap: 16px; align-items: flex-start;'
                    };
                case 'div':
                    return {
                        class: '',
                        width: '',
                        style: 'padding: 16px;'
                    };
                default:
                    return {
                        class: '',
                        width: '',
                        style: ''
                    };
            }
        },
        selectElement(elementId) {
            this.selectedElementId = elementId;
        },
        updateElementProp(key, value) {
            if (!this.selectedElement) {
                return;
            }

            this.selectedElement.props = {
                ...this.selectedElement.props,
                [key]: value
            };
        },
        onImageUpload(event) {
            const file = event.target.files && event.target.files[0];

            if (!file || !this.selectedElement || this.selectedElement.type !== 'image') {
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                this.selectedElement.props = {
                    ...this.selectedElement.props,
                    src: reader.result
                };
                this.setStatus('图片已更新。', 'success');
            };
            reader.readAsDataURL(file);
        },
        removeElement(elementId) {
            const location = this.findElementLocation(this.currentPage.elements, elementId);

            if (!location) {
                return;
            }

            location.list.splice(location.index, 1);

            if (
                this.selectedElementId &&
                this.elementContainsId(location.element, this.selectedElementId)
            ) {
                this.selectedElementId = location.parent ? location.parent.id : null;
            }

            this.setStatus('组件已删除。', 'warning');
        },
        addPage() {
            const title = this.newPageTitle || `页面 ${this.pages.length + 1}`;
            const page = {
                id: createId('page'),
                title,
                name: this.createUniquePageName(title, this.pages.length + 1),
                elements: []
            };

            this.pages.push(page);
            this.currentPageId = page.id;
            this.selectedElementId = null;
            this.newPageTitle = '';
            this.setStatus(`已创建页面“${page.title}”。`, 'success');
        },
        switchPage(pageId) {
            this.currentPageId = pageId;
            this.selectedElementId = null;
        },
        deleteCurrentPage() {
            if (this.pages.length === 1) {
                this.currentPage.elements = [];
                this.selectedElementId = null;
                this.setStatus('最后一个页面不能删除，已为你清空当前页面。', 'warning');
                return;
            }

            const currentIndex = this.pages.findIndex((page) => page.id === this.currentPageId);
            const currentTitle = this.currentPage.title;

            this.pages.splice(currentIndex, 1);

            const nextPage = this.pages[Math.max(0, currentIndex - 1)] || this.pages[0];
            this.currentPageId = nextPage.id;
            this.selectedElementId = null;
            this.setStatus(`已删除页面“${currentTitle}”。`, 'warning');
        },
        clearCanvas() {
            if (this.currentPage.elements.length === 0) {
                this.setStatus('当前页面已经是空白状态。', 'info');
                return;
            }

            this.currentPage.elements = [];
            this.selectedElementId = null;
            this.setStatus('当前页面内容已清空。', 'warning');
        },
        normalizePageName(value, fallbackIndex) {
            const normalized = String(value || '')
                .trim()
                .toLowerCase()
                .replace(/[^\w-]+/g, '-')
                .replace(/^-+|-+$/g, '');

            return normalized || `page-${fallbackIndex}`;
        },
        createUniquePageName(value, fallbackIndex, usedNames = null) {
            const names = usedNames || new Set(this.pages.map((page) => page.name));
            const baseName = this.normalizePageName(value, fallbackIndex);
            let nextName = baseName;
            let suffix = 2;

            while (names.has(nextName)) {
                nextName = `${baseName}-${suffix}`;
                suffix += 1;
            }

            names.add(nextName);
            return nextName;
        },
        normalizeCurrentPageName() {
            const usedNames = new Set(
                this.pages
                    .filter((page) => page.id !== this.currentPage.id)
                    .map((page) => page.name)
            );

            this.currentPage.name = this.createUniquePageName(
                this.currentPage.name || this.currentPage.title,
                this.pages.findIndex((page) => page.id === this.currentPage.id) + 1,
                usedNames
            );
        },
        buildProjectConfig() {
            const usedNames = new Set();

            return {
                title: this.pages[0] && this.pages[0].title ? this.pages[0].title : '可视化拖拽项目',
                pages: this.pages.map((page, index) => ({
                    name: this.createUniquePageName(page.name || page.title, index + 1, usedNames),
                    title: page.title || `页面 ${index + 1}`,
                    elements: deepClone(page.elements)
                }))
            };
        },
        async requestJson(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const json = await response.json();

            if (!response.ok || !json.success) {
                throw new Error(json.message || '请求失败');
            }

            return json;
        },
        async previewProject() {
            this.isPreviewLoading = true;

            try {
                const json = await this.requestJson('/api/preview', {
                    type: 'h5',
                    config: this.buildProjectConfig()
                });

                this.previewHtml = json.code || '';
                this.hasPreview = /<([A-Za-z][\w-]*)\b|\S/.test(this.previewHtml);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('previewModal')).show();
                this.setStatus('预览内容已更新。', 'success');
            } catch (error) {
                this.setStatus(error.message || '预览失败。', 'danger');
            } finally {
                this.isPreviewLoading = false;
            }
        },
        async generateCode() {
            this.isGenerating = true;

            try {
                const payload = {
                    config: this.buildProjectConfig()
                };
                const [wechatJson, h5Json] = await Promise.all([
                    this.requestJson('/api/generate/wechat', payload),
                    this.requestJson('/api/generate/h5', payload)
                ]);

                this.wechatCode = JSON.stringify(wechatJson.code, null, 2);
                this.h5Code = JSON.stringify(h5Json.code, null, 2);

                bootstrap.Modal.getOrCreateInstance(document.getElementById('codeModal')).show();
                this.setStatus('代码生成完成。', 'success');
            } catch (error) {
                this.setStatus(error.message || '生成代码失败。', 'danger');
            } finally {
                this.isGenerating = false;
            }
        }
    },
    beforeUnmount() {
        if (this.statusTimer) {
            window.clearTimeout(this.statusTimer);
        }
    }
}).mount('#app');
