<?php

use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\ParticipantController;
use App\Http\Controllers\Api\V1\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::post('/rooms', [RoomController::class, 'store'])
        ->middleware('throttle:room_creation')
        ->name('rooms.store');

    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');

    Route::post('/rooms/{room}/join', [RoomController::class, 'join'])
        ->middleware('throttle:room_join')
        ->name('rooms.join');

    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave'])
        ->middleware('room.auth:participant')
        ->name('rooms.leave');

    Route::post('/rooms/{room}/end', [RoomController::class, 'end'])
        ->middleware('room.auth:host')
        ->name('rooms.end');

    Route::get('/rooms/{room}/state', [RoomController::class, 'state'])
        ->middleware('room.auth:participant')
        ->name('rooms.state');

    Route::get('/rooms/{room}/participants', [ParticipantController::class, 'index'])
        ->middleware('room.auth:participant')
        ->name('participants.index');

    Route::patch('/rooms/{room}/participants/{participant}', [ParticipantController::class, 'update'])
        ->middleware('room.auth:host')
        ->name('participants.update');

    Route::get('/rooms/{room}/files', [FileController::class, 'index'])
        ->middleware('room.auth:participant')
        ->name('files.index');

    Route::post('/rooms/{room}/files', [FileController::class, 'store'])
        ->middleware(['room.auth:participant', 'throttle:file_upload'])
        ->name('files.store');

    Route::get('/rooms/{room}/files/{file}', [FileController::class, 'show'])
        ->middleware('room.auth:participant')
        ->name('files.show');

    Route::get('/rooms/{room}/files/{file}/download', [FileController::class, 'download'])
        ->middleware('room.auth:participant')
        ->name('files.download');

    Route::delete('/rooms/{room}/files/{file}', [FileController::class, 'destroy'])
        ->middleware('room.auth:participant')
        ->name('files.destroy');
});
