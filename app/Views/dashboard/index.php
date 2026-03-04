<h3>Student Dashboard</h3>
<div class="row">
<div class="col-md-7"><div class="card mb-3"><div class="card-body"><h5>Progress timeline</h5><table class="table table-sm"><tr><th>Date</th><th>Overall</th><th>Task</th><th>Coh</th><th>Lex</th><th>Gra</th></tr><?php foreach($progress as $p): ?><tr><td><?= e($p['d']) ?></td><td><?= e((string)$p['overall']) ?></td><td><?= e((string)$p['task']) ?></td><td><?= e((string)$p['coh']) ?></td><td><?= e((string)$p['lex']) ?></td><td><?= e((string)$p['gra']) ?></td></tr><?php endforeach; ?></table></div></div></div>
<div class="col-md-5"><div class="card mb-3"><div class="card-body"><h5>Error Heatmap</h5><?php foreach($heatmap as $h): ?><div class="d-flex justify-content-between"><span class="chip"><?= e($h['error_code']) ?></span><strong><?= e((string)$h['c']) ?></strong></div><?php endforeach; ?></div></div></div>
</div>
<div class="card"><div class="card-body"><h5>Peer mistakes feed (anonymous)</h5><?php foreach($peer as $p): ?><p><strong><?=e($p['topic_title'])?></strong> - <?=e($p['error_code'])?>: <em><?=e($p['snippet'])?></em></p><?php endforeach; ?></div></div>
