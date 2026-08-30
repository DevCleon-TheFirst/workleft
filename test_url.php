<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$url = \Illuminate\Support\Facades\URL::signedRoute('calendar.sync', ['user' => $user->id]);
echo "Signed URL: $url\n";
