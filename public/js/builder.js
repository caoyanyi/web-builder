const { createApp } = Vue;

const CONTAINER_TYPES = ['div', 'row'];
const FORM_FIELD_TYPES = ['input', 'textarea', 'select', 'radio-group', 'checkbox-group'];
const PROP_ORDER = ['content', 'text', 'summaryTitle', 'emptyText', 'label', 'required', 'helperText', 'placeholder', 'value', 'rows', 'minLength', 'maxLength', 'minValue', 'maxValue', 'fieldKey', 'stepIndex', 'stepTitle', 'conditionEnabled', 'conditionFieldKey', 'conditionOperator', 'conditionValue', 'inputType', 'options', 'optionLayout', 'validationPattern', 'validationMessage', 'height', 'src', 'alt', 'class', 'width', 'style', 'actionType', 'actionValue', 'submitEndpoint', 'submitMethod', 'submitResetForm', 'submitRedirectUrl'];
const HISTORY_LIMIT = 60;
const DRAG_KIND_COMPONENT = 'component';
const DRAG_KIND_ELEMENT = 'existing-element';
const LOCAL_DRAFT_STORAGE_KEY = 'web-builder-local-draft-v1';
const INTERNAL_PROP_KEYS = new Set(['templateKey', 'templateName']);
const DEFAULT_CONDITION_PROPS = {
    conditionEnabled: false,
    conditionFieldKey: '',
    conditionOperator: 'equals',
    conditionValue: ''
};
const DEFAULT_STEP_PROPS = {
    stepIndex: '1',
    stepTitle: ''
};
const BUTTON_ACTION_OPTIONS = [
    { value: 'none', label: '无动作' },
    { value: 'message', label: '提示消息' },
    { value: 'link', label: '跳转链接' },
    { value: 'step-prev', label: '上一步' },
    { value: 'step-next', label: '下一步' },
    { value: 'submit', label: '提交表单' }
];
const INPUT_TYPE_OPTIONS = [
    { value: 'text', label: '文本' },
    { value: 'tel', label: '手机号' },
    { value: 'email', label: '邮箱' },
    { value: 'number', label: '数字' }
];
const REQUEST_METHOD_OPTIONS = [
    { value: 'POST', label: 'POST' },
    { value: 'PUT', label: 'PUT' }
];
const OPTION_LAYOUT_OPTIONS = [
    { value: 'vertical', label: '纵向排列' },
    { value: 'horizontal', label: '横向排列' }
];
const CONDITION_OPERATOR_OPTIONS = [
    { value: 'equals', label: '等于' },
    { value: 'not_equals', label: '不等于' },
    { value: 'contains', label: '包含' },
    { value: 'not_contains', label: '不包含' },
    { value: 'filled', label: '已填写' },
    { value: 'empty', label: '为空' }
];
const CHOICE_OPTION_PRESETS = [
    {
        key: 'basic',
        label: '基础三项',
        options: 'option_a|选项一\noption_b|选项二\noption_c|选项三',
        value: ''
    },
    {
        key: 'yes_no',
        label: '是否选择',
        options: 'yes|是\nno|否',
        value: ''
    },
    {
        key: 'appointment',
        label: '预约时段',
        options: 'morning|上午\nafternoon|下午\nevening|晚上',
        value: ''
    },
    {
        key: 'package',
        label: '套餐方案',
        options: 'basic|基础版\npro|进阶版\nenterprise|企业版',
        value: 'pro'
    }
];
const COMMON_FIELD_PRESETS = [
    {
        key: 'contact_name',
        label: '姓名',
        types: ['input'],
        props: {
            label: '姓名',
            fieldKey: 'contact_name',
            inputType: 'text',
            placeholder: '请输入姓名',
            helperText: '请填写真实姓名，方便后续联系。',
            validationPattern: '',
            validationMessage: '',
            minLength: '2',
            maxLength: '20',
            minValue: '',
            maxValue: '',
            required: true,
            value: ''
        }
    },
    {
        key: 'contact_phone',
        label: '手机号',
        types: ['input'],
        props: {
            label: '手机号',
            fieldKey: 'contact_phone',
            inputType: 'tel',
            placeholder: '请输入手机号',
            helperText: '用于和您确认需求与回访进度。',
            validationPattern: '^1\\d{10}$',
            validationMessage: '请输入有效的 11 位手机号',
            minLength: '11',
            maxLength: '11',
            minValue: '',
            maxValue: '',
            required: true,
            value: ''
        }
    },
    {
        key: 'contact_email',
        label: '邮箱',
        types: ['input'],
        props: {
            label: '邮箱',
            fieldKey: 'contact_email',
            inputType: 'email',
            placeholder: '请输入邮箱地址',
            helperText: '我们会把方案或回执发送到这个邮箱。',
            validationPattern: '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$',
            validationMessage: '请输入有效的邮箱地址',
            minLength: '',
            maxLength: '80',
            minValue: '',
            maxValue: '',
            required: true,
            value: ''
        }
    },
    {
        key: 'company_name',
        label: '公司',
        types: ['input'],
        props: {
            label: '公司名称',
            fieldKey: 'company_name',
            inputType: 'text',
            placeholder: '请输入公司名称',
            helperText: '适合 B 端线索、商务合作或项目登记表。',
            validationPattern: '',
            validationMessage: '',
            minLength: '',
            maxLength: '60',
            minValue: '',
            maxValue: '',
            required: false,
            value: ''
        }
    },
    {
        key: 'job_title',
        label: '职位',
        types: ['input'],
        props: {
            label: '职位',
            fieldKey: 'job_title',
            inputType: 'text',
            placeholder: '请输入职位',
            helperText: '例如：市场负责人、运营经理、产品经理。',
            validationPattern: '',
            validationMessage: '',
            minLength: '',
            maxLength: '30',
            minValue: '',
            maxValue: '',
            required: false,
            value: ''
        }
    },
    {
        key: 'budget',
        label: '预算',
        types: ['input'],
        props: {
            label: '预算',
            fieldKey: 'budget',
            inputType: 'number',
            placeholder: '请输入预算金额',
            helperText: '建议填写预估预算，方便快速匹配方案。',
            validationPattern: '^\\d+(\\.\\d+)?$',
            validationMessage: '请输入数字',
            minLength: '',
            maxLength: '',
            minValue: '0',
            maxValue: '',
            required: false,
            value: ''
        }
    },
    {
        key: 'requirement',
        label: '需求说明',
        types: ['textarea'],
        props: {
            label: '需求说明',
            fieldKey: 'requirement',
            placeholder: '请描述项目背景、目标和预期效果',
            helperText: '建议写清目标用户、核心诉求、上线时间等关键信息。',
            rows: '5',
            minLength: '10',
            maxLength: '300',
            required: true,
            value: ''
        }
    },
    {
        key: 'remark',
        label: '备注',
        types: ['textarea'],
        props: {
            label: '备注',
            fieldKey: 'remark',
            placeholder: '请输入补充说明',
            helperText: '可填写特殊要求、补充说明或其他信息。',
            rows: '4',
            minLength: '',
            maxLength: '200',
            required: false,
            value: ''
        }
    }
];
const THEME_PRESETS = {
    forest: {
        name: '森绿',
        theme: {
            primary: '#0f766e',
            accent: '#f59e0b',
            surface: '#ffffff',
            pageBackground: '#f4f7f2',
            text: '#16302b',
            radius: '18px'
        }
    },
    ocean: {
        name: '海蓝',
        theme: {
            primary: '#0369a1',
            accent: '#fb7185',
            surface: '#ffffff',
            pageBackground: '#eff6ff',
            text: '#0f172a',
            radius: '20px'
        }
    },
    sunset: {
        name: '暖橙',
        theme: {
            primary: '#ea580c',
            accent: '#b45309',
            surface: '#fffaf5',
            pageBackground: '#fff7ed',
            text: '#431407',
            radius: '22px'
        }
    }
};
const SECTION_TEMPLATES = [
    {
        key: 'hero',
        name: 'Hero 首屏',
        icon: 'bi bi-stars',
        description: '标题、说明和双按钮组合，适合首页开场。',
        build() {
            return [
                {
                    type: 'div',
                    props: {
                        templateKey: 'hero',
                        templateName: 'Hero 首屏',
                        class: '',
                        width: '',
                        style: 'padding: 32px; border-radius: 24px; background: linear-gradient(135deg, #0f766e 0%, #155e75 100%); color: #ffffff;'
                    },
                    children: [
                        {
                            type: 'text',
                            props: {
                                content: '夏季新品发布',
                                class: '',
                                width: '',
                                style: 'font-size: 38px; font-weight: 700; line-height: 1.2; margin-bottom: 12px;'
                            },
                            children: []
                        },
                        {
                            type: 'text',
                            props: {
                                content: '用更快的方式搭出活动页、专题页和表单页，先把页面结构铺出来，再继续精修内容和样式。',
                                class: '',
                                width: '',
                                style: 'font-size: 16px; line-height: 1.8; opacity: 0.9; margin-bottom: 20px; max-width: 680px;'
                            },
                            children: []
                        },
                        {
                            type: 'row',
                            props: {
                                class: '',
                                width: '',
                                style: 'gap: 12px; align-items: center;'
                            },
                            children: [
                                {
                                    type: 'button',
                                    props: {
                                        text: '立即体验',
                                        class: 'btn btn-light',
                                        width: '',
                                        style: 'color: #0f766e; font-weight: 600;'
                                    },
                                    children: []
                                },
                                {
                                    type: 'button',
                                    props: {
                                        text: '查看详情',
                                        class: 'btn btn-outline-light',
                                        width: '',
                                        style: 'font-weight: 600;'
                                    },
                                    children: []
                                }
                            ]
                        }
                    ]
                }
            ];
        }
    },
    {
        key: 'features',
        name: '功能卡片',
        icon: 'bi bi-columns-gap',
        description: '快速插入三列能力介绍区，适合卖点展示。',
        build() {
            return [
                {
                    type: 'div',
                    props: {
                        templateKey: 'features',
                        templateName: '功能卡片',
                        class: '',
                        width: '',
                        style: 'padding: 28px; border-radius: 24px; background: #ffffff; border: 1px solid #d7e2d6;'
                    },
                    children: [
                        {
                            type: 'text',
                            props: {
                                content: '为什么选择这套方案',
                                class: '',
                                width: '',
                                style: 'font-size: 28px; font-weight: 700; line-height: 1.3; margin-bottom: 10px;'
                            },
                            children: []
                        },
                        {
                            type: 'text',
                            props: {
                                content: '把核心优势拆成更容易阅读的卡片，既方便编辑，也方便后续继续扩展。',
                                class: '',
                                width: '',
                                style: 'font-size: 15px; line-height: 1.8; color: #4b635c; margin-bottom: 18px;'
                            },
                            children: []
                        },
                        {
                            type: 'row',
                            props: {
                                class: '',
                                width: '',
                                style: 'gap: 16px; align-items: stretch;'
                            },
                            children: [
                                {
                                    type: 'div',
                                    props: {
                                        class: '',
                                        width: 'calc(33.333% - 11px)',
                                        style: 'padding: 20px; border-radius: 18px; background: #f8fbf7;'
                                    },
                                    children: [
                                        {
                                            type: 'text',
                                            props: {
                                                content: '搭建效率更高',
                                                class: '',
                                                width: '',
                                                style: 'font-size: 18px; font-weight: 700; margin-bottom: 8px;'
                                            },
                                            children: []
                                        },
                                        {
                                            type: 'text',
                                            props: {
                                                content: '通过拖拽和模板组合，先完成页面骨架，再补充品牌细节。',
                                                class: '',
                                                width: '',
                                                style: 'font-size: 14px; line-height: 1.8; color: #60756f;'
                                            },
                                            children: []
                                        }
                                    ]
                                },
                                {
                                    type: 'div',
                                    props: {
                                        class: '',
                                        width: 'calc(33.333% - 11px)',
                                        style: 'padding: 20px; border-radius: 18px; background: #f8fbf7;'
                                    },
                                    children: [
                                        {
                                            type: 'text',
                                            props: {
                                                content: '导出链路完整',
                                                class: '',
                                                width: '',
                                                style: 'font-size: 18px; font-weight: 700; margin-bottom: 8px;'
                                            },
                                            children: []
                                        },
                                        {
                                            type: 'text',
                                            props: {
                                                content: '同一份配置可以直接查看 H5 预览，并导出 H5 或微信小程序 ZIP。',
                                                class: '',
                                                width: '',
                                                style: 'font-size: 14px; line-height: 1.8; color: #60756f;'
                                            },
                                            children: []
                                        }
                                    ]
                                },
                                {
                                    type: 'div',
                                    props: {
                                        class: '',
                                        width: 'calc(33.333% - 11px)',
                                        style: 'padding: 20px; border-radius: 18px; background: #f8fbf7;'
                                    },
                                    children: [
                                        {
                                            type: 'text',
                                            props: {
                                                content: '适合继续扩展',
                                                class: '',
                                                width: '',
                                                style: 'font-size: 18px; font-weight: 700; margin-bottom: 8px;'
                                            },
                                            children: []
                                        },
                                        {
                                            type: 'text',
                                            props: {
                                                content: '后续可以继续叠加模板库、组件库、事件和数据能力，逐步走向低代码工具。',
                                                class: '',
                                                width: '',
                                                style: 'font-size: 14px; line-height: 1.8; color: #60756f;'
                                            },
                                            children: []
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ];
        }
    },
    {
        key: 'cta',
        name: '转化横幅',
        icon: 'bi bi-megaphone',
        description: '适合活动报名、产品试用和下载引导。',
        build() {
            return [
                {
                    type: 'row',
                    props: {
                        templateKey: 'cta',
                        templateName: '转化横幅',
                        class: '',
                        width: '',
                        style: 'gap: 20px; align-items: center; padding: 24px 28px; border-radius: 22px; background: linear-gradient(180deg, #f8fbf7 0%, #ffffff 100%); border: 1px solid #d7e2d6;'
                    },
                    children: [
                        {
                            type: 'div',
                            props: {
                                class: '',
                                width: 'calc(100% - 180px)',
                                style: ''
                            },
                            children: [
                                {
                                    type: 'text',
                                    props: {
                                        content: '把搭建完成的页面直接投入下一步验证',
                                        class: '',
                                        width: '',
                                        style: 'font-size: 24px; font-weight: 700; margin-bottom: 8px;'
                                    },
                                    children: []
                                },
                                {
                                    type: 'text',
                                    props: {
                                        content: '支持一键预览、生成代码、导出 ZIP，也保留了后续继续人工精修的空间。',
                                        class: '',
                                        width: '',
                                        style: 'font-size: 15px; line-height: 1.8; color: #60756f;'
                                    },
                                    children: []
                                }
                            ]
                        },
                        {
                            type: 'button',
                            props: {
                                text: '开始试用',
                                class: 'btn btn-success',
                                width: '160px',
                                style: 'font-weight: 600;'
                            },
                            children: []
                        }
                    ]
                }
            ];
        }
    },
    {
        key: 'contact',
        name: '表单区块',
        icon: 'bi bi-ui-checks-grid',
        description: '包含标题、输入框、文本域和提交按钮，适合报名或收集线索。',
        build() {
            return [
                {
                    type: 'div',
                    props: {
                        templateKey: 'contact',
                        templateName: '表单区块',
                        class: '',
                        width: '',
                        style: 'padding: 28px; border-radius: 24px; background: #ffffff; border: 1px solid #d7e2d6;'
                    },
                    children: [
                        {
                            type: 'text',
                            props: {
                                content: '预约咨询',
                                class: '',
                                width: '',
                                style: 'font-size: 28px; font-weight: 700; margin-bottom: 10px;'
                            },
                            children: []
                        },
                        {
                            type: 'text',
                            props: {
                                content: '留下你的联系方式和需求，我们会尽快与你取得联系。',
                                class: '',
                                width: '',
                                style: 'font-size: 15px; line-height: 1.8; color: #60756f; margin-bottom: 18px;'
                            },
                            children: []
                        },
                        {
                            type: 'row',
                            props: {
                                class: '',
                                width: '',
                                style: 'gap: 16px; align-items: flex-start;'
                            },
                            children: [
                                {
                                    type: 'input',
                                    props: {
                                        label: '联系人',
                                        required: true,
                                        placeholder: '请输入姓名',
                                        value: '',
                                        fieldKey: 'contact_name',
                                        class: 'form-control',
                                        width: 'calc(50% - 8px)',
                                        style: ''
                                    },
                                    children: []
                                },
                                {
                                    type: 'input',
                                    props: {
                                        label: '联系电话',
                                        required: true,
                                        placeholder: '请输入手机号',
                                        value: '',
                                        fieldKey: 'contact_phone',
                                        inputType: 'tel',
                                        validationPattern: '^1\\d{10}$',
                                        validationMessage: '请输入有效的 11 位手机号',
                                        class: 'form-control',
                                        width: 'calc(50% - 8px)',
                                        style: ''
                                    },
                                    children: []
                                }
                            ]
                        },
                        {
                            type: 'textarea',
                            props: {
                                label: '需求说明',
                                required: false,
                                placeholder: '请描述你的业务场景或页面诉求',
                                value: '',
                                rows: '5',
                                fieldKey: 'contact_requirement',
                                class: 'form-control',
                                width: '100%',
                                style: 'margin-top: 16px;'
                            },
                            children: []
                        },
                        {
                            type: 'button',
                            props: {
                                text: '提交预约',
                                class: 'btn btn-primary',
                                width: '',
                                style: 'margin-top: 18px;',
                                actionType: 'submit',
                                actionValue: '提交成功，我们会尽快联系你',
                                submitEndpoint: '/api/form-submissions',
                                submitMethod: 'POST',
                                submitResetForm: false,
                                submitRedirectUrl: ''
                            },
                            children: []
                        }
                    ]
                }
            ];
        }
    }
];
const TEMPLATE_FIELD_CONFIGS = {
    hero: [
        { key: 'hero_title', label: '主标题', path: [0], propKey: 'content' },
        { key: 'hero_description', label: '说明文案', path: [1], propKey: 'content' },
        { key: 'hero_primary_button', label: '主按钮文案', path: [2, 0], propKey: 'text' },
        { key: 'hero_secondary_button', label: '次按钮文案', path: [2, 1], propKey: 'text' }
    ],
    features: [
        { key: 'features_title', label: '区块标题', path: [0], propKey: 'content' },
        { key: 'features_description', label: '区块说明', path: [1], propKey: 'content' },
        { key: 'features_card_1_title', label: '卡片 1 标题', path: [2, 0, 0], propKey: 'content' },
        { key: 'features_card_1_desc', label: '卡片 1 说明', path: [2, 0, 1], propKey: 'content' },
        { key: 'features_card_2_title', label: '卡片 2 标题', path: [2, 1, 0], propKey: 'content' },
        { key: 'features_card_2_desc', label: '卡片 2 说明', path: [2, 1, 1], propKey: 'content' },
        { key: 'features_card_3_title', label: '卡片 3 标题', path: [2, 2, 0], propKey: 'content' },
        { key: 'features_card_3_desc', label: '卡片 3 说明', path: [2, 2, 1], propKey: 'content' }
    ],
    cta: [
        { key: 'cta_title', label: '标题', path: [0, 0], propKey: 'content' },
        { key: 'cta_description', label: '说明文案', path: [0, 1], propKey: 'content' },
        { key: 'cta_button_text', label: '按钮文案', path: [1], propKey: 'text' }
    ],
    contact: [
        { key: 'contact_title', label: '区块标题', path: [0], propKey: 'content' },
        { key: 'contact_description', label: '区块说明', path: [1], propKey: 'content' },
        { key: 'contact_name_label', label: '姓名字段标题', path: [2, 0], propKey: 'label' },
        { key: 'contact_phone_label', label: '电话字段标题', path: [2, 1], propKey: 'label' },
        { key: 'contact_textarea_label', label: '说明字段标题', path: [3], propKey: 'label' },
        { key: 'contact_button_text', label: '提交按钮文案', path: [4], propKey: 'text' }
    ]
};

function createId(prefix = 'node') {
    return `${prefix}_${Date.now().toString(36)}_${Math.random().toString(36).slice(2, 8)}`;
}

function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function createDefaultTheme() {
    return deepClone(THEME_PRESETS.forest.theme);
}

function parseChoiceOptions(rawOptions = '') {
    return String(rawOptions || '')
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line, index) => {
            const segments = line.split('|');
            const rawValue = segments[0];
            const rawLabel = segments.slice(1).join('|');
            const value = String(rawValue || `option_${index + 1}`).trim() || `option_${index + 1}`;
            const label = String(rawLabel || rawValue || `选项 ${index + 1}`).trim() || value;

            return {
                value,
                label
            };
        });
}

function parseChoiceValues(value) {
    return String(value || '')
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);
}

function escapeHtml(value = '') {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function applyConditionalDefaults(props = {}) {
    return {
        ...DEFAULT_STEP_PROPS,
        ...DEFAULT_CONDITION_PROPS,
        ...(props || {})
    };
}

function parseStepIndex(value = '1') {
    const parsed = Number.parseInt(String(value || '1'), 10);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
}

function getStepLabel(stepIndex = 1, stepTitle = '') {
    const title = String(stepTitle || '').trim();
    return title ? `第${stepIndex}步·${title}` : `第${stepIndex}步`;
}

function normalizeConditionOperator(operator = 'equals') {
    const nextOperator = String(operator || 'equals');
    return CONDITION_OPERATOR_OPTIONS.some((item) => item.value === nextOperator)
        ? nextOperator
        : 'equals';
}

function normalizeConditionActualValue(value) {
    if (Array.isArray(value)) {
        return value
            .map((item) => String(item || '').trim())
            .filter(Boolean);
    }

    return String(value || '').trim();
}

function evaluateConditionRule(actualValue, operator = 'equals', expectedValue = '') {
    const normalizedOperator = normalizeConditionOperator(operator);
    const normalizedActual = normalizeConditionActualValue(actualValue);
    const normalizedExpected = String(expectedValue || '').trim();

    if (normalizedOperator === 'filled') {
        return Array.isArray(normalizedActual) ? normalizedActual.length > 0 : normalizedActual !== '';
    }

    if (normalizedOperator === 'empty') {
        return Array.isArray(normalizedActual) ? normalizedActual.length === 0 : normalizedActual === '';
    }

    if (Array.isArray(normalizedActual)) {
        const included = normalizedActual.includes(normalizedExpected);
        return normalizedOperator === 'not_contains' ? !included : included;
    }

    if (normalizedOperator === 'contains') {
        return normalizedExpected !== '' && normalizedActual.includes(normalizedExpected);
    }

    if (normalizedOperator === 'not_contains') {
        return normalizedExpected === '' ? true : !normalizedActual.includes(normalizedExpected);
    }

    if (normalizedOperator === 'not_equals') {
        return normalizedActual !== normalizedExpected;
    }

    return normalizedActual === normalizedExpected;
}

function getConditionOperatorLabel(operator = 'equals') {
    const matched = CONDITION_OPERATOR_OPTIONS.find((item) => item.value === normalizeConditionOperator(operator));
    return matched ? matched.label : '等于';
}

function resolveElementFieldKey(element = null) {
    if (!element || !FORM_FIELD_TYPES.includes(element.type)) {
        return '';
    }

    const props = element.props || {};
    const fallbackKey = `field_${String(element.id || element.type).replace(/[^\w-]+/g, '_')}`;

    return props.fieldKey || fallbackKey;
}

function normalizeFieldKeyInput(value = '', { allowEmpty = false, fallback = 'field' } = {}) {
    const rawValue = String(value || '').trim();

    if (!rawValue) {
        return allowEmpty ? '' : fallback;
    }

    let normalized = rawValue
        .replace(/[^\w]+/g, '_')
        .replace(/^_+|_+$/g, '');

    if (!normalized) {
        return allowEmpty ? '' : fallback;
    }

    if (/^\d/.test(normalized)) {
        normalized = `${fallback}_${normalized}`;
    }

    return normalized;
}

function describeConditionRule(props = {}, fieldDefinitions = {}) {
    if (!props || !props.conditionEnabled || !props.conditionFieldKey) {
        return '';
    }

    const definition = fieldDefinitions[props.conditionFieldKey] || null;
    const fieldLabel = definition && definition.label ? definition.label : props.conditionFieldKey;
    const operatorLabel = getConditionOperatorLabel(props.conditionOperator || 'equals');
    const normalizedOperator = normalizeConditionOperator(props.conditionOperator || 'equals');

    if (normalizedOperator === 'filled' || normalizedOperator === 'empty') {
        return `${fieldLabel}${operatorLabel}`;
    }

    return `${fieldLabel}${operatorLabel}${props.conditionValue || '未设置'}`;
}

function describeStepRule(props = {}) {
    return getStepLabel(parseStepIndex(props.stepIndex || '1'), props.stepTitle || '');
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
        },
        fieldDefinitions: {
            type: Object,
            default: () => ({})
        },
        stepDefinitions: {
            type: Array,
            default: () => []
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
                'form-summary': '表单摘要',
                input: '输入框',
                textarea: '文本域',
                select: '下拉选择',
                'radio-group': '单选组',
                'checkbox-group': '多选组',
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
        },
        buttonWrapperStyle() {
            const props = this.element.props || {};
            return props.width ? `width: ${props.width};` : '';
        },
        buttonInnerStyle() {
            const props = this.element.props || {};
            return props.style || '';
        },
        buttonActionSummary() {
            const props = this.element.props || {};
            const actionType = props.actionType || 'none';
            const labels = {
                message: '提示消息',
                link: '跳转链接',
                'step-prev': '上一步',
                'step-next': '下一步',
                submit: '提交表单'
            };

            if (actionType === 'none') {
                return '';
            }

            return props.actionValue
                ? `${labels[actionType] || actionType}：${props.actionValue}`
                : (labels[actionType] || actionType);
        },
        choiceOptions() {
            return parseChoiceOptions(this.element.props && this.element.props.options);
        },
        choiceValues() {
            return parseChoiceValues(this.element.props && this.element.props.value);
        },
        isHorizontalChoiceLayout() {
            return (this.element.props && this.element.props.optionLayout) === 'horizontal';
        },
        helperText() {
            return String((this.element.props && this.element.props.helperText) || '').trim();
        },
        fieldAssistKeyBase() {
            const rawKey = (this.element.props && this.element.props.fieldKey)
                || this.element.id
                || this.element.type
                || 'field';
            return String(rawKey).replace(/[^\w-]+/g, '_');
        },
        fieldControlId() {
            return `builder-preview-control-${this.fieldAssistKeyBase}`;
        },
        helperTextId() {
            return this.helperText ? `builder-preview-help-${this.fieldAssistKeyBase}` : '';
        },
        fieldCounterId() {
            return this.fieldCounterText ? `builder-preview-count-${this.fieldAssistKeyBase}` : '';
        },
        fieldDescribedBy() {
            return [this.helperTextId, this.fieldCounterId].filter(Boolean).join(' ');
        },
        fieldCounterText() {
            if (!['input', 'textarea'].includes(this.element.type)) {
                return '';
            }

            const props = this.element.props || {};
            const maxLength = Number.parseInt(String(props.maxLength || ''), 10);
            if (!Number.isFinite(maxLength) || maxLength <= 0) {
                return '';
            }

            return `${String(props.value || '').length}/${maxLength}`;
        },
        summaryPreviewEntries() {
            return Object.values(this.fieldDefinitions || {})
                .sort((left, right) => String(left.label || left.key).localeCompare(String(right.label || right.key), 'zh-CN'))
                .slice(0, 5);
        },
        summaryPreviewOverflow() {
            return Math.max(0, Object.keys(this.fieldDefinitions || {}).length - this.summaryPreviewEntries.length);
        },
        visibilitySummary() {
            return describeConditionRule(this.element.props || {}, this.fieldDefinitions || {});
        },
        stepSummary() {
            const props = this.element.props || {};
            const stepIndex = parseStepIndex(props.stepIndex || '1');
            const stepTitle = String(props.stepTitle || '').trim();

            if (this.stepDefinitions.length <= 1 && stepIndex === 1 && !stepTitle) {
                return '';
            }

            return describeStepRule(props);
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
            :data-element-id="String(element.id)"
            :class="wrapperClasses"
            draggable="true"
            @click.stop="selectElement"
            @dragstart="onDragStart"
            @dragover.stop.prevent="onDragOverWrapper"
            @drop.stop.prevent="onDropInsert"
        >
            <div class="builder-node-toolbar">
                <span class="builder-node-badge">{{ typeLabel }}</span>
                <span v-if="stepSummary" class="status-pill">{{ stepSummary }}</span>
                <span v-if="visibilitySummary" class="status-pill">{{ visibilitySummary }}</span>

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
                    :field-definitions="fieldDefinitions"
                    :step-definitions="stepDefinitions"
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

            <div
                v-else-if="element.type === 'button'"
                class="builder-button-preview"
                :style="buttonWrapperStyle"
            >
                <button
                    type="button"
                    :class="element.props.class || 'btn btn-primary'"
                    :style="buttonInnerStyle"
                    @click.stop="selectElement"
                >
                    {{ element.props.text || '按钮' }}
                </button>
                <span v-if="buttonActionSummary" class="builder-action-hint">
                    {{ buttonActionSummary }}
                </span>
            </div>

            <div v-else-if="element.type === 'form-summary'" class="builder-summary-preview" :style="elementStyle">
                <div class="builder-summary-head">
                    <strong>{{ element.props.summaryTitle || '请确认以下信息' }}</strong>
                    <span>{{ Object.keys(fieldDefinitions || {}).length }} 项字段</span>
                </div>
                <div v-if="summaryPreviewEntries.length > 0" class="builder-summary-list">
                    <div v-for="field in summaryPreviewEntries" :key="field.key" class="builder-summary-item">
                        <span>{{ field.label }}</span>
                        <strong>待填写</strong>
                    </div>
                </div>
                <div v-else class="builder-summary-empty">
                    {{ element.props.emptyText || '当前还没有可汇总的表单字段' }}
                </div>
                <div v-if="summaryPreviewOverflow > 0" class="builder-summary-foot">
                    还有 {{ summaryPreviewOverflow }} 个字段会在预览和导出页面里自动汇总
                </div>
            </div>

            <div v-else-if="element.type === 'input'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label" :for="fieldControlId">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <input
                    :id="fieldControlId"
                    :type="element.props.inputType || 'text'"
                    :class="['builder-render-field', element.props.class || 'form-control']"
                    :placeholder="element.props.placeholder || '请输入内容'"
                    :value="element.props.value || ''"
                    :aria-describedby="fieldDescribedBy || undefined"
                    readonly
                >
                <div v-if="helperText || fieldCounterText" class="builder-field-meta">
                    <span v-if="helperText" :id="helperTextId" class="builder-field-help">{{ helperText }}</span>
                    <span v-if="fieldCounterText" :id="fieldCounterId" class="builder-field-count">{{ fieldCounterText }}</span>
                </div>
            </div>

            <div v-else-if="element.type === 'textarea'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label" :for="fieldControlId">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <textarea
                    :id="fieldControlId"
                    :class="['builder-render-field', 'builder-render-textarea', element.props.class || 'form-control']"
                    :rows="Number(element.props.rows || 4)"
                    :placeholder="element.props.placeholder || '请输入多行内容'"
                    :aria-describedby="fieldDescribedBy || undefined"
                    readonly
                >{{ element.props.value || '' }}</textarea>
                <div v-if="helperText || fieldCounterText" class="builder-field-meta">
                    <span v-if="helperText" :id="helperTextId" class="builder-field-help">{{ helperText }}</span>
                    <span v-if="fieldCounterText" :id="fieldCounterId" class="builder-field-count">{{ fieldCounterText }}</span>
                </div>
            </div>

            <div v-else-if="element.type === 'select'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label" :for="fieldControlId">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <select
                    :id="fieldControlId"
                    :class="['builder-render-field', element.props.class || 'form-control']"
                    :value="element.props.value || ''"
                    :aria-describedby="fieldDescribedBy || undefined"
                    disabled
                >
                    <option value="">{{ element.props.placeholder || '请选择' }}</option>
                    <option
                        v-for="option in choiceOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <div v-if="helperText" class="builder-field-meta">
                    <span :id="helperTextId" class="builder-field-help">{{ helperText }}</span>
                </div>
            </div>

            <div v-else-if="element.type === 'radio-group'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <div :class="['builder-choice-list', { 'is-horizontal': isHorizontalChoiceLayout }]">
                    <label v-for="option in choiceOptions" :key="option.value" class="builder-choice-item">
                        <input
                            type="radio"
                            :checked="(element.props.value || '') === option.value"
                            disabled
                        >
                        <span>{{ option.label }}</span>
                    </label>
                    <div v-if="choiceOptions.length === 0" class="builder-choice-empty">
                        请先在右侧配置选项内容
                    </div>
                </div>
                <div v-if="helperText" class="builder-field-meta">
                    <span :id="helperTextId" class="builder-field-help">{{ helperText }}</span>
                </div>
            </div>

            <div v-else-if="element.type === 'checkbox-group'" class="builder-field-group" :style="elementStyle">
                <label v-if="element.props.label" class="builder-field-label">
                    {{ element.props.label }}
                    <span v-if="element.props.required" class="builder-field-required">*</span>
                </label>
                <div :class="['builder-choice-list', { 'is-horizontal': isHorizontalChoiceLayout }]">
                    <label v-for="option in choiceOptions" :key="option.value" class="builder-choice-item">
                        <input
                            type="checkbox"
                            :checked="choiceValues.includes(option.value)"
                            disabled
                        >
                        <span>{{ option.label }}</span>
                    </label>
                    <div v-if="choiceOptions.length === 0" class="builder-choice-empty">
                        请先在右侧配置选项内容
                    </div>
                </div>
                <div v-if="helperText" class="builder-field-meta">
                    <span :id="helperTextId" class="builder-field-help">{{ helperText }}</span>
                </div>
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
            theme: createDefaultTheme(),
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
            submissionRecords: [],
            submissionSearchKeyword: '',
            submissionSourceFilter: 'all',
            submissionPageFilter: 'all',
            submissionAnalysisFieldKey: '',
            selectedSubmissionId: null,
            draftInfo: null,
            historyStack: [],
            historyIndex: -1,
            historyTimer: null,
            draftTimer: null,
            isApplyingHistory: false,
            viewportMode: 'desktop',
            activeDropTarget: null,
            isCanvasDragOver: false,
            isPreviewLoading: false,
            isGenerating: false,
            isSavingProject: false,
            isProjectLoading: false,
            isProjectListLoading: false,
            isSubmissionListLoading: false,
            isSubmissionClearing: false,
            deletingSubmissionId: null,
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
                { type: 'select', name: '下拉选择', icon: 'bi bi-menu-button-wide', description: '适合行业、套餐、来源等单选场景。' },
                { type: 'radio-group', name: '单选组', icon: 'bi bi-ui-radios', description: '适合性别、档位、意向等级等互斥选择。' },
                { type: 'checkbox-group', name: '多选组', icon: 'bi bi-ui-checks', description: '适合兴趣标签、服务需求等多选收集。' },
                { type: 'form-summary', name: '表单摘要', icon: 'bi bi-card-checklist', description: '自动汇总当前可见字段，适合放在最后一步做提交前复核。' },
                { type: 'spacer', name: '间距块', icon: 'bi bi-arrows-expand-vertical', description: '快速拉开区块间距，调节页面节奏。' }
            ],
            layoutComponents: [
                { type: 'row', name: '行布局', icon: 'bi bi-layout-three-columns', description: '横向排列多个组件，适合卡片组。' },
                { type: 'div', name: '容器', icon: 'bi bi-square', description: '纵向包裹内容，适合做内容模块。' }
            ],
            sectionTemplates: SECTION_TEMPLATES.map((template) => ({
                key: template.key,
                name: template.name,
                icon: template.icon,
                description: template.description
            })),
            buttonActionOptions: BUTTON_ACTION_OPTIONS,
            inputTypeOptions: INPUT_TYPE_OPTIONS,
            requestMethodOptions: REQUEST_METHOD_OPTIONS,
            optionLayoutOptions: OPTION_LAYOUT_OPTIONS,
            choiceOptionPresets: CHOICE_OPTION_PRESETS,
            commonFieldPresets: COMMON_FIELD_PRESETS,
            conditionOperatorOptions: CONDITION_OPERATOR_OPTIONS,
            themePresets: Object.entries(THEME_PRESETS).map(([key, preset]) => ({
                key,
                name: preset.name
            })),
            elementLabels: {
                text: '文本',
                image: '图片',
                button: '按钮',
                'form-summary': '表单摘要',
                input: '输入框',
                textarea: '文本域',
                select: '下拉选择',
                'radio-group': '单选组',
                'checkbox-group': '多选组',
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
                summaryTitle: '摘要标题',
                emptyText: '空态文案',
                label: '字段标签',
                required: '必填',
                helperText: '说明文案',
                placeholder: '占位提示',
                value: '默认值',
                rows: '可视行数',
                minLength: '最小长度',
                maxLength: '最大长度',
                minValue: '最小值',
                maxValue: '最大值',
                fieldKey: '字段标识',
                stepIndex: '所属步骤',
                stepTitle: '步骤标题',
                inputType: '字段类型',
                options: '选项配置',
                optionLayout: '选项排布',
                validationPattern: '校验规则',
                validationMessage: '校验提示',
                conditionEnabled: '条件显隐',
                conditionFieldKey: '依赖字段',
                conditionOperator: '判断方式',
                conditionValue: '比较值',
                width: '宽度',
                height: '高度',
                style: '内联样式',
                actionType: '按钮动作',
                actionValue: '动作内容',
                submitEndpoint: '提交接口',
                submitMethod: '请求方法',
                submitResetForm: '提交后清空',
                submitRedirectUrl: '提交后跳转'
            },
            propInputTypes: {
                src: 'text',
                alt: 'text',
                width: 'text',
                height: 'text',
                text: 'text',
                label: 'text',
                required: 'checkbox',
                helperText: 'text',
                placeholder: 'text',
                value: 'text',
                rows: 'number',
                minLength: 'number',
                maxLength: 'number',
                minValue: 'number',
                maxValue: 'number',
                fieldKey: 'text',
                stepIndex: 'number',
                stepTitle: 'text',
                inputType: 'text',
                options: 'text',
                optionLayout: 'text',
                validationPattern: 'text',
                validationMessage: 'text',
                conditionEnabled: 'checkbox',
                conditionFieldKey: 'text',
                conditionOperator: 'text',
                conditionValue: 'text',
                content: 'text',
                class: 'text',
                style: 'text',
                actionType: 'text',
                actionValue: 'text',
                submitEndpoint: 'text',
                submitMethod: 'text',
                submitResetForm: 'checkbox',
                submitRedirectUrl: 'text'
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
        safeSubmissionRecords() {
            return Array.isArray(this.submissionRecords) ? this.submissionRecords : [];
        },
        submissionSourceOptions() {
            return Array.from(new Set(
                this.safeSubmissionRecords
                    .map((item) => item && item.source ? String(item.source) : '')
                    .filter(Boolean)
            ));
        },
        submissionPageOptions() {
            return Array.from(new Set(
                this.safeSubmissionRecords
                    .map((item) => item && (item.page_title || item.page_name) ? String(item.page_title || item.page_name) : '')
                    .filter(Boolean)
            ));
        },
        filteredSubmissionRecords() {
            const keyword = String(this.submissionSearchKeyword || '').trim().toLowerCase();

            return this.safeSubmissionRecords.filter((submission) => {
                const source = String(submission.source || '');
                const pageLabel = String(submission.page_title || submission.page_name || '');

                if (this.submissionSourceFilter !== 'all' && source !== this.submissionSourceFilter) {
                    return false;
                }

                if (this.submissionPageFilter !== 'all' && pageLabel !== this.submissionPageFilter) {
                    return false;
                }

                if (!keyword) {
                    return true;
                }

                const fieldEntries = this.getSubmissionFieldEntries(submission)
                    .map((field) => `${field.label} ${field.key} ${field.displayValue}`)
                    .join(' ');
                const haystack = [
                    submission.id,
                    submission.project_name,
                    source,
                    pageLabel,
                    fieldEntries
                ].join(' ').toLowerCase();

                return haystack.includes(keyword);
            });
        },
        submissionStats() {
            const records = this.filteredSubmissionRecords;
            const todayKey = new Date().toISOString().slice(0, 10);
            const todayCount = records.filter((submission) => {
                const rawDate = submission.submitted_at || submission.created_at || '';
                return String(rawDate).slice(0, 10) === todayKey;
            }).length;
            const pageCount = new Set(records.map((submission) => submission.page_name || submission.page_title || '')).size;

            return {
                total: records.length,
                today: todayCount,
                pageCount
            };
        },
        submissionSourceBreakdown() {
            const statsMap = new Map();

            this.filteredSubmissionRecords.forEach((submission) => {
                const source = String(submission && submission.source ? submission.source : 'unknown');
                const nextItem = statsMap.get(source) || {
                    key: source,
                    label: this.getSubmissionSourceLabel(source),
                    count: 0
                };

                nextItem.count += 1;
                statsMap.set(source, nextItem);
            });

            return Array.from(statsMap.values())
                .sort((left, right) => right.count - left.count || left.label.localeCompare(right.label, 'zh-CN'));
        },
        submissionPageBreakdown() {
            const statsMap = new Map();

            this.filteredSubmissionRecords.forEach((submission) => {
                const pageKey = String(submission.page_name || submission.page_title || 'unknown');
                const pageLabel = String(submission.page_title || submission.page_name || '未命名页面');
                const nextItem = statsMap.get(pageKey) || {
                    key: pageKey,
                    label: pageLabel,
                    count: 0
                };

                nextItem.count += 1;
                statsMap.set(pageKey, nextItem);
            });

            return Array.from(statsMap.values())
                .sort((left, right) => right.count - left.count || left.label.localeCompare(right.label, 'zh-CN'));
        },
        submissionTrendSeries() {
            const counts = new Map();

            this.filteredSubmissionRecords.forEach((submission) => {
                const dateKey = this.normalizeSubmissionDateKey(submission.submitted_at || submission.created_at || '');

                if (!dateKey) {
                    return;
                }

                counts.set(dateKey, (counts.get(dateKey) || 0) + 1);
            });

            if (counts.size === 0) {
                return [];
            }

            const sortedKeys = Array.from(counts.keys()).sort();
            const latestKey = sortedKeys[sortedKeys.length - 1];
            const dateKeys = this.buildRecentDateKeys(latestKey, 7);
            const maxCount = Math.max(...dateKeys.map((dateKey) => counts.get(dateKey) || 0), 0);

            return dateKeys.map((dateKey) => {
                const count = counts.get(dateKey) || 0;
                return {
                    key: dateKey,
                    label: this.formatSubmissionDateLabel(dateKey, { short: true }),
                    fullLabel: this.formatSubmissionDateLabel(dateKey),
                    count,
                    barHeight: maxCount > 0 ? Math.max(count > 0 ? 14 : 0, Math.round((count / maxCount) * 100)) : 0
                };
            });
        },
        submissionTrendStats() {
            const series = this.submissionTrendSeries;

            if (series.length === 0) {
                return {
                    total: 0,
                    average: '0',
                    peakLabel: '',
                    peakCount: 0,
                    latestLabel: '',
                    latestCount: 0,
                    delta: 0,
                    direction: 'flat'
                };
            }

            const total = series.reduce((sum, item) => sum + item.count, 0);
            const peak = series.reduce((best, item) => {
                if (!best || item.count > best.count) {
                    return item;
                }

                return best;
            }, null);
            const latest = series[series.length - 1];
            const previous = series[series.length - 2] || null;
            const delta = previous ? latest.count - previous.count : latest.count;

            return {
                total,
                average: series.length > 0 ? (total / series.length).toFixed(total % series.length === 0 ? 0 : 1) : '0',
                peakLabel: peak ? peak.fullLabel : '',
                peakCount: peak ? peak.count : 0,
                latestLabel: latest ? latest.fullLabel : '',
                latestCount: latest ? latest.count : 0,
                delta,
                direction: delta > 0 ? 'up' : (delta < 0 ? 'down' : 'flat')
            };
        },
        submissionFieldCatalog() {
            const statsMap = new Map();

            this.filteredSubmissionRecords.forEach((submission) => {
                this.getSubmissionFieldEntries(submission).forEach((field) => {
                    const fieldMeta = this.getSubmissionFieldMeta(submission, field.key) || {};
                    const currentItem = statsMap.get(field.key) || {
                        key: field.key,
                        label: field.label,
                        type: fieldMeta.type || '',
                        recordCount: 0,
                        filledCount: 0
                    };

                    currentItem.recordCount += 1;
                    if (this.isSubmissionFieldFilled(field.rawValue)) {
                        currentItem.filledCount += 1;
                    }

                    if (!currentItem.type && fieldMeta.type) {
                        currentItem.type = fieldMeta.type;
                    }

                    statsMap.set(field.key, currentItem);
                });
            });

            return Array.from(statsMap.values())
                .sort((left, right) => {
                    if (right.filledCount !== left.filledCount) {
                        return right.filledCount - left.filledCount;
                    }

                    return left.label.localeCompare(right.label, 'zh-CN');
                });
        },
        activeSubmissionAnalysisFieldKey() {
            if (this.submissionFieldCatalog.length === 0) {
                return '';
            }

            if (this.submissionAnalysisFieldKey && this.submissionFieldCatalog.some((field) => field.key === this.submissionAnalysisFieldKey)) {
                return this.submissionAnalysisFieldKey;
            }

            return this.submissionFieldCatalog[0].key;
        },
        activeSubmissionAnalysisField() {
            return this.submissionFieldCatalog.find((field) => field.key === this.activeSubmissionAnalysisFieldKey) || null;
        },
        submissionValueDistribution() {
            const fieldKey = this.activeSubmissionAnalysisFieldKey;
            if (!fieldKey) {
                return [];
            }

            const valueMap = new Map();

            this.filteredSubmissionRecords.forEach((submission) => {
                const formData = submission && submission.form_data && typeof submission.form_data === 'object'
                    ? submission.form_data
                    : {};
                const rawValue = Object.prototype.hasOwnProperty.call(formData, fieldKey)
                    ? formData[fieldKey]
                    : undefined;
                const labels = this.getSubmissionFieldValueLabels(submission, fieldKey, rawValue);

                labels.forEach((label) => {
                    const currentItem = valueMap.get(label) || {
                        label,
                        count: 0
                    };
                    currentItem.count += 1;
                    valueMap.set(label, currentItem);
                });
            });

            const items = Array.from(valueMap.values())
                .sort((left, right) => right.count - left.count || left.label.localeCompare(right.label, 'zh-CN'));
            const maxCount = items[0] ? items[0].count : 0;
            const presentCount = items.reduce((total, item) => total + item.count, 0);

            return items.map((item) => ({
                ...item,
                percentage: presentCount > 0 ? Math.round((item.count / presentCount) * 100) : 0,
                barWidth: maxCount > 0 ? Math.max(10, Math.round((item.count / maxCount) * 100)) : 0
            }));
        },
        submissionAnalysisStats() {
            const fieldKey = this.activeSubmissionAnalysisFieldKey;
            const records = this.filteredSubmissionRecords;

            if (!fieldKey || records.length === 0) {
                return {
                    totalRecords: records.length,
                    filledRecords: 0,
                    emptyRecords: records.length,
                    fillRate: 0,
                    uniqueValueCount: 0,
                    topValueLabel: '',
                    topValueCount: 0
                };
            }

            let filledRecords = 0;

            records.forEach((submission) => {
                const formData = submission && submission.form_data && typeof submission.form_data === 'object'
                    ? submission.form_data
                    : {};
                const rawValue = Object.prototype.hasOwnProperty.call(formData, fieldKey)
                    ? formData[fieldKey]
                    : undefined;

                if (this.isSubmissionFieldFilled(rawValue)) {
                    filledRecords += 1;
                }
            });

            const topValue = this.submissionValueDistribution[0] || null;

            return {
                totalRecords: records.length,
                filledRecords,
                emptyRecords: Math.max(0, records.length - filledRecords),
                fillRate: records.length > 0 ? Math.round((filledRecords / records.length) * 100) : 0,
                uniqueValueCount: this.submissionValueDistribution.length,
                topValueLabel: topValue ? topValue.label : '',
                topValueCount: topValue ? topValue.count : 0
            };
        },
        fieldDefinitionMap() {
            return this.buildFieldDefinitionMap();
        },
        currentPageFieldDefinitionMap() {
            return this.buildPageFieldDefinitionMap({
                pageName: this.currentPage.name || 'index'
            });
        },
        currentPageFieldDiagnostics() {
            return this.buildPageFieldDiagnostics(this.currentPage);
        },
        projectFieldDiagnostics() {
            return this.buildProjectFieldDiagnostics();
        },
        selectedSubmission() {
            if (!this.selectedSubmissionId) {
                return null;
            }

            return this.safeSubmissionRecords.find((item) => String(item.id) === String(this.selectedSubmissionId)) || null;
        },
        pageCount() {
            return this.safePages.length;
        },
        currentElementCount() {
            return this.currentPageElements.length;
        },
        projectThemeStyle() {
            return {
                '--project-primary': this.theme.primary,
                '--project-accent': this.theme.accent,
                '--project-surface': this.theme.surface,
                '--project-page-bg': this.theme.pageBackground,
                '--project-text': this.theme.text,
                '--project-radius': this.theme.radius
            };
        },
        pageOutlineItems() {
            return this.buildOutlineItems(this.currentPageElements);
        },
        selectedElement() {
            if (!this.selectedElementId || !this.currentPage) {
                return null;
            }

            return this.findElementById(this.currentPageElements, this.selectedElementId);
        },
        selectedTemplateRoot() {
            if (!this.selectedElementId) {
                return null;
            }

            return this.findTemplateRootForElement(this.currentPageElements, this.selectedElementId);
        },
        selectedTemplateFields() {
            if (!this.selectedTemplateRoot) {
                return [];
            }

            const templateKey = this.selectedTemplateRoot.props && this.selectedTemplateRoot.props.templateKey;
            const fieldConfigs = TEMPLATE_FIELD_CONFIGS[templateKey] || [];

            return fieldConfigs.map((field) => ({
                ...field,
                value: this.getTemplateFieldValue(this.selectedTemplateRoot, field)
            }));
        },
        selectedButtonActionType() {
            if (!this.selectedElement || this.selectedElement.type !== 'button') {
                return 'none';
            }

            return this.selectedElement.props.actionType || 'none';
        },
        selectedInputType() {
            if (!this.selectedElement || this.selectedElement.type !== 'input') {
                return 'text';
            }

            return this.selectedElement.props.inputType || 'text';
        },
        availableCommonFieldPresets() {
            if (!this.selectedElementType) {
                return [];
            }

            return this.commonFieldPresets.filter((preset) => Array.isArray(preset.types) && preset.types.includes(this.selectedElementType));
        },
        selectedOptionLayout() {
            if (!this.selectedElement || !['radio-group', 'checkbox-group'].includes(this.selectedElement.type)) {
                return 'vertical';
            }

            return this.selectedElement.props.optionLayout || 'vertical';
        },
        currentSubmissionScopeLabel() {
            if (this.projectId) {
                return `项目 #${this.projectId}`;
            }

            return this.projectName || '未命名项目';
        },
        currentPageStepCatalog() {
            return this.buildPageStepCatalog(this.currentPageElements);
        },
        isCurrentPageMultiStep() {
            return this.currentPageStepCatalog.length > 1 || this.currentPageStepCatalog.some((step) => step.index > 1);
        },
        editablePropFields() {
            if (!this.selectedElement || !this.selectedElement.props) {
                return [];
            }

            const keys = Object.keys(this.selectedElement.props).filter((key) => !INTERNAL_PROP_KEYS.has(key));
            const orderedKeys = [
                ...PROP_ORDER.filter((key) => keys.includes(key)),
                ...keys.filter((key) => !PROP_ORDER.includes(key))
            ];
            const textareaKeys = new Set(['style', 'options']);
            const checkboxKeys = new Set(['required', 'submitResetForm']);
            const hiddenKeys = new Set(['actionType', 'actionValue', 'submitEndpoint', 'submitMethod', 'submitRedirectUrl', 'submitResetForm', 'conditionEnabled', 'conditionFieldKey', 'conditionOperator', 'conditionValue', 'stepIndex', 'stepTitle']);
            const selectKeys = new Set(['inputType', 'optionLayout', 'submitMethod']);

            if (this.selectedElement.type === 'text') {
                textareaKeys.add('content');
            }

            if (this.selectedElement.type === 'textarea') {
                textareaKeys.add('value');
            }

            return orderedKeys.map((key) => ({
                key,
                label: this.formName[key] || key,
                control: checkboxKeys.has(key)
                    ? 'checkbox'
                    : (selectKeys.has(key) ? 'select' : (textareaKeys.has(key) ? 'textarea' : 'input')),
                type: this.propInputTypes[key] || 'text',
                options: key === 'inputType'
                    ? this.inputTypeOptions
                    : (key === 'optionLayout'
                        ? this.optionLayoutOptions
                        : (key === 'submitMethod' ? this.requestMethodOptions : []))
            })).filter((field) => !hiddenKeys.has(field.key));
        },
        selectedElementType() {
            return this.selectedElement ? this.selectedElement.type : '';
        },
        selectedElementStepIndex() {
            return this.selectedElement ? parseStepIndex(this.selectedElement.props.stepIndex || '1') : 1;
        },
        selectedElementStepLabel() {
            return this.selectedElement ? getStepLabel(this.selectedElementStepIndex, this.selectedElement.props.stepTitle || '') : '';
        },
        selectedElementFieldKey() {
            return resolveElementFieldKey(this.selectedElement);
        },
        selectedFieldKeyIssue() {
            if (!this.selectedElement || !FORM_FIELD_TYPES.includes(this.selectedElement.type)) {
                return null;
            }

            const rawFieldKey = String((this.selectedElement.props && this.selectedElement.props.fieldKey) || '').trim();
            if (!rawFieldKey) {
                return {
                    tone: 'warning',
                    message: '当前未显式填写字段标识，预览和导出时会回退为自动生成的 key。'
                };
            }

            const normalizedFieldKey = normalizeFieldKeyInput(rawFieldKey, {
                allowEmpty: true,
                fallback: 'field'
            });
            if (normalizedFieldKey !== rawFieldKey) {
                return {
                    tone: 'warning',
                    message: `字段标识会被规范成 ${normalizedFieldKey}，建议使用英文、数字和下划线。`
                };
            }

            const duplicate = this.currentPageFieldDiagnostics.duplicates.find((item) => item.key === rawFieldKey);
            if (duplicate) {
                return {
                    tone: 'danger',
                    message: `当前页面有 ${duplicate.count} 个字段共用了 ${rawFieldKey}，会影响条件显隐、汇总和提交数据。`
                };
            }

            return {
                tone: 'muted',
                message: '建议使用英文、数字和下划线，并保证当前页面内唯一。'
            };
        },
        conditionalFieldOptions() {
            return Object.values(this.currentPageFieldDefinitionMap)
                .filter((field) => field.key !== this.selectedElementFieldKey)
                .sort((left, right) => String(left.label || left.key).localeCompare(String(right.label || right.key), 'zh-CN'));
        },
        selectedConditionOperator() {
            if (!this.selectedElement) {
                return 'equals';
            }

            return normalizeConditionOperator(this.selectedElement.props.conditionOperator || 'equals');
        },
        selectedConditionSourceDefinition() {
            if (!this.selectedElement) {
                return null;
            }

            const fieldKey = this.selectedElement.props.conditionFieldKey || '';
            return this.currentPageFieldDefinitionMap[fieldKey] || null;
        },
        shouldShowConditionValue() {
            return !['filled', 'empty'].includes(this.selectedConditionOperator);
        },
        selectedConditionValueOptions() {
            const definition = this.selectedConditionSourceDefinition;
            return definition && Array.isArray(definition.options) ? definition.options : [];
        },
        selectedConditionFieldIssue() {
            if (!this.selectedElement || !this.selectedElement.props || !this.selectedElement.props.conditionEnabled) {
                return null;
            }

            const conditionFieldKey = String(this.selectedElement.props.conditionFieldKey || '').trim();
            if (!conditionFieldKey) {
                return {
                    tone: 'warning',
                    message: '当前还没有选择依赖字段，条件显隐不会生效。'
                };
            }

            const duplicate = this.currentPageFieldDiagnostics.duplicates.find((item) => item.key === conditionFieldKey);
            if (duplicate) {
                return {
                    tone: 'danger',
                    message: `依赖字段 ${conditionFieldKey} 在当前页面重复出现，条件判断结果可能不稳定。`
                };
            }

            const invalidIssue = this.currentPageFieldDiagnostics.invalidConditions.find((item) => String(item.elementId) === String(this.selectedElement.id));
            if (invalidIssue) {
                return {
                    tone: 'danger',
                    message: `依赖字段 ${conditionFieldKey} 当前不存在，请重新选择有效字段。`
                };
            }

            return null;
        },
        shouldUseConditionValueOptions() {
            return this.shouldShowConditionValue && this.selectedConditionValueOptions.length > 0;
        },
        selectedConditionSummary() {
            return describeConditionRule(this.selectedElement ? this.selectedElement.props : {}, this.currentPageFieldDefinitionMap);
        },
        canUndo() {
            return this.historyIndex > 0;
        },
        canRedo() {
            return this.historyIndex >= 0 && this.historyIndex < this.historyStack.length - 1;
        }
    },
    mounted() {
        this.refreshLocalDraftInfo();
        this.initializeBuilder();
        window.addEventListener('keydown', this.handleKeydown);
        window.addEventListener('dragend', this.clearDropTarget);
        window.builderSubmitAction = (trigger, config = {}) => this.handleBuilderSubmitAction(trigger, config);
        window.builderStepAction = (trigger, direction = 'next') => this.handleBuilderStepAction(trigger, direction);
        this.$nextTick(() => {
            this.setupPreviewStageListeners();
        });
    },
    unmounted() {
        window.removeEventListener('keydown', this.handleKeydown);
        window.removeEventListener('dragend', this.clearDropTarget);
        this.teardownPreviewStageListeners();

        if (this.statusTimer) {
            window.clearTimeout(this.statusTimer);
        }

        if (this.historyTimer) {
            window.clearTimeout(this.historyTimer);
        }

        if (this.draftTimer) {
            window.clearTimeout(this.draftTimer);
        }

        if (window.builderSubmitAction) {
            delete window.builderSubmitAction;
        }

        if (window.builderStepAction) {
            delete window.builderStepAction;
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
        formatDateTime(value) {
            if (!value) {
                return '未知时间';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return String(value);
            }

            return new Intl.DateTimeFormat('zh-CN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date);
        },
        normalizeSubmissionDateKey(value) {
            const directValue = String(value || '').trim();

            if (/^\d{4}-\d{2}-\d{2}$/.test(directValue)) {
                return directValue;
            }

            const date = new Date(directValue);
            if (Number.isNaN(date.getTime())) {
                return '';
            }

            return date.toISOString().slice(0, 10);
        },
        formatSubmissionDateLabel(dateKey, options = {}) {
            if (!dateKey) {
                return '未知日期';
            }

            const [year, month, day] = String(dateKey).split('-');
            if (!year || !month || !day) {
                return String(dateKey);
            }

            if (options.short) {
                return `${month}-${day}`;
            }

            return `${year}-${month}-${day}`;
        },
        buildRecentDateKeys(endDateKey, length = 7) {
            const [year, month, day] = String(endDateKey || '').split('-').map((item) => Number(item));

            if (!year || !month || !day) {
                return [];
            }

            const result = [];

            for (let offset = length - 1; offset >= 0; offset -= 1) {
                const current = new Date(Date.UTC(year, month - 1, day - offset));
                result.push(current.toISOString().slice(0, 10));
            }

            return result;
        },
        normalizeTheme(theme = {}) {
            const defaults = createDefaultTheme();

            return {
                primary: theme.primary || defaults.primary,
                accent: theme.accent || defaults.accent,
                surface: theme.surface || defaults.surface,
                pageBackground: theme.pageBackground || defaults.pageBackground,
                text: theme.text || defaults.text,
                radius: theme.radius || defaults.radius
            };
        },
        applyThemePreset(key) {
            const preset = THEME_PRESETS[key];

            if (!preset) {
                this.setStatus('主题预设不存在。', 'danger');
                return;
            }

            this.theme = deepClone(preset.theme);
            this.captureHistory();
            this.setStatus(`已应用${preset.name}主题。`, 'success');
        },
        queueDraftSave() {
            if (this.draftTimer) {
                window.clearTimeout(this.draftTimer);
            }

            this.draftTimer = window.setTimeout(() => {
                this.draftTimer = null;
                this.saveLocalDraft(true);
            }, 500);
        },
        saveLocalDraft(silent = false) {
            try {
                const payload = {
                    version: 1,
                    savedAt: new Date().toISOString(),
                    snapshot: this.snapshotState()
                };

                window.localStorage.setItem(LOCAL_DRAFT_STORAGE_KEY, JSON.stringify(payload));
                this.draftInfo = {
                    savedAt: payload.savedAt,
                    projectName: payload.snapshot.projectName || '未命名项目'
                };

                if (!silent) {
                    this.setStatus('本地草稿已保存。', 'success');
                }
            } catch (error) {
                if (!silent) {
                    this.setStatus('本地草稿保存失败。', 'danger');
                }
            }
        },
        refreshLocalDraftInfo() {
            try {
                const rawDraft = window.localStorage.getItem(LOCAL_DRAFT_STORAGE_KEY);

                if (!rawDraft) {
                    this.draftInfo = null;
                    return;
                }

                const parsedDraft = JSON.parse(rawDraft);
                const snapshot = parsedDraft && parsedDraft.snapshot ? parsedDraft.snapshot : null;

                if (!snapshot) {
                    this.draftInfo = null;
                    return;
                }

                this.draftInfo = {
                    savedAt: parsedDraft.savedAt || null,
                    projectName: snapshot.projectName || '未命名项目'
                };
            } catch (error) {
                this.draftInfo = null;
            }
        },
        restoreLocalDraft() {
            try {
                const rawDraft = window.localStorage.getItem(LOCAL_DRAFT_STORAGE_KEY);

                if (!rawDraft) {
                    this.setStatus('当前没有可恢复的本地草稿。', 'info');
                    return;
                }

                const parsedDraft = JSON.parse(rawDraft);
                const snapshot = parsedDraft && parsedDraft.snapshot ? parsedDraft.snapshot : null;

                if (!snapshot) {
                    throw new Error('草稿内容无效');
                }

                this.applyHistorySnapshot(snapshot);
                this.resetHistory();
                this.refreshLocalDraftInfo();
                this.setStatus('已恢复本地草稿。', 'success');
            } catch (error) {
                this.setStatus(error.message || '恢复本地草稿失败。', 'danger');
            }
        },
        clearLocalDraft() {
            try {
                window.localStorage.removeItem(LOCAL_DRAFT_STORAGE_KEY);
                this.draftInfo = null;
                this.setStatus('本地草稿已清空。', 'warning');
            } catch (error) {
                this.setStatus('清空本地草稿失败。', 'danger');
            }
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
        applyButtonActionPreset(actionType) {
            const defaults = {
                none: '',
                message: '操作成功',
                link: '/pages/index/index',
                'step-prev': '',
                'step-next': '',
                submit: '提交成功'
            };

            const nextProps = {
                actionType,
                actionValue: ['none', 'step-prev', 'step-next'].includes(actionType)
                    ? ''
                    : (this.selectedElement && this.selectedElement.props.actionValue) || defaults[actionType] || ''
            };

            if (actionType === 'submit') {
                nextProps.submitEndpoint = (this.selectedElement && this.selectedElement.props.submitEndpoint) || '/api/form-submissions';
                nextProps.submitMethod = (this.selectedElement && this.selectedElement.props.submitMethod) || 'POST';
            }

            this.applySelectedProps(nextProps);
        },
        applyFieldPreset(className) {
            this.applySelectedProps({ class: className });
        },
        applyChoiceLayoutPreset(optionLayout) {
            this.applySelectedProps({ optionLayout });
        },
        applyChoiceOptionsPreset(presetKey) {
            const preset = CHOICE_OPTION_PRESETS.find((item) => item.key === presetKey);

            if (!preset) {
                return;
            }

            const nextProps = {
                options: preset.options
            };

            if (this.selectedElement && this.selectedElement.type === 'checkbox-group') {
                nextProps.value = '';
            } else {
                nextProps.value = preset.value || '';
            }

            if (this.selectedElement && this.selectedElement.type === 'select' && !this.selectedElement.props.placeholder) {
                nextProps.placeholder = '请选择';
            }

            this.applySelectedProps(nextProps);
        },
        buildUniqueFieldKey(baseKey = '') {
            const normalizedBaseKey = String(baseKey || 'field')
                .trim()
                .replace(/[^\w-]+/g, '_')
                .replace(/^_+|_+$/g, '') || 'field';
            const currentKey = this.selectedElementFieldKey || '';
            const usedKeys = new Set(Object.keys(this.currentPageFieldDefinitionMap || {}));

            if (!usedKeys.has(normalizedBaseKey) || currentKey === normalizedBaseKey) {
                return normalizedBaseKey;
            }

            let index = 2;
            let candidate = `${normalizedBaseKey}_${index}`;

            while (usedKeys.has(candidate) && currentKey !== candidate) {
                index += 1;
                candidate = `${normalizedBaseKey}_${index}`;
            }

            return candidate;
        },
        applyCommonFieldPreset(presetKey) {
            const preset = this.commonFieldPresets.find((item) => item.key === presetKey);

            if (!preset || !this.selectedElement || !preset.types.includes(this.selectedElement.type)) {
                return;
            }

            const nextProps = deepClone(preset.props || {});

            if (nextProps.fieldKey) {
                nextProps.fieldKey = this.buildUniqueFieldKey(nextProps.fieldKey);
            }

            this.applySelectedProps(nextProps);
            this.setStatus(`已应用“${preset.label}”字段预设。`, 'success');
        },
        applyFieldTypePreset(inputType) {
            const presets = {
                text: {
                    inputType: 'text',
                    validationPattern: '',
                    validationMessage: '',
                    placeholder: '请输入内容'
                },
                tel: {
                    inputType: 'tel',
                    validationPattern: '^1\\d{10}$',
                    validationMessage: '请输入有效的 11 位手机号',
                    placeholder: '请输入手机号'
                },
                email: {
                    inputType: 'email',
                    validationPattern: '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$',
                    validationMessage: '请输入有效的邮箱地址',
                    placeholder: '请输入邮箱'
                },
                number: {
                    inputType: 'number',
                    validationPattern: '^\\d+(\\.\\d+)?$',
                    validationMessage: '请输入数字',
                    placeholder: '请输入数字'
                }
            };

            const nextProps = presets[inputType];
            if (!nextProps) {
                return;
            }

            this.applySelectedProps(nextProps);
        },
        applyValidationPreset(type) {
            const presets = {
                none: {
                    validationPattern: '',
                    validationMessage: ''
                },
                phone: {
                    validationPattern: '^1\\d{10}$',
                    validationMessage: '请输入有效的 11 位手机号'
                },
                email: {
                    validationPattern: '^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$',
                    validationMessage: '请输入有效的邮箱地址'
                },
                number: {
                    validationPattern: '^\\d+(\\.\\d+)?$',
                    validationMessage: '请输入数字'
                }
            };

            if (!presets[type]) {
                return;
            }

            this.applySelectedProps(presets[type]);
        },
        applySubmitResultPreset(type) {
            const presets = {
                keep: {
                    submitEndpoint: '/api/form-submissions',
                    submitMethod: 'POST',
                    submitResetForm: false,
                    submitRedirectUrl: ''
                },
                reset: {
                    submitEndpoint: '/api/form-submissions',
                    submitMethod: 'POST',
                    submitResetForm: true
                },
                redirect: {
                    submitEndpoint: '/api/form-submissions',
                    submitMethod: 'POST',
                    submitRedirectUrl: this.projectType === 'wechat' ? '/pages/index/index' : 'https://example.com/success'
                }
            };

            if (!presets[type]) {
                return;
            }

            this.applySelectedProps(presets[type]);
        },
        getConditionDefaultValueForField(fieldKey) {
            const definition = this.currentPageFieldDefinitionMap[fieldKey] || null;
            const options = definition && Array.isArray(definition.options) ? definition.options : [];

            return options.length > 0 ? String(options[0].value || '') : '';
        },
        handleConditionEnabledChange(enabled) {
            if (!this.selectedElement) {
                return;
            }

            if (!enabled) {
                this.applySelectedProps({
                    conditionEnabled: false
                });
                return;
            }

            const conditionFieldKey = this.selectedElement.props.conditionFieldKey
                || (this.conditionalFieldOptions[0] && this.conditionalFieldOptions[0].key)
                || '';
            const conditionOperator = normalizeConditionOperator(this.selectedElement.props.conditionOperator || 'equals');
            const nextProps = {
                conditionEnabled: true,
                conditionFieldKey,
                conditionOperator
            };

            if (['filled', 'empty'].includes(conditionOperator)) {
                nextProps.conditionValue = '';
            } else {
                nextProps.conditionValue = this.selectedElement.props.conditionValue || this.getConditionDefaultValueForField(conditionFieldKey);
            }

            this.applySelectedProps(nextProps);
        },
        handleConditionFieldChange(fieldKey) {
            if (!this.selectedElement) {
                return;
            }

            const operator = this.selectedConditionOperator;
            const nextProps = {
                conditionFieldKey: fieldKey
            };
            const definition = this.currentPageFieldDefinitionMap[fieldKey] || null;
            const options = definition && Array.isArray(definition.options) ? definition.options : [];

            if (['filled', 'empty'].includes(operator)) {
                nextProps.conditionValue = '';
            } else if (options.length > 0) {
                const currentValue = this.selectedElement.props.conditionValue || '';
                const hasCurrentValue = options.some((option) => String(option.value) === String(currentValue));
                nextProps.conditionValue = hasCurrentValue ? currentValue : String(options[0].value || '');
            }

            this.applySelectedProps(nextProps);
        },
        handleConditionOperatorChange(operator) {
            if (!this.selectedElement) {
                return;
            }

            const nextOperator = normalizeConditionOperator(operator);
            const nextProps = {
                conditionOperator: nextOperator
            };

            if (['filled', 'empty'].includes(nextOperator)) {
                nextProps.conditionValue = '';
            } else if (this.selectedConditionValueOptions.length > 0) {
                const currentValue = this.selectedElement.props.conditionValue || '';
                const hasCurrentValue = this.selectedConditionValueOptions.some((option) => String(option.value) === String(currentValue));
                nextProps.conditionValue = hasCurrentValue
                    ? currentValue
                    : String(this.selectedConditionValueOptions[0].value || '');
            }

            this.applySelectedProps(nextProps);
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
        getSectionTemplateConfig(key) {
            return SECTION_TEMPLATES.find((template) => template.key === key) || null;
        },
        getRequestedProjectId() {
            const projectId = new URLSearchParams(window.location.search).get('project');
            return projectId ? String(projectId).trim() : '';
        },
        getRequestedTemplateKey() {
            const templateKey = new URLSearchParams(window.location.search).get('template');
            return templateKey && this.getSectionTemplateConfig(templateKey) ? templateKey : '';
        },
        syncProjectUrl(projectId = null) {
            if (!window.history || typeof window.history.replaceState !== 'function') {
                return;
            }

            const url = new URL(window.location.href);

            if (projectId) {
                url.searchParams.set('project', String(projectId));
                url.searchParams.delete('template');
            } else {
                url.searchParams.delete('project');
                url.searchParams.delete('template');
            }

            window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
        },
        async initializeBuilder() {
            await this.fetchProjects(true);
            await this.fetchSubmissions(true);

            const requestedProjectId = this.getRequestedProjectId();

            if (requestedProjectId) {
                await this.loadProject(requestedProjectId);

                if (this.projectId) {
                    return;
                }

                this.syncProjectUrl(null);
            }

            const requestedTemplateKey = this.getRequestedTemplateKey();

            if (requestedTemplateKey) {
                this.insertSectionTemplate(requestedTemplateKey);
                this.syncProjectUrl(null);
                this.resetHistory();
                return;
            }

            this.resetHistory();

            if (this.draftInfo) {
                this.setStatus('发现本地草稿，可在左侧项目管理区域恢复。', 'info');
            }
        },
        getButtonActionLabel(actionType) {
            const matched = BUTTON_ACTION_OPTIONS.find((item) => item.value === actionType);
            return matched ? matched.label : '无动作';
        },
        getButtonActionPlaceholder(actionType) {
            if (actionType === 'message') {
                return '例如：报名成功，我们会尽快联系你';
            }

            if (actionType === 'link') {
                return '例如：https://example.com 或 /pages/detail/detail';
            }

            if (actionType === 'step-prev') {
                return '上一步不需要额外内容';
            }

            if (actionType === 'step-next') {
                return '下一步不需要额外内容';
            }

            if (actionType === 'submit') {
                return '例如：提交成功，我们会尽快联系你';
            }

            return '当前动作不需要额外内容';
        },
        getButtonActionSummary(props = {}) {
            const actionType = props.actionType || 'none';

            if (actionType === 'none') {
                return '';
            }

            const label = this.getButtonActionLabel(actionType);
            const value = props.actionValue || '';
            const extras = [];

            if (actionType === 'step-prev' || actionType === 'step-next') {
                return label;
            }

            if (actionType === 'submit' && props.submitResetForm) {
                extras.push('提交后清空');
            }

            if (actionType === 'submit' && props.submitEndpoint) {
                extras.push(`提交到 ${props.submitEndpoint}`);
            }

            if (actionType === 'submit' && props.submitRedirectUrl) {
                extras.push(`跳转到 ${props.submitRedirectUrl}`);
            }

            const summary = value ? `${label}：${value}` : label;

            if (extras.length === 0) {
                return summary;
            }

            return `${summary}（${extras.join('，')}）`;
        },
        getSubmissionQueryParams() {
            const query = {};

            if (this.projectId) {
                query.project_id = this.projectId;
            } else if (this.projectName) {
                query.project_name = this.projectName;
            }

            return query;
        },
        buildQueryString(params = {}) {
            const search = new URLSearchParams();

            Object.entries(params).forEach(([key, value]) => {
                if (value === undefined || value === null || value === '') {
                    return;
                }

                search.set(key, String(value));
            });

            const query = search.toString();
            return query ? `?${query}` : '';
        },
        formatSubmissionMeta(submission) {
            const submittedAt = this.formatDateTime(submission.submitted_at || submission.created_at || '');
            const pageTitle = submission.page_title || submission.page_name || '未命名页面';
            const source = this.getSubmissionSourceLabel(submission.source || 'unknown');

            return `${submittedAt} · ${pageTitle} · ${source}`;
        },
        getSubmissionSourceLabel(source) {
            const sourceMap = {
                'builder-preview': '构建器预览',
                h5: 'H5 页面',
                wechat: '微信小程序'
            };

            return sourceMap[source] || source || 'unknown';
        },
        buildFieldDefinitionMap() {
            const definitions = {};

            this.safePages.forEach((page) => {
                this.collectFieldDefinitions(page.elements || [], page, definitions);
            });

            return definitions;
        },
        buildPageFieldDefinitionMap(pageContext = {}) {
            const pageName = pageContext.pageName || this.currentPage.name || 'index';
            const page = this.safePages.find((item) => item.name === pageName) || this.currentPage;
            const definitions = {};

            this.collectFieldDefinitions(page && page.elements ? page.elements : [], page, definitions);

            return definitions;
        },
        collectFieldKeyStats(elements, page, stats = {}) {
            (elements || []).forEach((element) => {
                const props = element && element.props ? element.props : {};
                const type = element && element.type ? element.type : '';

                if (FORM_FIELD_TYPES.includes(type)) {
                    const fieldKey = resolveElementFieldKey(element);
                    const nextItem = stats[fieldKey] || {
                        key: fieldKey,
                        count: 0,
                        labels: [],
                        elementIds: [],
                        pageName: page && page.name ? page.name : 'index',
                        pageTitle: page && page.title ? page.title : '首页'
                    };

                    nextItem.count += 1;
                    nextItem.elementIds.push(String(element.id || ''));
                    nextItem.labels.push(props.label || props.placeholder || fieldKey);
                    stats[fieldKey] = nextItem;
                }

                if (Array.isArray(element.children) && element.children.length > 0) {
                    this.collectFieldKeyStats(element.children, page, stats);
                }
            });

            return stats;
        },
        collectInvalidConditionRefs(elements, page, fieldStats = {}, issues = []) {
            (elements || []).forEach((element) => {
                const props = element && element.props ? element.props : {};

                if (props.conditionEnabled && props.conditionFieldKey) {
                    const dependencyKey = String(props.conditionFieldKey || '').trim();
                    if (dependencyKey && !fieldStats[dependencyKey]) {
                        issues.push({
                            elementId: String(element.id || ''),
                            elementType: element.type || '',
                            conditionFieldKey: dependencyKey,
                            pageName: page && page.name ? page.name : 'index',
                            pageTitle: page && page.title ? page.title : '首页'
                        });
                    }
                }

                if (Array.isArray(element.children) && element.children.length > 0) {
                    this.collectInvalidConditionRefs(element.children, page, fieldStats, issues);
                }
            });

            return issues;
        },
        buildPageFieldDiagnostics(page = null) {
            const currentPage = page || this.currentPage;
            const fieldStats = this.collectFieldKeyStats(currentPage && currentPage.elements ? currentPage.elements : [], currentPage, {});
            const duplicates = Object.values(fieldStats)
                .filter((item) => item.count > 1)
                .sort((left, right) => right.count - left.count || left.key.localeCompare(right.key, 'zh-CN'));
            const invalidConditions = this.collectInvalidConditionRefs(currentPage && currentPage.elements ? currentPage.elements : [], currentPage, fieldStats, []);

            return {
                pageName: currentPage && currentPage.name ? currentPage.name : 'index',
                pageTitle: currentPage && currentPage.title ? currentPage.title : '首页',
                fieldStats,
                duplicates,
                invalidConditions,
                hasIssues: duplicates.length > 0 || invalidConditions.length > 0
            };
        },
        buildProjectFieldDiagnostics() {
            const pages = this.safePages.map((page) => this.buildPageFieldDiagnostics(page));

            return {
                pages,
                duplicates: pages.flatMap((page) => page.duplicates),
                invalidConditions: pages.flatMap((page) => page.invalidConditions),
                hasIssues: pages.some((page) => page.hasIssues)
            };
        },
        buildFieldIntegrityMessage(diagnostics, actionLabel = '当前操作') {
            if (!diagnostics || !diagnostics.hasIssues) {
                return '';
            }

            const messageParts = [];
            const firstDuplicatePage = (diagnostics.pages || []).find((page) => page.duplicates.length > 0);
            const firstInvalidPage = (diagnostics.pages || []).find((page) => page.invalidConditions.length > 0);

            if (firstDuplicatePage) {
                const duplicateText = firstDuplicatePage.duplicates
                    .slice(0, 2)
                    .map((item) => `${item.key}（${item.count} 个）`)
                    .join('、');
                messageParts.push(`${firstDuplicatePage.pageTitle} 存在重复字段标识：${duplicateText}`);
            }

            if (firstInvalidPage) {
                const invalidText = firstInvalidPage.invalidConditions
                    .slice(0, 2)
                    .map((item) => item.conditionFieldKey)
                    .join('、');
                messageParts.push(`${firstInvalidPage.pageTitle} 存在失效的条件依赖：${invalidText}`);
            }

            return `${actionLabel}前请先修复字段配置问题。${messageParts.join('；')}`;
        },
        ensureProjectFieldIntegrity(actionLabel = '当前操作') {
            const diagnostics = this.projectFieldDiagnostics;

            if (!diagnostics.hasIssues) {
                return true;
            }

            this.setStatus(this.buildFieldIntegrityMessage(diagnostics, actionLabel), 'danger');
            return false;
        },
        collectFieldDefinitions(elements, page, definitions) {
            (elements || []).forEach((element) => {
                const props = element && element.props ? element.props : {};
                const type = element && element.type ? element.type : '';

                if (FORM_FIELD_TYPES.includes(type)) {
                    const fallbackKey = `field_${String(element.id || type).replace(/[^\w-]+/g, '_')}`;
                    const fieldKey = props.fieldKey || fallbackKey;

                    definitions[fieldKey] = {
                        key: fieldKey,
                        label: props.label || props.placeholder || fieldKey,
                        type,
                        options: parseChoiceOptions(props.options),
                        pageName: page && page.name ? page.name : 'index',
                        pageTitle: page && page.title ? page.title : '首页'
                    };
                }

                if (Array.isArray(element.children) && element.children.length > 0) {
                    this.collectFieldDefinitions(element.children, page, definitions);
                }
            });
        },
        buildPageStepCatalog(elements = []) {
            const stepMap = new Map();

            const visit = (items) => {
                (items || []).forEach((element) => {
                    const props = element && element.props ? element.props : {};
                    const stepIndex = parseStepIndex(props.stepIndex || '1');
                    const current = stepMap.get(stepIndex) || {
                        index: stepIndex,
                        title: '',
                        label: ''
                    };

                    if (!current.title && String(props.stepTitle || '').trim()) {
                        current.title = String(props.stepTitle || '').trim();
                    }

                    stepMap.set(stepIndex, current);

                    if (Array.isArray(element.children) && element.children.length > 0) {
                        visit(element.children);
                    }
                });
            };

            visit(elements);

            return Array.from(stepMap.values())
                .sort((left, right) => left.index - right.index)
                .map((step) => ({
                    ...step,
                    label: getStepLabel(step.index, step.title)
                }));
        },
        getFieldDefinition(fieldKey) {
            return this.fieldDefinitionMap[fieldKey] || null;
        },
        getSubmissionFieldMeta(submission, fieldKey) {
            const submissionFieldMeta = submission && submission.field_meta && typeof submission.field_meta === 'object'
                ? submission.field_meta[fieldKey]
                : null;

            if (submissionFieldMeta && typeof submissionFieldMeta === 'object') {
                return submissionFieldMeta;
            }

            return this.getFieldDefinition(fieldKey);
        },
        getSubmissionFieldLabel(submission, fieldKey) {
            const definition = this.getSubmissionFieldMeta(submission, fieldKey);
            return definition && definition.label ? definition.label : fieldKey;
        },
        getFieldOptionLabel(submission, fieldKey, rawValue) {
            const definition = this.getSubmissionFieldMeta(submission, fieldKey);
            const options = definition && Array.isArray(definition.options) ? definition.options : [];
            const matched = options.find((option) => String(option.value) === String(rawValue));

            return matched ? matched.label : rawValue;
        },
        formatSubmissionFieldValue(submission, fieldKey, fieldValue) {
            if (Array.isArray(fieldValue)) {
                return fieldValue.map((item) => this.getFieldOptionLabel(submission, fieldKey, item)).join(', ');
            }

            if (fieldValue === null || fieldValue === undefined || fieldValue === '') {
                return '未填写';
            }

            return String(this.getFieldOptionLabel(submission, fieldKey, fieldValue));
        },
        isSubmissionFieldFilled(fieldValue) {
            if (Array.isArray(fieldValue)) {
                return fieldValue.some((item) => String(item || '').trim() !== '');
            }

            return fieldValue !== null && fieldValue !== undefined && String(fieldValue).trim() !== '';
        },
        getSubmissionFieldValueLabels(submission, fieldKey, fieldValue) {
            if (!this.isSubmissionFieldFilled(fieldValue)) {
                return [];
            }

            if (Array.isArray(fieldValue)) {
                return fieldValue
                    .map((item) => this.getFieldOptionLabel(submission, fieldKey, item))
                    .map((item) => String(item || '').trim())
                    .filter(Boolean);
            }

            return [String(this.getFieldOptionLabel(submission, fieldKey, fieldValue) || '').trim()].filter(Boolean);
        },
        getSubmissionFieldEntries(submission) {
            const formData = submission && submission.form_data && typeof submission.form_data === 'object'
                ? submission.form_data
                : {};

            return Object.entries(formData).map(([fieldKey, fieldValue]) => ({
                key: fieldKey,
                label: this.getSubmissionFieldLabel(submission, fieldKey),
                rawValue: fieldValue,
                displayValue: this.formatSubmissionFieldValue(submission, fieldKey, fieldValue)
            }));
        },
        getSubmissionPreviewEntries(submission, limit = 3) {
            return this.getSubmissionFieldEntries(submission).slice(0, limit);
        },
        openSubmissionDetail(submission) {
            if (!submission) {
                return;
            }

            this.selectedSubmissionId = submission.id;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('submissionDetailModal')).show();
        },
        resetSubmissionFilters() {
            this.submissionSearchKeyword = '';
            this.submissionSourceFilter = 'all';
            this.submissionPageFilter = 'all';
        },
        selectSubmissionAnalysisField(fieldKey) {
            this.submissionAnalysisFieldKey = fieldKey || '';
        },
        escapeCsvValue(value) {
            const normalized = Array.isArray(value) ? value.join(', ') : String(value ?? '');
            const escaped = normalized.replace(/"/g, '""');
            return `"${escaped}"`;
        },
        exportSubmissionCsv() {
            if (this.filteredSubmissionRecords.length === 0) {
                this.setStatus('当前没有可导出的提交记录。', 'warning');
                return;
            }

            const dynamicFieldKeys = Array.from(new Set(
                this.filteredSubmissionRecords.flatMap((submission) => this.getSubmissionFieldEntries(submission).map((field) => field.key))
            ));
            const columnDefs = [
                { key: 'id', label: '记录ID' },
                { key: 'project_name', label: '项目名称' },
                { key: 'project_type', label: '项目类型' },
                { key: 'page_name', label: '页面标识' },
                { key: 'page_title', label: '页面标题' },
                { key: 'source', label: '来源' },
                { key: 'submitted_at', label: '提交时间' },
                ...dynamicFieldKeys.map((fieldKey) => ({
                    key: fieldKey,
                    label: this.getSubmissionFieldLabel(this.filteredSubmissionRecords.find((submission) => this.getSubmissionFieldEntries(submission).some((field) => field.key === fieldKey)) || null, fieldKey)
                }))
            ];
            const rows = this.filteredSubmissionRecords.map((submission) => {
                const formData = submission && submission.form_data && typeof submission.form_data === 'object'
                    ? submission.form_data
                    : {};
                const row = {
                    id: submission.id,
                    project_name: submission.project_name || '',
                    project_type: submission.project_type || '',
                    page_name: submission.page_name || '',
                    page_title: submission.page_title || '',
                    source: submission.source || '',
                    submitted_at: submission.submitted_at || submission.created_at || ''
                };

                dynamicFieldKeys.forEach((fieldKey) => {
                    row[fieldKey] = Object.prototype.hasOwnProperty.call(formData, fieldKey) ? formData[fieldKey] : '';
                });

                return columnDefs.map((column) => {
                    const value = dynamicFieldKeys.includes(column.key)
                        ? this.formatSubmissionFieldValue(submission, column.key, row[column.key])
                        : row[column.key];
                    return this.escapeCsvValue(value);
                }).join(',');
            });
            const csvContent = [columnDefs.map((column) => this.escapeCsvValue(column.label)).join(','), ...rows].join('\n');
            const filename = `${this.normalizePageName(this.projectName || 'submission-records', 1)}-submissions.csv`;
            downloadTextFile(filename, csvContent);
            this.setStatus(`已导出 ${this.filteredSubmissionRecords.length} 条提交记录。`, 'success');
        },
        getNodeByPath(root, path) {
            let current = root;

            for (const index of path || []) {
                if (!current || !Array.isArray(current.children) || !current.children[index]) {
                    return null;
                }

                current = current.children[index];
            }

            return current;
        },
        findTemplateRootForElement(elements, targetId, activeTemplateRoot = null) {
            for (const element of elements || []) {
                const nextTemplateRoot = element.props && element.props.templateKey
                    ? element
                    : activeTemplateRoot;

                if (String(element.id) === String(targetId)) {
                    return nextTemplateRoot;
                }

                const found = this.findTemplateRootForElement(element.children || [], targetId, nextTemplateRoot);

                if (found) {
                    return found;
                }
            }

            return null;
        },
        getTemplateFieldValue(templateRoot, field) {
            const targetNode = this.getNodeByPath(templateRoot, field.path);

            if (!targetNode || !targetNode.props) {
                return '';
            }

            return targetNode.props[field.propKey] || '';
        },
        updateTemplateField(fieldKey, value) {
            if (!this.selectedTemplateRoot) {
                return;
            }

            const templateKey = this.selectedTemplateRoot.props && this.selectedTemplateRoot.props.templateKey;
            const fieldConfig = (TEMPLATE_FIELD_CONFIGS[templateKey] || []).find((field) => field.key === fieldKey);

            if (!fieldConfig) {
                return;
            }

            const targetNode = this.getNodeByPath(this.selectedTemplateRoot, fieldConfig.path);

            if (!targetNode) {
                return;
            }

            targetNode.props = {
                ...targetNode.props,
                [fieldConfig.propKey]: value
            };
            this.queueHistoryCapture();
        },
        getTemplateInsertionTarget() {
            if (this.selectedElement && isContainerType(this.selectedElement.type)) {
                if (!Array.isArray(this.selectedElement.children)) {
                    this.selectedElement.children = [];
                }

                return {
                    list: this.selectedElement.children,
                    label: this.elementLabels[this.selectedElement.type] || this.selectedElement.type
                };
            }

            return {
                list: this.currentPage.elements,
                label: this.currentPage.title || '当前页面'
            };
        },
        insertSectionTemplate(key) {
            const template = this.getSectionTemplateConfig(key);

            if (!template || typeof template.build !== 'function') {
                this.setStatus('模板不存在或暂不可用。', 'danger');
                return;
            }

            const nextElements = template.build().map((element) => this.normalizeElement(element));

            if (nextElements.length === 0) {
                this.setStatus('模板内容为空。', 'warning');
                return;
            }

            const target = this.getTemplateInsertionTarget();
            target.list.push(...nextElements);
            this.selectedElementId = nextElements[0].id;
            this.captureHistory();
            this.setStatus(`已插入“${template.name}”到${target.label}。`, 'success');
        },
        buildOutlineItems(elements, depth = 0, items = []) {
            for (const element of elements || []) {
                items.push({
                    id: element.id,
                    type: element.type,
                    depth,
                    label: this.elementLabels[element.type] || element.type,
                    summary: this.getOutlineText(element)
                });

                if (Array.isArray(element.children) && element.children.length > 0) {
                    this.buildOutlineItems(element.children, depth + 1, items);
                }
            }

            return items;
        },
        getOutlineText(element) {
            const props = element && element.props ? element.props : {};

            switch (element.type) {
                case 'text':
                    return String(props.content || '文本内容').slice(0, 28);
                case 'button':
                    return props.text || '按钮文案';
                case 'form-summary':
                    return props.summaryTitle || '提交前复核';
                case 'image':
                    return props.alt || props.src || '图片资源';
                case 'input':
                case 'textarea':
                    return props.label || props.placeholder || '表单字段';
                case 'select':
                case 'radio-group':
                case 'checkbox-group':
                    return `${props.label || props.placeholder || '表单字段'} · ${parseChoiceOptions(props.options).length} 项`;
                case 'spacer':
                    return props.height || '间距块';
                case 'row':
                case 'div':
                    return props.class || props.style || '布局容器';
                default:
                    return '';
            }
        },
        focusElement(elementId) {
            this.selectedElementId = elementId;

            window.requestAnimationFrame(() => {
                const targetNode = document.querySelector(`[data-element-id="${String(elementId)}"]`);

                if (targetNode && typeof targetNode.scrollIntoView === 'function') {
                    targetNode.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });
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
                props: applyConditionalDefaults(element.props && typeof element.props === 'object' ? { ...element.props } : {}),
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
                theme: deepClone(this.theme),
                pages: deepClone(this.pages),
                currentPageId: this.currentPageId,
                selectedElementId: this.selectedElementId,
                viewportMode: this.viewportMode
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
            this.queueDraftSave();
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
            this.theme = this.normalizeTheme(snapshot.theme || {});
            this.pages = this.hydratePages(snapshot.pages || []);
            this.currentPageId = this.pages.some((page) => page.id === snapshot.currentPageId)
                ? snapshot.currentPageId
                : this.pages[0].id;
            this.selectedElementId = snapshot.selectedElementId || null;
            this.viewportMode = snapshot.viewportMode || 'desktop';
            this.isApplyingHistory = false;
            this.syncProjectUrl(this.projectId || null);
            this.queueDraftSave();
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
                    return applyConditionalDefaults({
                        content: '双击左侧组件后拖到画布，点击这里即可编辑文案。',
                        class: '',
                        width: '',
                        style: 'font-size: 16px; line-height: 1.7;'
                    });
                case 'image':
                    return applyConditionalDefaults({
                        src: '',
                        alt: '图片',
                        class: '',
                        width: '100%',
                        style: 'max-width: 320px;'
                    });
                case 'button':
                    return applyConditionalDefaults({
                        text: '立即操作',
                        class: 'btn btn-primary',
                        width: '',
                        style: '',
                        actionType: 'none',
                        actionValue: '',
                        submitEndpoint: '/api/form-submissions',
                        submitMethod: 'POST',
                        submitResetForm: false,
                        submitRedirectUrl: ''
                    });
                case 'form-summary':
                    return applyConditionalDefaults({
                        summaryTitle: '请确认以下信息',
                        emptyText: '当前还没有可汇总的表单字段',
                        class: '',
                        width: '100%',
                        style: ''
                    });
                case 'input':
                    return applyConditionalDefaults({
                        label: '输入项',
                        required: false,
                        helperText: '',
                        placeholder: '请输入内容',
                        value: '',
                        minLength: '',
                        maxLength: '',
                        minValue: '',
                        maxValue: '',
                        fieldKey: `field_${createId('input')}`,
                        inputType: 'text',
                        validationPattern: '',
                        validationMessage: '',
                        class: 'form-control',
                        width: '',
                        style: ''
                    });
                case 'textarea':
                    return applyConditionalDefaults({
                        label: '多行输入',
                        required: false,
                        helperText: '',
                        placeholder: '请输入多行内容',
                        value: '',
                        rows: '4',
                        minLength: '',
                        maxLength: '',
                        fieldKey: `field_${createId('textarea')}`,
                        validationPattern: '',
                        validationMessage: '',
                        class: 'form-control',
                        width: '',
                        style: ''
                    });
                case 'select':
                    return applyConditionalDefaults({
                        label: '下拉选择',
                        required: false,
                        helperText: '',
                        placeholder: '请选择',
                        value: '',
                        fieldKey: `field_${createId('select')}`,
                        options: 'option_a|选项一\noption_b|选项二\noption_c|选项三',
                        class: 'form-control',
                        width: '',
                        style: ''
                    });
                case 'radio-group':
                    return applyConditionalDefaults({
                        label: '单选项',
                        required: false,
                        helperText: '',
                        value: '',
                        fieldKey: `field_${createId('radio')}`,
                        options: 'option_a|选项一\noption_b|选项二\noption_c|选项三',
                        optionLayout: 'vertical',
                        class: '',
                        width: '',
                        style: ''
                    });
                case 'checkbox-group':
                    return applyConditionalDefaults({
                        label: '多选项',
                        required: false,
                        helperText: '',
                        value: '',
                        fieldKey: `field_${createId('checkbox')}`,
                        options: 'option_a|选项一\noption_b|选项二\noption_c|选项三',
                        optionLayout: 'vertical',
                        class: '',
                        width: '',
                        style: ''
                    });
                case 'spacer':
                    return applyConditionalDefaults({
                        height: '32px',
                        class: '',
                        width: '100%',
                        style: ''
                    });
                case 'row':
                    return applyConditionalDefaults({
                        class: '',
                        width: '',
                        style: 'gap: 16px; align-items: flex-start;'
                    });
                case 'div':
                    return applyConditionalDefaults({
                        class: '',
                        width: '',
                        style: 'padding: 16px;'
                    });
                default:
                    return applyConditionalDefaults({
                        class: '',
                        width: '',
                        style: ''
                    });
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

            if (key === 'fieldKey') {
                nextValue = normalizeFieldKeyInput(value, {
                    allowEmpty: true,
                    fallback: 'field'
                });
            }

            if (key === 'conditionFieldKey') {
                nextValue = String(value || '').trim();
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
            const names = usedNames || new Set(this.safePages.map((page) => page.name));
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
                this.safePages
                    .filter((page) => page.id !== this.currentPage.id)
                    .map((page) => page.name)
            );

            this.currentPage.name = this.createUniquePageName(
                this.currentPage.name || this.currentPage.title,
                this.safePages.findIndex((page) => page.id === this.currentPage.id) + 1,
                usedNames
            );
            this.captureHistory();
        },
        buildProjectConfig() {
            const usedNames = new Set();

            return {
                title: this.projectName || '未命名项目',
                theme: deepClone(this.theme),
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
            this.theme = this.normalizeTheme(config.theme || {});
            this.pages = pages;
            this.currentPageId = pages[0].id;
            this.selectedElementId = null;
            this.clearDropTarget();
            this.previewHtml = '';
            this.hasPreview = false;
            this.wechatCode = '';
            this.h5Code = '';
            this.syncProjectUrl(this.projectId || null);
            this.saveLocalDraft(true);
            this.fetchSubmissions(true);

            const diagnostics = this.buildProjectFieldDiagnostics();
            if (diagnostics.hasIssues) {
                this.setStatus(this.buildFieldIntegrityMessage(diagnostics, '当前项目加载后检测到'), 'warning');
            }
        },
        createNewProject() {
            const blankPage = this.createBlankPage();

            this.projectId = null;
            this.projectName = '未命名项目';
            this.projectType = 'h5';
            this.theme = createDefaultTheme();
            this.pages = [blankPage];
            this.currentPageId = blankPage.id;
            this.selectedElementId = null;
            this.clearDropTarget();
            this.newPageTitle = '';
            this.previewHtml = '';
            this.hasPreview = false;
            this.wechatCode = '';
            this.h5Code = '';
            this.syncProjectUrl(null);
            this.resetHistory();
            this.saveLocalDraft(true);
            this.fetchSubmissions(true);
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
                    const diagnostics = this.buildProjectFieldDiagnostics();
                    if (diagnostics.hasIssues) {
                        this.setStatus(`已导入项目“${this.projectName}”，但检测到字段配置风险。${this.buildFieldIntegrityMessage(diagnostics, '后续预览或导出')}`, 'warning');
                    } else {
                        this.setStatus(`已导入项目“${this.projectName}”。`, 'success');
                    }
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
        setupPreviewStageListeners() {
            const stage = this.$refs.previewStage;

            if (!stage || stage.dataset.previewListenersBound === '1') {
                return;
            }

            this.previewStageInteractionHandler = (event) => {
                const scope = event.target && typeof event.target.closest === 'function'
                    ? (event.target.closest('.page') || stage)
                    : stage;
                this.refreshPreviewConditionalVisibility(scope);
                this.refreshPreviewFieldAssistState(scope);
                this.refreshPreviewStepState(scope);
                this.refreshPreviewSummaryState(scope);
                this.refreshPreviewValidationState(scope, event.target);
            };
            stage.addEventListener('input', this.previewStageInteractionHandler);
            stage.addEventListener('change', this.previewStageInteractionHandler);
            stage.dataset.previewListenersBound = '1';
        },
        teardownPreviewStageListeners() {
            const stage = this.$refs.previewStage;

            if (!stage || !this.previewStageInteractionHandler) {
                return;
            }

            stage.removeEventListener('input', this.previewStageInteractionHandler);
            stage.removeEventListener('change', this.previewStageInteractionHandler);
            delete stage.dataset.previewListenersBound;
            this.previewStageInteractionHandler = null;
        },
        getPreviewFields(scope) {
            return Array.from((scope || this.$refs.previewStage || document).querySelectorAll('[data-builder-field="true"]'));
        },
        getPreviewFieldCounters(scope) {
            return Array.from((scope || this.$refs.previewStage || document).querySelectorAll('[data-field-counter]'));
        },
        getPreviewFieldErrorNode(field) {
            const fieldKey = field && field.dataset ? field.dataset.fieldKey || '' : '';

            if (!fieldKey) {
                return null;
            }

            const pageNode = this.getPreviewPageNode(field);
            return (pageNode || this.$refs.previewStage || document).querySelector(`[data-field-error="${fieldKey}"]`);
        },
        getPreviewSummaryBlocks(scope) {
            return Array.from((scope || this.$refs.previewStage || document).querySelectorAll('[data-summary-enabled="1"]'));
        },
        isPreviewFieldConditionVisible(field) {
            return !(field && typeof field.closest === 'function' && field.closest('[data-conditional-hidden="1"]'));
        },
        isPreviewFieldStepVisible(field) {
            return !(field && typeof field.closest === 'function' && field.closest('[data-step-hidden="1"]'));
        },
        getCurrentPreviewFields(scope) {
            return this.getPreviewFields(scope).filter((field) => this.isPreviewFieldConditionVisible(field) && this.isPreviewFieldStepVisible(field));
        },
        getSubmittablePreviewFields(scope) {
            return this.getPreviewFields(scope).filter((field) => this.isPreviewFieldConditionVisible(field));
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
        getPreviewFieldValue(field) {
            const fieldKind = field && field.dataset ? field.dataset.fieldKind || '' : '';

            if (fieldKind === 'checkbox-group') {
                return Array.from(field.querySelectorAll('input[type="checkbox"]:checked')).map((input) => input.value);
            }

            if (fieldKind === 'radio-group') {
                const checked = field.querySelector('input[type="radio"]:checked');
                return checked ? checked.value : '';
            }

            return field && field.value !== undefined ? field.value : '';
        },
        refreshPreviewFieldAssistState(scope) {
            const pageNode = this.getPreviewPageNode(scope);
            const fields = this.getPreviewFields(pageNode);
            const counters = this.getPreviewFieldCounters(pageNode);

            if (fields.length === 0 || counters.length === 0) {
                return;
            }

            counters.forEach((counter) => {
                const targetKey = counter.dataset.fieldCounter || '';
                const field = fields.find((item) => (item.dataset.counterTarget || item.dataset.fieldKey || '') === targetKey);

                if (!field) {
                    return;
                }

                const maxLength = Number.parseInt(String(field.dataset.maxLength || ''), 10);
                if (!Number.isFinite(maxLength) || maxLength <= 0) {
                    return;
                }

                counter.textContent = `${String(this.getPreviewFieldValue(field) || '').length}/${maxLength}`;
            });
        },
        getPreviewFieldDisplayValue(field) {
            if (!field) {
                return '未填写';
            }

            const fieldKind = field.dataset ? field.dataset.fieldKind || '' : '';
            const rawValue = this.getPreviewFieldValue(field);

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

            return this.isPreviewFieldEmpty(rawValue) ? '未填写' : String(rawValue);
        },
        buildPreviewSummaryEntries(scope) {
            return this.getSubmittablePreviewFields(scope).map((field, index) => ({
                key: field.dataset.fieldKey || `field_${index + 1}`,
                label: field.dataset.label || field.dataset.fieldKey || `字段 ${index + 1}`,
                value: this.getPreviewFieldDisplayValue(field)
            }));
        },
        refreshPreviewSummaryState(scope) {
            const pageNode = this.getPreviewPageNode(scope);
            const summaryBlocks = this.getPreviewSummaryBlocks(pageNode);

            if (summaryBlocks.length === 0) {
                return;
            }

            const entries = this.buildPreviewSummaryEntries(pageNode);

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
                            <span>${escapeHtml(entry.label)}</span>
                            <strong>${escapeHtml(entry.value)}</strong>
                        </div>
                    `).join('');
                }

                if (emptyNode) {
                    emptyNode.hidden = entries.length > 0;
                }
            });
        },
        refreshAllPreviewSummaryStates() {
            const stage = this.$refs.previewStage;

            if (!stage) {
                return;
            }

            const pages = Array.from(stage.querySelectorAll('.page'));

            if (pages.length === 0) {
                this.refreshPreviewSummaryState(stage);
                return;
            }

            pages.forEach((page) => this.refreshPreviewSummaryState(page));
        },
        collectPreviewFieldValues(scope) {
            const fieldValues = {};

            this.getPreviewFields(scope).forEach((field) => {
                const fieldKey = field.dataset.fieldKey || `field_${Object.keys(fieldValues).length + 1}`;
                fieldValues[fieldKey] = this.getPreviewFieldValue(field);
            });

            return fieldValues;
        },
        getPreviewStepBlocks(scope) {
            return Array.from((scope || this.$refs.previewStage || document).querySelectorAll('[data-step-enabled="1"]'));
        },
        getPreviewPageNode(scope) {
            if (scope && scope.classList && scope.classList.contains('page')) {
                return scope;
            }

            return (scope && typeof scope.closest === 'function' ? scope.closest('.page') : null) || this.$refs.previewStage || document;
        },
        getPreviewCurrentStep(scope) {
            const pageNode = this.getPreviewPageNode(scope);
            return parseStepIndex(pageNode && pageNode.dataset ? pageNode.dataset.currentStep || '1' : '1');
        },
        refreshPreviewStepState(scope, forcedStep = null) {
            const pageNode = this.getPreviewPageNode(scope);
            const stepBlocks = this.getPreviewStepBlocks(pageNode);

            if (stepBlocks.length === 0) {
                return;
            }

            const stepDefinitions = Array.from(new Map(stepBlocks.map((block) => {
                const stepIndex = parseStepIndex(block.dataset.stepIndex || '1');
                return [stepIndex, {
                    index: stepIndex,
                    title: block.dataset.stepTitle || ''
                }];
            })).values()).sort((left, right) => left.index - right.index);
            const totalSteps = stepDefinitions.length;
            const minStep = stepDefinitions[0] ? stepDefinitions[0].index : 1;
            const maxStep = stepDefinitions[totalSteps - 1] ? stepDefinitions[totalSteps - 1].index : minStep;
            const nextStep = forcedStep === null ? this.getPreviewCurrentStep(pageNode) : parseStepIndex(forcedStep);
            const currentStep = Math.max(minStep, Math.min(nextStep, maxStep));

            if (pageNode.dataset) {
                pageNode.dataset.currentStep = String(currentStep);
                pageNode.dataset.totalSteps = String(totalSteps);
            }

            stepBlocks.forEach((block) => {
                const stepIndex = parseStepIndex(block.dataset.stepIndex || '1');
                const isVisible = stepIndex === currentStep;
                block.hidden = !isVisible;
                block.dataset.stepHidden = isVisible ? '0' : '1';
            });

            Array.from(pageNode.querySelectorAll('[data-step-item]')).forEach((item) => {
                const itemStep = parseStepIndex(item.dataset.stepItem || '1');
                item.classList.toggle('is-active', itemStep === currentStep);
                item.classList.toggle('is-complete', itemStep < currentStep);
            });

            const summaryNode = pageNode.querySelector('[data-step-summary]');
            if (summaryNode) {
                const activeDefinition = stepDefinitions.find((step) => step.index === currentStep) || stepDefinitions[0];
                summaryNode.textContent = activeDefinition ? getStepLabel(activeDefinition.index, activeDefinition.title) : `第${currentStep}步`;
            }
        },
        refreshAllPreviewStepStates() {
            const stage = this.$refs.previewStage;

            if (!stage) {
                return;
            }

            const pages = Array.from(stage.querySelectorAll('.page'));

            if (pages.length === 0) {
                this.refreshPreviewStepState(stage, 1);
                return;
            }

            pages.forEach((page) => this.refreshPreviewStepState(page, 1));
        },
        refreshPreviewConditionalVisibility(scope) {
            const container = scope || this.$refs.previewStage || document;
            const conditionalBlocks = Array.from(container.querySelectorAll('[data-visibility-enabled="1"]'));

            if (conditionalBlocks.length === 0) {
                return;
            }

            const fieldValues = this.collectPreviewFieldValues(container);

            conditionalBlocks.forEach((block) => {
                const fieldKey = block.dataset.visibilityField || '';
                const operator = block.dataset.visibilityOperator || 'equals';
                const expectedValue = block.dataset.visibilityValue || '';
                const isVisible = !fieldKey
                    ? true
                    : evaluateConditionRule(fieldValues[fieldKey], operator, expectedValue);

                block.hidden = !isVisible;
                block.dataset.conditionalHidden = isVisible ? '0' : '1';
            });
        },
        refreshAllPreviewConditionalVisibility() {
            const stage = this.$refs.previewStage;

            if (!stage) {
                return;
            }

            const pages = Array.from(stage.querySelectorAll('.page'));

            if (pages.length === 0) {
                this.refreshPreviewConditionalVisibility(stage);
                return;
            }

            pages.forEach((page) => this.refreshPreviewConditionalVisibility(page));
        },
        setPreviewFieldError(field, message = '') {
            if (!field) {
                return;
            }

            const errorNode = this.getPreviewFieldErrorNode(field);
            const nextMessage = String(message || '').trim();

            field.classList.toggle('builder-field-invalid', Boolean(nextMessage));
            field.setAttribute('aria-invalid', nextMessage ? 'true' : 'false');

            if (errorNode) {
                errorNode.textContent = nextMessage;
                errorNode.hidden = !nextMessage;
            }
        },
        clearPreviewFieldError(field) {
            this.setPreviewFieldError(field, '');
        },
        clearPreviewFieldErrors(scope) {
            this.getPreviewFields(scope).forEach((field) => {
                this.clearPreviewFieldError(field);
            });
        },
        syncPreviewFieldValidation(field) {
            if (!field) {
                return '';
            }

            if (!this.isPreviewFieldConditionVisible(field) || !this.isPreviewFieldStepVisible(field)) {
                this.clearPreviewFieldError(field);
                return '';
            }

            const message = this.validatePreviewField(field);
            this.setPreviewFieldError(field, message);
            return message;
        },
        refreshPreviewValidationState(scope, target = null) {
            const currentTarget = target && typeof target.closest === 'function'
                ? target.closest('[data-builder-field="true"]')
                : null;

            if (currentTarget) {
                this.syncPreviewFieldValidation(currentTarget);
            }

            this.getPreviewFields(scope).forEach((field) => {
                if (field === currentTarget) {
                    return;
                }

                if (!this.isPreviewFieldConditionVisible(field) || !this.isPreviewFieldStepVisible(field)) {
                    this.clearPreviewFieldError(field);
                    return;
                }

                if (field.getAttribute('aria-invalid') === 'true') {
                    this.syncPreviewFieldValidation(field);
                }
            });
        },
        validatePreviewFields(fields = []) {
            let firstInvalidField = null;
            let firstMessage = '';

            (fields || []).forEach((field) => {
                const message = this.syncPreviewFieldValidation(field);

                if (!firstInvalidField && message) {
                    firstInvalidField = field;
                    firstMessage = message;
                }
            });

            if (!firstInvalidField) {
                return '';
            }

            this.focusPreviewField(firstInvalidField);
            return firstMessage || '表单校验失败';
        },
        isPreviewFieldEmpty(value) {
            return Array.isArray(value) ? value.length === 0 : !String(value || '').trim();
        },
        validatePreviewField(field) {
            const value = this.getPreviewFieldValue(field);
            const label = field.dataset.label || '当前字段';

            if (field.dataset.required === '1' && this.isPreviewFieldEmpty(value)) {
                return `${label}为必填项`;
            }

            if (Array.isArray(value)) {
                return '';
            }

            const stringValue = String(value || '').trim();
            if (!stringValue) {
                return '';
            }

            const minLength = Number.parseInt(String(field.dataset.minLength || ''), 10);
            if (Number.isFinite(minLength) && minLength > 0 && stringValue.length < minLength) {
                return `${label}至少输入 ${minLength} 个字符`;
            }

            const maxLength = Number.parseInt(String(field.dataset.maxLength || ''), 10);
            if (Number.isFinite(maxLength) && maxLength > 0 && stringValue.length > maxLength) {
                return `${label}最多输入 ${maxLength} 个字符`;
            }

            const minValue = field.dataset.minValue !== '' ? Number(field.dataset.minValue) : NaN;
            const maxValue = field.dataset.maxValue !== '' ? Number(field.dataset.maxValue) : NaN;
            const numericValue = Number(stringValue);

            if (Number.isFinite(minValue) && Number.isFinite(numericValue) && numericValue < minValue) {
                return `${label}不能小于 ${minValue}`;
            }

            if (Number.isFinite(maxValue) && Number.isFinite(numericValue) && numericValue > maxValue) {
                return `${label}不能大于 ${maxValue}`;
            }

            const pattern = field.dataset.pattern || '';
            if (!pattern) {
                return '';
            }

            try {
                const regex = new RegExp(pattern);
                if (!regex.test(stringValue)) {
                    return field.dataset.validationMessage || `${label}格式不正确`;
                }
            } catch (error) {
                return '';
            }

            return '';
        },
        focusPreviewField(field) {
            if (field && typeof field.focus === 'function') {
                field.focus();
                return;
            }

            const target = field ? field.querySelector('input, textarea, select') : null;
            if (target && typeof target.focus === 'function') {
                target.focus();
            }
        },
        resetPreviewField(field) {
            const fieldKind = field && field.dataset ? field.dataset.fieldKind || '' : '';

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

            if (field) {
                field.value = '';
            }
        },
        resolvePreviewPageContext(trigger) {
            const pageNode = trigger.closest('.page');
            const pageName = pageNode && pageNode.id
                ? String(pageNode.id).replace(/^page-/, '')
                : (this.currentPage.name || 'index');
            const page = this.safePages.find((item) => item.name === pageName) || this.currentPage;

            return {
                pageName,
                pageTitle: page && page.title ? page.title : '首页'
            };
        },
        buildSubmissionPayload(formData, pageContext = {}, source = 'builder-preview') {
            const fieldMeta = this.buildPageFieldDefinitionMap(pageContext);

            return {
                project_id: this.projectId || null,
                project_name: this.projectName || '未命名项目',
                project_type: this.projectType || 'h5',
                page_name: pageContext.pageName || this.currentPage.name || 'index',
                page_title: pageContext.pageTitle || this.currentPage.title || '首页',
                source,
                submitted_at: new Date().toISOString(),
                form_data: formData,
                field_meta: fieldMeta
            };
        },
        showToast(message = '', tone = 'info') {
            const text = String(message || '').trim();

            if (!text || typeof document === 'undefined') {
                return;
            }

            const toast = document.createElement('div');
            toast.className = `builder-inline-toast is-${tone || 'info'}`;
            toast.textContent = text;
            document.body.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.add('is-visible');
            });

            window.setTimeout(() => {
                toast.classList.remove('is-visible');
                window.setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 180);
            }, 2200);
        },
        async submitBuilderPreviewForm(formData, pageContext, config = {}) {
            const endpoint = config.submitEndpoint || '/api/form-submissions';
            const method = config.submitMethod || 'POST';
            const payload = this.buildSubmissionPayload(formData, pageContext, 'builder-preview');

            return this.requestJson(endpoint, {
                method,
                body: payload
            });
        },
        handleBuilderStepAction(trigger, direction = 'next') {
            const scope = trigger.closest('.page') || this.$refs.previewStage || document;
            const currentStep = this.getPreviewCurrentStep(scope);
            const totalSteps = Number((this.getPreviewPageNode(scope).dataset || {}).totalSteps || this.currentPageStepCatalog.length || 1);

            if (direction === 'prev') {
                this.clearPreviewFieldErrors(scope);
                this.refreshPreviewFieldAssistState(scope);
                this.refreshPreviewStepState(scope, Math.max(1, currentStep - 1));
                this.refreshPreviewSummaryState(scope);
                return false;
            }

            if (this.validatePreviewFields(this.getCurrentPreviewFields(scope))) {
                return false;
            }

            this.refreshPreviewFieldAssistState(scope);
            this.refreshPreviewStepState(scope, Math.min(totalSteps, currentStep + 1));
            this.refreshPreviewSummaryState(scope);
            this.refreshPreviewValidationState(scope);
            return false;
        },
        async handleBuilderSubmitAction(trigger, config = {}) {
            const scope = trigger.closest('.page') || document;
            const currentFields = this.getCurrentPreviewFields(scope);

            if (this.validatePreviewFields(currentFields)) {
                return false;
            }

            const formData = {};
            this.getSubmittablePreviewFields(scope).forEach((field) => {
                const fieldKey = field.dataset.fieldKey || `field_${Object.keys(formData).length + 1}`;
                formData[fieldKey] = this.getPreviewFieldValue(field);
            });

            const pageContext = this.resolvePreviewPageContext(trigger);

            try {
                if (config.submitEndpoint) {
                    await this.submitBuilderPreviewForm(formData, pageContext, config);
                    await this.fetchSubmissions(true);
                }
            } catch (error) {
                this.showToast(error.message || '提交失败，请检查接口配置', 'danger');
                return false;
            }

            if (config.resetForm) {
                this.getPreviewFields(scope).forEach((field) => {
                    this.resetPreviewField(field);
                });
                this.clearPreviewFieldErrors(scope);
                this.refreshPreviewConditionalVisibility(scope);
                this.refreshPreviewFieldAssistState(scope);
                this.refreshPreviewStepState(scope, 1);
                this.refreshPreviewSummaryState(scope);
            }

            this.showToast(config.successMessage || '提交成功', 'success');

            if (config.redirectUrl) {
                window.location.href = config.redirectUrl;
            }

            return true;
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

            if (!this.ensureProjectFieldIntegrity(type === 'h5' ? '导出 H5' : '导出微信小程序')) {
                return;
            }

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
        async fetchSubmissions(silent = false) {
            this.isSubmissionListLoading = true;

            try {
                const queryString = this.buildQueryString(this.getSubmissionQueryParams());
                const json = await this.requestJson(`/api/form-submissions${queryString}`);
                this.submissionRecords = Array.isArray(json.data) ? json.data : [];

                if (!silent) {
                    this.setStatus('提交记录已刷新。', 'success');
                }
            } catch (error) {
                this.submissionRecords = [];

                if (!silent) {
                    this.setStatus(error.message || '获取提交记录失败。', 'danger');
                }
            } finally {
                this.isSubmissionListLoading = false;
            }
        },
        async clearSubmissions() {
            const queryParams = this.getSubmissionQueryParams();

            if (Object.keys(queryParams).length === 0) {
                this.setStatus('请先保存项目或填写项目名称，再清空提交记录。', 'warning');
                return;
            }

            this.isSubmissionClearing = true;

            try {
                const json = await this.requestJson('/api/form-submissions/clear', {
                    method: 'POST',
                    body: queryParams
                });
                await this.fetchSubmissions(true);
                this.setStatus(json.message || '提交记录已清空。', 'warning');
            } catch (error) {
                this.setStatus(error.message || '清空提交记录失败。', 'danger');
            } finally {
                this.isSubmissionClearing = false;
            }
        },
        async deleteSubmission(submissionId) {
            this.deletingSubmissionId = submissionId;

            try {
                await this.requestJson(`/api/form-submissions/${submissionId}`, {
                    method: 'DELETE'
                });
                this.submissionRecords = this.safeSubmissionRecords.filter((item) => String(item.id) !== String(submissionId));
                this.setStatus('提交记录已删除。', 'warning');
            } catch (error) {
                this.setStatus(error.message || '删除提交记录失败。', 'danger');
            } finally {
                this.deletingSubmissionId = null;
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

                this.syncProjectUrl(this.projectId || null);
                await this.fetchProjects(true);
                await this.fetchSubmissions(true);
                this.captureHistory();
                this.saveLocalDraft(true);
                const diagnostics = this.buildProjectFieldDiagnostics();
                if (diagnostics.hasIssues) {
                    this.setStatus(`${isUpdate ? '项目已更新' : '项目已创建'}，但仍存在字段配置风险。${this.buildFieldIntegrityMessage(diagnostics, '后续预览或导出')}`, 'warning');
                } else {
                    this.setStatus(isUpdate ? '项目已更新。' : '项目已创建。', 'success');
                }
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
                const diagnostics = this.buildProjectFieldDiagnostics();
                if (diagnostics.hasIssues) {
                    this.setStatus(`已加载项目“${this.projectName}”，但检测到字段配置风险。${this.buildFieldIntegrityMessage(diagnostics, '后续预览或导出')}`, 'warning');
                } else {
                    this.setStatus(`已加载项目“${this.projectName}”。`, 'success');
                }
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
            const updatedAt = this.formatDateTime(project.updated_at || project.created_at || '未知时间');

            return `${updatedAt} · ${pageCount} 个页面`;
        },
        async previewProject() {
            this.flushHistoryCapture();

            if (!this.ensureProjectFieldIntegrity('预览')) {
                return;
            }

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
                this.$nextTick(() => {
                    this.setupPreviewStageListeners();
                    this.refreshAllPreviewConditionalVisibility();
                    this.refreshPreviewFieldAssistState(this.$refs.previewStage);
                    this.refreshAllPreviewStepStates();
                    this.refreshAllPreviewSummaryStates();
                });
                this.setStatus('预览内容已更新。', 'success');
            } catch (error) {
                this.setStatus(error.message || '预览失败。', 'danger');
            } finally {
                this.isPreviewLoading = false;
            }
        },
        async generateCode() {
            this.flushHistoryCapture();

            if (!this.ensureProjectFieldIntegrity('生成代码')) {
                return;
            }

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
        this.teardownPreviewStageListeners();

        if (this.statusTimer) {
            window.clearTimeout(this.statusTimer);
        }

        if (this.historyTimer) {
            window.clearTimeout(this.historyTimer);
        }

        if (window.builderSubmitAction) {
            delete window.builderSubmitAction;
        }

        if (window.builderStepAction) {
            delete window.builderStepAction;
        }
    }
}).mount('#app');
