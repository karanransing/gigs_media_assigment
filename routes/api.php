<?php

/*use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
*/

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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\BookController;
//use App\Http\Controllers\ProductController;

Route::post('login', [ApiController::class, 'authenticate']);
Route::post('register', [ApiController::class, 'register']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('get_user', [ApiController::class, 'get_user']);
    Route::post('logout', [ApiController::class, 'logout']);

    Route::get('view_profile/{u_id}', [ApiController::class, 'viewprofine']);
    Route::delete('delete_user/{u_id}', [ApiController::class, 'destroy']);
    Route::patch('update_user/{u_id}', [ApiController::class, 'update']);
    
    /* book crud operation routes */
    Route::get('book_list', [BookController::class, 'index']);
    Route::post('add_book', [BookController::class, 'store']);
    Route::patch('update_book/{b_id}', [BookController::class, 'update']);
    Route::delete('delete_book/{b_id}', [BookController::class, 'destroy']);
    Route::get('view_book/{b_id}', [BookController::class, 'show']);

    Route::post('rent_book/{b_id}', [BookController::class, 'rentbook']);
    Route::post('return_book/{b_id}', [BookController::class, 'returnbook']);
    Route::get('getuserbooks', [BookController::class, 'getuserbooks']);

});

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
