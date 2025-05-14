<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [ChatController::class, 'index']);
Route::get('/view', [ChatController::class, 'view'])->name('view');
Route::get('/group/{name}', [ChatController::class, 'groupView'])->name('group.view');
Route::post('/upload', [ChatController::class, 'upload'])->name('chat.upload');
