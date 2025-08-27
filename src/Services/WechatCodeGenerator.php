<?php

namespace App\Services;

class WechatCodeGenerator
{
    public function generate($config)
    {
        $pages = $config['pages'] ?? [];
        $components = $config['components'] ?? [];
        
        $appJs = $this->generateAppJs($config);
        $appJson = $this->generateAppJson($config);
        $appWxss = $this->generateAppWxss($config);
        
        $pageFiles = [];
        foreach ($pages as $page) {
            $pageFiles[] = [
                'name' => $page['name'],
                'js' => $this->generatePageJs($page),
                'wxml' => $this->generatePageWxml($page),
                'wxss' => $this->generatePageWxss($page),
                'json' => $this->generatePageJson($page)
            ];
        }
        
        $componentFiles = [];
        foreach ($components as $component) {
            $componentFiles[] = [
                'name' => $component['name'],
                'js' => $this->generatePageJs($component),
                'wxml' => $this->generatePageWxml($component),
                'wxss' => $this->generatePageWxss($component),
                'json' => $this->generateComponentJson($component)
            ];
        }
        
        return [
            'app.js' => $appJs,
            'app.json' => $appJson,
            'app.wxss' => $appWxss,
            'pages' => $pageFiles,
            'components' => $componentFiles
        ];
    }
    
    public function generatePreview($config)
    {
        // 生成预览用的简化代码
        $pages = $config['pages'] ?? [];
        $previewCode = '';
        
        foreach ($pages as $page) {
            $previewCode .= $this->generatePageWxml($page);
        }
        
        return $previewCode;
    }
    
    private function generateAppJs($config)
    {
        return 'App({
  globalData: {
    userInfo: null
  },
  onLaunch() {
    console.log("小程序启动");
  }
})';
    }
    
    private function generateAppJson($config)
    {
        $pages = $config['pages'] ?? [];
        $pagePaths = array_map(function($page) {
            return 'pages/' . $page['name'] . '/' . $page['name'];
        }, $pages);
        
        return json_encode([
            'pages' => $pagePaths,
            'window' => [
                'backgroundTextStyle' => 'light',
                'navigationBarBackgroundColor' => '#fff',
                'navigationBarTitleText' => $config['title'] ?? '我的小程序',
                'navigationBarTextStyle' => 'black'
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    private function generateAppWxss($config)
    {
        return '/* 全局样式 */
page {
  background-color: #f6f6f6;
  font-size: 16px;
  font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, Segoe UI, Arial, Roboto, "PingFang SC", "miui", "Hiragino Sans GB", "Microsoft Yahei", sans-serif;
}';
    }
    
    private function generatePageJs($page)
    {
        $data = $page['data'] ?? [];
        $methods = $page['methods'] ?? [];
        
        $dataStr = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $methodsStr = '';
        
        foreach ($methods as $method) {
            $methodsStr .= "  {$method['name']}() {\n    // {$method['description']}\n  },\n";
        }
        
        return "Page({
  data: {$dataStr},
  
{$methodsStr}
  onLoad() {
    console.log('页面加载');
  }
})";
    }
    
    private function generatePageWxml($page)
    {
        $elements = $page['elements'] ?? [];
        $wxml = '';
        
        foreach ($elements as $element) {
            $wxml .= $this->generateElementWxml($element);
        }
        
        return $wxml;
    }
    
    private function generatePageWxss($page)
    {
        $elements = $page['elements'] ?? [];
        $wxss = '';
        
        foreach ($elements as $element) {
            $wxss .= $this->generateElementWxss($element);
        }
        
        return $wxss;
    }
    
    private function generatePageJson($page)
    {
        return json_encode([
            'navigationBarTitleText' => $page['title'] ?? '页面',
            'usingComponents' => []
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    private function generateElementWxml($element)
    {
        $type = $element['type'];
        $props = $element['props'] ?? [];
        $children = $element['children'] ?? [];
        
        switch ($type) {
            case 'view':
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                $childrenWxml = '';
                foreach ($children as $child) {
                    $childrenWxml .= $this->generateElementWxml($child);
                }
                return "<view class=\"{$class}\" style=\"{$style}\">{$childrenWxml}</view>\n";
                
            case 'text':
                $content = $props['content'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "<text class=\"{$class}\" style=\"{$style}\">{$content}</text>\n";
                
            case 'image':
                $src = $props['src'] ?? '';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "<image src=\"{$src}\" class=\"{$class}\" style=\"{$style}\" mode=\"aspectFit\"></image>\n";
                
            case 'button':
                $text = $props['text'] ?? '按钮';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "<button class=\"{$class}\" style=\"{$style}\">{$text}</button>\n";
                
            default:
                return "<!-- 未知元素类型: {$type} -->\n";
        }
    }
    
    private function generateElementWxss($element)
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
    
    private function generateComponentJs($component)
    {
        $data = $component['data'] ?? [];
        $methods = $component['methods'] ?? [];
        
        $dataStr = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $methodsStr = '';
        
        foreach ($methods as $method) {
            $methodsStr .= "  {$method['name']}() {\n    // {$method['description']}\n  },\n";
        }
        
        return "Component({
  properties: {},
  data: {$dataStr},
  
{$methodsStr}
  methods: {}
})";
    }
    
    private function generateComponentWxml($component)
    {
        return $this->generatePageWxml($component);
    }
    
    private function generateComponentWxss($component)
    {
        return $this->generatePageWxss($component);
    }
    
    private function generateComponentJson($component)
    {
        return json_encode([
            'component' => true,
            'usingComponents' => []
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
