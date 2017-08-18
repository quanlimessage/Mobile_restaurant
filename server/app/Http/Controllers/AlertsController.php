<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Response,DateTime;
use App\Helper\RSA;
use GuzzleHttp\Client;

class AlertsController extends Controller
{
	private $sFirebaseUrl;
	private $sFirebaseToken;
	private $sFirebaseDbName = 'alert/';
	protected $em;

	public function __construct(EntityManagerInterface $em) {
		$this->sFirebaseUrl = config('firebase.DEFAULT_URL');
		$this->sFirebaseToken = config('firebase.DEFAULT_TOKEN');
		$this->em = $em;
	}

    public function index(){
		$firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);

        //Get List of food
        $alerts = json_decode($firebase->get($this->sFirebaseDbName));
        $result['error_code'] = 0;
        $result['error_msg'] = "";

        if(empty($alerts)){
            $json = array(
                'error_code' => 1500,
                'error_msg' => 'List alert empty',
            );
            return Response::json($json,404);
        }else {
            $result['alert_list'] = $alerts;
        }
        
        return response()->json($result, 200);
    }

    public function show($id = null) {
    	// Create firebase connection
    	$firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
    	$alerts = json_decode($firebase->get($this->sFirebaseDbName . $id)); 
    	$json = array();
		if(empty($alerts)) {
			$json = array(
                'error_code' => 1401,
                'error_msg' => 'Alert not found',
            );
            return Response::json($json,404);
		}
		$json['error_code'] = 0;
		$json['error_msg'] = "";
		$json['alert'] = $alerts;

		return response()->json($json, 200);
    }
    // add Alert
    public function store(Request $request) {
    	Validator::extend('table',function($attribute, $value, $params, $validator) {
            if(isset($value) && empty($this->em->find('App\Models\Tables', $value))){
                return false;
            }
            else {
                return true;
            }
        });
    	// validate food
		$rules = [
            'table_id'=>'required|integer|table',
		];

		$messages = [
			'table_id.required' => 'Table id is required',
            'table_id.integer' => 'Table id is number',
            'table_id.table' => 'Table id does not exist',
		];
		$validator = Validator::make($request->all(), $rules, $messages);
 		$validator->after(function($validator) use($request) {
            // Call back validate
            if(isset($request->table_id) && !empty($this->em->find('App\Models\Tables', $request->table_id))){
                $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
                $check = json_decode($firebase->get($this->sFirebaseDbName,array('orderBy'=> '"receiver"','equalTo' => '""')));
                foreach ($check as $key => $value) {
                    if($value->table_id == $request->table_id) {
                        $validator->errors()->add('table_id', 'Table busy');
                    }
                }
            }
        });
		if ($validator->fails()) {
            $json = array(
                'error_code' => 1502,
                'error_msg' => $validator->errors()->all(),
    		);
    		return Response::json($json,403);
        }

        $alert = (object) array('table_id' => '','time' => date('Y/m/d H:i:s'),'receiver' => '');
        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key))) {
                $alert->$key = $value;
            }   
        }
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $nodePushContent = json_decode($firebase->push('/alert', $alert));
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
        );

        return Response::json($json);
    }
    // Update Alert
    public function update(Request $request, $id = null){

    	// Create firebase connection
    	$firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
    	$oalert = json_decode($firebase->get($this->sFirebaseDbName . $id)); 
    	$json = array();
		if(empty($oalert)) {
			$json = array(
                'error_code' => 1501,
                'error_msg' => 'Alert not found',
            );
            return Response::json($json,404);
		}
    	$rules = [
            'table_id'=>'required|integer',
		];

		$messages = [
			'table_id.required' => 'Table id is required',
            'table_id.integer' => 'Table id is number',
		];
		$validator = Validator::make($request->all(), $rules, $messages);
 		$validator->after(function($validator) use($request) {
            // Call back validate
            if(isset($request->table_id) && empty($this->em->find('App\Models\Tables', $request->table_id))){
                $validator->errors()->add('table_id', 'Table id does not exist');
            }
        });
		if ($validator->fails()) {
            $json = array(
                'error_code' => 1502,
                'error_msg' => $validator->errors()->all(),
    		);
    		return Response::json($json,403);
        }

        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key))) {
                $oalert->$key = $value;
            }
        }
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
		$alert = json_decode($firebase->update($this->sFirebaseDbName.$id, $oalert));

		$json = array(
            'error_code' => 0,
            'error_msg' => 'Update complete',
        );
		
		return response()->json($json, 200);
    }

    public function destroy($id = null) {
    	// Create firebase connection
    	$firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
    	$oalert = json_decode($firebase->get($this->sFirebaseDbName . $id)); 
    	if (empty($oalert)) {
    		$json = array(
                'error_code' => 1401,
                'error_msg' => 'Alert not found',
            );
            return Response::json($json,404);
    	}

		//Delete order from Firebase by Alert ID
		if ($firebase->delete($this->sFirebaseDbName . $id)) {
			 $json = array(
	            'error_code' => 0,
	            'error_msg' => 'Delete complete',
        	);

        	return Response::json($json,200);
		}
    }

}
