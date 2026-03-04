<h3>My Submissions</h3>
<form class="mb-3"><input name="search" value="<?= e($search) ?>" placeholder="search" class="form-control"></form>
<table class="table"><tr><th>Title</th><th>Task</th><th>Status</th><th>Date</th></tr><?php foreach($items as $i): ?><tr><td><a href="/submission/<?= $i['id'] ?>"><?= e($i['title']) ?></a></td><td><?= e($i['task_title']) ?></td><td><?= e($i['status']) ?></td><td><?= e($i['created_at']) ?></td></tr><?php endforeach; ?></table>
<?php $pages=ceil($total/$perPage); for($p=1;$p<=$pages;$p++): ?><a class="btn btn-sm <?= $p===$page?'btn-primary':'btn-outline-primary' ?>" href="?page=<?=$p?>&search=<?=urlencode($search)?>"><?=$p?></a><?php endfor; ?>
