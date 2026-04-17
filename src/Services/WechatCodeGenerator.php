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
                'js' => $this->generatePageJs($page, $config),
                'wxml' => $this->generatePageWxml($page),
                'wxss' => $this->generatePageWxss($page),
                'json' => $this->generatePageJson($page)
            ];
        }
        
        $componentFiles = [];
        foreach ($components as $component) {
            $componentFiles[] = [
                'name' => $component['name'],
                'js' => $this->generatePageJs($component, $config),
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
        $theme = $this->resolveTheme($config['theme'] ?? []);
        $primary = $theme['primary'];
        $accent = $theme['accent'];
        $surface = $theme['surface'];
        $pageBackground = $theme['pageBackground'];
        $text = $theme['text'];
        $radius = $theme['radius'];

        return "/* 全局样式 */
page {
  background-color: {$pageBackground};
  font-size: 16px;
  color: {$text};
  font-family: -apple-system, BlinkMacSystemFont, \"Helvetica Neue\", Helvetica, Segoe UI, Arial, Roboto, \"PingFang SC\", \"miui\", \"Hiragino Sans GB\", \"Microsoft Yahei\", sans-serif;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 20rpx 32rpx;
  border-radius: {$radius};
  border: 2rpx solid transparent;
  font-size: 28rpx;
  line-height: 1.2;
}

.btn-primary {
  background: {$primary};
  color: #ffffff;
  border-color: {$primary};
}

.btn-outline-primary {
  background: transparent;
  color: {$primary};
  border-color: {$primary};
}

.btn-success {
  background: {$accent};
  color: #ffffff;
  border-color: {$accent};
}

.btn-dark {
  background: {$text};
  color: #ffffff;
  border-color: {$text};
}

.btn-light {
  background: #ffffff;
  color: {$primary};
  border-color: #ffffff;
}

.btn-outline-light {
  background: transparent;
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.9);
}

.form-control {
  width: 100%;
  padding: 20rpx 24rpx;
  border: 2rpx solid #cfd8d4;
  border-radius: {$radius};
  background: {$surface};
  color: {$text};
  min-height: 84rpx;
}

.choice-group {
  display: flex;
  flex-direction: column;
  gap: 20rpx;
  padding: 20rpx 24rpx;
  border: 2rpx solid #d7e2d6;
  border-radius: {$radius};
  background: {$surface};
}

.choice-group-horizontal {
  flex-direction: row;
  flex-wrap: wrap;
  gap: 20rpx 28rpx;
}

.choice-option {
  display: inline-flex;
  align-items: center;
  gap: 12rpx;
}

.picker-display {
  display: flex;
  align-items: center;
}";
    }
    
    private function generatePageJs($page, array $config = [])
    {
        $data = $page['data'] ?? [];
        if (!is_array($data)) {
            $data = [];
        }

        $formSchema = $this->extractFormSchema($page['elements'] ?? []);
        $formValues = [];
        $formSchemaMap = [];
        $formDisplayValues = [];
        $formCheckedMap = [];
        foreach ($formSchema as $field) {
            $formValues[$field['key']] = $field['value'] ?? '';
            $formSchemaMap[$field['key']] = $field;

            if (($field['type'] ?? '') === 'select') {
                $formDisplayValues[$field['key']] = $this->resolveSelectDisplayValue($field);
            }

            if (($field['type'] ?? '') === 'checkbox-group') {
                $formCheckedMap[$field['key']] = $this->buildCheckboxCheckedMap($field);
            }
        }

        $data['formValues'] = $formValues;
        $data['formSchema'] = $formSchema;
        $data['formSchemaMap'] = $formSchemaMap;
        $data['formDisplayValues'] = $formDisplayValues;
        $data['formCheckedMap'] = $formCheckedMap;
        $data['builderProjectTitle'] = $config['title'] ?? '未命名项目';
        $data['builderProjectType'] = 'wechat';
        $data['builderPageName'] = $page['name'] ?? 'index';
        $data['builderPageTitle'] = $page['title'] ?? '首页';
        $methods = $page['methods'] ?? [];
        
        $dataStr = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $methodsStr = '';
        
        foreach ($methods as $method) {
            $methodsStr .= "  {$method['name']}() {\n    // {$method['description']}\n  },\n";
        }
        
        return "Page({
  data: {$dataStr},
  
{$methodsStr}  handleFieldInput(event) {
    const { fieldKey = '', fieldType = 'text' } = event.currentTarget.dataset || {};

    if (!fieldKey) {
      return;
    }

    const nextData = {};
    const nextValue = fieldType === 'number' ? String(event.detail.value || '').replace(/[^\d.]/g, '') : event.detail.value;
    nextData['formValues.' + fieldKey] = nextValue;
    this.setData(nextData);
  },

  handlePickerChange(event) {
    const { fieldKey = '' } = event.currentTarget.dataset || {};
    const schemaMap = this.data.formSchemaMap || {};
    const fieldSchema = schemaMap[fieldKey];

    if (!fieldKey || !fieldSchema) {
      return;
    }

    const options = Array.isArray(fieldSchema.options) ? fieldSchema.options : [];
    const optionIndex = Number(event.detail.value || 0);
    const selectedOption = options[optionIndex];

    if (!selectedOption) {
      return;
    }

    const nextData = {};
    nextData['formValues.' + fieldKey] = selectedOption.value;
    nextData['formDisplayValues.' + fieldKey] = selectedOption.label || selectedOption.value || (fieldSchema.placeholder || '请选择');
    this.setData(nextData);
  },

  handleChoiceChange(event) {
    const { fieldKey = '', fieldType = '' } = event.currentTarget.dataset || {};
    const schemaMap = this.data.formSchemaMap || {};
    const fieldSchema = schemaMap[fieldKey];

    if (!fieldKey || !fieldSchema) {
      return;
    }

    if (fieldType === 'checkbox-group') {
      const selectedValues = Array.isArray(event.detail.value) ? event.detail.value : [];
      const checkedMap = {};
      const options = Array.isArray(fieldSchema.options) ? fieldSchema.options : [];

      options.forEach((option) => {
        checkedMap[option.checkedKey] = selectedValues.includes(option.value);
      });

      const nextData = {};
      nextData['formValues.' + fieldKey] = selectedValues;
      nextData['formCheckedMap.' + fieldKey] = checkedMap;
      this.setData(nextData);
      return;
    }

    const nextData = {};
    nextData['formValues.' + fieldKey] = event.detail.value || '';
    this.setData(nextData);
  },

  handleAction(event) {
    const {
      actionType = 'none',
      actionValue = '',
      submitEndpoint = '',
      submitMethod = 'POST',
      submitReset = '0',
      submitRedirect = ''
    } = event.currentTarget.dataset || {};

    if (!actionType || actionType === 'none') {
      return;
    }

    if (actionType === 'message') {
      wx.showToast({
        title: actionValue || '操作成功',
        icon: 'none'
      });
      return;
    }

    if (actionType === 'submit') {
      const schema = Array.isArray(this.data.formSchema) ? this.data.formSchema : [];
      const values = this.data.formValues || {};
      const invalidField = schema.find((field) => {
        const rawValue = values[field.key];
        const value = Array.isArray(rawValue) ? rawValue.map((item) => String(item || '').trim()).filter(Boolean) : String(rawValue || '').trim();

        if (field.required && ((Array.isArray(value) && value.length === 0) || (!Array.isArray(value) && !value))) {
          return true;
        }

        if (Array.isArray(value) || !value || !field.pattern) {
          return false;
        }

        try {
          return !(new RegExp(field.pattern)).test(value);
        } catch (error) {
          return false;
        }
      });

      if (invalidField) {
        wx.showToast({
          title: invalidField.required && !String(values[invalidField.key] || '').trim()
            ? ((invalidField.label || '当前字段') + '为必填项')
            : (invalidField.message || ((invalidField.label || '当前字段') + '格式不正确')),
          icon: 'none'
        });
        return;
      }

      console.log('builder form submit', values);

      const finalizeSubmit = () => {
        if (submitReset === '1') {
          const resetData = {};
          schema.forEach((field) => {
            if (field.type === 'checkbox-group') {
              resetData['formValues.' + field.key] = [];
              const checkedMap = {};
              (field.options || []).forEach((option) => {
                checkedMap[option.checkedKey] = false;
              });
              resetData['formCheckedMap.' + field.key] = checkedMap;
              return;
            }

            resetData['formValues.' + field.key] = '';

            if (field.type === 'select') {
              resetData['formDisplayValues.' + field.key] = field.placeholder || '请选择';
            }
          });
          this.setData(resetData);
        }

        wx.showToast({
          title: actionValue || '提交成功',
          icon: 'success'
        });

        if (submitRedirect) {
          setTimeout(() => {
            wx.navigateTo({
              url: submitRedirect
            });
          }, 300);
        }
      };

      if (!submitEndpoint) {
        finalizeSubmit();
        return;
      }

      wx.request({
        url: submitEndpoint,
        method: submitMethod || 'POST',
        header: {
          'content-type': 'application/json'
        },
        data: {
          project_name: this.data.builderProjectTitle || '未命名项目',
          project_type: this.data.builderProjectType || 'wechat',
          page_name: this.data.builderPageName || 'index',
          page_title: this.data.builderPageTitle || '首页',
          source: 'wechat',
          submitted_at: new Date().toISOString(),
          form_data: values
        },
        success: (response) => {
          const json = response.data || {};

          if (response.statusCode >= 400 || json.success === false) {
            wx.showToast({
              title: json.message || '提交失败',
              icon: 'none'
            });
            return;
          }

          finalizeSubmit();
        },
        fail: () => {
          wx.showToast({
            title: '提交失败，请检查接口配置',
            icon: 'none'
          });
        }
      });

      return;
    }

    if (actionType === 'link') {
      if (!actionValue) {
        return;
      }

      if (/^(https?:)?\\/\\//.test(actionValue)) {
        wx.setClipboardData({
          data: actionValue
        });
        return;
      }

      wx.navigateTo({
        url: actionValue
      });
    }
  },

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
            case 'div':
            case 'row':
                $class = $props['class'] ?? '';
                $layoutStyle = $type === 'row' ? 'display:flex; flex-wrap:wrap;' : '';
                $style = $layoutStyle . ($props['style'] ?? '');
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
                $actionType = $props['actionType'] ?? 'none';
                $actionValue = htmlspecialchars($props['actionValue'] ?? '', ENT_QUOTES, 'UTF-8');
                $submitEndpoint = htmlspecialchars($props['submitEndpoint'] ?? '', ENT_QUOTES, 'UTF-8');
                $submitMethod = htmlspecialchars($props['submitMethod'] ?? 'POST', ENT_QUOTES, 'UTF-8');
                $submitReset = !empty($props['submitResetForm']) ? '1' : '0';
                $submitRedirect = htmlspecialchars($props['submitRedirectUrl'] ?? '', ENT_QUOTES, 'UTF-8');
                $actionAttrs = $actionType !== 'none'
                    ? " bindtap=\"handleAction\" data-action-type=\"{$actionType}\" data-action-value=\"{$actionValue}\" data-submit-endpoint=\"{$submitEndpoint}\" data-submit-method=\"{$submitMethod}\" data-submit-reset=\"{$submitReset}\" data-submit-redirect=\"{$submitRedirect}\""
                    : '';
                return "<button class=\"{$class}\" style=\"{$style}\"{$actionAttrs}>{$text}</button>\n";

            case 'input':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<text style="color:#c2410c;">*</text>' : '';
                $placeholder = $props['placeholder'] ?? '';
                $class = $props['class'] ?? '';
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = $this->resolveFieldKey($element);
                $fieldType = htmlspecialchars($this->resolveWechatInputType($props['inputType'] ?? 'text'), ENT_QUOTES, 'UTF-8');
                $wrapperStyle = $width ? "width:{$width};" : '';
                $labelWxml = $label ? "<text style=\"display:block;margin-bottom:6px;\">{$label}{$required}</text>" : '';
                return "<view style=\"{$wrapperStyle}\">{$labelWxml}<input type=\"{$fieldType}\" class=\"{$class}\" style=\"{$style}\" placeholder=\"{$placeholder}\" value=\"{{formValues.{$fieldKey}}}\" data-field-key=\"{$fieldKey}\" data-field-type=\"{$fieldType}\" bindinput=\"handleFieldInput\" /></view>\n";

            case 'textarea':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<text style="color:#c2410c;">*</text>' : '';
                $placeholder = $props['placeholder'] ?? '';
                $class = $props['class'] ?? '';
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = $this->resolveFieldKey($element);
                $wrapperStyle = $width ? "width:{$width};" : '';
                $labelWxml = $label ? "<text style=\"display:block;margin-bottom:6px;\">{$label}{$required}</text>" : '';
                return "<view style=\"{$wrapperStyle}\">{$labelWxml}<textarea class=\"{$class}\" style=\"{$style}\" placeholder=\"{$placeholder}\" value=\"{{formValues.{$fieldKey}}}\" data-field-key=\"{$fieldKey}\" bindinput=\"handleFieldInput\"></textarea></view>\n";

            case 'select':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<text style="color:#c2410c;">*</text>' : '';
                $class = trim('form-control picker-display ' . ($props['class'] ?? ''));
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = $this->resolveFieldKey($element);
                $wrapperStyle = $width ? "width:{$width};" : '';
                $labelWxml = $label ? "<text style=\"display:block;margin-bottom:6px;\">{$label}{$required}</text>" : '';
                return "<view style=\"{$wrapperStyle}\">{$labelWxml}<picker mode=\"selector\" range=\"{{formSchemaMap.{$fieldKey}.options}}\" range-key=\"label\" data-field-key=\"{$fieldKey}\" bindchange=\"handlePickerChange\"><view class=\"{$class}\" style=\"{$style}\">{{formDisplayValues.{$fieldKey}}}</view></picker></view>\n";

            case 'radio-group':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<text style="color:#c2410c;">*</text>' : '';
                $class = trim('choice-group ' . ((($props['optionLayout'] ?? 'vertical') === 'horizontal') ? 'choice-group-horizontal ' : '') . ($props['class'] ?? ''));
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = $this->resolveFieldKey($element);
                $wrapperStyle = $width ? "width:{$width};" : '';
                $labelWxml = $label ? "<text style=\"display:block;margin-bottom:6px;\">{$label}{$required}</text>" : '';
                $optionsWxml = $this->buildWechatChoiceGroupWxml('radio', $fieldKey);
                return "<view style=\"{$wrapperStyle}\">{$labelWxml}<radio-group class=\"{$class}\" style=\"{$style}\" data-field-key=\"{$fieldKey}\" data-field-type=\"radio-group\" bindchange=\"handleChoiceChange\">{$optionsWxml}</radio-group></view>\n";

            case 'checkbox-group':
                $label = $props['label'] ?? '';
                $required = !empty($props['required']) ? '<text style="color:#c2410c;">*</text>' : '';
                $class = trim('choice-group ' . ((($props['optionLayout'] ?? 'vertical') === 'horizontal') ? 'choice-group-horizontal ' : '') . ($props['class'] ?? ''));
                $width = $props['width'] ?? '';
                $style = $props['style'] ?? '';
                $fieldKey = $this->resolveFieldKey($element);
                $wrapperStyle = $width ? "width:{$width};" : '';
                $labelWxml = $label ? "<text style=\"display:block;margin-bottom:6px;\">{$label}{$required}</text>" : '';
                $optionsWxml = $this->buildWechatChoiceGroupWxml('checkbox', $fieldKey);
                return "<view style=\"{$wrapperStyle}\">{$labelWxml}<checkbox-group class=\"{$class}\" style=\"{$style}\" data-field-key=\"{$fieldKey}\" data-field-type=\"checkbox-group\" bindchange=\"handleChoiceChange\">{$optionsWxml}</checkbox-group></view>\n";

            case 'spacer':
                $height = $props['height'] ?? '32px';
                $class = $props['class'] ?? '';
                $style = $props['style'] ?? '';
                return "<view class=\"{$class}\" style=\"height:{$height};{$style}\"></view>\n";
                
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

    private function extractFormSchema(array $elements): array
    {
        $schema = [];

        foreach ($elements as $element) {
            $type = $element['type'] ?? '';
            $props = $element['props'] ?? [];

            if (in_array($type, ['input', 'textarea', 'select', 'radio-group', 'checkbox-group'], true)) {
                $fieldValue = $props['value'] ?? '';

                if ($type === 'checkbox-group') {
                    $fieldValue = $this->parseChoiceValues($fieldValue);
                }

                $schema[] = [
                    'key' => $this->resolveFieldKey($element),
                    'type' => $type,
                    'label' => $props['label'] ?? ($props['placeholder'] ?? '当前字段'),
                    'required' => !empty($props['required']),
                    'value' => $fieldValue,
                    'placeholder' => $props['placeholder'] ?? '请选择',
                    'pattern' => $props['validationPattern'] ?? '',
                    'message' => $props['validationMessage'] ?? '',
                    'options' => in_array($type, ['select', 'radio-group', 'checkbox-group'], true)
                        ? $this->parseChoiceOptions($props['options'] ?? '')
                        : [],
                ];
            }

            if (!empty($element['children']) && is_array($element['children'])) {
                $schema = array_merge($schema, $this->extractFormSchema($element['children']));
            }
        }

        return $schema;
    }

    private function resolveFieldKey(array $element): string
    {
        $props = $element['props'] ?? [];

        if (!empty($props['fieldKey'])) {
            $normalized = preg_replace('/[^\w]+/', '_', (string) $props['fieldKey']);
            if (!preg_match('/^[A-Za-z_]/', $normalized)) {
                $normalized = 'field_' . $normalized;
            }
            return $normalized;
        }

        $base = (string) ($element['id'] ?? uniqid('field_', false));
        return 'field_' . preg_replace('/[^\w]+/', '_', $base);
    }

    private function resolveWechatInputType(string $inputType): string
    {
        switch ($inputType) {
            case 'tel':
                return 'number';
            case 'number':
                return 'digit';
            case 'email':
                return 'text';
            default:
                return 'text';
        }
    }

    private function buildWechatChoiceGroupWxml(string $type, string $fieldKey): string
    {
        if ($type === 'radio') {
            return "<label wx:for=\"{{formSchemaMap.{$fieldKey}.options}}\" wx:key=\"value\" class=\"choice-option\"><radio value=\"{{item.value}}\" checked=\"{{formValues.{$fieldKey} === item.value}}\" /><text>{{item.label}}</text></label>";
        }

        return "<label wx:for=\"{{formSchemaMap.{$fieldKey}.options}}\" wx:key=\"value\" class=\"choice-option\"><checkbox value=\"{{item.value}}\" checked=\"{{formCheckedMap.{$fieldKey}[item.checkedKey]}}\" /><text>{{item.label}}</text></label>";
    }

    private function parseChoiceOptions(string $rawOptions): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawOptions) ?: [];
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
                'checkedKey' => preg_replace('/[^\w]+/', '_', $value) ?: ('option_' . ($index + 1)),
            ];
        }

        return $options;
    }

    private function parseChoiceValues($rawValue): array
    {
        $values = array_map('trim', explode(',', (string) $rawValue));
        return array_values(array_filter($values, static fn ($value) => $value !== ''));
    }

    private function resolveSelectDisplayValue(array $field): string
    {
        $options = $field['options'] ?? [];
        $currentValue = (string) ($field['value'] ?? '');

        foreach ($options as $option) {
            if ((string) ($option['value'] ?? '') === $currentValue) {
                return (string) ($option['label'] ?? $option['value']);
            }
        }

        return (string) ($field['placeholder'] ?? '请选择');
    }

    private function buildCheckboxCheckedMap(array $field): array
    {
        $selectedValues = is_array($field['value'] ?? null) ? $field['value'] : [];
        $checkedMap = [];

        foreach ($field['options'] ?? [] as $option) {
            $checkedMap[$option['checkedKey']] = in_array($option['value'], $selectedValues, true);
        }

        return $checkedMap;
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
