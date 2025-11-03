<?php

use App\Http\Controllers\PostController;

use App\Http\Controllers\Profile\ProfileEditController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/user/post' ,[PostController::class , 'posts']);
Route::post('/user/post' ,[PostController::class , 'posts']);
Route::post('/user/createpost' ,[PostController::class , 'store']);
Route::post('/user/edit/{id}' , [ProfileEditController::class , 'update']);
Route::post('/user/delete' , [PostController::class ,'delete']);