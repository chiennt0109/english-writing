<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repository;

class AdminController extends Controller
{
    public function users(): void
    {
        if (!can_access(['admin'])) redirect('/dashboard');
        $users=(new Repository())->allUsers();
        $this->view('admin/users', compact('users'));
    }

    public function topics(): void
    {
        if (!can_access(['admin'])) redirect('/dashboard');
        $topics=(new Repository())->topics();
        $this->view('admin/topics', compact('topics'));
    }

    public function tasks(): void
    {
        if (!can_access(['admin'])) redirect('/dashboard');
        $topicId=(int)($_GET['topic_id']??1);
        $repo=new Repository();
        $tasks=$repo->tasksByTopic($topicId);
        $topics=$repo->topics();
        $this->view('admin/tasks', compact('tasks','topics','topicId'));
    }
}
