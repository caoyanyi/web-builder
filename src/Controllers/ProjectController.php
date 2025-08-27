<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Project;

class ProjectController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $project = new Project();
        $project->name = $data['name'] ?? '未命名项目';
        $project->type = $data['type'] ?? 'h5';
        $project->config = json_encode($data['config'] ?? []);
        $project->created_at = date('Y-m-d H:i:s');
        
        $projectId = $project->save();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'id' => $projectId,
            'message' => '项目创建成功'
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $project = Project::find($id);
        
        if (!$project) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '项目不存在'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $project
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        
        $project = Project::find($id);
        if (!$project) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '项目不存在'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $project->name = $data['name'] ?? $project->name;
        $project->config = json_encode($data['config'] ?? []);
        $project->updated_at = date('Y-m-d H:i:s');
        
        $project->save();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '项目更新成功'
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $project = Project::find($id);
        
        if (!$project) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '项目不存在'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $project->delete();
        
        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => '项目删除成功'
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
