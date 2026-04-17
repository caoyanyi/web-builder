<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <?php
        $builderCssVersion = @filemtime(__DIR__ . '/../public/css/builder.css') ?: time();
        $builderJsVersion = @filemtime(__DIR__ . '/../public/js/builder.js') ?: time();
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拖拽构建器 - 可视化生成器</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/builder.css?v=<?php echo $builderCssVersion; ?>" rel="stylesheet">
    <script src="vendor/vue/vue.global.prod.js"></script>
</head>
<body>
        <div id="app" class="builder-container">
        <div class="toolbar">
            <div class="container-fluid toolbar-inner">
                <div class="toolbar-copy">
                    <h5 class="toolbar-title mb-0">
                        <i class="bi bi-grid-1x2-fill"></i>
                        拖拽构建器
                    </h5>
                    <div class="toolbar-meta">
                        <span class="status-pill">{{ pageCount }} 个页面</span>
                        <span class="status-pill">{{ currentElementCount }} 个组件</span>
                        <span class="status-pill">Ctrl/Cmd + Z 撤销</span>
                        <span v-if="selectedElement" class="status-pill status-pill-active">
                            已选中 {{ elementLabels[selectedElement.type] || selectedElement.type }}
                        </span>
                    </div>
                </div>

                <div class="toolbar-actions">
                    <div v-if="statusMessage" :class="['builder-alert', `builder-alert-${statusVariant}`]">
                        {{ statusMessage }}
                    </div>

                    <div class="toolbar-action-groups">
                        <div class="btn-group" role="group">
                            <button @click="undoHistory" class="btn btn-outline-secondary btn-sm" :disabled="!canUndo">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                撤销
                            </button>
                            <button @click="redoHistory" class="btn btn-outline-secondary btn-sm" :disabled="!canRedo">
                                <i class="bi bi-arrow-clockwise"></i>
                                重做
                            </button>
                        </div>

                        <div class="btn-group" role="group">
                            <button @click="previewProject" class="btn btn-outline-primary btn-sm" :disabled="isPreviewLoading">
                                <span v-if="isPreviewLoading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-eye"></i>
                                预览
                            </button>
                            <button @click="generateCode" class="btn btn-primary btn-sm" :disabled="isGenerating">
                                <span v-if="isGenerating" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-download"></i>
                                生成代码
                            </button>
                        </div>

                        <div class="btn-group" role="group">
                            <button @click="exportProjectBundle('h5')" class="btn btn-outline-success btn-sm" :disabled="isExportingH5">
                                <span v-if="isExportingH5" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-file-earmark-zip"></i>
                                导出 H5 ZIP
                            </button>
                            <button @click="exportProjectBundle('wechat')" class="btn btn-outline-success btn-sm" :disabled="isExportingWechat">
                                <span v-if="isExportingWechat" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-file-earmark-zip"></i>
                                导出微信 ZIP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0 flex-grow-1 builder-workspace">
            <div class="col-xl-3 col-lg-4 sidebar">
                <div class="workspace-panel">
                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">项目管理</h6>
                            <span v-if="projectId" class="status-pill">ID {{ projectId }}</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">项目名称</label>
                            <input
                                v-model.trim="projectName"
                                type="text"
                                class="form-control"
                                placeholder="例如：春季活动专题"
                                @input="queueHistoryCapture"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">目标类型</label>
                            <select v-model="projectType" class="form-select" @change="queueHistoryCapture">
                                <option value="h5">H5</option>
                                <option value="wechat">微信小程序</option>
                            </select>
                        </div>

                        <div class="theme-panel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">项目主题</label>
                                <span class="status-pill">导出与预览生效</span>
                            </div>

                            <div class="shortcut-grid shortcut-grid-tight mb-3">
                                <button
                                    v-for="preset in themePresets"
                                    :key="preset.key"
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    @click="applyThemePreset(preset.key)"
                                >
                                    {{ preset.name }}
                                </button>
                            </div>

                            <div class="theme-color-grid">
                                <label class="theme-color-item">
                                    <span>主色</span>
                                    <input v-model="theme.primary" type="color" @input="queueHistoryCapture">
                                </label>
                                <label class="theme-color-item">
                                    <span>强调色</span>
                                    <input v-model="theme.accent" type="color" @input="queueHistoryCapture">
                                </label>
                                <label class="theme-color-item">
                                    <span>页面底色</span>
                                    <input v-model="theme.pageBackground" type="color" @input="queueHistoryCapture">
                                </label>
                                <label class="theme-color-item">
                                    <span>卡片底色</span>
                                    <input v-model="theme.surface" type="color" @input="queueHistoryCapture">
                                </label>
                                <label class="theme-color-item">
                                    <span>文字色</span>
                                    <input v-model="theme.text" type="color" @input="queueHistoryCapture">
                                </label>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">圆角尺寸</label>
                                <input
                                    v-model.trim="theme.radius"
                                    type="text"
                                    class="form-control"
                                    placeholder="例如：18px"
                                    @input="queueHistoryCapture"
                                >
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button @click="saveProject" class="btn btn-primary btn-sm" :disabled="isSavingProject">
                                <span v-if="isSavingProject" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-save"></i>
                                {{ projectId ? '更新项目' : '保存项目' }}
                            </button>
                            <button @click="createNewProject" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-file-earmark-plus"></i>
                                新建项目
                            </button>
                        </div>

                        <div class="builder-mini-actions">
                            <button @click="exportProjectJson" type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-box-arrow-down"></i>
                                导出 JSON
                            </button>
                            <button @click="triggerImportFile" type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-box-arrow-in-down"></i>
                                导入 JSON
                            </button>
                        </div>

                        <input
                            ref="projectImportInput"
                            type="file"
                            class="d-none"
                            accept="application/json,.json"
                            @change="importProjectFile"
                        >
                    </div>

                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">本地草稿</h6>
                            <span class="status-pill" v-if="draftInfo">{{ formatDateTime(draftInfo.savedAt) }}</span>
                        </div>

                        <p class="section-desc mb-3">编辑内容会自动保存在当前浏览器，适合保存未正式提交的搭建进度。</p>

                        <div v-if="draftInfo" class="draft-card mb-3">
                            <strong>{{ draftInfo.projectName }}</strong>
                            <span>最近保存：{{ formatDateTime(draftInfo.savedAt) }}</span>
                        </div>

                        <div v-else class="saved-empty-state mb-3">
                            还没有本地草稿，开始编辑后会自动生成。
                        </div>

                        <div class="draft-actions">
                            <button @click="saveLocalDraft(false)" type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-device-hdd"></i>
                                立即存草稿
                            </button>
                            <button @click="restoreLocalDraft" type="button" class="btn btn-outline-primary btn-sm" :disabled="!draftInfo">
                                <i class="bi bi-arrow-repeat"></i>
                                恢复草稿
                            </button>
                            <button @click="clearLocalDraft" type="button" class="btn btn-outline-danger btn-sm" :disabled="!draftInfo">
                                <i class="bi bi-trash3"></i>
                                清空草稿
                            </button>
                        </div>
                    </div>

                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">提交记录</h6>
                            <span class="status-pill">{{ filteredSubmissionRecords.length }} / {{ safeSubmissionRecords.length }} 条</span>
                        </div>

                        <p class="section-desc mb-3">当前显示 {{ currentSubmissionScopeLabel }} 的最近表单提交。保存项目后，提交记录会更准确地绑定到项目 ID。</p>

                        <div class="submission-stats mb-3">
                            <div class="submission-stat-card">
                                <strong>{{ submissionStats.total }}</strong>
                                <span>筛选后记录</span>
                            </div>
                            <div class="submission-stat-card">
                                <strong>{{ submissionStats.today }}</strong>
                                <span>今日新增</span>
                            </div>
                            <div class="submission-stat-card">
                                <strong>{{ submissionStats.pageCount }}</strong>
                                <span>涉及页面</span>
                            </div>
                        </div>

                        <div class="submission-filters">
                            <div>
                                <label class="form-label">关键词</label>
                                <input
                                    v-model.trim="submissionSearchKeyword"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="搜索字段值、来源或页面"
                                >
                            </div>
                            <div>
                                <label class="form-label">来源</label>
                                <select v-model="submissionSourceFilter" class="form-select form-select-sm">
                                    <option value="all">全部来源</option>
                                    <option v-for="source in submissionSourceOptions" :key="source" :value="source">
                                        {{ getSubmissionSourceLabel(source) }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">页面</label>
                                <select v-model="submissionPageFilter" class="form-select form-select-sm">
                                    <option value="all">全部页面</option>
                                    <option v-for="pageLabel in submissionPageOptions" :key="pageLabel" :value="pageLabel">
                                        {{ pageLabel }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="builder-mini-actions submission-actions mb-3">
                            <button @click="fetchSubmissions" type="button" class="btn btn-outline-secondary btn-sm" :disabled="isSubmissionListLoading">
                                <span v-if="isSubmissionListLoading" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-arrow-clockwise"></i>
                                刷新记录
                            </button>
                            <button @click="exportSubmissionCsv" type="button" class="btn btn-outline-secondary btn-sm" :disabled="filteredSubmissionRecords.length === 0">
                                <i class="bi bi-filetype-csv"></i>
                                导出 CSV
                            </button>
                            <button @click="resetSubmissionFilters" type="button" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-funnel"></i>
                                重置筛选
                            </button>
                            <button @click="clearSubmissions" type="button" class="btn btn-outline-danger btn-sm" :disabled="isSubmissionClearing || safeSubmissionRecords.length === 0">
                                <span v-if="isSubmissionClearing" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                <i v-else class="bi bi-eraser"></i>
                                清空当前项目
                            </button>
                        </div>

                        <div v-if="filteredSubmissionRecords.length > 0" class="submission-insights mb-3">
                            <div class="submission-insight-card">
                                <div class="submission-insight-head">
                                    <div>
                                        <strong>聚合概览</strong>
                                        <p class="mb-0 text-muted small">基于当前筛选结果自动汇总来源与页面分布。</p>
                                    </div>
                                </div>

                                <div class="submission-ranking-grid">
                                    <div>
                                        <label class="form-label small mb-2">来源排行</label>
                                        <div class="submission-ranking-list">
                                            <div v-for="item in submissionSourceBreakdown.slice(0, 5)" :key="item.key" class="submission-ranking-row">
                                                <span>{{ item.label }}</span>
                                                <strong>{{ item.count }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small mb-2">页面排行</label>
                                        <div class="submission-ranking-list">
                                            <div v-for="item in submissionPageBreakdown.slice(0, 5)" :key="item.key" class="submission-ranking-row">
                                                <span>{{ item.label }}</span>
                                                <strong>{{ item.count }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="submission-insight-card">
                                <div class="submission-insight-head">
                                    <div>
                                        <strong>字段分析</strong>
                                        <p class="mb-0 text-muted small">选择一个字段，查看填写率和取值分布。</p>
                                    </div>
                                    <span class="status-pill" v-if="activeSubmissionAnalysisField">{{ activeSubmissionAnalysisField.label }}</span>
                                </div>

                                <div v-if="submissionFieldCatalog.length === 0" class="saved-empty-state">
                                    当前筛选结果里没有可分析字段。
                                </div>

                                <template v-else>
                                    <div class="mb-3">
                                        <label class="form-label">分析字段</label>
                                        <select
                                            class="form-select form-select-sm"
                                            :value="activeSubmissionAnalysisFieldKey"
                                            @change="selectSubmissionAnalysisField($event.target.value)"
                                        >
                                            <option v-for="field in submissionFieldCatalog" :key="field.key" :value="field.key">
                                                {{ field.label }}（{{ field.filledCount }}/{{ filteredSubmissionRecords.length }}）
                                            </option>
                                        </select>
                                    </div>

                                    <div class="submission-analysis-stats">
                                        <div class="submission-analysis-stat">
                                            <strong>{{ submissionAnalysisStats.fillRate }}%</strong>
                                            <span>填写率</span>
                                        </div>
                                        <div class="submission-analysis-stat">
                                            <strong>{{ submissionAnalysisStats.filledRecords }}</strong>
                                            <span>已填写</span>
                                        </div>
                                        <div class="submission-analysis-stat">
                                            <strong>{{ submissionAnalysisStats.uniqueValueCount }}</strong>
                                            <span>不同取值</span>
                                        </div>
                                        <div class="submission-analysis-stat">
                                            <strong>{{ submissionAnalysisStats.topValueCount }}</strong>
                                            <span>Top 值次数</span>
                                        </div>
                                    </div>

                                    <div v-if="submissionAnalysisStats.topValueLabel" class="submission-analysis-highlight">
                                        最常见取值：<strong>{{ submissionAnalysisStats.topValueLabel }}</strong>
                                    </div>

                                    <div v-if="submissionValueDistribution.length === 0" class="saved-empty-state">
                                        这个字段在当前筛选结果中还没有有效取值。
                                    </div>

                                    <div v-else class="submission-distribution-list">
                                        <div v-for="item in submissionValueDistribution.slice(0, 8)" :key="item.label" class="submission-distribution-row">
                                            <div class="submission-distribution-meta">
                                                <span>{{ item.label }}</span>
                                                <strong>{{ item.count }} 次 · {{ item.percentage }}%</strong>
                                            </div>
                                            <div class="submission-distribution-track">
                                                <div class="submission-distribution-bar" :style="{ width: `${item.barWidth}%` }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div v-if="safeSubmissionRecords.length === 0" class="saved-empty-state">
                            还没有提交记录。给按钮配置“提交表单”动作并打开预览后，就可以把当前页面表单数据投递到本地接口。
                        </div>

                        <div v-else-if="filteredSubmissionRecords.length === 0" class="saved-empty-state">
                            当前筛选条件下没有匹配记录，可以重置筛选后再查看。
                        </div>

                        <div v-else class="submission-list">
                            <div v-for="submission in filteredSubmissionRecords" :key="submission.id" class="submission-card">
                                <div class="submission-card-head">
                                    <div>
                                        <strong>#{{ submission.id }} {{ submission.page_title || submission.page_name }}</strong>
                                        <p class="mb-0 text-muted small">{{ formatSubmissionMeta(submission) }}</p>
                                    </div>
                                    <div class="submission-card-actions">
                                        <button
                                            @click="openSubmissionDetail(submission)"
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                        >
                                            <i class="bi bi-eye"></i>
                                            详情
                                        </button>
                                        <button
                                            @click="deleteSubmission(submission.id)"
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            :disabled="deletingSubmissionId === submission.id"
                                        >
                                            <span v-if="deletingSubmissionId === submission.id" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                            <i v-else class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="submission-fields">
                                    <div v-for="field in getSubmissionPreviewEntries(submission)" :key="field.key" class="submission-field-row">
                                        <span>{{ field.label }}</span>
                                        <code>{{ field.displayValue }}</code>
                                    </div>
                                </div>

                                <div v-if="getSubmissionFieldEntries(submission).length > 3" class="submission-more">
                                    还有 {{ getSubmissionFieldEntries(submission).length - 3 }} 个字段，点击“详情”查看完整内容
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-section">
                        <h6 class="section-title">组件库</h6>
                        <p class="section-desc">拖到中间画布，或拖进容器组件中继续嵌套。组件支持复制、排序和撤销重做。</p>
                    </div>

                    <div class="panel-section">
                        <h6 class="section-subtitle">基础组件</h6>
                        <div
                            v-for="component in basicComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <div class="component-item-title">
                                <i :class="component.icon"></i>
                                <span>{{ component.name }}</span>
                            </div>
                            <small>{{ component.description }}</small>
                        </div>
                    </div>

                    <div class="panel-section">
                        <h6 class="section-subtitle">表单组件</h6>
                        <div
                            v-for="component in formComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <div class="component-item-title">
                                <i :class="component.icon"></i>
                                <span>{{ component.name }}</span>
                            </div>
                            <small>{{ component.description }}</small>
                        </div>
                    </div>

                    <div class="panel-section">
                        <h6 class="section-subtitle">布局组件</h6>
                        <div
                            v-for="component in layoutComponents"
                            :key="component.type"
                            class="component-item"
                            draggable="true"
                            @dragstart="handleDragStart($event, component)"
                        >
                            <div class="component-item-title">
                                <i :class="component.icon"></i>
                                <span>{{ component.name }}</span>
                            </div>
                            <small>{{ component.description }}</small>
                        </div>
                    </div>

                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">区块模板</h6>
                            <span class="status-pill">{{ sectionTemplates.length }} 套</span>
                        </div>
                        <p class="section-desc mb-3">一键插入常用页面区块，会自动生成成组结构，适合在空白页快速起稿。</p>

                        <div class="template-library">
                            <button
                                v-for="template in sectionTemplates"
                                :key="template.key"
                                type="button"
                                class="template-card"
                                @click="insertSectionTemplate(template.key)"
                            >
                                <div class="template-card-head">
                                    <i :class="template.icon"></i>
                                    <span>{{ template.name }}</span>
                                </div>
                                <p>{{ template.description }}</p>
                                <small>插入到当前页面，或插入到已选中的容器组件里。</small>
                            </button>
                        </div>
                    </div>

                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">已保存项目</h6>
                            <button @click="fetchProjects" type="button" class="btn btn-outline-secondary btn-sm" :disabled="isProjectListLoading">
                                <span v-if="isProjectListLoading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                <i v-else class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>

                        <div v-if="safeSavedProjects.length === 0" class="saved-empty-state">
                            暂无已保存项目，保存后会显示在这里。
                        </div>

                        <div v-else class="saved-projects">
                            <div
                                v-for="project in safeSavedProjects"
                                :key="project.id"
                                :class="['saved-project-card', { active: project.id === projectId }]"
                            >
                                <div class="saved-project-head">
                                    <div>
                                        <h6 class="mb-1">{{ project.name }}</h6>
                                        <p class="mb-0 text-muted small">{{ formatProjectMeta(project) }}</p>
                                    </div>
                                    <span class="status-pill">{{ project.type === 'wechat' ? '微信' : 'H5' }}</span>
                                </div>

                                <div class="saved-project-actions">
                                    <button @click="loadProject(project.id)" type="button" class="btn btn-outline-primary btn-sm" :disabled="isProjectLoading">
                                        加载
                                    </button>
                                    <button @click="removeProject(project.id)" type="button" class="btn btn-outline-danger btn-sm" :disabled="isProjectLoading">
                                        删除
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-8 main-content">
                <div class="canvas-shell">
                    <div class="page-toolbar">
                        <div>
                            <h6 class="section-title mb-2">页面管理</h6>
                            <div class="page-chip-group">
                                <button
                                    v-for="page in safePages"
                                    :key="page.id"
                                    type="button"
                                    :class="['page-chip', { active: currentPageId === page.id }]"
                                    @click="switchPage(page.id)"
                                >
                                    <span>{{ page.title }}</span>
                                    <small>{{ page.name }}</small>
                                </button>
                            </div>
                        </div>

                        <div class="page-create-box">
                            <label class="form-label">新增页面</label>
                            <div class="input-group input-group-sm">
                                <input
                                    v-model.trim="newPageTitle"
                                    type="text"
                                    class="form-control"
                                    placeholder="例如：活动页"
                                    @keyup.enter="addPage"
                                >
                                <button @click="addPage" class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="canvas-headline">
                        <div>
                            <h6 class="mb-1">页面画布</h6>
                            <p class="mb-0 text-muted">当前页面：{{ currentPage.title }}，点击组件即可编辑属性，支持嵌套拖拽、复制、上下排序。</p>
                        </div>
                        <div class="canvas-headline-actions">
                            <div class="btn-group btn-group-sm" role="group">
                                <button @click="setViewportMode('desktop')" :class="['btn', viewportMode === 'desktop' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-display"></i>
                                    桌面
                                </button>
                                <button @click="setViewportMode('tablet')" :class="['btn', viewportMode === 'tablet' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-tablet"></i>
                                    平板
                                </button>
                                <button @click="setViewportMode('mobile')" :class="['btn', viewportMode === 'mobile' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-phone"></i>
                                    手机
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm" role="group">
                                <button @click="duplicateCurrentPage" class="btn btn-outline-secondary">
                                    <i class="bi bi-copy"></i>
                                    复制页面
                                </button>
                                <button @click="clearCanvas" class="btn btn-outline-danger">
                                    <i class="bi bi-trash3"></i>
                                    清空页面
                                </button>
                                <button @click="deleteCurrentPage" class="btn btn-outline-secondary">
                                    <i class="bi bi-folder-minus"></i>
                                    删除页面
                                </button>
                            </div>
                        </div>
                    </div>

                    <div :class="['canvas-frame', `canvas-frame-${viewportMode}`]">
                        <div
                            class="canvas"
                            :class="{
                                'drag-over': isCanvasDragOver,
                                'drag-root': activeDropTarget && activeDropTarget.mode === 'root'
                            }"
                            @dragover="onCanvasDragOver"
                            @dragleave="onCanvasDragLeave"
                            @drop="onRootDrop"
                            @click="clearSelection"
                        >
                            <div v-if="currentPageElements.length === 0" class="canvas-empty-state">
                                <i class="bi bi-inboxes display-5"></i>
                                <h5>从左侧拖拽组件开始构建</h5>
                                <p>现在支持表单组件、模板区块、项目导入导出、历史撤销重做、跨容器拖拽移动和 ZIP 导出。</p>
                                <div class="empty-template-actions">
                                    <button @click.stop="insertSectionTemplate('hero')" type="button" class="btn btn-success btn-sm">插入 Hero</button>
                                    <button @click.stop="insertSectionTemplate('features')" type="button" class="btn btn-outline-secondary btn-sm">插入功能卡片</button>
                                    <button @click.stop="insertSectionTemplate('contact')" type="button" class="btn btn-outline-secondary btn-sm">插入表单区块</button>
                                </div>
                            </div>

                            <component-renderer
                                v-for="element in currentPageElements"
                                :key="element.id"
                                :element="element"
                                :selected-element-id="selectedElementId"
                                :drop-state="getDropStateForElement(element.id)"
                                :field-definitions="currentPageFieldDefinitionMap"
                                @select-element="selectElement"
                                @remove-element="removeElement"
                                @duplicate-element="duplicateElement"
                                @move-element="moveElement"
                                @insert-drop="onInsertDrop"
                                @container-drop="onContainerDrop"
                                @preview-drop-target="setDropTarget"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 properties-panel">
                <div class="workspace-panel">
                    <div class="panel-section">
                        <h6 class="section-title">页面信息</h6>
                        <div class="mb-3">
                            <label class="form-label">页面标题</label>
                            <input
                                v-model.trim="currentPage.title"
                                type="text"
                                class="form-control"
                                placeholder="请输入页面标题"
                                @input="queueHistoryCapture"
                            >
                        </div>
                        <div class="mb-2">
                            <label class="form-label">页面标识</label>
                            <input
                                v-model.trim="currentPage.name"
                                type="text"
                                class="form-control"
                                placeholder="index"
                                @input="queueHistoryCapture"
                                @blur="normalizeCurrentPageName"
                            >
                        </div>
                        <div class="form-text">用于生成代码的文件名和路由，建议使用英文、数字和连字符。</div>
                    </div>

                    <div class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">页面结构</h6>
                            <span class="status-pill">{{ pageOutlineItems.length }} 项</span>
                        </div>

                        <div v-if="pageOutlineItems.length === 0" class="saved-empty-state">
                            当前页面还没有组件，可以先插入模板区块，或从左侧组件库开始拖拽。
                        </div>

                        <div v-else class="outline-list">
                            <button
                                v-for="item in pageOutlineItems"
                                :key="item.id"
                                type="button"
                                :class="['outline-item', { active: String(selectedElementId) === String(item.id) }]"
                                :style="{ '--outline-depth': item.depth }"
                                @click="focusElement(item.id)"
                            >
                                <span class="outline-item-label">{{ item.label }}</span>
                                <small>{{ item.summary }}</small>
                            </button>
                        </div>
                    </div>

                    <div v-if="selectedElement" class="panel-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">组件属性</h6>
                            <button type="button" class="btn btn-outline-danger btn-sm" @click="removeElement(selectedElement.id)">
                                <i class="bi bi-trash3"></i>
                                删除
                            </button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">组件类型</label>
                            <input type="text" class="form-control" :value="elementLabels[selectedElement.type] || selectedElement.type" readonly>
                        </div>

                        <div v-if="selectedTemplateFields.length > 0" class="property-shortcuts">
                            <label class="form-label">模板内容快编</label>
                            <div class="form-text mb-3">当前选中的组件属于模板区块，可以直接在这里改写核心文案。</div>
                            <div v-for="field in selectedTemplateFields" :key="field.key" class="mb-3">
                                <label class="form-label">{{ field.label }}</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :value="field.value"
                                    @input="updateTemplateField(field.key, $event.target.value)"
                                >
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'button'" class="property-shortcuts">
                            <label class="form-label">按钮快捷样式</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-sm btn-primary" @click="applyButtonPreset('btn btn-primary')">主按钮</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="applyButtonPreset('btn btn-outline-primary')">描边按钮</button>
                                <button type="button" class="btn btn-sm btn-success" @click="applyButtonPreset('btn btn-success')">成功按钮</button>
                                <button type="button" class="btn btn-sm btn-dark" @click="applyButtonPreset('btn btn-dark')">深色按钮</button>
                            </div>
                            <div class="shortcut-grid shortcut-grid-tight mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('')">自动宽度</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('100%')">整行按钮</button>
                            </div>
                            <label class="form-label mt-3">按钮动作</label>
                            <div class="shortcut-grid">
                                <button
                                    v-for="option in buttonActionOptions"
                                    :key="option.value"
                                    type="button"
                                    :class="['btn btn-sm', selectedButtonActionType === option.value ? 'btn-success' : 'btn-outline-secondary']"
                                    @click="applyButtonActionPreset(option.value)"
                                >
                                    {{ option.label }}
                                </button>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">动作内容</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :disabled="selectedButtonActionType === 'none'"
                                    :placeholder="getButtonActionPlaceholder(selectedButtonActionType)"
                                    :value="selectedElement.props.actionValue || ''"
                                    @input="updateElementProp('actionValue', $event.target.value)"
                                >
                                <div class="form-text">提示消息会在 H5 中弹窗，在微信小程序代码中生成提示；跳转链接会生成对应跳转逻辑。</div>
                            </div>
                            <div v-if="selectedButtonActionType === 'submit'" class="mt-3">
                                <label class="form-label">提交结果</label>
                                <div class="shortcut-grid">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySubmitResultPreset('keep')">保留表单</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySubmitResultPreset('reset')">提交后清空</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySubmitResultPreset('redirect')">提交后跳转</button>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">提交接口</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="/api/form-submissions"
                                        :value="selectedElement.props.submitEndpoint || ''"
                                        @input="updateElementProp('submitEndpoint', $event.target.value)"
                                    >
                                    <div class="form-text">留空时只做前端校验和成功提示；填写接口后会把当前页面表单数据提交出去。微信小程序建议填写完整 HTTPS 接口地址。</div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">请求方法</label>
                                    <select
                                        class="form-select"
                                        :value="selectedElement.props.submitMethod || 'POST'"
                                        @change="updateElementProp('submitMethod', $event.target.value)"
                                    >
                                        <option v-for="option in requestMethodOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-check form-switch builder-switch mt-3">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :checked="Boolean(selectedElement.props.submitResetForm)"
                                        @change="updateElementProp('submitResetForm', $event.target.checked, { control: 'checkbox' })"
                                    >
                                    <label class="form-check-label">提交成功后清空当前页面表单</label>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">提交后跳转</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        :placeholder="projectType === 'wechat' ? '/pages/result/result' : 'https://example.com/success'"
                                        :value="selectedElement.props.submitRedirectUrl || ''"
                                        @input="updateElementProp('submitRedirectUrl', $event.target.value)"
                                    >
                                    <div class="form-text">H5 可填写外链或站内地址，微信小程序建议填写页面路径。</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'image'" class="property-shortcuts">
                            <label class="form-label">图片快捷尺寸</label>
                            <div class="shortcut-grid shortcut-grid-tight">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('160px')">160px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('320px')">320px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('100%')">铺满</button>
                            </div>
                            <div class="shortcut-grid shortcut-grid-tight mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyImageStylePreset('border-radius: 16px;')">圆角</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyImageStylePreset('object-fit: cover; aspect-ratio: 16 / 9;')">横幅</button>
                            </div>
                        </div>

                        <div v-if="['input', 'textarea', 'select', 'radio-group', 'checkbox-group'].includes(selectedElementType)" class="property-shortcuts">
                            <label class="form-label">表单快捷配置</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldPreset('form-control')">标准输入</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldPreset('form-control form-control-sm')">紧凑输入</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('50%')">半宽</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyWidthPreset('100%')">整行</button>
                            </div>
                            <div v-if="selectedElementType === 'input'" class="mt-3">
                                <label class="form-label">字段类型</label>
                                <div class="shortcut-grid">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldTypePreset('text')">文本</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldTypePreset('tel')">手机号</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldTypePreset('email')">邮箱</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyFieldTypePreset('number')">数字</button>
                                </div>
                            </div>
                            <div v-if="selectedElementType === 'radio-group' || selectedElementType === 'checkbox-group'" class="mt-3">
                                <label class="form-label">选项排布</label>
                                <div class="shortcut-grid">
                                    <button
                                        type="button"
                                        :class="['btn btn-sm', selectedOptionLayout === 'vertical' ? 'btn-success' : 'btn-outline-secondary']"
                                        @click="applyChoiceLayoutPreset('vertical')"
                                    >
                                        纵向
                                    </button>
                                    <button
                                        type="button"
                                        :class="['btn btn-sm', selectedOptionLayout === 'horizontal' ? 'btn-success' : 'btn-outline-secondary']"
                                        @click="applyChoiceLayoutPreset('horizontal')"
                                    >
                                        横向
                                    </button>
                                </div>
                            </div>
                            <div v-if="selectedElementType === 'select' || selectedElementType === 'radio-group' || selectedElementType === 'checkbox-group'" class="mt-3">
                                <label class="form-label">选项预设</label>
                                <div class="shortcut-grid">
                                    <button
                                        v-for="preset in choiceOptionPresets"
                                        :key="preset.key"
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        @click="applyChoiceOptionsPreset(preset.key)"
                                    >
                                        {{ preset.label }}
                                    </button>
                                </div>
                            </div>
                            <div v-if="selectedElementType === 'input' || selectedElementType === 'textarea'" class="mt-3">
                                <label class="form-label">校验预设</label>
                                <div class="shortcut-grid">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyValidationPreset('none')">清空校验</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyValidationPreset('phone')">手机号</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyValidationPreset('email')">邮箱</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyValidationPreset('number')">数字</button>
                                </div>
                            </div>
                        </div>

                        <div class="property-shortcuts">
                            <label class="form-label">条件显隐</label>
                            <div class="form-check form-switch builder-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :checked="Boolean(selectedElement.props.conditionEnabled)"
                                    @change="handleConditionEnabledChange($event.target.checked)"
                                >
                                <label class="form-check-label">根据当前页其他字段值决定是否显示</label>
                            </div>

                            <div v-if="selectedElement.props.conditionEnabled" class="border rounded p-3 mt-3 bg-light">
                                <div v-if="conditionalFieldOptions.length === 0" class="form-text">
                                    当前页还没有可作为条件源的表单字段，请先添加输入框、选择框或选项组。
                                </div>
                                <template v-else>
                                    <div>
                                        <label class="form-label">依赖字段</label>
                                        <select
                                            class="form-select"
                                            :value="selectedElement.props.conditionFieldKey || conditionalFieldOptions[0].key"
                                            @change="handleConditionFieldChange($event.target.value)"
                                        >
                                            <option v-for="field in conditionalFieldOptions" :key="field.key" :value="field.key">
                                                {{ field.label }}（{{ field.key }}）
                                            </option>
                                        </select>
                                    </div>
                                    <div class="mt-3">
                                        <label class="form-label">判断方式</label>
                                        <div class="shortcut-grid">
                                            <button
                                                v-for="option in conditionOperatorOptions"
                                                :key="option.value"
                                                type="button"
                                                :class="['btn btn-sm', selectedConditionOperator === option.value ? 'btn-success' : 'btn-outline-secondary']"
                                                @click="handleConditionOperatorChange(option.value)"
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="shouldShowConditionValue" class="mt-3">
                                        <label class="form-label">比较值</label>
                                        <select
                                            v-if="shouldUseConditionValueOptions"
                                            class="form-select"
                                            :value="selectedElement.props.conditionValue || ''"
                                            @change="updateElementProp('conditionValue', $event.target.value)"
                                        >
                                            <option v-for="option in selectedConditionValueOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                        <input
                                            v-else
                                            type="text"
                                            class="form-control"
                                            :value="selectedElement.props.conditionValue || ''"
                                            placeholder="例如：yes / pro / enterprise"
                                            @input="updateElementProp('conditionValue', $event.target.value)"
                                        >
                                        <div class="form-text">多选字段建议优先使用“包含 / 不包含”；隐藏后的字段不会参与校验和提交。</div>
                                    </div>
                                    <div v-if="selectedConditionSummary" class="form-text mt-3">
                                        当前规则：{{ selectedConditionSummary }}
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'spacer'" class="property-shortcuts">
                            <label class="form-label">间距快捷配置</label>
                            <div class="shortcut-grid shortcut-grid-tight">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySpacerPreset('16px')">16px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySpacerPreset('32px')">32px</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applySpacerPreset('64px')">64px</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'div'" class="property-shortcuts">
                            <label class="form-label">容器快捷布局</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyContainerPreset('padding: 16px; border-radius: 16px; background: #ffffff;')">卡片容器</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyContainerPreset('padding: 24px; background: linear-gradient(180deg, #f8fbf7 0%, #ffffff 100%); border-radius: 20px;')">分组区块</button>
                            </div>
                        </div>

                        <div v-if="selectedElementType === 'row'" class="property-shortcuts">
                            <label class="form-label">行布局快捷排版</label>
                            <div class="shortcut-grid">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyRowPreset('gap: 12px; align-items: center;')">紧凑横排</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="applyRowPreset('gap: 20px; flex-wrap: wrap; align-items: stretch;')">卡片栅格</button>
                            </div>
                        </div>

                        <div class="mb-3" v-if="selectedElement.type === 'image'">
                            <label class="form-label">图片上传</label>
                            <input type="file" class="form-control" accept="image/*" @change="onImageUpload($event)">
                            <div class="form-text">图片会以 Base64 形式内联，便于离线预览与导出。</div>
                        </div>

                        <div v-for="field in editablePropFields" :key="field.key" class="mb-3">
                            <label class="form-label">{{ field.label }}</label>
                            <textarea
                                v-if="field.control === 'textarea'"
                                class="form-control"
                                rows="4"
                                :value="selectedElement.props[field.key]"
                                @input="updateElementProp(field.key, $event.target.value, field)"
                            ></textarea>
                            <div v-else-if="field.control === 'checkbox'" class="form-check form-switch builder-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    :checked="Boolean(selectedElement.props[field.key])"
                                    @change="updateElementProp(field.key, $event.target.checked, field)"
                                >
                            </div>
                            <select
                                v-else-if="field.control === 'select'"
                                class="form-select"
                                :value="selectedElement.props[field.key]"
                                @change="updateElementProp(field.key, $event.target.value, field)"
                            >
                                <option v-for="option in field.options" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <input
                                v-else
                                :type="field.type"
                                class="form-control"
                                :value="selectedElement.props[field.key]"
                                @input="updateElementProp(field.key, $event.target.value, field)"
                            >
                            <div v-if="field.key === 'options'" class="form-text">
                                每行一个选项，格式为 `value|标签`，例如：`pro|进阶版`
                            </div>
                            <div v-if="field.key === 'value' && selectedElementType === 'checkbox-group'" class="form-text">
                                多选组默认值可填写多个 value，并用英文逗号分隔。
                            </div>
                        </div>
                    </div>

                    <div v-else class="panel-section panel-empty">
                        <h6 class="section-title">组件属性</h6>
                        <p>画布中点击任意组件后，这里会显示可编辑属性。</p>
                        <p class="mb-2 text-muted">支持在组件卡片右上角直接完成复制、上移、下移和删除，也可以直接拖到别的容器里。</p>
                        <p class="mb-0 text-muted">导入 JSON 后会按当前构建器结构恢复页面和组件，并可直接导出 ZIP。</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">项目预览（H5）</h5>
                            <p class="text-muted small mb-0">预览内容根据当前全部页面实时生成。</p>
                        </div>
                        <div class="preview-header-actions">
                            <div class="btn-group btn-group-sm" role="group">
                                <button @click="setViewportMode('desktop')" :class="['btn', viewportMode === 'desktop' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-display"></i>
                                </button>
                                <button @click="setViewportMode('tablet')" :class="['btn', viewportMode === 'tablet' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-tablet"></i>
                                </button>
                                <button @click="setViewportMode('mobile')" :class="['btn', viewportMode === 'mobile' ? 'btn-success' : 'btn-outline-secondary']">
                                    <i class="bi bi-phone"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div :class="['preview-shell', `preview-shell-${viewportMode}`]">
                            <div ref="previewStage" class="preview-stage" :style="projectThemeStyle" v-html="previewHtml"></div>
                        </div>
                        <div v-if="!hasPreview" class="text-muted small mt-3">暂无内容，请先在画布中添加组件。</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="codeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">生成代码</h5>
                            <p class="text-muted small mb-0">当前展示的是完整项目结构，可直接用于后续整理导出。</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#wechatTab">微信小程序</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#h5Tab">H5</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="wechatTab">
                                <pre class="code-preview"><code v-text="wechatCode"></code></pre>
                            </div>
                            <div class="tab-pane fade" id="h5Tab">
                                <pre class="code-preview"><code v-text="h5Code"></code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="submissionDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">提交记录详情</h5>
                            <p class="text-muted small mb-0" v-if="selectedSubmission">
                                #{{ selectedSubmission.id }} · {{ formatSubmissionMeta(selectedSubmission) }}
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" v-if="selectedSubmission">
                        <div class="submission-detail-meta">
                            <div class="submission-detail-item">
                                <span>项目</span>
                                <strong>{{ selectedSubmission.project_name || '未命名项目' }}</strong>
                            </div>
                            <div class="submission-detail-item">
                                <span>页面</span>
                                <strong>{{ selectedSubmission.page_title || selectedSubmission.page_name }}</strong>
                            </div>
                            <div class="submission-detail-item">
                                <span>来源</span>
                                <strong>{{ getSubmissionSourceLabel(selectedSubmission.source) }}</strong>
                            </div>
                            <div class="submission-detail-item">
                                <span>提交时间</span>
                                <strong>{{ formatDateTime(selectedSubmission.submitted_at || selectedSubmission.created_at) }}</strong>
                            </div>
                        </div>

                        <div class="submission-detail-list">
                            <div v-for="field in getSubmissionFieldEntries(selectedSubmission)" :key="field.key" class="submission-detail-row">
                                <div class="submission-detail-labels">
                                    <strong>{{ field.label }}</strong>
                                    <small>{{ field.key }}</small>
                                </div>
                                <code>{{ field.displayValue }}</code>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" v-else>
                        <div class="saved-empty-state">当前没有可查看的提交记录。</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/builder.js?v=<?php echo $builderJsVersion; ?>"></script>
</body>
</html>
