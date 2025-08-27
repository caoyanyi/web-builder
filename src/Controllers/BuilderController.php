<?php

namespace App\Controllers;

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
        return $this->view->render($response, 'index.php');
    }

    public function builder(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'builder.php');
    }
}
