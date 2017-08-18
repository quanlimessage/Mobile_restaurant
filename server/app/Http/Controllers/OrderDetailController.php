<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tables;
use App\Models\Foods;
use Doctrine\ORM\EntityManagerInterface;
use Response,DateTime;
use App\Helper\RSA;

class OrderDetailController extends Controller
{
	private $sFirebaseUrl;
	private $sFirebaseToken;
	private $sFirebaseDbName = 'order/';
	protected $em;

	public function __construct(EntityManagerInterface $em) {
		$this->sFirebaseUrl = config('firebase.DEFAULT_URL');
		$this->sFirebaseToken = config('firebase.DEFAULT_TOKEN');
		$this->em = $em;
	}

    public function index(Request $request){
		$json = array();
        // validate order detail
        $rules = [
            'order_id' => 'required',
        ];

        $messages = [
            'order_id.required' => 'Order id is required',

        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function($validator) use($request) {
            // Call back validate
            $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
            $order = json_decode($firebase->get('/order/'.$request->order_id));
            if(isset($request->order_id) && empty($order)){
                $validator->errors()->add('order_id', 'Order id does not exist');
            }
        });
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1602,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

		//Get List of order detail
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
		$aOrders = json_decode($firebase->get($this->sFirebaseDbName.$request->order_id));
		$result['error_code'] = 0;
		$result['error_msg'] = "";

		if(empty($aOrders->order_detail_list)){
            $json = array(
                'error_code' => 1600,
                'error_msg' => 'List order detail empty',
            );
            return Response::json($json,404);
        }else {
        	$result['order_detail_list'] = $aOrders->order_detail_list;
        }
		
		return response()->json($result, 200);
    }

    public function show(Request $request,$id = null) {
    	$json = array();
        // validate order detail
        $rules = [
            'order_id' => 'required',
        ];

        $messages = [
            'order_id.required' => 'Order id is required',

        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function($validator) use($request) {
            // Call back validate
            $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
            $order = json_decode($firebase->get('/order/'.$request->order_id));
            if(isset($request->order_id) && empty($order)){
                $validator->errors()->add('order_id', 'Order id does not exist');
            }
        });
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1602,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }
        // Create firebase connection
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrder = json_decode($firebase->get($this->sFirebaseDbName . $request->order_id));
        if(isset($aOrder) && !empty($aOrder->order_detail_list)) {
            foreach ($aOrder->order_detail_list as $key => $value) {
                if($key == $id) {
                    $check = 1;
                    break;
                }
            }
            if(!isset($check)) {
                $json = array(
                'error_code' => 1601,
                'error_msg' => 'Order Detail not found',
                );
                return Response::json($json,404);
            }
            
        }else {
             $json = array(
                'error_code' => 1601,
                'error_msg' => 'Order Detail not found',
                );
                return Response::json($json,404);
        }

		$json['error_code'] = 0;
		$json['error_msg'] = "";
		$json['order_detail'] = $aOrder->order_detail_list->$id;
		return response()->json($json, 200);
    }
    // add Order
    public function store(Request $request) {
    	
    	$rsa = new RSA();
    	// validate food
		$rules = [
			'order_id' => 'required',
            'food_id' => 'required|integer',
		];

		$messages = [
			'order_id.required' => 'Order id is required',
			'food_id.required' => 'Food id is required',
            'food_id.integer' => 'Food id is number',

		];
		$validator = Validator::make($request->all(), $rules, $messages);
 		$validator->after(function($validator) use($request) {
            // Call back validate
            if(isset($request->food_id) && empty($this->em->find('App\Models\Foods', $request->food_id))){
                $validator->errors()->add('food_id', 'Food id does not exist');
            }
            // Call back validate
            $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
            $order = json_decode($firebase->get('/order/'.$request->order_id));
            if(isset($request->order_id) && empty($order)){
                $validator->errors()->add('order_id', 'Order id does not exist');
            }
        });
		if ($validator->fails()) {
            $json = array(
                'error_code' => 1602,
                'error_msg' => $validator->errors()->all(),
    		);
    		return Response::json($json,403);
        }
        $order_detail_id = str_random(20);
        $order_detail[$order_detail_id] = (object) array('order_detail_id' => $order_detail_id,'food_id' => '', 'amount' => 0, 'note' => '', 'state' => 'Sent');
        foreach ($request->all() as $key => $value) {
            if($request->input($key) != '' && array_key_exists($key, $order_detail[$order_detail_id])) {
               $order_detail[$order_detail_id]->$key = $value;
            }   
        }
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrder = json_decode($firebase->get($this->sFirebaseDbName . $request->order_id));
        
        if(isset($aOrder->order_detail_list) && !empty($aOrder->order_detail_list)) {
            $aOrder->order_detail_list = array_merge((array) $aOrder->order_detail_list,$order_detail);
        }
        else {
            $aOrder->order_detail_list = $order_detail;
        }
        $order = json_decode($firebase->update($this->sFirebaseDbName.$request->order_id, $aOrder));
      			
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
        );

        return Response::json($json);
    }

    // Add order detail list to order
    public function addDetails(Request $request) {
        $rsa = new RSA();
        // validate food
        $rules = [
            'order_id' => 'required'
        ];

        $messages = [
            'order_id.required' => 'Order id is required',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function($validator) use($request) {

            $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
            $order = json_decode($firebase->get('/order/'.$request->order_id));
            if(isset($request->order_id) && empty($order)){
                $validator->errors()->add('order_id', 'Order id does not exist');
            }

            foreach ($request->detail_list as $key => $value) {
                //var_dump($value);
                $food_id = $value["food_id"];
                if (!isset($food_id) && !is_numeric($food_id)) {
                    $validator->errors()->add('detail_index('. $key .')', 'Food id is not a number');
                }
                else if(empty($this->em->find('App\Models\Foods', $food_id))){
                    $validator->errors()->add('food_id('. $food_id .')', 'Food id('. $food_id .') does not exist');
                }
            }
        });

        if ($validator->fails()) {
            $json = array(
                'error_code' => 1602,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

        foreach ($request->detail_list as $detail) {
            $order_detail_id = str_random(20);
            while (isset($order_detail[$order_detail_id])) {
                $order_detail_id = str_random(20);
            }
            $order_detail[$order_detail_id] = (object) array('order_detail_id' => $order_detail_id,'food_id' => '', 'amount' => 0, 'note' => '', 'state' => 0);
            
            foreach ($detail as $key => $value) {
                               
                if($detail[$key] != '' && array_key_exists($key, $order_detail[$order_detail_id])) {
                   $order_detail[$order_detail_id]->$key = $value;
                }   
            }
        }
        
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrder = json_decode($firebase->get($this->sFirebaseDbName . $request->order_id));
        
        if(isset($aOrder->order_detail_list) && !empty($aOrder->order_detail_list)) {
            $aOrder->order_detail_list = array_merge((array) $aOrder->order_detail_list,$order_detail);
        }
        else {
            $aOrder->order_detail_list = $order_detail;
        }
        $order = json_decode($firebase->update($this->sFirebaseDbName.$request->order_id, $aOrder));
                
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
        );

        return Response::json($json);
    }

    public function update(Request $request, $id = null){
    	$json = array();
		// validate order detail
		$rules = [
			'order_id' => 'required',
            'food_id' => 'required|integer',
		];

		$messages = [
			'order_id.required' => 'Order id is required',
			'food_id.required' => 'Food id is required',
            'food_id.integer' => 'Food id is number',

		];
		$validator = Validator::make($request->all(), $rules, $messages);
 		$validator->after(function($validator) use($request) {
            // Call back validate
            if(isset($request->food_id) && empty($this->em->find('App\Models\Foods', $request->food_id))){
                $validator->errors()->add('food_id', 'Food id does not exist');
            }
            // Call back validate
            $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
            $order = json_decode($firebase->get('/order/'.$request->order_id));
            if(isset($request->order_id) && empty($order)){
                $validator->errors()->add('order_id', 'Order id does not exist');
            }
        });
		if ($validator->fails()) {
            $json = array(
                'error_code' => 1602,
                'error_msg' => $validator->errors()->all(),
    		);
    		return Response::json($json,403);
        }
        // Create firebase connection
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrder = json_decode($firebase->get($this->sFirebaseDbName . $request->order_id));
        if(isset($aOrder) && !empty($aOrder->order_detail_list)) {
            foreach ($aOrder->order_detail_list as $key => $value) {
                if($key == $id) {
                    $check = 1;
                    break;
                }
            }
            if(!isset($check)) {
                $json = array(
                'error_code' => 1601,
                'error_msg' => 'Order Detail not found',
                );
                return Response::json($json,404);
            }
            
        }else {
             $json = array(
                'error_code' => 1601,
                'error_msg' => 'Order Detail not found',
                );
                return Response::json($json,404);
        }
        $order_detail[$id] = $aOrder->order_detail_list->$id;
        foreach ($request->all() as $k => $v) {
            if($request->input($k) !='' && array_key_exists($k, $order_detail[$id]) || $k =='note') {
                $order_detail[$id]->$k = $v;
                if($request->input('note') == '') {
                    $order_detail[$id]->note = "";
                }
            }   
        }

        $order_detail_list = array_merge((array) $aOrder->order_detail_list,$order_detail);
        $order = json_decode($firebase->update($this->sFirebaseDbName.$request->order_id, $aOrder));
		$json = array(
            'error_code' => 0,
            'error_msg' => 'Update complete',
        );
		
		return response()->json($json, 200);
    }

    public function destroy(Request $request,$id = null) {
        // validate food
        $rules = [
            'order_id' => 'required',
        ];

        $messages = [
            'order_id.required' => 'Order id is required',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function($validator) use($request) {
            // Call back validate
            $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
            $order = json_decode($firebase->get('/order/'.$request->order_id));
            if(isset($request->order_id) && empty($order)){
                $validator->errors()->add('order_id', 'Order id does not exist');
            }
        });
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1602,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

    	// Create firebase connection
    	$firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
    	$aOrder = json_decode($firebase->get($this->sFirebaseDbName . $request->order_id));
        if(isset($aOrder) && !empty($aOrder->order_detail_list)) {
            foreach ($aOrder->order_detail_list as $key => $value) {
                if($key == $id) {
                    $check = 1;
                    break;
                }
            }
            if(!isset($check)) {
                $json = array(
                'error_code' => 1601,
                'error_msg' => 'Order Detail not found',
                );
                return Response::json($json,404);
            }
            
        }else {
             $json = array(
                'error_code' => 1601,
                'error_msg' => 'Order Detail not found',
                );
                return Response::json($json,404);
        } 
		//Delete order from Firebase by Order ID
        unset($aOrder->order_detail_list->$id);
		if ($firebase->update($this->sFirebaseDbName.$request->order_id, $aOrder)) {
			 $json = array(
	            'error_code' => 0,
	            'error_msg' => 'Delete complete',
        	);

        	return Response::json($json,200);
		}
    }

}
