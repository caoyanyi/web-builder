<?php

namespace App\Controllers;

use App\Models\FormSubmission;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FormSubmissionController
{
    public function index(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $records = array_map(
            static function (FormSubmission $record) {
                return $record->toArray();
            },
            FormSubmission::all([
                'project_id' => $queryParams['project_id'] ?? '',
                'project_name' => $queryParams['project_name'] ?? '',
                'page_name' => $queryParams['page_name'] ?? '',
            ])
        );

        return $this->json($response, [
            'success' => true,
            'data' => $records,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->getRequestData($request);
        $formData = isset($data['form_data']) && is_array($data['form_data']) ? $data['form_data'] : [];

        if ($formData === []) {
            return $this->json($response, [
                'success' => false,
                'message' => '提交内容不能为空',
            ], 422);
        }

        $record = new FormSubmission();
        $record->project_id = isset($data['project_id']) && $data['project_id'] !== '' ? (int) $data['project_id'] : null;
        $record->project_name = $data['project_name'] ?? '未命名项目';
        $record->project_type = $data['project_type'] ?? 'h5';
        $record->page_name = $data['page_name'] ?? 'index';
        $record->page_title = $data['page_title'] ?? '首页';
        $record->source = $data['source'] ?? 'builder-preview';
        $record->form_data = $formData;
        $record->field_meta = isset($data['field_meta']) && is_array($data['field_meta'])
            ? $this->normalizeFieldMeta($data['field_meta'])
            : [];
        $record->meta = [
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'ip' => $this->resolveClientIp($request),
            'referer' => $request->getHeaderLine('Referer'),
            'extra' => isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [],
        ];
        $record->submitted_at = $data['submitted_at'] ?? date('Y-m-d H:i:s');
        $record->save();

        return $this->json($response, [
            'success' => true,
            'data' => $record->toArray(),
            'message' => '表单提交已记录',
        ]);
    }

    public function clear(Request $request, Response $response): Response
    {
        $data = $this->getRequestData($request);
        $filters = [
            'project_id' => $data['project_id'] ?? '',
            'project_name' => $data['project_name'] ?? '',
            'page_name' => $data['page_name'] ?? '',
        ];

        if (($filters['project_id'] === '' || $filters['project_id'] === null) && ($filters['project_name'] === '' || $filters['project_name'] === null) && ($filters['page_name'] === '' || $filters['page_name'] === null)) {
            return $this->json($response, [
                'success' => false,
                'message' => '清空提交记录前请先指定项目范围',
            ], 422);
        }

        $removedCount = FormSubmission::clear($filters);

        return $this->json($response, [
            'success' => true,
            'removed_count' => $removedCount,
            'message' => $removedCount > 0 ? '提交记录已清空' : '没有可清空的提交记录',
        ]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $record = FormSubmission::find($args['id']);

        if (!$record) {
            return $this->json($response, [
                'success' => false,
                'message' => '提交记录不存在',
            ], 404);
        }

        $record->delete();

        return $this->json($response, [
            'success' => true,
            'message' => '提交记录已删除',
        ]);
    }

    private function getRequestData(Request $request): array
    {
        $data = $request->getParsedBody();

        return is_array($data) ? $data : [];
    }

    private function resolveClientIp(Request $request): string
    {
        $serverParams = $request->getServerParams();
        return (string) ($serverParams['REMOTE_ADDR'] ?? '');
    }

    private function normalizeFieldMeta(array $fieldMeta): array
    {
        $normalized = [];

        foreach ($fieldMeta as $fieldKey => $meta) {
            if (!is_array($meta)) {
                continue;
            }

            $normalizedOptions = [];
            foreach (($meta['options'] ?? []) as $option) {
                if (!is_array($option)) {
                    continue;
                }

                $normalizedOptions[] = [
                    'value' => (string) ($option['value'] ?? ''),
                    'label' => (string) ($option['label'] ?? ($option['value'] ?? '')),
                ];
            }

            $normalized[(string) $fieldKey] = [
                'key' => (string) ($meta['key'] ?? $fieldKey),
                'label' => (string) ($meta['label'] ?? $fieldKey),
                'type' => (string) ($meta['type'] ?? ''),
                'options' => $normalizedOptions,
                'page_name' => (string) ($meta['page_name'] ?? ''),
                'page_title' => (string) ($meta['page_title'] ?? ''),
            ];
        }

        return $normalized;
    }

    private function json(Response $response, array $payload, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}
