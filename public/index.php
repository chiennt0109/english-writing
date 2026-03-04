<?php
session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $path = __DIR__ . '/../app/' . str_replace('App\\', '', $class) . '.php';
        $path = str_replace('\\', '/', $path);
        if (file_exists($path)) require $path;
    }
});

use App\Core\Env;
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\TeacherController;
use App\Controllers\AdminController;
use App\Controllers\ReportController;

Env::load(__DIR__ . '/../.env');
require __DIR__ . '/../app/Core/helpers.php';

$router = new Router();
$router->get('/', fn() => redirect('/dashboard'));
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/topics', [StudentController::class, 'topics']);
$router->get('/tasks', [StudentController::class, 'tasks']);
$router->get('/write', [StudentController::class, 'write']);
$router->post('/write', [StudentController::class, 'submit']);
$router->get('/submissions', [StudentController::class, 'submissions']);
$router->get('/submission/{id}', [StudentController::class, 'showSubmission']);
$router->post('/submission/{id}/revision', [StudentController::class, 'revision']);
$router->get('/model-essays', [StudentController::class, 'modelEssays']);
$router->get('/mistakes/topic/{id}', [StudentController::class, 'mistakesByTopic']);

$router->get('/teacher/submissions', [TeacherController::class, 'submissions']);
$router->get('/teacher/review/{id}', [TeacherController::class, 'review']);
$router->post('/teacher/review/{id}', [TeacherController::class, 'saveReview']);

$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/topics', [AdminController::class, 'topics']);
$router->get('/admin/tasks', [AdminController::class, 'tasks']);

$router->get('/reports/errors', [ReportController::class, 'errors']);
$router->get('/reports/topics', [ReportController::class, 'topics']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
