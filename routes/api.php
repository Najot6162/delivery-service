<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\DeliveryController;
use \App\Http\Controllers\RelocationController;
use \App\Http\Controllers\BranchController;
use \App\Http\Controllers\ConfigTimeController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\CarController;
use \App\Http\Controllers\CallCenterController;
use App\Http\Controllers\DashboardController;
use \App\Http\Controllers\ProblemController;
use \App\Http\Controllers\NotificationController;
use \App\Http\Controllers\FileController;
use \Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\WebNotificationController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Broadcast::routes(['middleware' => 'auth:api']);

Route::group(['namespace' => 'Api'], function () {

    Route::post('/login', [AuthController:: class, 'login']);
    Route::post('/login_grant', [AuthController::class, 'loginGrant']);
    Route::get('/download-image/{month}/{url}', [FileController::class, 'downloadImageFile']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        //Delivery Notification
        Route::get('/get-delivery-notification',[WebNotificationController::class,'getDeliveryNotification']);
        Route::get('/read-notification/{notification}',[WebNotificationController::class,'readNotification']);

        //Delivery Api
        Route::post('/create-delivery', [DeliveryController::class, 'CreateDelivery']);
        Route::get('/get-all-delivery', [DeliveryController::class, 'gettAllDelivery']);//->middleware(['admin']);
        Route::put('/update-delivery/{id}', [DeliveryController::class, 'updateDelivery']); //->middleware(['admin']);
        Route::put('/back-step/{id}', [DeliveryController::class, 'backStep']);
        Route::put('/update-pickup-time/{id}', [DeliveryController::class, 'updatePickupTime']);
        //File Upload
        Route::post('/upload_file', [FileController::class, 'uploadFile']);//->middleware(['admin']);

        //ConfigTime Api
        Route::get('/check-time', [ConfigTimeController::class, 'checkTime']);
        Route::post('/crete-config-time', [ConfigTimeController::class, 'creteConfigTime']);
        Route::put('/update-config-time', [ConfigTimeController::class, 'updateConfigTime']);
        Route::get('/get-all-config-time', [ConfigTimeController::class, 'getAllConfigTime']);

        //Branch Api
        Route::post('/create-branch', [BranchController::class, 'createBranch']);
        Route::put('/update-branch/{id}', [BranchController::class, 'updateBranch']);
        Route::get('/get-all-branchs', [BranchController::class, 'getAllBranch']);
        Route::get('/get-all-regions', [BranchController::class, 'getAllBranchRegion']);
        Route::post('/create-region', [BranchController::class, 'createRegion']);
        //Relocation Api
        Route::post('/create-relocation', [RelocationController::class, 'CreateRelocation']);
        Route::get('/get-all-relocation', [RelocationController::class, 'getAllRelocation']);
        Route::put('/update-relocation/{id}', [RelocationController::class, 'updateRelocation']);
        Route::get('/get-relocation/{id}', [RelocationController::class, 'getRelocation']);

        //Driver Api
        Route::post('/create-driver', [UserController::class, 'createDriver']);
        Route::put('/update-driver/{id}', [UserController::class, 'updateDriver']);
        Route::get('/get-all-driver', [UserController::class, 'getAllDrivers']);//->middleware(['driver']);
        Route::get('/get-delivery/{id}', [UserController::class, 'getDelivery']);
        Route::get('/get-driver-only-active', [UserController::class, 'getAllDriversOnlyActive']);
        Route::get('/get-driver/{id}', [UserController::class, 'getDriver']);

        //Car Api
        Route::post('/create-car', [CarController::class, 'createCar']);
        Route::put('/update-car/{id}', [CarController::class, 'updateCar']);
        Route::get('/get-all-car', [CarController::class, 'getAllCars']);
        Route::get('/get-car-term/{id}', [CarController::class, 'getCarTerm']);
        Route::put('/update-car-term/{id}', [CarController::class, 'updateCarTerm']);
        Route::delete('/delete-car/{id}', [CarController::class, 'deleteCar']);
        Route::put('/update-active-car/{id}', [CarController::class, 'updateOnlyActiveCar']);
        Route::get('/get-car/{id}', [CarController::class, 'getCar']);

//      Call Center Api
//    Route::get('/get-all-call-delivery', [CallCenterController::class, 'getAllCallDelivery']);

        //Service Api
        Route::post('/create-problem', [ProblemController::class, 'createProblem']);
        Route::get('/get-all-problems', [ProblemController::class, 'getAllProblems']);
        Route::put('/update-problem/{id}', [ProblemController::class, 'updateProblem']);
        Route::get('/get-problem-item/{id}', [ProblemController::class, 'getProblemItem']);
        Route::post('/add-comment', [ProblemController::class, 'addCommentToTimeStep']);

        Route::get('/get-problem/{id}', [ProblemController::class, 'getProblem']);

        //Service Center
        Route::post('/create-problem-service', [ProblemController::class, 'createProblemService']);
        Route::put('/update-problem-service/{id}', [ProblemController::class, 'updateProblemService']);
        Route::get('/get-all-problem-service', [ProblemController::class, 'getAllProblemServices']);
        Route::get('/get-problem-service/{id}', [ProblemController::class, 'getProblemService']);
        Route::delete('/problem-service/{id}', [ProblemController::class, 'deleteProblemService']);
        Route::get('/get-all-users-with-branch', [UserController::class, 'getAllUsersWithBranch']);
        Route::put('/update-user-branch/{id}', [UserController::class, 'updateUserBranch']);

        //Dashboard Api
        Route::get('/get-counts', [DashboardController::class, 'getCounts']);

        //Permission Api
        Route::get('/get-roles_group', [UserController::class, 'roleGroup']);
        Route::get('/get-permission', [UserController::class, 'getPermission']);
        Route::put('/update-permission', [UserController::class, 'updatePermission']);
        Route::get('/get-menus', [UserController::class, 'getMenus']);
        Route::post('/create-role-permission', [UserController::class, 'createRoleWithPermission']);

        //Users Api
        Route::get('/get-users', [UserController::class, 'getUsers']);
        Route::put('/update-user/{id}', [UserController::class, 'updateUser']);
        Route::put('/update-user-active/{id}', [UserController::class, 'updateUserActive']);
        Route::get('/get-user/{id}', [UserController::class, 'getUser']);
        Route::get('/get-all-users', [UserController::class, 'getAllUsers']);
        Route::post('/create-user', [UserController::class, 'createUser']);
        Route::post('/create-fcm-token/{id}', [NotificationController::class, 'createFcmToken']);
        Route::get('/get-auth-user', [AuthController::class, 'getAuthUser']);
        Route::put('/update-user-deleted/{id}', [UserController::class, 'UserIsDelete']);

    });
    //Api for Mobile app
    Route::get('/download-app', [FileController::class, 'getDownload']);
    Route::get('/read-json-app-file', [FileController::class, 'readAppJsonFile']);
    Route::get('/get-mobile-menus/{id}', [UserController::class, 'getPermissionForMobile']);
    Route::post('/upload-app', [FileController::class, 'uploadAppFile']);
});

Route::get('unauthorized', function () {
    return response()->json([
        'error' => 'Unauthorized.'
    ], 401);
})->name('unauthorized');



