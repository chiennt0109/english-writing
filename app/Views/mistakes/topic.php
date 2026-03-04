<h3>Common Mistakes - <?= e($topic['title'] ?? '') ?></h3>
<table class="table"><tr><th>Error code</th><th>Frequency</th></tr><?php foreach($mistakes as $m): ?><tr><td><?= e($m['error_code']) ?></td><td><?= e((string)$m['c']) ?></td></tr><?php endforeach; ?></table>
<div class="card"><div class="card-body"><h5>Useful vocabulary & phrases</h5><span class="chip">sustainable development</span><span class="chip">long-term perspective</span><span class="chip">from my perspective</span><span class="chip">it is widely believed that</span></div></div>
