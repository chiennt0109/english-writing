<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(app_name()) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>.chip{display:inline-block;padding:.2rem .5rem;background:#eef;border-radius:12px;margin:.1rem}.error{background:#ffe2e2;padding:2px 4px;border-radius:4px}</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
<div class="container"><a class="navbar-brand" href="<?= e(url('/dashboard')) ?>">Writing Coach</a>
<div class="navbar-nav">
<a class="nav-link" href="<?= e(url('/topics')) ?>">Topics</a><a class="nav-link" href="<?= e(url('/submissions')) ?>">Submissions</a><a class="nav-link" href="<?= e(url('/model-essays')) ?>">Model Essays</a>
<?php if (can_access(['teacher','admin'])): ?><a class="nav-link" href="<?= e(url('/teacher/submissions')) ?>">Teacher</a><a class="nav-link" href="<?= e(url('/reports/errors')) ?>">Reports</a><?php endif; ?>
<?php if (can_access(['admin'])): ?><a class="nav-link" href="<?= e(url('/admin/users')) ?>">Admin</a><?php endif; ?>
<a class="nav-link" href="<?= e(url('/logout')) ?>">Logout</a>
</div></div></nav>
<?php $baseEsc = e(base_path()); $contentPatched = str_replace(['href="/','action="/','src="/'], ['href="'.$baseEsc.'/','action="'.$baseEsc.'/','src="'.$baseEsc.'/'], $content); ?>
<div class="container pb-5"><?= $contentPatched ?></div>
</body></html>
