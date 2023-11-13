<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MakeController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReplacementController;
use App\Http\Controllers\SideController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VersionController;
use App\Http\Middleware\IsAdmin;
use App\Models\Make;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/token', function () {
    return csrf_token();
})->name('getToken');

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

//Route::get('/dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');



    Route::get('lang/{lang}', [LanguageController::class,'switchLang'])->name('lang');


    /*Route::get('/mmv',function () {
         return Inertia::render('MMV',[
             'makes' => Make::select('*')->orderBy('name','ASC')->getQuery()->paginate(15),
         ]);
     })->name('mmv');*/

    Route::prefix('warehouse')->name('warehouse.')->group(function () {

        Route::get('/', function () {
            return Redirect::route('warehouse.products.index');
        });

        Route::resource('products', ProductController::class)->except('create', 'show');
        Route::post('products/{sku}/photos/upload', [ProductController::class, 'uploadPhotos'])->name('products.photos.upload');


        Route::get('products/new',[ProductController::class,'newEditIndex'])->name('products.newIndex');
        Route::get('products/{sku}/editing',[ProductController::class,'newEditIndex'])->name('products.editIndex');
        Route::delete('products/{skus}/deletes',[ProductController::class,'destroyBulk'])->name('products.destroyBulk');
        Route::resource('products',ProductController::class);
    });


});

require __DIR__ . '/auth.php';
