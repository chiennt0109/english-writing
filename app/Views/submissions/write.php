<h3>Write Essay</h3>
<div class="card"><div class="card-body">
<h5><?= e($task['title'] ?? 'Invalid task') ?></h5><p><?= e($task['prompt'] ?? '') ?></p>
<form method="post" action="/write"><input type="hidden" name="_csrf" value="<?= csrf_token() ?>"><input type="hidden" name="task_id" value="<?= (int)($task['id'] ?? 0) ?>">
<div class="mb-2"><label>Title</label><input name="title" class="form-control" required></div>
<div class="mb-2"><label>Content</label><textarea id="content" name="content" rows="12" class="form-control" required></textarea><small id="wc">Word count: 0</small></div>
<button class="btn btn-success">Submit</button></form></div></div>
<script>const t=document.getElementById('content');const wc=document.getElementById('wc');t?.addEventListener('input',()=>wc.textContent='Word count: '+t.value.trim().split(/\s+/).filter(Boolean).length);</script>
