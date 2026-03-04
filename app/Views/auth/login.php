<div class="row justify-content-center"><div class="col-md-4">
<div class="card"><div class="card-body">
<h4>Login</h4>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="/login">
<input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
<div class="mb-2"><label>Email</label><input class="form-control" name="email" required></div>
<div class="mb-2"><label>Password</label><input type="password" class="form-control" name="password" required></div>
<button class="btn btn-primary w-100">Login</button></form>
<p class="mt-3 small">Demo: admin/admin123, teacher/teacher123, student/student123</p>
</div></div></div></div>
