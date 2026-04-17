<?php

namespace App\Services;

class H5CodeGenerator
{
    public function generate($config)
    {
        $pages = $config['pages'] ?? [];
        $components = $config['components'] ?? [];
        
        $html = $this->generateHtml($config);
        $css = $this->generateCss($config);
        $js = $this->generateJs($config);
        
        $pageFiles = [];
        foreach ($pages as $page) {
            $pageFiles[] = [
                'name' => $page['name'],
                'html' => $this->generatePageHtml($page),
                'css' => $this->generatePageCss($page),
                'js' => $this->generatePageJs($page)
            ];
        }
        
        $componentFiles = [];
        foreach ($components as $component) {
            $componentFiles[] = [
                'name' => $component['name'],
                'html' => $this->generatePageHtml($component),
                'css' => $this->generatePageCss($component),
                'js' => $this->generatePageJs($component)
            ];
        }
        
        return [
            'index.html' => $html,
            'style.css' => $css,
            'script.js' => $js,
            'pages' => $pageFiles,
            'components' => $componentFiles
        ];
    }
    
    public function generatePreview($config)
    {
        $pages = $config['pages'] ?? [];
        $previewCode = '';
        
        foreach ($pages as $page) {
            // 确保保留原始元素顺序，使用json_encode和json_decode强制保持数组结构
            $preservedPage = json_decode(json_encode($page), true);
            $previewCode .= $this->generatePageHtml($preservedPage);
        }
        
        return $previewCode;
    }
    
    private function generateHtml($config)
    {
        $title = $config['title'] ?? 'H5页面';
        $pages = $config['pages'] ?? [];
        
        $pageLinks = '';
        foreach ($pages as $page) {
            $pageLinks .= "<li><a href=\"#{$page['name']}\">{$page['title']}</a></li>\n";
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang=\"zh-CN\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$title}</title>
    <link rel=\"stylesheet\" href=\"style.css\">
</head>
<body>
    <nav class=\"navigation\">
        <ul>
            {$pageLinks}
        </ul>
    </nav>
    
    <main class=\"main-content\">
        <div id=\"app\">
            <!-- 页面内容将通过JavaScript动态加载 -->
        </div>
    </main>
    
    <script src=\"script.js\"></script>
</body>
</html>
HTML;
    }
    
    private function generateCss($config)
    {
        $theme = $this->resolveTheme($config['theme'] ?? []);
        $primary = $theme['primary'];
        $accent = $theme['accent'];
        $surface = $theme['surface'];
        $pageBackground = $theme['pageBackground'];
        $text = $theme['text'];
        $radius = $theme['radius'];

        return <<<CSS
/* 全局样式 */
:root {
    --builder-primary: {$primary};
    --builder-accent: {$accent};
    --builder-surface: {$surface};
    --builder-page-bg: {$pageBackground};
    --builder-text: {$text};
    --builder-radius: {$radius};
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: var(--builder-text);
    background-color: var(--builder-page-bg);
}

.navigation {
    background: var(--builder-surface);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

.navigation ul {
    list-style: none;
    display: flex;
    padding: 0 20px;
}

.navigation li {
    margin: 0;
}

.navigation a {
    display: block;
    padding: 15px 20px;
    text-decoration: none;
    color: #333;
    transition: color 0.3s;
}

.navigation a:hover {
    color: var(--builder-primary);
}

.main-content {
    margin-top: 60px;
    padding: 20px;
}

.page {
    background: var(--builder-surface);
    border-radius: var(--builder-radius);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.72rem 1.15rem;
    border-radius: calc(var(--builder-radius) - 6px);
    border: 1px solid transparent;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    line-height: 1.2;
}

.btn-primary {
    background: var(--builder-primary);
    color: #ffffff;
    border-color: var(--builder-primary);
}

.btn-outline-primary {
    background: transparent;
    color: var(--builder-primary);
    border-color: var(--builder-primary);
}

.btn-success {
    background: var(--builder-accent);
    color: #ffffff;
    border-color: var(--builder-accent);
}

.btn-dark {
    background: var(--builder-text);
    color: #ffffff;
    border-color: var(--builder-text);
}

.btn-light {
    background: #ffffff;
    color: var(--builder-primary);
    border-color: rgba(255, 255, 255, 0.8);
}

.btn-outline-light {
    background: transparent;
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.85);
}

.form-control {
    width: 100%;
    padding: 0.72rem 0.9rem;
    border: 1px solid #cfd8d4;
    border-radius: calc(var(--builder-radius) - 8px);
    background: #ffffff;
    color: var(--builder-text);
}

.choice-group {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding: 0.9rem 1rem;
    border: 1px solid #d7e2d6;
    border-radius: calc(var(--builder-radius) - 8px);
    background: rgba(255, 255, 255, 0.9);
}

.choice-group-horizontal {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 0.8rem 1rem;
}

.choice-option {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: var(--builder-text);
    font-size: 14px;
}

.builder-step-progress {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    padding: 1rem 1.1rem;
    border-radius: 18px;
    border: 1px solid rgba(159, 195, 175, 0.45);
    background: linear-gradient(180deg, #ffffff 0%, #f7fbf8 100%);
}

.builder-step-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    color: var(--builder-text);
}

.builder-step-head span {
    color: var(--builder-text-muted);
    font-size: 0.84rem;
}

.builder-step-track {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 0.75rem;
}

.builder-step-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.8rem;
    border-radius: 14px;
    border: 1px solid rgba(215, 226, 214, 0.85);
    background: rgba(248, 251, 247, 0.95);
    color: var(--builder-text-muted);
}

.builder-step-item.is-active {
    border-color: rgba(15, 118, 110, 0.28);
    background: rgba(215, 243, 236, 0.78);
    color: var(--builder-brand);
}

.builder-step-item.is-complete {
    border-color: rgba(15, 118, 110, 0.18);
    color: var(--builder-text);
}

.builder-step-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.8rem;
    height: 1.8rem;
    border-radius: 999px;
    background: rgba(215, 226, 214, 0.9);
    font-weight: 700;
    font-size: 0.85rem;
}

.builder-step-item.is-active .builder-step-indicator,
.builder-step-item.is-complete .builder-step-indicator {
    background: var(--builder-brand);
    color: #ffffff;
}

.builder-step-text {
    font-size: 0.84rem;
    line-height: 1.4;
}

.builder-form-summary {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    margin-bottom: 1rem;
    padding: 1rem 1.05rem;
    border-radius: 18px;
    border: 1px solid rgba(159, 195, 175, 0.45);
    background: linear-gradient(180deg, #ffffff 0%, #f7fbf8 100%);
}

.builder-form-summary-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}

.builder-form-summary-head strong {
    color: var(--builder-text);
    font-size: 0.95rem;
}

.builder-form-summary-head span {
    color: var(--builder-text-muted);
    font-size: 0.82rem;
}

.builder-form-summary-list {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.builder-form-summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.8rem;
    border-radius: 14px;
    border: 1px solid rgba(215, 226, 214, 0.82);
    background: rgba(248, 251, 247, 0.94);
}

.builder-form-summary-item span {
    color: var(--builder-text-muted);
    font-size: 0.84rem;
    word-break: break-word;
}

.builder-form-summary-item strong {
    color: var(--builder-text);
    font-size: 0.84rem;
    text-align: right;
    word-break: break-word;
}

.builder-form-summary-empty {
    padding: 0.9rem 1rem;
    border-radius: 14px;
    border: 1px dashed rgba(159, 195, 175, 0.75);
    background: rgba(248, 251, 247, 0.82);
    color: var(--builder-text-muted);
    font-size: 0.84rem;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .navigation ul {
        flex-direction: column;
    }
    
    .main-content {
        padding: 10px;
    }
}
CSS;
    }
    
    private function generateJs($config)
    {
        $pages = $config['pages'] ?? [];
        $pageData = json_encode($pages, JSON_UNESCAPED_UNICODE);
        $projectTitle = json_encode($config['title'] ?? '未命名项目', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $template = <<<'JS'
// 主应用逻辑
class App {
    constructor() {
        this.currentPage = null;
        this.pages = __PAGE_DATA__;
        this.projectTitle = __PROJECT_TITLE__;
        this.init();
    }
    
    init() {
        this.bindEvents();
        window.builderSubmitAction = (trigger, config = {}) => this.handleSubmitAction(trigger, config);
        window.builderStepAction = (trigger, direction = 'next') => this.handleStepAction(trigger, direction);
        this.loadPage(this.pages[0]?.name || 'home');
    }
    
    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' && e.target.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const pageName = e.target.getAttribute('href').substring(1);
                this.loadPage(pageName);
            }
        });

        document.addEventListener('input', (event) => {
            if (event.target && typeof event.target.closest === 'function' && event.target.closest('[data-builder-field="true"]')) {
                const scope = event.target.closest('.page') || document;
                this.refreshConditionalVisibility(scope);
                this.refreshSummaryState(scope);
            }
        });

        document.addEventListener('change', (event) => {
            if (event.target && typeof event.target.closest === 'function' && event.target.closest('[data-builder-field="true"]')) {
                const scope = event.target.closest('.page') || document;
                this.refreshConditionalVisibility(scope);
                this.refreshSummaryState(scope);
            }
        });
    }

    getButtonActionCode(props = {}) {
        const actionType = props.actionType || 'none';
        const actionValue = props.actionValue || '';

        if (actionType === 'message') {
            return `alert(${JSON.stringify(actionValue || '操作成功')})`;
        }

        if (actionType === 'link') {
            return `window.location.href=${JSON.stringify(actionValue || '#')}`;
        }

        if (actionType === 'step-prev') {
            return `window.builderStepAction(this, 'prev')`;
        }

        if (actionType === 'step-next') {
            return `window.builderStepAction(this, 'next')`;
        }

        if (actionType === 'submit') {
            return `window.builderSubmitAction(this, ${JSON.stringify({
                successMessage: actionValue || '提交成功',
                submitEndpoint: props.submitEndpoint || '',
                submitMethod: props.submitMethod || 'POST',
                resetForm: Boolean(props.submitResetForm),
                redirectUrl: props.submitRedirectUrl || ''
            })})`;
        }

        return props.onclick || '';
    }

    escapeHtmlAttribute(value = '') {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    parseChoiceOptions(raw = '') {
        return String(raw || '')
            .split(/\r?\n/)
            .map((line) => line.trim())
            .filter(Boolean)
            .map((line, index) => {
                const segments = line.split('|');
                const value = String(segments[0] || `option_${index + 1}`).trim() || `option_${index + 1}`;
                const label = String(segments.slice(1).join('|') || segments[0] || `选项 ${index + 1}`).trim() || value;

                return { value, label };
            });
    }

    parseStepIndex(value = '1') {
        const parsed = Number.parseInt(String(value || '1'), 10);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
    }

    getStepLabel(stepIndex = 1, stepTitle = '') {
        const title = String(stepTitle || '').trim();
        return title ? `第${stepIndex}步·${title}` : `第${stepIndex}步`;
    }

    collectStepDefinitions(elements = [], definitions = new Map()) {
        (elements || []).forEach((element) => {
            const props = element?.props || {};
            const stepIndex = this.parseStepIndex(props.stepIndex || '1');
            const current = definitions.get(stepIndex) || {
                index: stepIndex,
                title: ''
            };

            if (!current.title && String(props.stepTitle || '').trim()) {
                current.title = String(props.stepTitle || '').trim();
            }

            definitions.set(stepIndex, current);

            if (Array.isArray(element?.children) && element.children.length > 0) {
                this.collectStepDefinitions(element.children, definitions);
            }
        });

        return Array.from(definitions.values()).sort((left, right) => left.index - right.index);
    }

    buildStepProgressHtml(page = {}) {
        const steps = this.collectStepDefinitions(page.elements || []);

        if (steps.length <= 1 || steps.every((step) => step.index === 1)) {
            return '';
        }

        const items = steps.map((step) => `
            <div class="builder-step-item" data-step-item="${step.index}">
                <span class="builder-step-indicator">${step.index}</span>
                <span class="builder-step-text">${this.escapeHtmlAttribute(this.getStepLabel(step.index, step.title || ''))}</span>
            </div>
        `).join('');

        return `<div class="builder-step-progress" data-step-progress="1"><div class="builder-step-head"><strong>分步表单</strong><span data-step-summary></span></div><div class="builder-step-track">${items}</div></div>`;
    }

    buildSummaryHtml(props = {}) {
        return `<div class="builder-form-summary ${props.class || ''}" style="${props.width ? `width:${props.width};` : ''}${props.style || ''}" data-summary-enabled="1"><div class="builder-form-summary-head"><strong>${this.escapeHtmlAttribute(props.summaryTitle || '请确认以下信息')}</strong><span data-summary-count>正在汇总</span></div><div class="builder-form-summary-list" data-summary-list></div><div class="builder-form-summary-empty" data-summary-empty-state>${this.escapeHtmlAttribute(props.emptyText || '当前还没有可汇总的表单字段')}</div></div>`;
    }

    getFieldValue(field) {
        const fieldKind = field.dataset.fieldKind || '';

        if (fieldKind === 'checkbox-group') {
            return Array.from(field.querySelectorAll('input[type="checkbox"]:checked')).map((input) => input.value);
        }

        if (fieldKind === 'radio-group') {
            const checked = field.querySelector('input[type="radio"]:checked');
            return checked ? checked.value : '';
        }

        return field.value || '';
    }

    getFieldDisplayValue(field) {
        const fieldKind = field.dataset.fieldKind || '';
        const rawValue = this.getFieldValue(field);

        if (fieldKind === 'checkbox-group') {
            const labels = Array.from(field.querySelectorAll('input[type="checkbox"]:checked')).map((input) => {
                const labelNode = input.closest('label');
                const textNode = labelNode ? labelNode.querySelector('span') : null;
                return textNode ? textNode.textContent.trim() : input.value;
            }).filter(Boolean);
            return labels.length > 0 ? labels.join('、') : '未填写';
        }

        if (fieldKind === 'radio-group') {
            const checked = field.querySelector('input[type="radio"]:checked');
            if (!checked) {
                return '未填写';
            }

            const labelNode = checked.closest('label');
            const textNode = labelNode ? labelNode.querySelector('span') : null;
            return textNode ? textNode.textContent.trim() : checked.value;
        }

        if (fieldKind === 'select') {
            const option = field.options && field.selectedIndex >= 0 ? field.options[field.selectedIndex] : null;
            const optionLabel = option && option.value !== '' ? option.textContent.trim() : '';
            return optionLabel || '未填写';
        }

        return this.isEmptyFieldValue(rawValue) ? '未填写' : String(rawValue);
    }

    getFormFields(scope = document) {
        return Array.from((scope || document).querySelectorAll('[data-builder-field="true"]'));
    }

    isFieldConditionVisible(field) {
        return !(field && typeof field.closest === 'function' && field.closest('[data-conditional-hidden="1"]'));
    }

    isFieldStepVisible(field) {
        return !(field && typeof field.closest === 'function' && field.closest('[data-step-hidden="1"]'));
    }

    getCurrentStepFields(scope = document) {
        return this.getFormFields(scope).filter((field) => this.isFieldConditionVisible(field) && this.isFieldStepVisible(field));
    }

    getSubmittableFields(scope = document) {
        return this.getFormFields(scope).filter((field) => this.isFieldConditionVisible(field));
    }

    collectFormValues(scope = document) {
        const values = {};

        this.getFormFields(scope).forEach((field) => {
            const fieldKey = field.dataset.fieldKey || field.name || `field_${Object.keys(values).length + 1}`;
            values[fieldKey] = this.getFieldValue(field);
        });

        return values;
    }

    buildSummaryEntries(scope = document) {
        return this.getSubmittableFields(scope).map((field, index) => ({
            key: field.dataset.fieldKey || field.name || `field_${index + 1}`,
            label: field.dataset.label || field.dataset.fieldKey || `字段 ${index + 1}`,
            value: this.getFieldDisplayValue(field)
        }));
    }

    getPageNode(scope = document) {
        if (scope && scope.classList && scope.classList.contains('page')) {
            return scope;
        }

        return (scope && typeof scope.closest === 'function' ? scope.closest('.page') : null) || document;
    }

    getStepBlocks(scope = document) {
        return Array.from((scope || document).querySelectorAll('[data-step-enabled="1"]'));
    }

    getCurrentStep(scope = document) {
        const pageNode = this.getPageNode(scope);
        return this.parseStepIndex(pageNode?.dataset?.currentStep || '1');
    }

    evaluateConditionRule(actualValue, operator = 'equals', expectedValue = '') {
        const normalizedExpected = String(expectedValue || '').trim();
        const normalizedActual = Array.isArray(actualValue)
            ? actualValue.map((item) => String(item || '').trim()).filter(Boolean)
            : String(actualValue || '').trim();

        if (operator === 'filled') {
            return Array.isArray(normalizedActual) ? normalizedActual.length > 0 : normalizedActual !== '';
        }

        if (operator === 'empty') {
            return Array.isArray(normalizedActual) ? normalizedActual.length === 0 : normalizedActual === '';
        }

        if (Array.isArray(normalizedActual)) {
            const included = normalizedActual.includes(normalizedExpected);
            return operator === 'not_contains' ? !included : included;
        }

        if (operator === 'contains') {
            return normalizedExpected !== '' && normalizedActual.includes(normalizedExpected);
        }

        if (operator === 'not_contains') {
            return normalizedExpected === '' ? true : !normalizedActual.includes(normalizedExpected);
        }

        if (operator === 'not_equals') {
            return normalizedActual !== normalizedExpected;
        }

        return normalizedActual === normalizedExpected;
    }

    refreshConditionalVisibility(scope = document) {
        const container = scope || document;
        const conditionalBlocks = Array.from(container.querySelectorAll('[data-visibility-enabled="1"]'));

        if (conditionalBlocks.length === 0) {
            return;
        }

        const fieldValues = this.collectFormValues(container);

        conditionalBlocks.forEach((block) => {
            const fieldKey = block.dataset.visibilityField || '';
            const operator = block.dataset.visibilityOperator || 'equals';
            const expectedValue = block.dataset.visibilityValue || '';
            const isVisible = !fieldKey ? true : this.evaluateConditionRule(fieldValues[fieldKey], operator, expectedValue);

            block.hidden = !isVisible;
            block.dataset.conditionalHidden = isVisible ? '0' : '1';
        });
    }

    refreshStepState(scope = document, forcedStep = null) {
        const pageNode = this.getPageNode(scope);
        const stepBlocks = this.getStepBlocks(pageNode);

        if (stepBlocks.length === 0) {
            return;
        }

        const stepDefinitions = Array.from(new Map(stepBlocks.map((block) => {
            const stepIndex = this.parseStepIndex(block.dataset.stepIndex || '1');
            return [stepIndex, {
                index: stepIndex,
                title: block.dataset.stepTitle || ''
            }];
        })).values()).sort((left, right) => left.index - right.index);
        const minStep = stepDefinitions[0]?.index || 1;
        const maxStep = stepDefinitions[stepDefinitions.length - 1]?.index || minStep;
        const nextStep = forcedStep === null ? this.getCurrentStep(pageNode) : this.parseStepIndex(forcedStep);
        const currentStep = Math.max(minStep, Math.min(nextStep, maxStep));

        if (pageNode.dataset) {
            pageNode.dataset.currentStep = String(currentStep);
            pageNode.dataset.totalSteps = String(stepDefinitions.length);
        }

        stepBlocks.forEach((block) => {
            const stepIndex = this.parseStepIndex(block.dataset.stepIndex || '1');
            const isVisible = stepIndex === currentStep;
            block.hidden = !isVisible;
            block.dataset.stepHidden = isVisible ? '0' : '1';
        });

        Array.from(pageNode.querySelectorAll('[data-step-item]')).forEach((item) => {
            const itemStep = this.parseStepIndex(item.dataset.stepItem || '1');
            item.classList.toggle('is-active', itemStep === currentStep);
            item.classList.toggle('is-complete', itemStep < currentStep);
        });

        const summary = pageNode.querySelector('[data-step-summary]');
        if (summary) {
            const activeDefinition = stepDefinitions.find((step) => step.index === currentStep) || stepDefinitions[0];
            summary.textContent = activeDefinition ? this.getStepLabel(activeDefinition.index, activeDefinition.title || '') : `第${currentStep}步`;
        }
    }

    refreshSummaryState(scope = document) {
        const pageNode = this.getPageNode(scope);
        const summaryBlocks = Array.from((pageNode || document).querySelectorAll('[data-summary-enabled="1"]'));

        if (summaryBlocks.length === 0) {
            return;
        }

        const entries = this.buildSummaryEntries(pageNode);

        summaryBlocks.forEach((block) => {
            const listNode = block.querySelector('[data-summary-list]');
            const emptyNode = block.querySelector('[data-summary-empty-state]');
            const countNode = block.querySelector('[data-summary-count]');

            if (countNode) {
                countNode.textContent = entries.length > 0 ? `共 ${entries.length} 项` : '暂无可复核字段';
            }

            if (listNode) {
                listNode.innerHTML = entries.map((entry) => `
                    <div class="builder-form-summary-item">
                        <span>${this.escapeHtmlAttribute(entry.label)}</span>
                        <strong>${this.escapeHtmlAttribute(entry.value)}</strong>
                    </div>
                `).join('');
            }

            if (emptyNode) {
                emptyNode.hidden = entries.length > 0;
            }
        });
    }

    validateFields(fields = []) {
        const invalidField = (fields || []).find((field) => this.validateField(field));

        if (!invalidField) {
            return '';
        }

        const message = this.validateField(invalidField);
        alert(message || '表单校验失败');
        this.focusField(invalidField);
        return message;
    }

    handleStepAction(trigger, direction = 'next') {
        const scope = trigger.closest('.page') || document;
        const currentStep = this.getCurrentStep(scope);
        const totalSteps = Number(this.getPageNode(scope)?.dataset?.totalSteps || this.collectStepDefinitions(this.currentPage?.elements || []).length || 1);

        if (direction === 'prev') {
            this.refreshStepState(scope, Math.max(1, currentStep - 1));
            this.refreshSummaryState(scope);
            return false;
        }

        if (this.validateFields(this.getCurrentStepFields(scope))) {
            return false;
        }

        this.refreshStepState(scope, Math.min(totalSteps, currentStep + 1));
        this.refreshSummaryState(scope);
        return false;
    }

    isEmptyFieldValue(value) {
        return Array.isArray(value) ? value.length === 0 : !String(value || '').trim();
    }

    focusField(field) {
        if (typeof field.focus === 'function') {
            field.focus();
            return;
        }

        const focusTarget = field.querySelector('input, textarea, select');
        if (focusTarget && typeof focusTarget.focus === 'function') {
            focusTarget.focus();
        }
    }

    resetField(field) {
        const fieldKind = field.dataset.fieldKind || '';

        if (fieldKind === 'checkbox-group') {
            field.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                input.checked = false;
            });
            return;
        }

        if (fieldKind === 'radio-group') {
            field.querySelectorAll('input[type="radio"]').forEach((input) => {
                input.checked = false;
            });
            return;
        }

        field.value = '';
    }

    validateField(field) {
        const rawValue = this.getFieldValue(field);
        const label = field.dataset.label || '当前字段';

        if (field.dataset.required === '1' && this.isEmptyFieldValue(rawValue)) {
            return `${label}为必填项`;
        }

        if (Array.isArray(rawValue)) {
            return '';
        }

        const value = String(rawValue || '').trim();
        if (!value) {
            return '';
        }

        const pattern = field.dataset.pattern || '';
        if (!pattern) {
            return '';
        }

        try {
            const regex = new RegExp(pattern);
            if (!regex.test(value)) {
                return field.dataset.validationMessage || `${label}格式不正确`;
            }
        } catch (error) {
            console.warn('invalid builder validation pattern', pattern, error);
        }

        return '';
    }

    async submitFormData(formData, config = {}) {
        if (!config.submitEndpoint) {
            return { success: true };
        }

        const payload = {
            project_name: this.projectTitle || '未命名项目',
            project_type: 'h5',
            page_name: this.currentPage?.name || 'index',
            page_title: this.currentPage?.title || '首页',
            source: 'h5',
            submitted_at: new Date().toISOString(),
            form_data: formData,
            field_meta: config.fieldMeta || {}
        };

        const response = await fetch(config.submitEndpoint, {
            method: config.submitMethod || 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const json = await response.json().catch(() => ({}));

        if (!response.ok || json.success === false) {
            throw new Error(json.message || '提交失败，请检查接口配置');
        }

        return json;
    }

    buildFieldMeta(fields = []) {
        const fieldMeta = {};

        fields.forEach((field) => {
            const fieldKey = field.dataset.fieldKey || '';
            if (!fieldKey) {
                return;
            }

            const fieldType = field.dataset.fieldKind || field.tagName.toLowerCase();
            const fieldLabel = field.dataset.label || fieldKey;
            const meta = {
                key: fieldKey,
                label: fieldLabel,
                type: fieldType,
                options: [],
                page_name: this.currentPage?.name || 'index',
                page_title: this.currentPage?.title || '首页'
            };

            if (fieldType === 'select') {
                meta.options = Array.from(field.querySelectorAll('option'))
                    .filter((option) => option.value !== '')
                    .map((option) => ({
                        value: option.value,
                        label: option.textContent.trim() || option.value
                    }));
            }

            if (fieldType === 'radio-group' || fieldType === 'checkbox-group') {
                meta.options = Array.from(field.querySelectorAll('input'))
                    .map((input) => {
                        const labelNode = input.closest('label');
                        const textNode = labelNode ? labelNode.querySelector('span') : null;

                        return {
                            value: input.value,
                            label: textNode ? textNode.textContent.trim() : input.value
                        };
                    });
            }

            fieldMeta[fieldKey] = meta;
        });

        return fieldMeta;
    }

    async handleSubmitAction(trigger, config = {}) {
        const scope = trigger.closest('.page') || document;
        if (this.validateFields(this.getCurrentStepFields(scope))) {
            return false;
        }

        const formData = {};
        const submittableFields = this.getSubmittableFields(scope);
        submittableFields.forEach((field) => {
            const fieldKey = field.dataset.fieldKey || field.name || `field_${Object.keys(formData).length + 1}`;
            formData[fieldKey] = this.getFieldValue(field);
        });

        console.log('builder form submit', formData);
        const fieldMeta = this.buildFieldMeta(submittableFields);

        try {
            await this.submitFormData(formData, {
                ...config,
                fieldMeta
            });
        } catch (error) {
            alert(error.message || '提交失败');
            return false;
        }

        if (config.resetForm) {
            this.getFormFields(scope).forEach((field) => {
                this.resetField(field);
            });
            this.refreshConditionalVisibility(scope);
            this.refreshStepState(scope, 1);
            this.refreshSummaryState(scope);
        }

        alert(config.successMessage || '提交成功');

        if (config.redirectUrl) {
            window.location.href = config.redirectUrl;
        }

        return true;
    }
    
    loadPage(pageName) {
        const page = this.pages.find(p => p.name === pageName);
        if (!page) return;
        
        this.currentPage = page;
        this.renderPage(page);
        this.updateNavigation(pageName);
    }
    
    renderPage(page) {
        const app = document.getElementById('app');
        app.innerHTML = this.generatePageHtml(page);
        this.loadPageScripts(page);
        this.refreshConditionalVisibility(app.querySelector('.page') || app);
        this.refreshStepState(app.querySelector('.page') || app, 1);
        this.refreshSummaryState(app.querySelector('.page') || app);
    }
    
    generatePageHtml(page) {
        let html = `<div class="page" id="page-${page.name}">`;
        html += this.buildStepProgressHtml(page);
        
        if (page.elements) {
            page.elements.forEach(element => {
                html += this.generateElementHtml(element);
            });
        }
        
        html += '</div>';
        return html;
    }
    
    generateElementHtml(element) {
        const { type, props = {} } = element;
        let html = '';
        
        switch (type) {
            case 'div':
                const children = element.children ? element.children.map(child => this.generateElementHtml(child)).join('') : '';
                html = `<div class="${props.class || ''}" style="${props.style || ''}">${children}</div>`;
                break;

            case 'row':
                const rowChildren = element.children ? element.children.map(child => this.generateElementHtml(child)).join('') : '';
                html = `<div class="${props.class || ''}" style="display:flex;flex-wrap:wrap;${props.style || ''}">${rowChildren}</div>`;
                break;
                
            case 'text':
                html = `<p class="${props.class || ''}" style="${props.style || ''}">${props.content || ''}</p>`;
                break;
                
            case 'image':
                html = `<img src="${props.src || ''}" class="${props.class || ''}" style="${props.style || ''}" alt="${props.alt || ''}">`;
                break;
                
            case 'button':
                html = `<button class="${props.class || ''}" style="${props.style || ''}" onclick="${this.escapeHtmlAttribute(this.getButtonActionCode(props))}">${props.text || '按钮'}</button>`;
                break;

            case 'form-summary':
                html = this.buildSummaryHtml(props);
                break;

            case 'input':
                const inputLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                html = `<div style="${props.width ? `width:${props.width};` : ''}">${inputLabel}<input type="${props.inputType || 'text'}" data-builder-field="true" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || props.placeholder || '输入框')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'input'}`)}" data-pattern="${this.escapeHtmlAttribute(props.validationPattern || '')}" data-validation-message="${this.escapeHtmlAttribute(props.validationMessage || '')}" class="${props.class || ''}" style="${props.style || ''}" placeholder="${props.placeholder || ''}" value="${props.value || ''}"></div>`;
                break;

            case 'textarea':
                const textareaLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                html = `<div style="${props.width ? `width:${props.width};` : ''}">${textareaLabel}<textarea data-builder-field="true" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || props.placeholder || '文本域')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'textarea'}`)}" data-pattern="${this.escapeHtmlAttribute(props.validationPattern || '')}" data-validation-message="${this.escapeHtmlAttribute(props.validationMessage || '')}" class="${props.class || ''}" style="${props.style || ''}" rows="${props.rows || '4'}" placeholder="${props.placeholder || ''}">${props.value || ''}</textarea></div>`;
                break;

            case 'select':
                const selectLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                const selectOptions = this.parseChoiceOptions(props.options || '');
                const selectMarkup = [`<option value="">${this.escapeHtmlAttribute(props.placeholder || '请选择')}</option>`]
                    .concat(selectOptions.map((option) => `<option value="${this.escapeHtmlAttribute(option.value)}"${String(props.value || '') === option.value ? ' selected' : ''}>${this.escapeHtmlAttribute(option.label)}</option>`))
                    .join('');
                html = `<div style="${props.width ? `width:${props.width};` : ''}">${selectLabel}<select data-builder-field="true" data-field-kind="select" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || props.placeholder || '下拉选择')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'select'}`)}" class="${props.class || 'form-control'}" style="${props.style || ''}">${selectMarkup}</select></div>`;
                break;

            case 'radio-group':
                const radioLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                const radioOptions = this.parseChoiceOptions(props.options || '');
                const radioLayoutClass = props.optionLayout === 'horizontal' ? 'choice-group choice-group-horizontal' : 'choice-group';
                const radioMarkup = radioOptions.length > 0
                    ? radioOptions.map((option) => `<label class="choice-option"><input type="radio" name="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'radio'}`)}" value="${this.escapeHtmlAttribute(option.value)}"${String(props.value || '') === option.value ? ' checked' : ''}> <span>${this.escapeHtmlAttribute(option.label)}</span></label>`).join('')
                    : '<div class="text-muted">请先配置选项</div>';
                html = `<div style="${props.width ? `width:${props.width};` : ''}">${radioLabel}<div data-builder-field="true" data-field-kind="radio-group" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || '单选组')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'radio'}`)}" class="${[radioLayoutClass, props.class || ''].filter(Boolean).join(' ')}" style="${props.style || ''}">${radioMarkup}</div></div>`;
                break;

            case 'checkbox-group':
                const checkboxLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                const checkboxOptions = this.parseChoiceOptions(props.options || '');
                const checkboxValues = String(props.value || '').split(',').map((item) => item.trim()).filter(Boolean);
                const checkboxLayoutClass = props.optionLayout === 'horizontal' ? 'choice-group choice-group-horizontal' : 'choice-group';
                const checkboxMarkup = checkboxOptions.length > 0
                    ? checkboxOptions.map((option) => `<label class="choice-option"><input type="checkbox" value="${this.escapeHtmlAttribute(option.value)}"${checkboxValues.includes(option.value) ? ' checked' : ''}> <span>${this.escapeHtmlAttribute(option.label)}</span></label>`).join('')
                    : '<div class="text-muted">请先配置选项</div>';
                html = `<div style="${props.width ? `width:${props.width};` : ''}">${checkboxLabel}<div data-builder-field="true" data-field-kind="checkbox-group" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || '多选组')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'checkbox'}`)}" class="${[checkboxLayoutClass, props.class || ''].filter(Boolean).join(' ')}" style="${props.style || ''}">${checkboxMarkup}</div></div>`;
                break;

            case 'spacer':
                html = `<div class="${props.class || ''}" style="height:${props.height || '32px'};${props.style || ''}"></div>`;
                break;
                
            case 'form':
                html = `<form class="${props.class || ''}" style="${props.style || ''}">${props.content || ''}</form>`;
                break;
                
            default:
                html = `<!-- 未知元素类型: ${type} -->`;
                break;
        }

        return this.wrapStepHtml(this.wrapConditionalHtml(html, props), props);
    }

    wrapConditionalHtml(html, props = {}) {
        if (!props.conditionEnabled || !props.conditionFieldKey) {
            return html;
        }

        return `<div data-visibility-enabled="1" data-visibility-field="${this.escapeHtmlAttribute(props.conditionFieldKey || '')}" data-visibility-operator="${this.escapeHtmlAttribute(props.conditionOperator || 'equals')}" data-visibility-value="${this.escapeHtmlAttribute(props.conditionValue || '')}">${html}</div>`;
    }

    wrapStepHtml(html, props = {}) {
        const stepIndex = this.parseStepIndex(props.stepIndex || '1');
        return `<div data-step-enabled="1" data-step-index="${stepIndex}" data-step-title="${this.escapeHtmlAttribute(props.stepTitle || '')}">${html}</div>`;
    }
    
    loadPageScripts(page) {
        if (page.scripts) {
            page.scripts.forEach(script => {
                try {
                    eval(script);
                } catch (error) {
                    console.error('脚本执行错误:', error);
                }
            });
        }
    }
    
    updateNavigation(pageName) {
        document.querySelectorAll('.navigation a').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + pageName) {
                link.classList.add('active');
            }
        });
    }
}

// 初始化应用
document.addEventListener('DOMContentLoaded', () => {
    window.app = new App();
});
JS;

        return str_replace(
            ['__PAGE_DATA__', '__PROJECT_TITLE__'],
            [$pageData, $projectTitle],
            $template
        );
    }
    
    private function generatePageHtml($page)
    {
        $elements = $page['elements'] ?? [];
        $html = "<div class=\"page\" id=\"page-{$page['name']}\">\n";
        $html .= $this->buildStepProgressMarkup($page);
        
        foreach ($elements as $element) {
            $html .= $this->generateElementHtml($element);
        }
        
        $html .= "</div>";
        return $html;
    }
    
    private function generatePageCss($page)
    {
        $elements = $page['elements'] ?? [];
        $css = "/* 页面 {$page['name']} 样式 */\n";
        
        foreach ($elements as $element) {
            $css .= $this->generateElementCss($element);
        }
        
        return $css;
    }
    
    private function generatePageJs($page)
    {
        $methods = $page['methods'] ?? [];
        $js = "// 页面 {$page['name']} 脚本\n";
        
        foreach ($methods as $method) {
            $js .= "function {$method['name']}() {\n";
            $js .= "    // {$method['description']}\n";
            $js .= "    console.log('{$method['name']} 被调用');\n";
            $js .= "}\n\n";
        }
        
        return $js;
    }
    
    private function generateElementHtml($element)
    {
        $type = $element['type'];
        $props = $element['props'] ?? [];
        $children = $element['children'] ?? [];
        $html = '';
        
        switch ($type) {
            case 'div':
                $class = $props['class'] ?? 'border-primary bg-white';
                $style = $props['style'] ?? 'min-height: 60px;';
                $childrenHtml = '';
                foreach ($children as $child) {
                    $childrenHtml .= $this->generateElementHtml($child);
                }
                $emptyContent = empty($children) ? '        <div class="text-muted text-sm w-100 text-center py-2">拖拽组件到此处</div>' : '';
                $html = "    <div class='w-100 p-3 border relative {$class}' style='{$style}'>\n  <div class='w-100 mt-4'>\n{$emptyContent}\n{$childrenHtml}        </div>\n    </div>\n";
                break;
                
            case 'row':
                $class = $props['class'] ?? 'border-info bg-white';
                $style = $props['style'] ?? 'min-height: 60px; gap: 8px;';
                $childrenHtml = '';
                foreach ($children as $child) {
                    $childrenHtml .= $this->generateElementHtml($child);
                }
                $emptyContent = empty($children) ? '        <div class="text-muted text-sm w-100 text-center py-2">拖拽组件到此处</div>' : '';
                $html = "    <div class='w-100 d-flex flex-wrap p-3 border relative {$class}' style='display:flex; flex-wrap:wrap; {$style}'>\n{$emptyContent}\n{$childrenHtml}    </div>\n";
                break;
                
            case 'text':
                $content = $props['content'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $html = "    <p class=\"{$class}\" style=\"{$style}\">{$content}</p>\n";
                break;
                
            case 'image':
                $src = $props['src'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $alt = $props['alt'] ?? '';
                $html = "    <img src=\"{$src}\" class=\"{$class}\" style=\"{$style}\" alt=\"{$alt}\">\n";
                break;
                
            case 'button':
                $text = $props['text'] ?? '按钮';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $onclick = htmlspecialchars($this->resolveButtonActionCode($props), ENT_QUOTES, 'UTF-8');
                $html = "    <button class=\"{$class}\" style=\"{$style}\" onclick=\"{$onclick}\">{$text}</button>\n";
                break;

            case 'form-summary':
                $html = $this->buildSummaryMarkup($props);
                break;

            case 'input':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<span style="color:#c2410c;"> *</span>' : '';
                $placeholder = $props['placeholder'] ?? '';
                $value = $props['value'] ?? '';
                $class = $props['class'] ?? 'form-control';
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $inputType = htmlspecialchars($props['inputType'] ?? 'text', ENT_QUOTES, 'UTF-8');
                $fieldKey = htmlspecialchars($props['fieldKey'] ?? ('field_' . preg_replace('/[^\w-]+/', '_', (string) ($element['id'] ?? 'input'))), ENT_QUOTES, 'UTF-8');
                $fieldLabel = htmlspecialchars($label ?: ($placeholder ?: '输入框'), ENT_QUOTES, 'UTF-8');
                $validationPattern = htmlspecialchars($props['validationPattern'] ?? '', ENT_QUOTES, 'UTF-8');
                $validationMessage = htmlspecialchars($props['validationMessage'] ?? '', ENT_QUOTES, 'UTF-8');
                $wrapperStyle = $width ? "width: {$width};" : '';
                $labelHtml = $label ? "    <label style=\"display:block;margin-bottom:6px;font-weight:600;\">{$label}{$required}</label>\n" : '';
                $requiredAttr = !empty($props['required']) ? '1' : '0';
                $html = "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <input type=\"{$inputType}\" data-builder-field=\"true\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" data-pattern=\"{$validationPattern}\" data-validation-message=\"{$validationMessage}\" class=\"{$class}\" style=\"{$style}\" placeholder=\"{$placeholder}\" value=\"{$value}\">\n    </div>\n";
                break;

            case 'textarea':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<span style="color:#c2410c;"> *</span>' : '';
                $placeholder = $props['placeholder'] ?? '';
                $value = $props['value'] ?? '';
                $rows = $props['rows'] ?? '4';
                $class = $props['class'] ?? 'form-control';
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = htmlspecialchars($props['fieldKey'] ?? ('field_' . preg_replace('/[^\w-]+/', '_', (string) ($element['id'] ?? 'textarea'))), ENT_QUOTES, 'UTF-8');
                $fieldLabel = htmlspecialchars($label ?: ($placeholder ?: '文本域'), ENT_QUOTES, 'UTF-8');
                $validationPattern = htmlspecialchars($props['validationPattern'] ?? '', ENT_QUOTES, 'UTF-8');
                $validationMessage = htmlspecialchars($props['validationMessage'] ?? '', ENT_QUOTES, 'UTF-8');
                $wrapperStyle = $width ? "width: {$width};" : '';
                $labelHtml = $label ? "    <label style=\"display:block;margin-bottom:6px;font-weight:600;\">{$label}{$required}</label>\n" : '';
                $requiredAttr = !empty($props['required']) ? '1' : '0';
                $html = "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <textarea data-builder-field=\"true\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" data-pattern=\"{$validationPattern}\" data-validation-message=\"{$validationMessage}\" class=\"{$class}\" style=\"{$style}\" rows=\"{$rows}\" placeholder=\"{$placeholder}\">{$value}</textarea>\n    </div>\n";
                break;

            case 'select':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<span style="color:#c2410c;"> *</span>' : '';
                $placeholder = htmlspecialchars($props['placeholder'] ?? '请选择', ENT_QUOTES, 'UTF-8');
                $class = $props['class'] ?? 'form-control';
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = htmlspecialchars($props['fieldKey'] ?? ('field_' . preg_replace('/[^\w-]+/', '_', (string) ($element['id'] ?? 'select'))), ENT_QUOTES, 'UTF-8');
                $fieldLabel = htmlspecialchars($label ?: ($props['placeholder'] ?? '下拉选择'), ENT_QUOTES, 'UTF-8');
                $wrapperStyle = $width ? "width: {$width};" : '';
                $labelHtml = $label ? "    <label style=\"display:block;margin-bottom:6px;font-weight:600;\">{$label}{$required}</label>\n" : '';
                $requiredAttr = !empty($props['required']) ? '1' : '0';
                $optionsHtml = $this->buildSelectOptionsHtml($props['options'] ?? '', $props['value'] ?? '', $placeholder);
                $html = "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <select data-builder-field=\"true\" data-field-kind=\"select\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" class=\"{$class}\" style=\"{$style}\">{$optionsHtml}</select>\n    </div>\n";
                break;

            case 'radio-group':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<span style="color:#c2410c;"> *</span>' : '';
                $class = trim(((($props['optionLayout'] ?? 'vertical') === 'horizontal') ? 'choice-group choice-group-horizontal ' : 'choice-group ') . ($props['class'] ?? ''));
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = htmlspecialchars($props['fieldKey'] ?? ('field_' . preg_replace('/[^\w-]+/', '_', (string) ($element['id'] ?? 'radio'))), ENT_QUOTES, 'UTF-8');
                $fieldLabel = htmlspecialchars($label ?: '单选组', ENT_QUOTES, 'UTF-8');
                $wrapperStyle = $width ? "width: {$width};" : '';
                $labelHtml = $label ? "    <label style=\"display:block;margin-bottom:6px;font-weight:600;\">{$label}{$required}</label>\n" : '';
                $requiredAttr = !empty($props['required']) ? '1' : '0';
                $optionsHtml = $this->buildChoiceGroupHtml('radio', $props['options'] ?? '', $props['value'] ?? '', $fieldKey);
                $html = "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <div data-builder-field=\"true\" data-field-kind=\"radio-group\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" class=\"{$class}\" style=\"{$style}\">{$optionsHtml}</div>\n    </div>\n";
                break;

            case 'checkbox-group':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<span style="color:#c2410c;"> *</span>' : '';
                $class = trim(((($props['optionLayout'] ?? 'vertical') === 'horizontal') ? 'choice-group choice-group-horizontal ' : 'choice-group ') . ($props['class'] ?? ''));
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = htmlspecialchars($props['fieldKey'] ?? ('field_' . preg_replace('/[^\w-]+/', '_', (string) ($element['id'] ?? 'checkbox'))), ENT_QUOTES, 'UTF-8');
                $fieldLabel = htmlspecialchars($label ?: '多选组', ENT_QUOTES, 'UTF-8');
                $wrapperStyle = $width ? "width: {$width};" : '';
                $labelHtml = $label ? "    <label style=\"display:block;margin-bottom:6px;font-weight:600;\">{$label}{$required}</label>\n" : '';
                $requiredAttr = !empty($props['required']) ? '1' : '0';
                $optionsHtml = $this->buildChoiceGroupHtml('checkbox', $props['options'] ?? '', $props['value'] ?? '', $fieldKey);
                $html = "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <div data-builder-field=\"true\" data-field-kind=\"checkbox-group\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" class=\"{$class}\" style=\"{$style}\">{$optionsHtml}</div>\n    </div>\n";
                break;

            case 'spacer':
                $height = $props['height'] ?? '32px';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $html = "    <div class=\"{$class}\" style=\"height: {$height}; {$style}\"></div>\n";
                break;
                
            case 'form':
                $content = $props['content'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $html = "    <form class=\"{$class}\" style=\"{$style}\">\n        {$content}\n    </form>\n";
                break;
                
            default:
                $html = "    <!-- 未知元素类型: {$type} -->\n";
                break;
        }

        return $this->wrapConditionalHtml($html, $props);
    }
    
    private function generateElementCss($element)
    {
        $type = $element['type'];
        $props = $element['props'] ?? [];
        $style = $props['style'] ?? '';
        
        if (empty($style)) {
            return '';
        }
        
        $selector = ".{$type}-" . uniqid();
        return "{$selector} {\n  {$style}\n}\n\n";
    }

    private function resolveButtonActionCode($props)
    {
        $actionType = $props['actionType'] ?? 'none';
        $actionValue = $props['actionValue'] ?? '';

        if ($actionType === 'message') {
            $message = $actionValue ?: '操作成功';
            return 'alert(' . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ')';
        }

        if ($actionType === 'link') {
            $target = $actionValue ?: '#';
            return 'window.location.href=' . json_encode($target, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($actionType === 'step-prev') {
            return "window.builderStepAction(this,'prev')";
        }

        if ($actionType === 'step-next') {
            return "window.builderStepAction(this,'next')";
        }

        if ($actionType === 'submit') {
            $message = $actionValue ?: '提交成功';
            $config = [
                'successMessage' => $message,
                'submitEndpoint' => $props['submitEndpoint'] ?? '',
                'submitMethod' => $props['submitMethod'] ?? 'POST',
                'resetForm' => !empty($props['submitResetForm']),
                'redirectUrl' => $props['submitRedirectUrl'] ?? '',
            ];

            return 'window.builderSubmitAction(this,' . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ')';
        }

        return $props['onclick'] ?? '';
    }

    private function wrapConditionalHtml(string $html, array $props): string
    {
        if (empty($props['conditionEnabled']) || empty($props['conditionFieldKey'])) {
            return $this->wrapStepHtml($html, $props);
        }

        $fieldKey = htmlspecialchars((string) ($props['conditionFieldKey'] ?? ''), ENT_QUOTES, 'UTF-8');
        $operator = htmlspecialchars((string) ($props['conditionOperator'] ?? 'equals'), ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars((string) ($props['conditionValue'] ?? ''), ENT_QUOTES, 'UTF-8');

        $wrappedHtml = "    <div data-visibility-enabled=\"1\" data-visibility-field=\"{$fieldKey}\" data-visibility-operator=\"{$operator}\" data-visibility-value=\"{$value}\">\n{$html}    </div>\n";
        return $this->wrapStepHtml($wrappedHtml, $props);
    }

    private function wrapStepHtml(string $html, array $props): string
    {
        $stepIndex = $this->resolveStepIndex($props);
        $stepTitle = htmlspecialchars((string) ($props['stepTitle'] ?? ''), ENT_QUOTES, 'UTF-8');
        return "    <div data-step-enabled=\"1\" data-step-index=\"{$stepIndex}\" data-step-title=\"{$stepTitle}\">\n{$html}    </div>\n";
    }

    private function buildSummaryMarkup(array $props): string
    {
        $class = htmlspecialchars((string) ($props['class'] ?? ''), ENT_QUOTES, 'UTF-8');
        $style = htmlspecialchars((((string) ($props['width'] ?? '')) !== '' ? ('width:' . (string) $props['width'] . ';') : '') . (string) ($props['style'] ?? ''), ENT_QUOTES, 'UTF-8');
        $summaryTitle = htmlspecialchars((string) ($props['summaryTitle'] ?? '请确认以下信息'), ENT_QUOTES, 'UTF-8');
        $emptyText = htmlspecialchars((string) ($props['emptyText'] ?? '当前还没有可汇总的表单字段'), ENT_QUOTES, 'UTF-8');

        return "    <div class=\"builder-form-summary {$class}\" style=\"{$style}\" data-summary-enabled=\"1\">\n        <div class=\"builder-form-summary-head\"><strong>{$summaryTitle}</strong><span data-summary-count>正在汇总</span></div>\n        <div class=\"builder-form-summary-list\" data-summary-list></div>\n        <div class=\"builder-form-summary-empty\" data-summary-empty-state>{$emptyText}</div>\n    </div>\n";
    }

    private function resolveStepIndex(array $props): int
    {
        $stepIndex = (int) ($props['stepIndex'] ?? 1);
        return $stepIndex > 0 ? $stepIndex : 1;
    }

    private function buildStepDefinitions(array $elements, array &$definitions = []): array
    {
        foreach ($elements as $element) {
            $props = $element['props'] ?? [];
            $stepIndex = $this->resolveStepIndex($props);

            if (!isset($definitions[$stepIndex])) {
                $definitions[$stepIndex] = [
                    'index' => $stepIndex,
                    'title' => '',
                ];
            }

            if ($definitions[$stepIndex]['title'] === '' && !empty($props['stepTitle'])) {
                $definitions[$stepIndex]['title'] = trim((string) $props['stepTitle']);
            }

            if (!empty($element['children']) && is_array($element['children'])) {
                $this->buildStepDefinitions($element['children'], $definitions);
            }
        }

        ksort($definitions);
        return array_values($definitions);
    }

    private function buildStepProgressMarkup(array $page): string
    {
        $definitions = $this->buildStepDefinitions($page['elements'] ?? []);

        if (count($definitions) <= 1 && (($definitions[0]['index'] ?? 1) === 1)) {
            return '';
        }

        $itemsHtml = '';
        foreach ($definitions as $definition) {
            $index = (int) ($definition['index'] ?? 1);
            $title = trim((string) ($definition['title'] ?? ''));
            $label = $title !== '' ? "第{$index}步·{$title}" : "第{$index}步";
            $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $itemsHtml .= "        <div class=\"builder-step-item\" data-step-item=\"{$index}\"><span class=\"builder-step-indicator\">{$index}</span><span class=\"builder-step-text\">{$safeLabel}</span></div>\n";
        }

        return "    <div class=\"builder-step-progress\" data-step-progress=\"1\">\n        <div class=\"builder-step-head\"><strong>分步表单</strong><span data-step-summary></span></div>\n        <div class=\"builder-step-track\">\n{$itemsHtml}        </div>\n    </div>\n";
    }

    private function buildSelectOptionsHtml($rawOptions, $selectedValue, $placeholder): string
    {
        $optionsHtml = '<option value="">' . $placeholder . '</option>';

        foreach ($this->parseChoiceOptions($rawOptions) as $option) {
            $value = htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8');
            $selected = (string) $selectedValue === (string) $option['value'] ? ' selected' : '';
            $optionsHtml .= "<option value=\"{$value}\"{$selected}>{$label}</option>";
        }

        return $optionsHtml;
    }

    private function buildChoiceGroupHtml(string $inputType, $rawOptions, $rawValue, string $fieldKey): string
    {
        $selectedValues = $this->parseChoiceValues($rawValue);
        $type = $inputType === 'radio' ? 'radio' : 'checkbox';
        $name = htmlspecialchars($fieldKey, ENT_QUOTES, 'UTF-8');
        $html = '';

        foreach ($this->parseChoiceOptions($rawOptions) as $option) {
            $value = htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($option['label'], ENT_QUOTES, 'UTF-8');
            $checked = in_array((string) $option['value'], $selectedValues, true) ? ' checked' : '';
            $inputName = $type === 'radio' ? " name=\"{$name}\"" : '';
            $html .= "<label class=\"choice-option\"><input type=\"{$type}\"{$inputName} value=\"{$value}\"{$checked}> <span>{$label}</span></label>";
        }

        if ($html === '') {
            return '<div class="text-muted">请先配置选项</div>';
        }

        return $html;
    }

    private function parseChoiceOptions($rawOptions): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $rawOptions) ?: [];
        $options = [];

        foreach ($lines as $index => $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $segments = explode('|', $line, 2);
            $value = trim((string) ($segments[0] ?? ''));
            $label = trim((string) ($segments[1] ?? ($segments[0] ?? '')));

            if ($value === '') {
                $value = 'option_' . ($index + 1);
            }

            if ($label === '') {
                $label = $value;
            }

            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    private function parseChoiceValues($rawValue): array
    {
        $values = array_map('trim', explode(',', (string) $rawValue));
        return array_values(array_filter($values, static fn ($value) => $value !== ''));
    }

    private function resolveTheme(array $theme): array
    {
        return [
            'primary' => $theme['primary'] ?? '#0f766e',
            'accent' => $theme['accent'] ?? '#f59e0b',
            'surface' => $theme['surface'] ?? '#ffffff',
            'pageBackground' => $theme['pageBackground'] ?? '#f4f7f2',
            'text' => $theme['text'] ?? '#16302b',
            'radius' => $theme['radius'] ?? '18px',
        ];
    }
    
    private function generateComponentHtml($component)
    {
        return $this->generatePageHtml($component);
    }
    
    private function generateComponentCss($component)
    {
        return $this->generatePageCss($component);
    }
    
    private function generateComponentJs($component)
    {
        return $this->generatePageJs($component);
    }
}
