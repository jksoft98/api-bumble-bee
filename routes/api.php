<?php

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



Route::group([
    'middleware' => 'api',
    'namespace'  => 'App\Http\Controllers',
    'prefix'     => 'auth/v1'

], function ($router) { 


    /****************************************************************************************
    * Customer Register.
    *
    *  Required param - phone
    *                 - full_name
    *                 - email
    *                 - address
    *                 - dob
    *                 - nic
    *                 - password
    *                 - password_confirmation
    *
    *  Optional param - NO
    *
    *  Usage  - commonly used for customer registration.
    /****************************************************************************************/
    Route::post('/customer-register'                            , 'AuthController@customerRegister');



    /****************************************************************************************
    *  Admin login.
    *
    *  Required param - email
    *                 - password
    *
    *  Optional param - NO
    *
    *  Usage  - commonly used for login to the admin portal.
    /****************************************************************************************/
    Route::post('/admin-login'                                  , 'AuthController@adminLogin');



    /****************************************************************************************
    *  Admin logout.
    *
    *  Required header - authorization - JWT Token 
    *
    *  Optional param - NO
    *
    *  Usage  - commonly used for logout from the admin portal.
    /****************************************************************************************/
    Route::post('/admin-logout'                                 , 'AuthController@adminLogout');




    Route::post('/user-create'                                  , 'AuthController@userCreate');

    Route::post('/user-edit'                                    , 'AuthController@userEdit');


    Route::post('/user-role-create'                             , 'ActionController@userRoleCreate');

    Route::post('/user-role-edit'                               , 'ActionController@userRoleEdit');

    Route::post('/change-user-status'                           , 'ActionController@changeUserStatus');

    Route::put('/customer-edit'                                 , 'AuthController@customerEdit');

    Route::put('/change-customer-status'                        , 'ActionController@changeCustomerStatus');

    Route::put('/change-role-status'                            , 'ActionController@changeRoleStatus');

    Route::post('/brand-create'                                 , 'ActionController@brandCreate');

    Route::put('/brand-edit'                                    , 'ActionController@brandEdit');

    Route::put('/change-brand-status'                           , 'ActionController@changeBrandStatus');

    Route::post('/category-create'                              , 'ActionController@categoryCreate');

    Route::put('/category-edit'                                 , 'ActionController@categoryEdit');

    Route::put('/change-category-status'                        , 'ActionController@changeCategoryStatus');

    Route::post('/product-create'                               , 'ActionController@productCreate');

    Route::put('/product-edit'                                  , 'ActionController@productEdit');

    


    Route::get('/get-all-user-role-data'                        , 'DataController@getAllUserRoles');

    Route::get('/get-single-user-data'                          , 'DataController@getSingleUser');

    Route::get('/get-all-users-data'                            , 'DataController@getAllUsers');

    Route::get('/get-user-permission-data-as-pluck'             , 'DataController@getUserPermissionsAsPluck');

    Route::get('/get-single-user-role-data'                     , 'DataController@getSingleUserRole');

    Route::get('/get-allowed-notification-user-role-as-pluck'   , 'DataController@getAllowedNotificationUserRolePluck');

    Route::get('/get-single-user-notification-data'             , 'DataController@getSingleUserNotification');

    Route::get('/get-all-customer-data'                         , 'DataController@getAllCustomers');

    Route::get('/get-dashboard-data'                            , 'DataController@getDashboardData');

    Route::get('/get-single-customer-data'                      , 'DataController@getSingleCustomer');

    Route::get('/get-all-brand-data'                            , 'DataController@getAllBrands');

    Route::get('/get-all-category-data'                         , 'DataController@getAllCategories');

    Route::get('/get-all-vendor-data'                           , 'DataController@getAllVendors');

    Route::get('/get-single-product-data'                       , 'DataController@getSingleProduct');

    Route::get('/get-all-product-data'                          , 'DataController@getAllProducts');

});
