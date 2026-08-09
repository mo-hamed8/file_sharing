<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/rooms', [RoomController::class, 'store'])
    ->middleware('throttle:room_creation')
    ->name('rooms.store');

Route::get('/join', [JoinController::class, 'show'])->name('join.show');
Route::post('/join', [JoinController::class, 'store'])
    ->middleware('throttle:room_join')
    ->name('join.store');

Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
Route::get('/r/{room}', [RoomController::class, 'shortLink'])->name('rooms.short');

Route::post('/rooms/{room}/join', [ParticipantController::class, 'store'])
    ->middleware('throttle:room_join')
    ->name('rooms.join');

Route::post('/rooms/{room}/leave', [ParticipantController::class, 'destroy'])
    ->middleware('room.auth:participant')
    ->name('rooms.leave');

Route::post('/rooms/{room}/end', [RoomController::class, 'end'])
    ->middleware('room.auth:host')
    ->name('rooms.end');

Route::get('/rooms/{room}/state', [RoomController::class, 'state'])
    ->middleware('room.auth:participant')
    ->name('rooms.state');

Route::get('/rooms/{room}/qr', [RoomController::class, 'qr'])->name('rooms.qr');

Route::post('/rooms/{room}/files', [FileController::class, 'store'])
    ->middleware(['room.auth:participant', 'throttle:file_upload'])
    ->name('rooms.files.store');

Route::get('/rooms/{room}/files/{file}/download', [FileController::class, 'download'])
    ->middleware('room.auth:participant')
    ->name('rooms.files.download');

Route::delete('/rooms/{room}/files/{file}', [FileController::class, 'destroy'])
    ->middleware('room.auth:participant')
    ->name('rooms.files.destroy');
