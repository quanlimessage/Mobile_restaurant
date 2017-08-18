<?php

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
use Illuminate\Support\Facades\Input;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::resource('api/manager/category', 'CategoryController');
Route::resource('api/manager/foods', 'FoodsController');
Route::resource('api/manager/tables', 'TablesController');
Route::resource('api/manager/orders', 'OrdersController', ['only'=>['update', 'show']]);
Route::resource('api/manager/alerts', 'AlertsController');
Route::resource('api/manager/orderdetails', 'OrderDetailController');

Route::group(['middleware' => ['auth:api']], function() {
    
    // Route::resource('api/manager/category', 'CategoryController');
    // Route::resource('api/manager/foods', 'FoodsController');
    // Route::resource('api/manager/tables', 'TablesController');
    // Route::resource('api/manager/orders', 'OrdersController', ['only'=>['update', 'show']]);
    // Route::resource('api/manager/alerts', 'AlertsController');
    // Route::resource('api/manager/orderdetails', 'OrderDetailController');
});

Route::post('api/client/orderdetails', 'OrderDetailController@addDetails');
Route::put('api/client/orderdetails/{id}', 'OrderDetailController@update');
Route::resource('api/client/orders', 'OrdersController', ['only' => ['index', 'show', 'store', 'update']]);
Route::resource('api/client/category', 'CategoryController', ['only'=>['index', 'show']]);
Route::resource('api/client/foods', 'FoodsController',['only'=>['index', 'show']]);
Route::resource('api/client/alerts', 'AlertsController', ['only'=>['store']]);
Route::get('api/luis/{luis}', 'OrdersController@luis');

Route::group(['middleware' => ['client']], function() {
    //Route::post('api/client/orders/{token}', 'OrdersController@addOrder');
    /////////// TEST ///////////////
    // Route::post('api/client/orderdetails', 'OrderDetailController@addDetails');
    // Route::put('api/client/orderdetails/{id}', 'OrderDetailController@update');
    // Route::resource('api/client/orders', 'OrdersController', ['only' => ['index', 'show', 'store', 'update']]);
    // Route::resource('api/client/category', 'CategoryController', ['only'=>['index', 'show']]);
    // Route::resource('api/client/foods', 'FoodsController',['only'=>['index', 'show']]);
    // Route::resource('api/client/alerts', 'AlertsController', ['only'=>['store']]);
    // Route::get('api/luis/{luis}', 'OrdersController@luis');
    // Route::resource('api/client/foods/category/{category_id}/{token}', 'FoodsController@getByCategoryID');
    ///////////////////////////////
});

// Route::get('/callback', function (Request $request) {
//     $http = new GuzzleHttp\Client;
Route::resource('api/manager/tables', 'TablesController');

Route::get('/callback', function (Request $request) {
    $http = new GuzzleHttp\Client;

	$response = $http->post('http://localhost/mobile_restaurant/server/public/oauth/token', [
	    'form_params' => [
	        'grant_type' => 'password',
	        'client_id' => '2',
	        'client_secret' => 'riLc61gVyK0bmmUaATyVeTCWMlHa99F9GHCOa3uk',
	        'username' => 'd_trung@stagegroup.jp',
	        'password' => '123456',
	        'scope' => '*',
	    ],
	]);

//  $response = $http->post('http://localhost/restaurant/server/public/oauth/token', [
//      'form_params' => [
//          'grant_type' => 'password',
//          'client_id' => '2',
//          'client_secret' => 'riLc61gVyK0bmmUaATyVeTCWMlHa99F9GHCOa3uk',
//          'username' => 'd_trung@stagegroup.jp',
//          'password' => '123456',
//          'scope' => '*',
//      ],
//  ]);

//  return json_decode((string) $response->getBody(), true)['access_token'];
 });

Route::post('/api/login', function (Request $request) {
    $username = input::get('username');
    $password = input::get('password');

    $http = new GuzzleHttp\Client;

    try {
        $response = $http->post('http://153.126.150.57/restaurant/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => '2',
                'client_secret' => 'dX9HFoQ5YqKelWcNVxYvxiVG7R0VdfOFVJ9NMSUL',
                'username' => $username,
                'password' => $password,
                'scope' => '*',
            ],
        ]);

        $token_type = json_decode((string) $response->getBody(), true)['token_type'];
        $token_key = json_decode((string) $response->getBody(), true)['access_token'];

        $token = array("token"=>$token_type." ".$token_key);
        //return json_decode((string) $response->getBody(), true)['access_token'];
        return json_encode($token, JSON_FORCE_OBJECT);
    } catch (Exception $e) {
        header("HTTP/1.1 401 Unauthorized");
        $token = array("error"=>"The user credentials were incorrect.");
        return response()->json($token,401);
    }
    
});