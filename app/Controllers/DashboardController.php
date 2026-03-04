<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repository;

class DashboardController extends Controller
{
    public function index(): void
    {
        if (!current_user()) redirect('/login');
        $repo = new Repository();
        $progress = $repo->dashboardProgress((int)current_user()['id']);
        $heatmap = $repo->errorHeatmap((int)current_user()['id']);
        $peer = $repo->peerFeed();
        $this->view('dashboard/index', compact('progress','heatmap','peer'));
    }
}
