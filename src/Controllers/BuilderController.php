<?php

namespace App\Controllers;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

class BuilderController
{
    private $view;

    public function __construct(PhpRenderer $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'index.php', [
            'recentProjects' => $this->getRecentProjects(),
        ]);
    }

    public function builder(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'builder.php');
    }

    private function getRecentProjects(): array
    {
        $projects = array_slice(Project::all(), 0, 3);

        return array_map(function (Project $project) {
            $config = is_array($project->config) ? $project->config : [];
            $pages = isset($config['pages']) && is_array($config['pages']) ? $config['pages'] : [];
            $pageCount = count($pages);
            $firstPage = $pages[0] ?? [];
            $entryTitle = $firstPage['title'] ?? $firstPage['name'] ?? '未命名页面';

            return [
                'id' => $project->id,
                'name' => $project->name,
                'type' => $project->type,
                'page_count' => $pageCount,
                'entry_title' => $entryTitle,
                'updated_at' => $project->updated_at ?: $project->created_at,
                'summary' => $this->buildProjectSummary($pageCount, $project->type, $entryTitle),
                'theme_color' => $this->extractThemeColor($config),
            ];
        }, $projects);
    }

    private function buildProjectSummary(int $pageCount, string $type, string $entryTitle): string
    {
        $target = $type === 'wechat' ? '微信小程序' : 'H5 页面';
        $pageCopy = $pageCount > 0 ? sprintf('%d 个页面', $pageCount) : '待继续完善';

        return sprintf('%s · %s · 入口页 %s', $target, $pageCopy, $entryTitle);
    }

    private function extractThemeColor(array $config): string
    {
        $primary = $config['theme']['primary'] ?? null;

        if (is_string($primary) && preg_match('/^#[0-9a-fA-F]{6}$/', $primary)) {
            return $primary;
        }

        return '#0f766e';
    }
}
