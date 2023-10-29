<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Users only routes
 */
//Route::group(['middleware' => ['useronly']], function () {
Route::group(['middleware' => ['api', 'useronly']], function () {
    /**
     * Space Controller
     */
    // Create a new space
    Route::post('/createNewSpace', [SpaceController::class, 'createNewSpace']);
    // Edit space, save changes
    Route::post('/editSpace', [SpaceController::class, 'editSpace']);
    // Return the specific space from the unique space name
    Route::post('/getTheSpace', [SpaceController::class, 'getTheSpace']);
    // Return a list of channels of given format
    Route::post('/getChannelsList', [SpaceController::class, 'getChannelsList']);
    // Return a list of spaces
    Route::get('/getSpacesList', [SpaceController::class, 'getSpacesList']);
    // Get members of space list
    Route::post('/getMembersOfSpaceList', [SpaceController::class, 'getMembersOfSpaceList']);
    
    /**
     * Channel Controller
     */
    // Create new channel in a space
    Route::post('/createNewChannel', [ChannelController::class, 'createNewChannel']);
    // Edit existing channel in a space
    Route::post('/editChannel', [ChannelController::class, 'editChannel']);
    
    /**
     * Message Controller
     */
    // Insert new message
    Route::post('/insertNewMessage', [MessageController::class, 'insertNewMessage']);
    // Get previous 10 messages
    Route::post('/getMessages', [MessageController::class, 'getMessages']);
});

/**
 * User Authentication
 */
// User create
Route::post('/userCreate', [AuthController::class, 'userCreate']);
// User login
Route::post('/userLogin', [AuthController::class, 'userLogin']);
// User logout
Route::get('/userLogout', [AuthController::class, 'userLogout']);
// Refresh JWT token
Route::get('/refreshJWT', [AuthController::class, 'refreshJWT']);
// Check for user login
Route::get('/userLoggedInTest', [AuthController::class, 'userLoggedInTest']);
// Return user data by the Auth facade
Route::post('/userData', [AuthController::class, 'userData']);
Route::get('/userData', [AuthController::class, 'userData']);