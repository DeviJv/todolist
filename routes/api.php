<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('activity', ActivityController::class);
Route::get('activity_trash', [ActivityController::class, 'getAllTrashed']);
Route::get('activity_restore/{id?}', [ActivityController::class, 'restore']);
Route::delete('activity_permanent/{id?}', [ActivityController::class, 'delete_permanent']);

Route::apiResource('todo', TodoController::class);
Route::get('todo_trash', [TodoController::class, 'getAllTrashed']);
Route::get('todo_restore/{id?}', [TodoController::class, 'restore']);
Route::delete('todo_permanent/{id?}', [TodoController::class, 'delete_permanent']);