<?php
// FILE: /app/core/Router.php

class Router {
    private $controller = 'DashboardController';
    private $method = 'index';
    private $params = array();

    public function __construct() {
        $url = $this->parseUrl();

        // Check if it's an API request
        if (isset($url[0]) && $url[0] === 'api') {
            $this->handleApiRequest($url);
            return;
        }

        // Handle regular web requests
        if (isset($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerPath = APP_PATH . '/controllers/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }
        }

        require_once APP_PATH . '/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        $this->params = $url ? array_values($url) : array();

        call_user_func_array(array($this->controller, $this->method), $this->params);
    }

    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return array();
    }

    private function handleApiRequest($url) {
        require_once APP_PATH . '/controllers/ApiController.php';
        $apiController = new ApiController();

        // Remove 'api' from url
        array_shift($url);

        // Get resource and action
        $resource = isset($url[0]) ? $url[0] : '';
        $action = isset($url[1]) ? $url[1] : '';

        // Route to appropriate API method
        $method = $_SERVER['REQUEST_METHOD'];

        if ($resource === 'candidates' && $action === 'create' && $method === 'POST') {
            $apiController->createCandidate();
        } elseif ($resource === 'employees' && $action === 'create' && $method === 'POST') {
            $apiController->createEmployee();
        } elseif ($resource === 'attendance' && $action === 'record' && $method === 'POST') {
            $apiController->recordAttendance();
        } elseif ($resource === 'jobs' && $method === 'GET') {
            $apiController->getJobs();
        } elseif ($resource === 'employees' && $method === 'GET') {
            $apiController->getEmployees();
        } else {
            http_response_code(404);
            echo json_encode(array('error' => 'Endpoint not found'));
        }

        exit;
    }
}
