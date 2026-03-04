<h3>Tasks - <?= e($topic['title'] ?? '') ?></h3>
<?php foreach($tasks as $task): ?>
<div class="card mb-3"><div class="card-body"><h5><?=e($task['title'])?></h5><p><?=e($task['prompt'])?></p>
<p>Range: <?=e((string)$task['min_words'])?>-<?=e((string)$task['max_words'])?> | Level <?=e($task['level'])?></p>
<a href="/write?task_id=<?= $task['id'] ?>" class="btn btn-primary btn-sm">Write now</a></div></div>
<?php endforeach; ?>
