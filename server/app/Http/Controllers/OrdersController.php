<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Tables;
use App\Models\Foods;
use Doctrine\ORM\EntityManagerInterface;
use Response,DateTime;
use App\Helper\RSA;
use GuzzleHttp\Client;

class OrdersController extends Controller
{
    private $sFirebaseUrl;
    private $sFirebaseToken;
    private $sFirebaseDbName = 'order/';
    private $sFirebaseDbOrderDetail = 'order_detail/';
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->sFirebaseUrl = config('firebase.DEFAULT_URL');
        $this->sFirebaseToken = config('firebase.DEFAULT_TOKEN');
        $this->em = $em;
    }

    public function index(){
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);

        //Get List of food
        $aOrders = json_decode($firebase->get($this->sFirebaseDbName));
        $result['error_code'] = 0;
        $result['error_msg'] = "";

        if(empty($aOrders)){
            $json = array(
                'error_code' => 1400,
                'error_msg' => 'List order empty',
            );
            return Response::json($json,404);
        }else {
            $result['order_list'] = $aOrders;
        }
        
        return response()->json($result, 200);
    }

    public function show($id = null) {
        // Create firebase connection
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrders = json_decode($firebase->get($this->sFirebaseDbName . $id)); 
        $json = array();
        if(empty($aOrders)) {
            $json = array(
                'error_code' => 1401,
                'error_msg' => 'Order not found',
            );
            return Response::json($json,404);
        }
        $json['error_code'] = 0;
        $json['error_msg'] = "";
        $json['order'] = $aOrders;

        return response()->json($json, 200);
    }
    // add Order
    public function store(Request $request) {
        $rsa = new RSA();
        // validate food
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
                'error_code' => 1402,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

        $order = (object) array('table_id' => '','time' => date('Y/m/d H:i:s'),'note' => '', 'status' => '0');
        foreach ($request->all() as $key => $value) {
            if($request->input($key) != '' && array_key_exists($key, $order)) {
               $order->$key = $value;
            }   
        }
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $nodePushContent = json_decode($firebase->push('/order', $order));
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
            'order_id' => $nodePushContent->name,
        );

        return Response::json($json);
    }
    // Update Order
    public function update(Request $request, $id = null){

        // Create firebase connection
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrder = json_decode($firebase->get($this->sFirebaseDbName . $id)); 
        $json = array();
        if(empty($aOrder)) {
            $json = array(
                'error_code' => 1401,
                'error_msg' => 'Order not found',
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
                'error_code' => 1402,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

        foreach ($request->all() as $key => $value) {
            if($request->input($key) != '' && array_key_exists($key, $aOrder) || $key =='note') {
                $aOrder->$key = $value;
                if($request->input('note') == '') {
                    $aOrder->note = "";
                }
            }
        }
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $order = json_decode($firebase->update($this->sFirebaseDbName.$id, $aOrder));

        $json = array(
            'error_code' => 0,
            'error_msg' => 'Update complete',
        );
        
        return response()->json($json, 200);
    }

    public function destroy($id = null) {
        // Create firebase connection
        $firebase = new \Firebase\FirebaseLib($this->sFirebaseUrl, $this->sFirebaseToken);
        $aOrder = json_decode($firebase->get($this->sFirebaseDbName . $id)); 
        if (empty($aOrder)) {
            $json = array(
                'error_code' => 1401,
                'error_msg' => 'Order not found',
            );
            return Response::json($json,404);
        }
        //Delete order from Firebase by Order ID
        if ($firebase->delete($this->sFirebaseDbName . $id)) {
            $json = array(
                'error_code' => 0,
                'error_msg' => 'Delete complete',
            );

            return Response::json($json,200);
        }
    }

    public function luis(Request $request){
        $json = array();

        $mess = urldecode($request->luis);
        $url = 'https://westus.api.cognitive.microsoft.com/luis/v2.0/apps/95e2077e-37ce-4ca9-9330-8f35d98ab42f?subscription-key=dab74d0e133249fcb8cddb91344f4ec7&staging=true&timezoneOffset=0&q=';
        $url .= urlencode($mess);
        
        $result = json_decode(file_get_contents($url));

        if (empty($result->entities)) {
            return Response::json(array(
                    'error_code' => 1201,
                    'error_msg' => "I don't understand what's your mean. Please ask again.",
                ), 200);
        
        }else{
            $function = $result->topScoringIntent->intent;

            $aFoods = $this->LookForFoodName($result->entities);

            /*switch ($function) {
                case '食べ物が食べたい':
                    $json = $this->LookForFoodName($result->entities);
                    break;
                default:
                    $json = $this->LookForFoodCount($result->entities);
                    break;
            }*/

            if( empty($aFoods) ){
                return Response::json(array(
                    'speak_list' => array(
                        'error_code' => 1201,
                        'error_msg' => 'Food not found',
                    )
                ), 404);
            }

            foreach ($aFoods as $oFood) {
                $json[] = array(
                    'food_id' => $oFood->id,
                    'quantity' => 1
                );
            }
        }

        return Response::json(array(
            'speak_list' => $json
        ),200);
    }

    private function LookForFoodName($entities) {
        $json = array();
        
        $type = explode("::",$entities[0]->type)[1];
        $name_search = $entities[0]->entity;

        $results = $this->em->getRepository('App\Models\Foods')->createQueryBuilder('cm')
            ->select('cm')
            ->where('cm.name_jp LIKE :name_jp or cm.name_en LIKE :name_en or cm.name_vi LIKE :name_vi')
            ->setParameter('name_jp', "%".$name_search."%")
            ->setParameter('name_en', "%".$name_search."%")
            ->setParameter('name_vi', "%".$name_search."%")
            ->getQuery()
            ->getResult()
        ;

        return $results;
    }

}
