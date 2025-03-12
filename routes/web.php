<?php

use App\Http\Controllers\PollController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PollController::class, 'index'])->name('polls.index');

Route::get('/polls/create', [PollController::class, 'create'])->name('polls.create');
Route::post('/polls', [PollController::class, 'store'])->name('polls.store');
Route::get('/polls/{poll:slug}', [PollController::class, 'show'])->name('polls.show');

Route::post('/polls/{poll:slug}/vote', [VoteController::class, 'store'])
    ->middleware('throttle:votes')
    ->name('polls.vote');
