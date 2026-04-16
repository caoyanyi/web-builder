<?php

namespace App\Controllers;

use App\Models\Project;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProjectController
{
    public function index(Request $request, Response $response): Response
    {
        $projects = array_map(
            function (Project $project) {
                return $project->toArray();
            },
            Project::all()
        );

        return $this->json($response, [
            'success' => true,
            'data' => $projects,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->getRequestData($request);

        $project = new Project();
        $project->name = $data['name'] ?? '未命名项目';
        $project->type = $data['type'] ?? 'h5';
        $project->config = $data['config'] ?? [];

        $projectId = $project->save();

        return $this->json($response, [
            'success' => true,
            'id' => $projectId,
            'data' => $project->toArray(),
            'message' => '项目创建成功',
        ]);
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $project = Project::find($args['id']);

        if (!$project) {
            return $this->json($response, [
                'success' => false,
                'message' => '项目不存在',
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'data' => $project->toArray(),
        ]);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $project = Project::find($args['id']);

        if (!$project) {
            return $this->json($response, [
                'success' => false,
                'message' => '项目不存在',
            ], 404);
        }

        $data = $this->getRequestData($request);

        $project->name = $data['name'] ?? $project->name;
        $project->type = $data['type'] ?? $project->type;
        $project->config = isset($data['config']) && is_array($data['config'])
            ? $data['config']
            : $project->config;

        $project->save();

        return $this->json($response, [
            'success' => true,
            'data' => $project->toArray(),
            'message' => '项目更新成功',
        ]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $project = Project::find($args['id']);

        if (!$project) {
            return $this->json($response, [
                'success' => false,
                'message' => '项目不存在',
            ], 404);
        }

        $project->delete();

        return $this->json($response, [
            'success' => true,
            'message' => '项目删除成功',
        ]);
    }

    private function getRequestData(Request $request): array
    {
        $data = $request->getParsedBody();

        return is_array($data) ? $data : [];
    }

    private function json(Response $response, array $payload, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}
