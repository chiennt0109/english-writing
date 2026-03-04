<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repository;

class TeacherController extends Controller
{
    public function submissions(): void
    {
        if (!can_access(['teacher','admin'])) redirect('/dashboard');
        $repo=new Repository();
        $items=$repo->teacherSubmissions(['topic_id'=>(int)($_GET['topic_id']??0),'status'=>$_GET['status']??'']);
        $topics=$repo->topics();
        $this->view('teacher/submissions', compact('items','topics'));
    }

    public function review(string $id): void
    {
        if (!can_access(['teacher','admin'])) redirect('/dashboard');
        $repo=new Repository();
        $submission=$repo->submission((int)$id);
        $feedback=$repo->autoFeedback((int)$id);
        $review=$repo->teacherReview((int)$id);
        $this->view('teacher/review', compact('submission','feedback','review'));
    }

    public function saveReview(string $id): void
    {
        if (!verify_csrf()) { http_response_code(419); exit('CSRF mismatch'); }
        $repo = new Repository();
        $overall = round(((float)$_POST['score_task']+(float)$_POST['score_coh']+(float)$_POST['score_lex']+(float)$_POST['score_gra'])/4,1);
        $repo->upsertTeacherReview([
            'submission_id'=>(int)$id,
            'teacher_id'=>current_user()['id'],
            'score_task'=>(float)$_POST['score_task'],
            'score_coh'=>(float)$_POST['score_coh'],
            'score_lex'=>(float)$_POST['score_lex'],
            'score_gra'=>(float)$_POST['score_gra'],
            'overall'=>$overall,
            'comments'=>trim($_POST['comments']??''),
            'featured'=>isset($_POST['featured'])?1:0,
        ]);
        redirect('/teacher/review/'.$id);
    }
}
