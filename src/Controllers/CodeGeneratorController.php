<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\WechatCodeGenerator;
use App\Services\H5CodeGenerator;

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
        $data = $request->getParsedBody();
        $config = $data['config'] ?? [];
        
        try {
            $code = $this->wechatGenerator->generate($config);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'code' => $code,
                'message' => '微信小程序代码生成成功'
            ]));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '代码生成失败: ' . $e->getMessage()
            ]));
            return $response->withStatus(500);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function generateH5(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $config = $data['config'] ?? [];
        
        try {
            $code = $this->h5Generator->generate($config);
            $response->getBody()->write(json_encode([
                'success' => true,
                'code' => $code,
                'message' => 'H5代码生成成功'
            ]));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '代码生成失败: ' . $e->getMessage()
            ]));
            return $response->withStatus(500);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function preview(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $config = $data['config'] ?? [];
        $type = $data['type'] ?? 'h5';
        
        try {
            if ($type === 'wechat') {
                $code = $this->wechatGenerator->generatePreview($config);
            } else {
                $code = $this->h5Generator->generatePreview($config);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'code' => $code,
                'message' => '预览代码生成成功'
            ]));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => '预览代码生成失败: ' . $e->getMessage()
            ]));
            return $response->withStatus(500);
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
