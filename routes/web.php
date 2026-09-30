<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildLearningController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParentController;
use App\Http\Middleware\EnsureActive;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', EnsureActive::class])->group(function () {
    Route::get('/parent/dashboard', [ParentController::class, 'dashboard'])->name('parent.dashboard');
    Route::get('/parent/children', [ParentController::class, 'dashboard'])->name('parent.children.index');
    Route::get('/parent/children/create', [ParentController::class, 'createChild'])->name('parent.children.create');
    Route::post('/parent/children', [ParentController::class, 'storeChild'])->name('parent.children.store');
    Route::get('/parent/children/{child}', [ParentController::class, 'edit'])->name('parent.children.edit');
    Route::put('/parent/children/{child}', [ParentController::class, 'update'])->name('parent.children.update');
    Route::delete('/parent/children/{child}', [ParentController::class, 'destroy'])->name('parent.children.destroy');
    Route::get('/parent/children/{child}/progress', [ParentController::class, 'progress'])->name('parent.children.progress');
    Route::delete('/parent/children/{child}/progress', [ParentController::class, 'reset'])->name('parent.children.reset');
    Route::prefix('learn/{child}')->middleware('ensure.child.access')->name('child.')->group(function () {
        Route::get('/', [ChildLearningController::class, 'index'])->name('dashboard');
        Route::get('/categories', [ChildLearningController::class, 'categories'])->name('categories');
        Route::get('/categories/{category}', [ChildLearningController::class, 'category'])->name('category');
        Route::get('/lessons/{lesson}', [ChildLearningController::class, 'lesson'])->name('lesson');
        Route::post('/lessons/{lesson}/bookmark', [ChildLearningController::class, 'bookmark'])->name('bookmark');
        Route::post('/lessons/{lesson}/complete', [ChildLearningController::class, 'completeLesson'])->name('lesson.complete');
        Route::get('/games', [ChildLearningController::class, 'games'])->name('games');
        Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activity');
        Route::post('/activities/{activity}/check', [ActivityController::class, 'check'])->middleware('throttle:120,1')->name('activity.check');
        Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete'])->middleware('throttle:30,1')->block(10, 10)->name('activity.complete');
    });
    Route::prefix('admin')->middleware(EnsureAdmin::class)->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [AdminController::class, 'dashboard'])->name('reports');
        Route::get('/{resource}', [AdminController::class, 'index'])->name('index');
        Route::get('/{resource}/create', [AdminController::class, 'create'])->name('create');
        Route::post('/{resource}', [AdminController::class, 'store'])->name('store');
        Route::get('/{resource}/{id}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{resource}/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{resource}/{id}',[AdminController::class, 'destroy'])->name('destroy');
    });
});
