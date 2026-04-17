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
        
        $template = <<<'JS'
// 主应用逻辑
class App {
    constructor() {
        this.currentPage = null;
        this.pages = __PAGE_DATA__;
        this.init();
    }
    
    init() {
        this.bindEvents();
        window.builderSubmitAction = (trigger, config = {}) => this.handleSubmitAction(trigger, config);
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

        if (actionType === 'submit') {
            return `window.builderSubmitAction(this, ${JSON.stringify({
                successMessage: actionValue || '提交成功',
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

    handleSubmitAction(trigger, config = {}) {
        const scope = trigger.closest('.page') || document;
        const fields = Array.from(scope.querySelectorAll('[data-builder-field="true"]'));
        const invalidField = fields.find((field) => this.validateField(field));

        if (invalidField) {
            const message = this.validateField(invalidField);
            alert(message || '表单校验失败');
            this.focusField(invalidField);
            return false;
        }

        const formData = {};
        fields.forEach((field) => {
            const fieldKey = field.dataset.fieldKey || field.name || `field_${Object.keys(formData).length + 1}`;
            formData[fieldKey] = this.getFieldValue(field);
        });

        console.log('builder form submit', formData);

        if (config.resetForm) {
            fields.forEach((field) => {
                this.resetField(field);
            });
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
    }
    
    generatePageHtml(page) {
        let html = `<div class="page" id="page-${page.name}">`;
        
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
        
        switch (type) {
            case 'div':
                const children = element.children ? element.children.map(child => this.generateElementHtml(child)).join('') : '';
                return `<div class="${props.class || ''}" style="${props.style || ''}">${children}</div>`;

            case 'row':
                const rowChildren = element.children ? element.children.map(child => this.generateElementHtml(child)).join('') : '';
                return `<div class="${props.class || ''}" style="display:flex;flex-wrap:wrap;${props.style || ''}">${rowChildren}</div>`;
                
            case 'text':
                return `<p class="${props.class || ''}" style="${props.style || ''}">${props.content || ''}</p>`;
                
            case 'image':
                return `<img src="${props.src || ''}" class="${props.class || ''}" style="${props.style || ''}" alt="${props.alt || ''}">`;
                
            case 'button':
                return `<button class="${props.class || ''}" style="${props.style || ''}" onclick="${this.escapeHtmlAttribute(this.getButtonActionCode(props))}">${props.text || '按钮'}</button>`;

            case 'input':
                const inputLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                return `<div style="${props.width ? `width:${props.width};` : ''}">${inputLabel}<input type="${props.inputType || 'text'}" data-builder-field="true" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || props.placeholder || '输入框')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'input'}`)}" data-pattern="${this.escapeHtmlAttribute(props.validationPattern || '')}" data-validation-message="${this.escapeHtmlAttribute(props.validationMessage || '')}" class="${props.class || ''}" style="${props.style || ''}" placeholder="${props.placeholder || ''}" value="${props.value || ''}"></div>`;

            case 'textarea':
                const textareaLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                return `<div style="${props.width ? `width:${props.width};` : ''}">${textareaLabel}<textarea data-builder-field="true" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || props.placeholder || '文本域')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'textarea'}`)}" data-pattern="${this.escapeHtmlAttribute(props.validationPattern || '')}" data-validation-message="${this.escapeHtmlAttribute(props.validationMessage || '')}" class="${props.class || ''}" style="${props.style || ''}" rows="${props.rows || '4'}" placeholder="${props.placeholder || ''}">${props.value || ''}</textarea></div>`;

            case 'select':
                const selectLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                const selectOptions = this.parseChoiceOptions(props.options || '');
                const selectMarkup = [`<option value="">${this.escapeHtmlAttribute(props.placeholder || '请选择')}</option>`]
                    .concat(selectOptions.map((option) => `<option value="${this.escapeHtmlAttribute(option.value)}"${String(props.value || '') === option.value ? ' selected' : ''}>${this.escapeHtmlAttribute(option.label)}</option>`))
                    .join('');
                return `<div style="${props.width ? `width:${props.width};` : ''}">${selectLabel}<select data-builder-field="true" data-field-kind="select" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || props.placeholder || '下拉选择')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'select'}`)}" class="${props.class || 'form-control'}" style="${props.style || ''}">${selectMarkup}</select></div>`;

            case 'radio-group':
                const radioLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                const radioOptions = this.parseChoiceOptions(props.options || '');
                const radioLayoutClass = props.optionLayout === 'horizontal' ? 'choice-group choice-group-horizontal' : 'choice-group';
                const radioMarkup = radioOptions.length > 0
                    ? radioOptions.map((option) => `<label class="choice-option"><input type="radio" name="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'radio'}`)}" value="${this.escapeHtmlAttribute(option.value)}"${String(props.value || '') === option.value ? ' checked' : ''}> <span>${this.escapeHtmlAttribute(option.label)}</span></label>`).join('')
                    : '<div class="text-muted">请先配置选项</div>';
                return `<div style="${props.width ? `width:${props.width};` : ''}">${radioLabel}<div data-builder-field="true" data-field-kind="radio-group" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || '单选组')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'radio'}`)}" class="${[radioLayoutClass, props.class || ''].filter(Boolean).join(' ')}" style="${props.style || ''}">${radioMarkup}</div></div>`;

            case 'checkbox-group':
                const checkboxLabel = props.label ? `<label style="display:block;margin-bottom:6px;font-weight:600;">${props.label}${props.required ? '<span style="color:#c2410c;"> *</span>' : ''}</label>` : '';
                const checkboxOptions = this.parseChoiceOptions(props.options || '');
                const checkboxValues = String(props.value || '').split(',').map((item) => item.trim()).filter(Boolean);
                const checkboxLayoutClass = props.optionLayout === 'horizontal' ? 'choice-group choice-group-horizontal' : 'choice-group';
                const checkboxMarkup = checkboxOptions.length > 0
                    ? checkboxOptions.map((option) => `<label class="choice-option"><input type="checkbox" value="${this.escapeHtmlAttribute(option.value)}"${checkboxValues.includes(option.value) ? ' checked' : ''}> <span>${this.escapeHtmlAttribute(option.label)}</span></label>`).join('')
                    : '<div class="text-muted">请先配置选项</div>';
                return `<div style="${props.width ? `width:${props.width};` : ''}">${checkboxLabel}<div data-builder-field="true" data-field-kind="checkbox-group" data-required="${props.required ? '1' : '0'}" data-label="${this.escapeHtmlAttribute(props.label || '多选组')}" data-field-key="${this.escapeHtmlAttribute(props.fieldKey || `field_${element.id || 'checkbox'}`)}" class="${[checkboxLayoutClass, props.class || ''].filter(Boolean).join(' ')}" style="${props.style || ''}">${checkboxMarkup}</div></div>`;

            case 'spacer':
                return `<div class="${props.class || ''}" style="height:${props.height || '32px'};${props.style || ''}"></div>`;
                
            case 'form':
                return `<form class="${props.class || ''}" style="${props.style || ''}">${props.content || ''}</form>`;
                
            default:
                return `<!-- 未知元素类型: ${type} -->`;
        }
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

        return str_replace('__PAGE_DATA__', $pageData, $template);
    }
    
    private function generatePageHtml($page)
    {
        $elements = $page['elements'] ?? [];
        $html = "<div class=\"page\" id=\"page-{$page['name']}\">\n";
        
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
        
        switch ($type) {
            case 'div':
                $class = $props['class'] ?? 'border-primary bg-white';
                $style = $props['style'] ?? 'min-height: 60px;';
                $childrenHtml = '';
                foreach ($children as $child) {
                    $childrenHtml .= $this->generateElementHtml($child);
                }
                $emptyContent = empty($children) ? '        <div class="text-muted text-sm w-100 text-center py-2">拖拽组件到此处</div>' : '';
                return "    <div class='w-100 p-3 border relative {$class}' style='{$style}'>\n  <div class='w-100 mt-4'>\n{$emptyContent}\n{$childrenHtml}        </div>\n    </div>\n";
                
            case 'row':
                $class = $props['class'] ?? 'border-info bg-white';
                $style = $props['style'] ?? 'min-height: 60px; gap: 8px;';
                $childrenHtml = '';
                foreach ($children as $child) {
                    $childrenHtml .= $this->generateElementHtml($child);
                }
                $emptyContent = empty($children) ? '        <div class="text-muted text-sm w-100 text-center py-2">拖拽组件到此处</div>' : '';
                return "    <div class='w-100 d-flex flex-wrap p-3 border relative {$class}' style='display:flex; flex-wrap:wrap; {$style}'>\n{$emptyContent}\n{$childrenHtml}    </div>\n";
                
            case 'text':
                $content = $props['content'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "    <p class=\"{$class}\" style=\"{$style}\">{$content}</p>\n";
                
            case 'image':
                $src = $props['src'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $alt = $props['alt'] ?? '';
                return "    <img src=\"{$src}\" class=\"{$class}\" style=\"{$style}\" alt=\"{$alt}\">\n";
                
            case 'button':
                $text = $props['text'] ?? '按钮';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $onclick = htmlspecialchars($this->resolveButtonActionCode($props), ENT_QUOTES, 'UTF-8');
                return "    <button class=\"{$class}\" style=\"{$style}\" onclick=\"{$onclick}\">{$text}</button>\n";

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
                return "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <input type=\"{$inputType}\" data-builder-field=\"true\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" data-pattern=\"{$validationPattern}\" data-validation-message=\"{$validationMessage}\" class=\"{$class}\" style=\"{$style}\" placeholder=\"{$placeholder}\" value=\"{$value}\">\n    </div>\n";

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
                return "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <textarea data-builder-field=\"true\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" data-pattern=\"{$validationPattern}\" data-validation-message=\"{$validationMessage}\" class=\"{$class}\" style=\"{$style}\" rows=\"{$rows}\" placeholder=\"{$placeholder}\">{$value}</textarea>\n    </div>\n";

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
                return "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <select data-builder-field=\"true\" data-field-kind=\"select\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" class=\"{$class}\" style=\"{$style}\">{$optionsHtml}</select>\n    </div>\n";

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
                return "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <div data-builder-field=\"true\" data-field-kind=\"radio-group\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" class=\"{$class}\" style=\"{$style}\">{$optionsHtml}</div>\n    </div>\n";

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
                return "    <div style=\"{$wrapperStyle}\">\n{$labelHtml}    <div data-builder-field=\"true\" data-field-kind=\"checkbox-group\" data-required=\"{$requiredAttr}\" data-label=\"{$fieldLabel}\" data-field-key=\"{$fieldKey}\" class=\"{$class}\" style=\"{$style}\">{$optionsHtml}</div>\n    </div>\n";

            case 'spacer':
                $height = $props['height'] ?? '32px';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "    <div class=\"{$class}\" style=\"height: {$height}; {$style}\"></div>\n";
                
            case 'form':
                $content = $props['content'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "    <form class=\"{$class}\" style=\"{$style}\">\n        {$content}\n    </form>\n";
                
            default:
                return "    <!-- 未知元素类型: {$type} -->\n";
        }
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

        if ($actionType === 'submit') {
            $message = $actionValue ?: '提交成功';
            $config = [
                'successMessage' => $message,
                'resetForm' => !empty($props['submitResetForm']),
                'redirectUrl' => $props['submitRedirectUrl'] ?? '',
            ];

            return 'window.builderSubmitAction(this,' . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ')';
        }

        return $props['onclick'] ?? '';
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
