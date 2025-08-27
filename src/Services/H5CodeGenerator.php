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
            $previewCode .= $this->generatePageHtml($page);
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
        return <<<CSS
/* 全局样式 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f5f5f5;
}

.navigation {
    background: #fff;
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
    color: #007bff;
}

.main-content {
    margin-top: 60px;
    padding: 20px;
}

.page {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        
        return <<<JS
// 主应用逻辑
class App {
    constructor() {
        this.currentPage = null;
        this.pages = {$pageData};
        this.init();
    }
    
    init() {
        this.bindEvents();
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
        let html = `<div class=\"page\" id=\"page-${page.name}\">`;
        
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
                return `<div class=\"${props.class || ''}\" style=\"${props.style || ''}\">${children}</div>`;
                
            case 'text':
                return `<p class=\"${props.class || ''}\" style=\"${props.style || ''}\">${props.content || ''}</p>`;
                
            case 'image':
                return `<img src=\"${props.src || ''}\" class=\"${props.class || ''}\" style=\"${props.style || ''}\" alt=\"${props.alt || ''}\">`;
                
            case 'button':
                return `<button class=\"${props.class || ''}\" style=\"${props.style || ''}\" onclick=\"${props.onclick || ''}\">${props.text || '按钮'}</button>`;
                
            case 'form':
                return `<form class=\"${props.class || ''}\" style=\"${props.style || ''}\">${props.content || ''}</form>`;
                
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
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $childrenHtml = '';
                foreach ($children as $child) {
                    $childrenHtml .= $this->generateElementHtml($child);
                }
                return "    <div class=\"{$class}\" style=\"{$style}\">\n{$childrenHtml}    </div>\n";
                
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
                $onclick = $props['onclick'] ?? '';
                return "    <button class=\"{$class}\" style=\"{$style}\" onclick=\"{$onclick}\">{$text}</button>\n";
                
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
