<?php
namespace App\Models;

class Repository extends BaseModel
{
    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function topics(): array
    {
        return $this->db->query('SELECT * FROM topics ORDER BY title')->fetchAll();
    }

    public function topic(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM topics WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function tasksByTopic(int $topicId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE topic_id=? ORDER BY id');
        $stmt->execute([$topicId]);
        return $stmt->fetchAll();
    }

    public function task(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createSubmission(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO submissions (user_id, task_id, title, content, word_count, status, created_at) VALUES (?, ?, ?, ?, ?, ?, datetime("now"))');
        $stmt->execute([$data['user_id'], $data['task_id'], $data['title'], $data['content'], $data['word_count'], $data['status']]);
        return (int)$this->db->lastInsertId();
    }

    public function addSubmissionVersion(int $submissionId, int $versionNo, string $content): void
    {
        $stmt = $this->db->prepare('INSERT INTO submission_versions (submission_id, version_no, content, created_at) VALUES (?, ?, ?, datetime("now"))');
        $stmt->execute([$submissionId, $versionNo, $content]);
    }

    public function submission(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT s.*, t.title as task_title, t.prompt, tp.title as topic_title FROM submissions s JOIN tasks t ON s.task_id=t.id JOIN topics tp ON t.topic_id=tp.id WHERE s.id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function submissionsByUser(int $userId, int $limit, int $offset, string $search=''): array
    {
        $sql = 'SELECT s.*, t.title as task_title FROM submissions s JOIN tasks t ON s.task_id=t.id WHERE s.user_id=?';
        $params = [$userId];
        if ($search !== '') { $sql .= ' AND (s.title LIKE ? OR s.content LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; }
        $sql .= ' ORDER BY s.created_at DESC LIMIT ? OFFSET ?';
        $params[]=$limit; $params[]=$offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countSubmissionsByUser(int $userId, string $search=''): int
    {
        $sql='SELECT COUNT(*) c FROM submissions WHERE user_id=?';
        $params=[$userId];
        if ($search!=='') { $sql.=' AND (title LIKE ? OR content LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; }
        $stmt=$this->db->prepare($sql); $stmt->execute($params); return (int)$stmt->fetch()['c'];
    }

    public function saveAutoFeedback(int $submissionId, array $annotations, array $summary, array $counts): void
    {
        $stmt = $this->db->prepare('INSERT INTO auto_feedback (submission_id, annotations_json, summary_json, error_counts_json, created_at) VALUES (?, ?, ?, ?, datetime("now"))');
        $stmt->execute([$submissionId, json_encode($annotations), json_encode($summary), json_encode($counts)]);
    }

    public function autoFeedback(int $submissionId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM auto_feedback WHERE submission_id=? ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([$submissionId]);
        return $stmt->fetch() ?: null;
    }

    public function teacherReview(int $submissionId): ?array
    {
        $stmt = $this->db->prepare('SELECT tr.*, u.name as teacher_name FROM teacher_reviews tr JOIN users u ON tr.teacher_id=u.id WHERE tr.submission_id=? ORDER BY reviewed_at DESC LIMIT 1');
        $stmt->execute([$submissionId]);
        return $stmt->fetch() ?: null;
    }

    public function upsertTeacherReview(array $data): void
    {
        $existing = $this->teacherReview($data['submission_id']);
        if ($existing) {
            $stmt = $this->db->prepare('UPDATE teacher_reviews SET score_task=?, score_coh=?, score_lex=?, score_gra=?, overall=?, comments=?, featured=?, reviewed_at=datetime("now") WHERE id=?');
            $stmt->execute([$data['score_task'],$data['score_coh'],$data['score_lex'],$data['score_gra'],$data['overall'],$data['comments'],$data['featured'],$existing['id']]);
        } else {
            $stmt = $this->db->prepare('INSERT INTO teacher_reviews (submission_id, teacher_id, score_task, score_coh, score_lex, score_gra, overall, comments, featured, reviewed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime("now"))');
            $stmt->execute([$data['submission_id'],$data['teacher_id'],$data['score_task'],$data['score_coh'],$data['score_lex'],$data['score_gra'],$data['overall'],$data['comments'],$data['featured']]);
        }
        if ((int)$data['featured']===1) {
            $stmt2 = $this->db->prepare('INSERT OR IGNORE INTO featured_essays (submission_id, approved_by, approved_at) VALUES (?, ?, datetime("now"))');
            $stmt2->execute([$data['submission_id'],$data['teacher_id']]);
        }
        $this->db->prepare('UPDATE submissions SET status="reviewed" WHERE id=?')->execute([$data['submission_id']]);
    }

    public function featuredEssays(array $filters=[]): array
    {
        $sql='SELECT s.id,s.title,s.content,tr.overall,tp.title as topic_title,t.level FROM featured_essays f JOIN submissions s ON f.submission_id=s.id JOIN tasks t ON s.task_id=t.id JOIN topics tp ON t.topic_id=tp.id LEFT JOIN teacher_reviews tr ON tr.submission_id=s.id WHERE 1=1';
        $params=[];
        if (!empty($filters['topic_id'])) { $sql.=' AND tp.id=?'; $params[]=$filters['topic_id']; }
        if (!empty($filters['level'])) { $sql.=' AND t.level=?'; $params[]=$filters['level']; }
        $sql.=' ORDER BY f.approved_at DESC';
        $stmt=$this->db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }

    public function teacherSubmissions(array $filters=[]): array
    {
        $sql='SELECT s.*,u.name as student_name,tp.title as topic_title,tr.overall FROM submissions s JOIN users u ON s.user_id=u.id JOIN tasks t ON s.task_id=t.id JOIN topics tp ON t.topic_id=tp.id LEFT JOIN teacher_reviews tr ON tr.submission_id=s.id WHERE 1=1';
        $params=[];
        if (!empty($filters['topic_id'])) {$sql.=' AND tp.id=?';$params[]=$filters['topic_id'];}
        if (!empty($filters['status'])) {$sql.=' AND s.status=?';$params[]=$filters['status'];}
        $sql.=' ORDER BY s.created_at DESC';
        $stmt=$this->db->prepare($sql);$stmt->execute($params);return $stmt->fetchAll();
    }

    public function topicMistakes(int $topicId): array
    {
        $stmt = $this->db->prepare('SELECT ee.error_code, COUNT(*) c FROM error_events ee JOIN submissions s ON ee.submission_id=s.id JOIN tasks t ON s.task_id=t.id WHERE t.topic_id=? GROUP BY ee.error_code ORDER BY c DESC LIMIT 10');
        $stmt->execute([$topicId]);
        return $stmt->fetchAll();
    }

    public function peerFeed(int $limit=20): array
    {
        $stmt = $this->db->prepare('SELECT tp.title as topic_title, ee.error_code, ee.snippet FROM error_events ee JOIN submissions s ON ee.submission_id=s.id JOIN tasks t ON s.task_id=t.id JOIN topics tp ON t.topic_id=tp.id ORDER BY ee.created_at DESC LIMIT ?');
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function dashboardProgress(int $userId): array
    {
        $stmt=$this->db->prepare('SELECT date(s.created_at) d, AVG(tr.overall) overall, AVG(tr.score_task) task, AVG(tr.score_coh) coh, AVG(tr.score_lex) lex, AVG(tr.score_gra) gra FROM submissions s LEFT JOIN teacher_reviews tr ON tr.submission_id=s.id WHERE s.user_id=? GROUP BY date(s.created_at) ORDER BY d');
        $stmt->execute([$userId]); return $stmt->fetchAll();
    }

    public function errorHeatmap(int $userId): array
    {
        $stmt=$this->db->prepare('SELECT ee.error_code, COUNT(*) c FROM error_events ee JOIN submissions s ON ee.submission_id=s.id WHERE s.user_id=? GROUP BY ee.error_code ORDER BY c DESC');
        $stmt->execute([$userId]); return $stmt->fetchAll();
    }

    public function allUsers(): array { return $this->db->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC')->fetchAll(); }

    public function errorReportRows(): array { return $this->db->query('SELECT error_code, COUNT(*) total FROM error_events GROUP BY error_code ORDER BY total DESC')->fetchAll(); }

    public function topicReportRows(): array { return $this->db->query('SELECT tp.title topic, strftime("%Y-%m", ee.created_at) month, ee.error_code, COUNT(*) total FROM error_events ee JOIN submissions s ON s.id=ee.submission_id JOIN tasks t ON s.task_id=t.id JOIN topics tp ON tp.id=t.topic_id GROUP BY tp.title, month, ee.error_code ORDER BY month DESC,total DESC')->fetchAll(); }

    public function nextVersionNo(int $submissionId): int
    {
        $stmt=$this->db->prepare('SELECT COALESCE(MAX(version_no),0)+1 n FROM submission_versions WHERE submission_id=?');
        $stmt->execute([$submissionId]);
        return (int)$stmt->fetch()['n'];
    }

    public function updateSubmissionContent(int $submissionId, string $content): void
    {
        $stmt=$this->db->prepare('UPDATE submissions SET content=?, word_count=?, status="submitted" WHERE id=?');
        $stmt->execute([$content, str_word_count(strip_tags($content)), $submissionId]);
    }

    public function recordErrorEvents(int $submissionId, array $counts, string $snippet): void
    {
        $stmt=$this->db->prepare('INSERT INTO error_events (submission_id,error_code,severity,snippet,created_at) VALUES (?,?,?,?,datetime("now"))');
        foreach ($counts as $code=>$count) {
            for ($i=0;$i<$count;$i++) {
                $stmt->execute([$submissionId,$code,'medium',substr($snippet,0,120)]);
            }
        }
    }

}
