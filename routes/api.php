<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\MemberController;
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

/**
 * Users only routes
 */
//Route::group(['middleware' => ['useronly']], function () {
Route::group(['middleware' => ['api', 'useronly']], function () {
    /**
     * Member Controller
     */
    // Create a new membership of a space
    Route::post('/createMember', [MemberController::class, 'createMember']);
    // Delete a member from a space
    Route::post('/deleteMember', [MemberController::class, 'deleteMember']);
    // Change a membership role
    Route::post('/updateMembershipRole', [MemberController::class, 'updateMembershipRole']);

    /**
     * Space Controller
     */
    // Create a new space
    Route::post('/createSpace', [SpaceController::class, 'createSpace']);
    // Read space from the unique space name
    Route::post('/readSpace', [SpaceController::class, 'readSpace']);
    // Read highlighted spaces list
    Route::get('/readHighlightedSpacesList', [SpaceController::class, 'readHighlightedSpacesList']);
    // Read member of spaces list
    Route::get('/readMemberOfSpacesList', [SpaceController::class, 'readMemberOfSpacesList']);
    // Read members of space list
    Route::post('/readMembersOfSpaceList', [SpaceController::class, 'readMembersOfSpaceList']);
    // Update space, save changes
    Route::post('/updateSpace', [SpaceController::class, 'updateSpace']);
    // Delete a space, and its channels and messages
    Route::post('/deleteSpace', [SpaceController::class, 'deleteSpace']);
    
    /**
     * Channel Controller
     */
    // Create new channel in a space
    Route::post('/createChannel', [ChannelController::class, 'createChannel']);
    // Read "FORMAT" channels list
    Route::post('/readChannelsList', [ChannelController::class, 'readChannelsList']);
    // Update existing channel in a space
    Route::post('/updateChannel', [ChannelController::class, 'updateChannel']);
    // Delete existing channel in a space
    Route::post('/deleteChannel', [ChannelController::class, 'deleteChannel']);
    
    /**
     * Message Controller
     */
    // Insert new message
    Route::post('/createMessage', [MessageController::class, 'createMessage']);
    // Get previous 10 messages
    Route::post('/read10Messages', [MessageController::class, 'read10Messages']);
    // Update existing message
    Route::post('/updateExistingMessage', [MessageController::class, 'updateExistingMessage']);
    // Delete message
    Route::post('/deleteMessage', [MessageController::class, 'deleteMessage']);
});

/**
 * Auth Controller
 */
// User create
Route::post('/createUser', [AuthController::class, 'createUser']);
// Return user data by the Auth facade
Route::post('/readUser', [AuthController::class, 'readUser']);
Route::get('/readUser', [AuthController::class, 'readUser']);
// User login
Route::post('/userLogin', [AuthController::class, 'userLogin']);
// User logout
Route::get('/userLogout', [AuthController::class, 'userLogout']);
// Check for user login
Route::get('/userLoggedInTest', [AuthController::class, 'userLoggedInTest']);
// Refresh JWT token
Route::get('/refreshJWT', [AuthController::class, 'refreshJWT']);