const { createApp } = Vue;

const CONTAINER_TYPES = ['div', 'row'];
const PROP_ORDER = ['content', 'text', 'label', 'required', 'placeholder', 'value', 'rows', 'height', 'src', 'alt', 'class', 'width', 'style'];
const HISTORY_LIMIT = 60;
const DRAG_KIND_COMPONENT = 'component';
const DRAG_KIND_ELEMENT = 'existing-element';

function createId(prefix = 'node') {
    return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
}

function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function isContainerType(type) {
    return CONTAINER_TYPES.includes(type);
}

function downloadTextFile(filename, content) {
    const blob = new Blob([content], { type: 'application/json;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
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
        },
        dropState: {
            type: Object,
            default: null
        }
    },
    emits: ['select-element', 'remove-element', 'duplicate-element', 'move-element', 'insert-drop', 'container-drop', 'preview-drop-target'],
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
                    'is-selected': String(this.selectedElementId) === String(this.element.id),
                    'drop-before': this.dropState && this.dropState.mode === 'before',
                    'drop-after': this.dropState && this.dropState.mode === 'after',
                    'drop-inside': this.dropState && this.dropState.mode === 'inside'
                }
            ];
        },
        typeLabel() {
            const labels = {
                text: '文本',
                image: '图片',
                button: '按钮',
                input: '输入框',
                textarea: '文本域',
                spacer: '间距块',
                row: '行布局',
                div: '容器'
            };

            return labels[this.element.type] || this.element.type;
        },
        elementStyle() {
            const props = this.element.props || {};
            const width = props.width ? `width: ${props.width};` : '';
            const height = this.element.type === 'spacer' && props.height ? `height: ${props.height};` : '';
            const style = props.style || '';

            return `${width}${height}${style}`;
        }
    },
    methods: {
        selectElement() {
            this.$emit('select-element', this.element.id);
        },
        removeElement() {
            this.$emit('remove-element', this.element.id);
        },
        duplicateElement() {
            this.$emit('duplicate-element', this.element.id);
        },
        moveElement(offset) {
            this.$emit('move-element', {
                elementId: this.element.id,
                offset
            });
        },
        onDragStart(event) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/drag-kind', DRAG_KIND_ELEMENT);
            event.dataTransfer.setData('text/element-id', String(this.element.id));
        },
        onDragOverWrapper(event) {
            const dragKind = event.dataTransfer.getData('text/drag-kind');

            if (!dragKind) {
                return;
            }

            event.dataTransfer.dropEffect = dragKind === DRAG_KIND_ELEMENT ? 'move' : 'copy';

            const rect = event.currentTarget.getBoundingClientRect();
            const placement = (event.clientY - rect.top) < rect.height / 2 ? 'before' : 'after';
            this.$emit('preview-drop-target', {
                targetId: this.element.id,
                mode: placement
            });
        },
        onDropInsert(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const placement = (event.clientY - rect.top) < rect.height / 2 ? 'before' : 'after';

            this.$emit('insert-drop', {
                targetId: this.element.id,
                placement,
                originalEvent: event
            });
        },
        onDragOverContainer(event) {
            const dragKind = event.dataTransfer.getData('text/drag-kind');

            if (!dragKind) {
                return;
            }

            event.dataTransfer.dropEffect = dragKind === DRAG_KIND_ELEMENT ? 'move' : 'copy';
            this.$emit('preview-drop-target', {
                targetId: this.element.id,
                mode: 'inside'
            });
        },
        onDropContainer(event) {
            this.$emit('container-drop', {
                targetId: this.element.id,
                originalEvent: event
            });
        }
    },
    template: `
        <div
            :class="wrapperClasses"
            draggable="true"
            @click.stop="selectElement"
            @dragstart="onDragStart"
            @dragover.stop.prevent="onDragOverWrapper"
            @drop.stop.prevent="onDropInsert"
        >
            <div class="builder-node-toolbar">
                <span class="builder-node-badge">{{ typeLabel }}</span>

                <div class="builder-node-actions">
                    <button type="button" class="builder-node-icon" title="上移" @click.stop="moveElement(-1)">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button type="button" class="builder-node-icon" title="下移" @click.stop="moveElement(1)">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <button type="button" class="builder-node-icon" title="复制组件" @click.stop="duplicateElement">
                        <i class="bi bi-copy"></i>
                    </button>
                    <button type="button" class="builder-node-icon builder-node-remove" title="删除组件" @click.stop="removeElement">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>

            <div
                v-if="isContainer"
                :class="[
                    'builder-render-surface',
                    element.type === 'row' ? 'builder-render-row' : 'builder-render-stack',
                    { 'is-drop-inside': dropState && dropState.mode === 'inside' }
                ]"
                :style="elementStyle"
                @click.stop="selectElement"
                @dragover.stop.prevent="onDragOverContainer"
                @drop.stop.prevent="onDropContainer"
            >
                <div v-if="!hasChildren" class="builder-drop-hint">
                    拖拽组件到这个{{ typeLabel }}
                </div>

                <component-renderer
                    v-for="child in element.children"
                    :key="child.id"
                    :element="child"
                    :selected-element-id="selectedElementId"
                    :drop-state="dropState && String(dropState.targetId) === String(child.id) ? dropState : null"
                    @select-element="$emit('select-element', $event)"
                    @remove-element="$emit('remove-element', $event)"
                    @duplicate-element="$emit('duplicate-element', $event)"
                    @move-element="$emit('move-element', $event)"
                    @insert-drop="$emit('insert-drop', $event)"
                    @container-drop="$emit('container-drop', $event)"
                    @preview-drop-target="$emit('preview-drop-target', $event)"
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

            <div v-else-if="element.type === 'input'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <input
                    type="text"
                    :class="['builder-render-field', element.props.class || 'form-control']"
                    :placeholder="element.props.placeholder || '请输入内容'"
                    :value="element.props.value || ''"
                    readonly
                >
            </div>

            <div v-else-if="element.type === 'textarea'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <textarea
                    :class="['builder-render-field', 'builder-render-textarea', element.props.class || 'form-control']"
                    :rows="Number(element.props.rows || 4)"
                    :placeholder="element.props.placeholder || '请输入多行内容'"
                    readonly
                >{{ element.props.value || '' }}</textarea>
            </div>

            <div
                v-else-if="element.type === 'spacer'"
                class="builder-render-spacer"
                :style="elementStyle"
            >
                间距 {{ element.props.height || '32px' }}
            </div>

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
            projectId: null,
            projectName: '未命名项目',
            projectType: 'h5',
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
            savedProjects: [],
            historyStack: [],
            historyIndex: -1,
            historyTimer: null,
            isApplyingHistory: false,
            viewportMode: 'desktop',
            activeDropTarget: null,
            isCanvasDragOver: false,
            isPreviewLoading: false,
            isGenerating: false,
            isSavingProject: false,
            isProjectLoading: false,
            isProjectListLoading: false,
            isExportingH5: false,
            isExportingWechat: false,
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
            formComponents: [
                { type: 'input', name: '输入框', icon: 'bi bi-input-cursor-text', description: '适合表单、搜索区和基础信息收集。' },
                { type: 'textarea', name: '文本域', icon: 'bi bi-textarea-resize', description: '适合意见反馈、备注和长文本录入。' },
                { type: 'spacer', name: '间距块', icon: 'bi bi-arrows-expand-vertical', description: '快速拉开区块间距，调节页面节奏。' }
            ],
            layoutComponents: [
                { type: 'row', name: '行布局', icon: 'bi bi-layout-three-columns', description: '横向排列多个组件，适合卡片组。' },
                { type: 'div', name: '容器', icon: 'bi bi-square', description: '纵向包裹内容，适合做内容模块。' }
            ],
            elementLabels: {
                text: '文本',
                image: '图片',
                button: '按钮',
                input: '输入框',
                textarea: '文本域',
                spacer: '间距块',
                row: '行布局',
                div: '容器'
            },
            formName: {
                content: '内容',
                class: 'CSS 类名',
                src: '图片地址',
                alt: '图片描述',
                text: '按钮文案',
                label: '字段标签',
                required: '必填',
                placeholder: '占位提示',
                value: '默认值',
                rows: '可视行数',
                width: '宽度',
                height: '高度',
                style: '内联样式'
            },
            propInputTypes: {
                src: 'text',
                alt: 'text',
                width: 'text',
                height: 'text',
                text: 'text',
                label: 'text',
                required: 'checkbox',
                placeholder: 'text',
                value: 'text',
                rows: 'number',
                content: 'text',
                class: 'text',
                style: 'text'
            }
        };
    },
    computed: {
        safePages() {
            return Array.isArray(this.pages) ? this.pages : [];
        },
        currentPage() {
            const page = this.safePages.find((item) => item.id === this.currentPageId) || this.safePages[0];

            if (!page) {
                return {
                    id: null,
                    name: 'index',
                    title: '首页',
                    elements: []
                };
            }

            if (!Array.isArray(page.elements)) {
                page.elements = [];
            }

            return page;
        },
        currentPageElements() {
            return Array.isArray(this.currentPage.elements) ? this.currentPage.elements : [];
        },
        safeSavedProjects() {
            return Array.isArray(this.savedProjects) ? this.savedProjects : [];
        },
        pageCount() {
            return this.safePages.length;
        },
        currentElementCount() {
            return this.currentPageElements.length;
        },
        selectedElement() {
            if (!this.selectedElementId || !this.currentPage) {
                return null;
            }

            return this.findElementById(this.currentPageElements, this.selectedElementId);
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
            const textareaKeys = new Set(['style']);
            const checkboxKeys = new Set(['required']);

            if (this.selectedElement.type === 'text') {
                textareaKeys.add('content');
            }

            if (this.selectedElement.type === 'textarea') {
                textareaKeys.add('value');
            }

            return orderedKeys.map((key) => ({
                key,
                label: this.formName[key] || key,
                control: checkboxKeys.has(key) ? 'checkbox' : (textareaKeys.has(key) ? 'textarea' : 'input'),
                type: this.propInputTypes[key] || 'text'
            }));
        },
        selectedElementType() {
            return this.selectedElement ? this.selectedElement.type : '';
        },
        canUndo() {
            return this.historyIndex > 0;
        },
        canRedo() {
            return this.historyIndex >= 0 && this.historyIndex < this.historyStack.length - 1;
        }
    },
    mounted() {
        this.fetchProjects(true);
        this.resetHistory();
        window.addEventListener('keydown', this.handleKeydown);
        window.addEventListener('dragend', this.clearDropTarget);
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
        setViewportMode(mode) {
            this.viewportMode = mode;
        },
        setDropTarget(payload) {
            this.activeDropTarget = payload;
            if (!payload || payload.mode !== 'root') {
                this.isCanvasDragOver = false;
            }
        },
        clearDropTarget() {
            this.activeDropTarget = null;
            this.isCanvasDragOver = false;
        },
        getDropStateForElement(elementId) {
            if (!this.activeDropTarget) {
                return null;
            }

            return String(this.activeDropTarget.targetId) === String(elementId)
                ? this.activeDropTarget
                : null;
        },
        applySelectedProps(partialProps) {
            if (!this.selectedElement) {
                return;
            }

            this.selectedElement.props = {
                ...this.selectedElement.props,
                ...partialProps
            };
            this.captureHistory();
        },
        applyButtonPreset(className) {
            this.applySelectedProps({ class: className });
        },
        applyFieldPreset(className) {
            this.applySelectedProps({ class: className });
        },
        applyWidthPreset(width) {
            this.applySelectedProps({ width });
        },
        applyImageStylePreset(style) {
            this.applySelectedProps({ style });
        },
        applySpacerPreset(height) {
            this.applySelectedProps({ height });
        },
        applyContainerPreset(style) {
            this.applySelectedProps({ style });
        },
        applyRowPreset(style) {
            this.applySelectedProps({ style });
        },
        createBlankPage() {
            return {
                id: createId('page'),
                name: 'index',
                title: '首页',
                elements: []
            };
        },
        normalizeElement(element) {
            const normalized = {
                id: element.id || createId('el'),
                type: element.type,
                props: element.props && typeof element.props === 'object' ? { ...element.props } : {},
                children: []
            };

            normalized.children = Array.isArray(element.children)
                ? element.children.map((child) => this.normalizeElement(child))
                : [];

            return normalized;
        },
        normalizePage(page, index) {
            return {
                id: page.id || createId('page'),
                name: this.normalizePageName(page.name || page.title, index + 1),
                title: page.title || `页面 ${index + 1}`,
                elements: Array.isArray(page.elements)
                    ? page.elements.map((element) => this.normalizeElement(element))
                    : []
            };
        },
        hydratePages(pages) {
            if (!Array.isArray(pages) || pages.length === 0) {
                return [this.createBlankPage()];
            }

            return pages.map((page, index) => this.normalizePage(page, index));
        },
        snapshotState() {
            return {
                projectId: this.projectId,
                projectName: this.projectName,
                projectType: this.projectType,
                pages: deepClone(this.pages),
                currentPageId: this.currentPageId,
                selectedElementId: this.selectedElementId
            };
        },
        getSnapshotSignature(snapshot) {
            return JSON.stringify(snapshot);
        },
        resetHistory() {
            if (this.historyTimer) {
                window.clearTimeout(this.historyTimer);
                this.historyTimer = null;
            }

            const snapshot = this.snapshotState();
            this.historyStack = [snapshot];
            this.historyIndex = 0;
        },
        captureHistory() {
            if (this.isApplyingHistory) {
                return;
            }

            const snapshot = this.snapshotState();
            const signature = this.getSnapshotSignature(snapshot);
            const currentSnapshot = this.historyStack[this.historyIndex];

            if (currentSnapshot && this.getSnapshotSignature(currentSnapshot) === signature) {
                return;
            }

            const nextStack = this.historyStack.slice(0, this.historyIndex + 1);
            nextStack.push(snapshot);

            if (nextStack.length > HISTORY_LIMIT) {
                nextStack.shift();
            }

            this.historyStack = nextStack;
            this.historyIndex = this.historyStack.length - 1;
        },
        queueHistoryCapture() {
            if (this.isApplyingHistory) {
                return;
            }

            if (this.historyTimer) {
                window.clearTimeout(this.historyTimer);
            }

            this.historyTimer = window.setTimeout(() => {
                this.historyTimer = null;
                this.captureHistory();
            }, 350);
        },
        flushHistoryCapture() {
            if (!this.historyTimer) {
                return;
            }

            window.clearTimeout(this.historyTimer);
            this.historyTimer = null;
            this.captureHistory();
        },
        applyHistorySnapshot(snapshot) {
            this.isApplyingHistory = true;
            this.projectId = snapshot.projectId || null;
            this.projectName = snapshot.projectName || '未命名项目';
            this.projectType = snapshot.projectType || 'h5';
            this.pages = this.hydratePages(snapshot.pages || []);
            this.currentPageId = this.pages.some((page) => page.id === snapshot.currentPageId)
                ? snapshot.currentPageId
                : this.pages[0].id;
            this.selectedElementId = snapshot.selectedElementId || null;
            this.isApplyingHistory = false;
        },
        undoHistory() {
            this.flushHistoryCapture();

            if (!this.canUndo) {
                return;
            }

            this.historyIndex -= 1;
            this.applyHistorySnapshot(this.historyStack[this.historyIndex]);
            this.setStatus('已撤销上一步操作。', 'info');
        },
        redoHistory() {
            this.flushHistoryCapture();

            if (!this.canRedo) {
                return;
            }

            this.historyIndex += 1;
            this.applyHistorySnapshot(this.historyStack[this.historyIndex]);
            this.setStatus('已恢复下一步操作。', 'info');
        },
        handleKeydown(event) {
            if (!(event.ctrlKey || event.metaKey)) {
                return;
            }

            const key = String(event.key || '').toLowerCase();

            if (key === 'z' && !event.shiftKey) {
                event.preventDefault();
                this.undoHistory();
            }

            if (key === 'y' || (key === 'z' && event.shiftKey)) {
                event.preventDefault();
                this.redoHistory();
            }
        },
        getDragPayload(event) {
            const dragKind = event.dataTransfer.getData('text/drag-kind');

            if (dragKind === DRAG_KIND_COMPONENT) {
                return {
                    kind: DRAG_KIND_COMPONENT,
                    componentType: event.dataTransfer.getData('text/component-type')
                };
            }

            if (dragKind === DRAG_KIND_ELEMENT) {
                return {
                    kind: DRAG_KIND_ELEMENT,
                    elementId: event.dataTransfer.getData('text/element-id')
                };
            }

            return null;
        },
        handleDragStart(event, component) {
            event.dataTransfer.effectAllowed = 'copy';
            event.dataTransfer.setData('text/drag-kind', DRAG_KIND_COMPONENT);
            event.dataTransfer.setData('text/component-type', component.type);
        },
        onCanvasDragOver(event) {
            event.preventDefault();
            this.isCanvasDragOver = true;
            this.setDropTarget({
                targetId: 'root',
                mode: 'root'
            });

            const dragPayload = this.getDragPayload(event);

            if (dragPayload) {
                event.dataTransfer.dropEffect = dragPayload.kind === DRAG_KIND_ELEMENT ? 'move' : 'copy';
            }
        },
        onCanvasDragLeave() {
            this.isCanvasDragOver = false;
            this.clearDropTarget();
        },
        onRootDrop(event) {
            event.preventDefault();
            this.isCanvasDragOver = false;
            this.clearDropTarget();
            const dragPayload = this.getDragPayload(event);

            if (!dragPayload) {
                return;
            }

            this.handleDropAction(dragPayload, {
                mode: 'append-root'
            });
        },
        onContainerDrop({ targetId, originalEvent }) {
            this.clearDropTarget();
            const dragPayload = this.getDragPayload(originalEvent);

            if (!dragPayload) {
                return;
            }

            this.handleDropAction(dragPayload, {
                mode: 'append-container',
                targetId
            });
        },
        onInsertDrop({ targetId, placement, originalEvent }) {
            this.clearDropTarget();
            const dragPayload = this.getDragPayload(originalEvent);

            if (!dragPayload) {
                return;
            }

            this.handleDropAction(dragPayload, {
                mode: 'insert-relative',
                targetId,
                placement
            });
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
        cloneElementWithNewIds(element) {
            return {
                id: createId('el'),
                type: element.type,
                props: deepClone(element.props || {}),
                children: (element.children || []).map((child) => this.cloneElementWithNewIds(child))
            };
        },
        createElementFromDragPayload(dragPayload, destination) {
            if (dragPayload.kind === DRAG_KIND_COMPONENT) {
                return {
                    element: this.createElement(dragPayload.componentType),
                    sourceLocation: null
                };
            }

            const sourceLocation = this.findElementLocation(this.currentPageElements, dragPayload.elementId);

            if (!sourceLocation) {
                return null;
            }

            if (
                destination.mode === 'insert-relative' &&
                String(sourceLocation.element.id) === String(destination.targetId)
            ) {
                return null;
            }

            if (
                destination.mode === 'append-container' &&
                this.elementContainsId(sourceLocation.element, destination.targetId)
            ) {
                this.setStatus('不能把组件移动到它自己的子级容器里。', 'danger');
                return null;
            }

            if (
                destination.mode === 'insert-relative' &&
                this.elementContainsId(sourceLocation.element, destination.targetId)
            ) {
                this.setStatus('不能把组件移动到它自己的内部位置。', 'danger');
                return null;
            }

            sourceLocation.list.splice(sourceLocation.index, 1);

            return {
                element: sourceLocation.element,
                sourceLocation
            };
        },
        resolveDestination(destination) {
            if (destination.mode === 'append-root') {
                return {
                    list: this.currentPageElements,
                    index: this.currentPageElements.length
                };
            }

            if (destination.mode === 'append-container') {
                const container = this.findElementById(this.currentPageElements, destination.targetId);

                if (!container || !isContainerType(container.type)) {
                    this.setStatus('目标位置不是有效容器。', 'danger');
                    return null;
                }

                if (!Array.isArray(container.children)) {
                    container.children = [];
                }

                return {
                    list: container.children,
                    index: container.children.length
                };
            }

            if (destination.mode === 'insert-relative') {
                const location = this.findElementLocation(this.currentPageElements, destination.targetId);

                if (!location) {
                    return null;
                }

                return {
                    list: location.list,
                    index: destination.placement === 'before' ? location.index : location.index + 1
                };
            }

            return null;
        },
        handleDropAction(dragPayload, destination) {
            const insertionPoint = this.resolveDestination(destination);

            if (!insertionPoint) {
                return;
            }

            const dragResult = this.createElementFromDragPayload(dragPayload, destination);

            if (!dragResult) {
                return;
            }

            const { element, sourceLocation } = dragResult;
            let targetIndex = insertionPoint.index;

            if (
                sourceLocation &&
                sourceLocation.list === insertionPoint.list &&
                sourceLocation.index < targetIndex
            ) {
                targetIndex -= 1;
            }

            const safeIndex = Math.max(0, Math.min(targetIndex, insertionPoint.list.length));
            insertionPoint.list.splice(safeIndex, 0, element);
            this.selectedElementId = element.id;
            this.captureHistory();

            if (dragPayload.kind === DRAG_KIND_ELEMENT) {
                this.setStatus('组件已移动到新位置。', 'success');
            } else {
                this.setStatus(`已添加${this.elementLabels[element.type] || element.type}组件。`, 'success');
            }
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
                case 'input':
                    return {
                        label: '输入项',
                        required: false,
                        placeholder: '请输入内容',
                        value: '',
                        class: 'form-control',
                        width: '',
                        style: ''
                    };
                case 'textarea':
                    return {
                        label: '多行输入',
                        required: false,
                        placeholder: '请输入多行内容',
                        value: '',
                        rows: '4',
                        class: 'form-control',
                        width: '',
                        style: ''
                    };
                case 'spacer':
                    return {
                        height: '32px',
                        class: '',
                        width: '100%',
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
        updateElementProp(key, value, field = null) {
            if (!this.selectedElement) {
                return;
            }

            let nextValue = value;

            if (field && field.control === 'checkbox') {
                nextValue = Boolean(value);
            }

            this.selectedElement.props = {
                ...this.selectedElement.props,
                [key]: nextValue
            };
            this.queueHistoryCapture();
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
                this.captureHistory();
                this.setStatus('图片已更新。', 'success');
            };
            reader.readAsDataURL(file);
        },
        removeElement(elementId) {
            const location = this.findElementLocation(this.currentPageElements, elementId);

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

            this.captureHistory();
            this.setStatus('组件已删除。', 'warning');
        },
        duplicateElement(elementId) {
            const location = this.findElementLocation(this.currentPageElements, elementId);

            if (!location) {
                return;
            }

            const clonedElement = this.cloneElementWithNewIds(location.element);
            location.list.splice(location.index + 1, 0, clonedElement);
            this.selectedElementId = clonedElement.id;
            this.captureHistory();
            this.setStatus('组件已复制。', 'success');
        },
        moveElement({ elementId, offset }) {
            const location = this.findElementLocation(this.currentPageElements, elementId);

            if (!location) {
                return;
            }

            const targetIndex = location.index + offset;

            if (targetIndex < 0 || targetIndex >= location.list.length) {
                return;
            }

            const [item] = location.list.splice(location.index, 1);
            location.list.splice(targetIndex, 0, item);
            this.captureHistory();
            this.setStatus('组件顺序已调整。', 'info');
        },
        addPage() {
            const title = this.newPageTitle || `页面 ${this.safePages.length + 1}`;
            const page = {
                id: createId('page'),
                title,
                name: this.createUniquePageName(title, this.safePages.length + 1),
                elements: []
            };

            this.safePages.push(page);
            this.currentPageId = page.id;
            this.selectedElementId = null;
            this.newPageTitle = '';
            this.captureHistory();
            this.setStatus(`已创建页面“${page.title}”。`, 'success');
        },
        duplicateCurrentPage() {
            const pageIndex = this.safePages.findIndex((page) => page.id === this.currentPageId);
            const sourcePage = this.currentPage;
            const duplicatePage = {
                id: createId('page'),
                title: `${sourcePage.title} 副本`,
                name: this.createUniquePageName(`${sourcePage.name}-copy`, pageIndex + 2),
                elements: sourcePage.elements.map((element) => this.cloneElementWithNewIds(element))
            };

            this.safePages.splice(pageIndex + 1, 0, duplicatePage);
            this.currentPageId = duplicatePage.id;
            this.selectedElementId = null;
            this.captureHistory();
            this.setStatus(`已复制页面“${sourcePage.title}”。`, 'success');
        },
        switchPage(pageId) {
            this.currentPageId = pageId;
            this.selectedElementId = null;
        },
        deleteCurrentPage() {
            if (this.safePages.length === 1) {
                this.currentPage.elements = [];
                this.selectedElementId = null;
                this.captureHistory();
                this.setStatus('最后一个页面不能删除，已为你清空当前页面。', 'warning');
                return;
            }

            const currentIndex = this.safePages.findIndex((page) => page.id === this.currentPageId);
            const currentTitle = this.currentPage.title;

            this.safePages.splice(currentIndex, 1);

            const nextPage = this.safePages[Math.max(0, currentIndex - 1)] || this.safePages[0];
            this.currentPageId = nextPage.id;
            this.selectedElementId = null;
            this.captureHistory();
            this.setStatus(`已删除页面“${currentTitle}”。`, 'warning');
        },
        clearCanvas() {
            if (this.currentPageElements.length === 0) {
                this.setStatus('当前页面已经是空白状态。', 'info');
                return;
            }

            this.currentPage.elements = [];
            this.selectedElementId = null;
            this.captureHistory();
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
            this.captureHistory();
        },
        buildProjectConfig() {
            const usedNames = new Set();

            return {
                title: this.projectName || '未命名项目',
                pages: this.safePages.map((page, index) => ({
                    name: this.createUniquePageName(page.name || page.title, index + 1, usedNames),
                    title: page.title || `页面 ${index + 1}`,
                    elements: deepClone(page.elements)
                }))
            };
        },
        applyProject(project) {
            const config = project && project.config ? project.config : {};
            const pages = this.hydratePages(config.pages || []);

            this.projectId = project.id || null;
            this.projectName = project.name || config.title || '未命名项目';
            this.projectType = project.type || 'h5';
            this.pages = pages;
            this.currentPageId = pages[0].id;
            this.selectedElementId = null;
            this.clearDropTarget();
            this.previewHtml = '';
            this.hasPreview = false;
            this.wechatCode = '';
            this.h5Code = '';
        },
        createNewProject() {
            const blankPage = this.createBlankPage();

            this.projectId = null;
            this.projectName = '未命名项目';
            this.projectType = 'h5';
            this.pages = [blankPage];
            this.currentPageId = blankPage.id;
            this.selectedElementId = null;
            this.clearDropTarget();
            this.newPageTitle = '';
            this.previewHtml = '';
            this.hasPreview = false;
            this.wechatCode = '';
            this.h5Code = '';
            this.resetHistory();
            this.setStatus('已切换到新的空白项目。', 'success');
        },
        buildExportPayload() {
            return {
                version: 1,
                exportedAt: new Date().toISOString(),
                name: this.projectName || '未命名项目',
                type: this.projectType,
                config: this.buildProjectConfig()
            };
        },
        exportProjectJson() {
            this.flushHistoryCapture();
            const filename = `${this.normalizePageName(this.projectName, 1) || 'builder-project'}.json`;
            downloadTextFile(filename, JSON.stringify(this.buildExportPayload(), null, 2));
            this.setStatus('项目 JSON 已导出。', 'success');
        },
        triggerImportFile() {
            if (this.$refs.projectImportInput) {
                this.$refs.projectImportInput.value = '';
                this.$refs.projectImportInput.click();
            }
        },
        importProjectFile(event) {
            const file = event.target.files && event.target.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                try {
                    const parsed = JSON.parse(reader.result);
                    const importedProject = parsed && parsed.config
                        ? {
                            id: null,
                            name: parsed.name || '导入项目',
                            type: parsed.type || 'h5',
                            config: parsed.config
                        }
                        : {
                            id: null,
                            name: file.name.replace(/\.json$/i, '') || '导入项目',
                            type: 'h5',
                            config: parsed
                        };

                    if (!importedProject.config || !Array.isArray(importedProject.config.pages)) {
                        throw new Error('导入文件缺少有效的 pages 配置');
                    }

                    this.applyProject(importedProject);
                    this.resetHistory();
                    this.setStatus(`已导入项目“${this.projectName}”。`, 'success');
                } catch (error) {
                    this.setStatus(error.message || '导入项目失败。', 'danger');
                }
            };
            reader.readAsText(file, 'utf-8');
        },
        async requestJson(url, options = {}) {
            const fetchOptions = {
                method: options.method || 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            if (options.body !== undefined) {
                fetchOptions.body = JSON.stringify(options.body);
            }

            const response = await fetch(url, fetchOptions);
            const json = await response.json();

            if (!response.ok || !json.success) {
                throw new Error(json.message || '请求失败');
            }

            return json;
        },
        async requestBlob(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const contentType = response.headers.get('Content-Type') || '';

            if (!response.ok || contentType.includes('application/json')) {
                const json = await response.json();
                throw new Error(json.message || '下载失败');
            }

            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const matched = disposition.match(/filename="([^"]+)"/);

            return {
                blob,
                filename: matched ? matched[1] : 'builder-export.zip'
            };
        },
        triggerBlobDownload(blob, filename) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        },
        async exportProjectBundle(type) {
            this.flushHistoryCapture();

            if (type === 'h5') {
                this.isExportingH5 = true;
            } else {
                this.isExportingWechat = true;
            }

            try {
                const { blob, filename } = await this.requestBlob(`/api/export/${type}`, {
                    config: this.buildProjectConfig()
                });

                this.triggerBlobDownload(blob, filename);
                this.setStatus(`${type === 'h5' ? 'H5' : '微信小程序'} 压缩包已开始下载。`, 'success');
            } catch (error) {
                this.setStatus(error.message || '导出压缩包失败。', 'danger');
            } finally {
                if (type === 'h5') {
                    this.isExportingH5 = false;
                } else {
                    this.isExportingWechat = false;
                }
            }
        },
        async fetchProjects(silent = false) {
            this.isProjectListLoading = true;

            try {
                const json = await this.requestJson('/api/projects');
                this.savedProjects = Array.isArray(json.data) ? json.data : [];

                if (!silent) {
                    this.setStatus('项目列表已刷新。', 'success');
                }
            } catch (error) {
                this.savedProjects = [];

                if (!silent) {
                    this.setStatus(error.message || '获取项目列表失败。', 'danger');
                }
            } finally {
                this.isProjectListLoading = false;
            }
        },
        async saveProject() {
            this.flushHistoryCapture();
            this.isSavingProject = true;

            try {
                const isUpdate = Boolean(this.projectId);
                const payload = {
                    name: this.projectName || '未命名项目',
                    type: this.projectType,
                    config: this.buildProjectConfig()
                };
                const url = this.projectId ? `/api/projects/${this.projectId}` : '/api/projects';
                const method = this.projectId ? 'PUT' : 'POST';
                const json = await this.requestJson(url, {
                    method,
                    body: payload
                });

                if (json.data) {
                    this.projectId = json.data.id;
                    this.projectName = json.data.name;
                    this.projectType = json.data.type;
                } else if (json.id) {
                    this.projectId = json.id;
                }

                await this.fetchProjects(true);
                this.captureHistory();
                this.setStatus(isUpdate ? '项目已更新。' : '项目已创建。', 'success');
            } catch (error) {
                this.setStatus(error.message || '保存项目失败。', 'danger');
            } finally {
                this.isSavingProject = false;
            }
        },
        async loadProject(projectId) {
            this.isProjectLoading = true;

            try {
                const json = await this.requestJson(`/api/projects/${projectId}`);
                this.applyProject(json.data);
                this.resetHistory();
                this.setStatus(`已加载项目“${this.projectName}”。`, 'success');
            } catch (error) {
                this.setStatus(error.message || '加载项目失败。', 'danger');
            } finally {
                this.isProjectLoading = false;
            }
        },
        async removeProject(projectId) {
            this.isProjectLoading = true;

            try {
                await this.requestJson(`/api/projects/${projectId}`, {
                    method: 'DELETE'
                });

                if (String(this.projectId) === String(projectId)) {
                    this.createNewProject();
                }

                await this.fetchProjects(true);
                this.setStatus('项目已删除。', 'warning');
            } catch (error) {
                this.setStatus(error.message || '删除项目失败。', 'danger');
            } finally {
                this.isProjectLoading = false;
            }
        },
        formatProjectMeta(project) {
            const pageCount = project.config && Array.isArray(project.config.pages)
                ? project.config.pages.length
                : 0;
            const updatedAt = project.updated_at || project.created_at || '未知时间';

            return `${updatedAt} · ${pageCount} 个页面`;
        },
        async previewProject() {
            this.flushHistoryCapture();
            this.isPreviewLoading = true;

            try {
                const json = await this.requestJson('/api/preview', {
                    method: 'POST',
                    body: {
                        type: 'h5',
                        config: this.buildProjectConfig()
                    }
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
            this.flushHistoryCapture();
            this.isGenerating = true;

            try {
                const payload = {
                    config: this.buildProjectConfig()
                };
                const [wechatJson, h5Json] = await Promise.all([
                    this.requestJson('/api/generate/wechat', {
                        method: 'POST',
                        body: payload
                    }),
                    this.requestJson('/api/generate/h5', {
                        method: 'POST',
                        body: payload
                    })
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
        window.removeEventListener('keydown', this.handleKeydown);
        window.removeEventListener('dragend', this.clearDropTarget);

        if (this.statusTimer) {
            window.clearTimeout(this.statusTimer);
        }

        if (this.historyTimer) {
            window.clearTimeout(this.historyTimer);
        }
    }
}).mount('#app');
