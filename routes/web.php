<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('products', ProductController::class);
Route::post('/products/{product}/bid', [ProductController::class, 'placeBid'])->name('products.bid');
Route::get('/live', [ProductController::class, 'index'])->name('products.live');
Route::get('/my-win', [ProductController::class, 'bidderWins'])->name('products.mywin');
Route::post('/products/{product}/message', [ProductController::class, 'sendMessage'])->name('products.message');
Route::get('/search', [ProductController::class, 'search'])->name('products.search');

   Route::get('/users', [HomeController::class, 'index'])->name('admin.users.index');
    Route::get('/users/search', [HomeController::class, 'search'])->name('admin.users.search');
    Route::put('/users/{user}/status', [HomeController::class, 'updateStatus'])->name('admin.users.status');


});

require __DIR__.'/auth.php';
