<?php

namespace App\Controllers;

use App\Services\H5CodeGenerator;
use App\Services\WechatCodeGenerator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CodeGeneratorController
{
    private $wechatGenerator;
    private $h5Generator;

    public function __construct()
    {
        $this->wechatGenerator = new WechatCodeGenerator();
        $this->h5Generator = new H5CodeGenerator();
    }

    public function generateWechat(Request $request, Response $response): Response
    {
        $data = $this->getRequestData($request);
        $config = $data['config'] ?? [];

        try {
            $code = $this->wechatGenerator->generate($config);

            return $this->json($response, [
                'success' => true,
                'code' => $code,
                'message' => '微信小程序代码生成成功',
            ]);
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => '代码生成失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateH5(Request $request, Response $response): Response
    {
        $data = $this->getRequestData($request);
        $config = $data['config'] ?? [];

        try {
            $code = $this->h5Generator->generate($config);

            return $this->json($response, [
                'success' => true,
                'code' => $code,
                'message' => 'H5代码生成成功',
            ]);
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => '代码生成失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function preview(Request $request, Response $response): Response
    {
        $data = $this->getRequestData($request);
        $config = $data['config'] ?? [];
        $type = $data['type'] ?? 'h5';

        try {
            if ($type === 'wechat') {
                $code = $this->wechatGenerator->generatePreview($config);
            } else {
                $code = $this->h5Generator->generatePreview($config);
            }

            return $this->json($response, [
                'success' => true,
                'code' => $code,
                'message' => '预览代码生成成功',
            ]);
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => '预览代码生成失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportBundle(Request $request, Response $response, array $args): Response
    {
        $data = $this->getRequestData($request);
        $config = $data['config'] ?? [];
        $type = $args['type'] ?? 'h5';

        try {
            if (!class_exists(\ZipArchive::class)) {
                throw new \RuntimeException('当前 PHP 环境未启用 ZipArchive 扩展');
            }

            $files = $type === 'wechat'
                ? $this->wechatGenerator->generate($config)
                : $this->h5Generator->generate($config);

            $tmpFile = tempnam(sys_get_temp_dir(), 'builder_zip_');
            $zip = new \ZipArchive();
            $result = $zip->open($tmpFile, \ZipArchive::OVERWRITE);

            if ($result !== true) {
                throw new \RuntimeException('压缩包创建失败');
            }

            if ($type === 'wechat') {
                $this->appendWechatBundle($zip, $files);
            } else {
                $this->appendH5Bundle($zip, $files);
            }

            $zip->close();

            $archiveContent = file_get_contents($tmpFile);

            if ($archiveContent === false) {
                throw new \RuntimeException('压缩包读取失败');
            }

            @unlink($tmpFile);

            $filename = $this->buildArchiveFilename($config['title'] ?? 'builder-project', $type);
            $response->getBody()->write($archiveContent);

            return $response
                ->withHeader('Content-Type', 'application/zip')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withHeader('Content-Length', (string) strlen($archiveContent));
        } catch (\Exception $e) {
            return $this->json($response, [
                'success' => false,
                'message' => '导出压缩包失败: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function appendH5Bundle(\ZipArchive $zip, array $files): void
    {
        foreach ($files as $path => $content) {
            if ($path === 'pages' || $path === 'components') {
                continue;
            }

            $zip->addFromString($path, (string) $content);
        }

        foreach ($files['pages'] ?? [] as $page) {
            $name = $page['name'] ?? 'page';
            $zip->addFromString("pages/{$name}/{$name}.html", (string) ($page['html'] ?? ''));
            $zip->addFromString("pages/{$name}/{$name}.css", (string) ($page['css'] ?? ''));
            $zip->addFromString("pages/{$name}/{$name}.js", (string) ($page['js'] ?? ''));
        }

        foreach ($files['components'] ?? [] as $component) {
            $name = $component['name'] ?? 'component';
            $zip->addFromString("components/{$name}/{$name}.html", (string) ($component['html'] ?? ''));
            $zip->addFromString("components/{$name}/{$name}.css", (string) ($component['css'] ?? ''));
            $zip->addFromString("components/{$name}/{$name}.js", (string) ($component['js'] ?? ''));
        }
    }

    private function appendWechatBundle(\ZipArchive $zip, array $files): void
    {
        foreach ($files as $path => $content) {
            if ($path === 'pages' || $path === 'components') {
                continue;
            }

            $zip->addFromString($path, (string) $content);
        }

        foreach ($files['pages'] ?? [] as $page) {
            $name = $page['name'] ?? 'page';
            $zip->addFromString("pages/{$name}/{$name}.js", (string) ($page['js'] ?? ''));
            $zip->addFromString("pages/{$name}/{$name}.wxml", (string) ($page['wxml'] ?? ''));
            $zip->addFromString("pages/{$name}/{$name}.wxss", (string) ($page['wxss'] ?? ''));
            $zip->addFromString("pages/{$name}/{$name}.json", (string) ($page['json'] ?? ''));
        }

        foreach ($files['components'] ?? [] as $component) {
            $name = $component['name'] ?? 'component';
            $zip->addFromString("components/{$name}/{$name}.js", (string) ($component['js'] ?? ''));
            $zip->addFromString("components/{$name}/{$name}.wxml", (string) ($component['wxml'] ?? ''));
            $zip->addFromString("components/{$name}/{$name}.wxss", (string) ($component['wxss'] ?? ''));
            $zip->addFromString("components/{$name}/{$name}.json", (string) ($component['json'] ?? ''));
        }
    }

    private function buildArchiveFilename(string $title, string $type): string
    {
        $baseName = strtolower(trim($title));
        $baseName = preg_replace('/[^\w-]+/u', '-', $baseName);
        $baseName = trim($baseName, '-');
        $baseName = $baseName ?: 'builder-project';

        return $baseName . '-' . $type . '.zip';
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
