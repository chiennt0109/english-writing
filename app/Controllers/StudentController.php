<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\FeedbackEngine;
use App\Models\Repository;

class StudentController extends Controller
{
    public function topics(): void
    {
        $repo = new Repository();
        $topics = $repo->topics();
        $this->view('topics/index', compact('topics'));
    }

    public function tasks(): void
    {
        $topicId = (int)($_GET['topic_id'] ?? 0);
        $repo = new Repository();
        $tasks = $repo->tasksByTopic($topicId);
        $topic = $repo->topic($topicId);
        $this->view('tasks/index', compact('tasks','topic'));
    }

    public function write(): void
    {
        $taskId = (int)($_GET['task_id'] ?? 0);
        $repo = new Repository();
        $task = $repo->task($taskId);
        $this->view('submissions/write', compact('task'));
    }

    public function submit(): void
    {
        if (!verify_csrf()) { http_response_code(419); exit('CSRF mismatch'); }
        $repo = new Repository();
        $taskId=(int)($_POST['task_id']??0);
        $task = $repo->task($taskId);
        $content = trim($_POST['content'] ?? '');
        if (!$task || strlen($content)<20) { exit('Invalid input'); }
        $title = trim($_POST['title'] ?? 'Untitled');
        $wordCount = str_word_count(strip_tags($content));
        $submissionId = $repo->createSubmission([
            'user_id'=>current_user()['id'], 'task_id'=>$taskId, 'title'=>$title,
            'content'=>$content,'word_count'=>$wordCount,'status'=>'submitted'
        ]);
        $repo->addSubmissionVersion($submissionId, 1, $content);
        [$ann,$summary,$counts]=(new FeedbackEngine())->analyze($content,$task);
        $repo->saveAutoFeedback($submissionId,$ann,$summary,$counts);
        $repo->recordErrorEvents($submissionId, $counts, $content);
        redirect('/submission/'.$submissionId);
    }

    public function submissions(): void
    {
        $page=max(1,(int)($_GET['page']??1)); $search=trim($_GET['search']??''); $perPage=5;
        $repo=new Repository();
        $total=$repo->countSubmissionsByUser((int)current_user()['id'],$search);
        $items=$repo->submissionsByUser((int)current_user()['id'],$perPage,($page-1)*$perPage,$search);
        $this->view('submissions/index', compact('items','page','total','perPage','search'));
    }

    public function showSubmission(string $id): void
    {
        $repo=new Repository();
        $submission=$repo->submission((int)$id);
        $feedback=$repo->autoFeedback((int)$id);
        $review=$repo->teacherReview((int)$id);
        $this->view('submissions/show', compact('submission','feedback','review'));
    }

    public function revision(string $id): void
    {
        if (!verify_csrf()) { http_response_code(419); exit('CSRF mismatch'); }
        $repo=new Repository();
        $submission=$repo->submission((int)$id);
        $content=trim($_POST['content']??'');
        if (!$submission || $content==='') exit('Invalid');
        $versionNo=$repo->nextVersionNo((int)$id);
        $repo->addSubmissionVersion((int)$id,$versionNo,$content);
        $repo->updateSubmissionContent((int)$id, $content);
        [$ann,$summary,$counts]=(new FeedbackEngine())->analyze($content,['prompt'=>$submission['prompt'],'min_words'=>100,'max_words'=>320]);
        $repo->saveAutoFeedback((int)$id,$ann,$summary,$counts);
        redirect('/submission/'.$id);
    }

    public function modelEssays(): void
    {
        $repo=new Repository();
        $essays=$repo->featuredEssays(['topic_id'=>(int)($_GET['topic_id']??0),'level'=>$_GET['level']??'']);
        $topics=$repo->topics();
        $this->view('model/index', compact('essays','topics'));
    }

    public function mistakesByTopic(string $id): void
    {
        $repo=new Repository();
        $topic=$repo->topic((int)$id);
        $mistakes=$repo->topicMistakes((int)$id);
        $this->view('mistakes/topic', compact('topic','mistakes'));
    }
}
