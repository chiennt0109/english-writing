<h3>Admin - Topics</h3><?php foreach($topics as $t): ?><div class="card mb-2"><div class="card-body"><h5><?=e($t['title'])?></h5><p><?=e($t['description'])?></p></div></div><?php endforeach; ?>
