<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repository;

class ReportController extends Controller
{
    public function errors(): void
    {
        if (!can_access(['teacher','admin'])) redirect('/dashboard');
        $rows=(new Repository())->errorReportRows();
        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="errors_report.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['error_code','total']);
            foreach ($rows as $r) fputcsv($out, $r);
            fclose($out);
            return;
        }
        $this->view('reports/errors', compact('rows'));
    }

    public function topics(): void
    {
        if (!can_access(['teacher','admin'])) redirect('/dashboard');
        $rows=(new Repository())->topicReportRows();
        $this->view('reports/topics', compact('rows'));
    }
}
