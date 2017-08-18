<?php

namespace App\Http\Controllers;
use App\Models\Tables;
use Illuminate\Http\Request;
use Response,DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Models\TablesDescription;
use Validator;

use App\Helper\rsa;

class TablesController extends Controller
{
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function index() {
        $results = $this->em->getRepository('App\Models\Tables')->findAll();
        $json = array();
        // validate table
        if(empty($results)){
            $json = array(
                'error_code' => 1200,
                'error_msg' => 'List table empty',
            );
            return Response::json($json,404);
        }
        $json['error_code'] = 0;
        $json['error_msg'] = "";
        foreach ($results as $value) {
            $json['table_list'][$value->id] = [
                "id" => $value->id,
                "name_jp" => $value->name_jp,
                "name_en" => $value->name_en,
                "name_vi" => $value->name_vi,
                'description_jp' => $value->description_jp,
                'description_en' => $value->description_en,
                'description_vi' => $value->description_vi,
                'table_status' => $value->table_status,
                'num_of_seat' => $value->num_of_seat,
                "updated_at" => $value->updated_at->format('c')
            ];
        }
        
        return response::json($json, 200);
    }
    public function show($id) {

        $result = $this->em->find('App\Models\Tables', $id);
        $json = array();
        if(empty($result)){
            $json = array(
                'error_code' => 1201,
                'error_msg' => 'Table not found',
            );
            return Response::json($json,404);
        }
        $json['error_code'] = 0;
        $json['error_msg'] = "";
        $json['table'] = [
            "id" => $result->id,
            "name_jp" => $result->name_jp,
            "name_en" => $result->name_en,
            "name_vi" => $result->name_vi,
            'description_jp' => $result->description_jp,
            'description_en' => $result->description_en,
            'description_vi' => $result->description_vi,
            'table_status' => $result->table_status,
            'num_of_seat' => $result->num_of_seat,
            "updated_at" => $result->updated_at->format('c')
        ];

        return Response::json($json,200);
    }

    public function store(Request $request) {
        // validate table
        $rules = [
            'name_jp' =>'required_without_all:name_en,name_vi',
            'description_jp' =>'required_without_all:description_en,description_vi',
            'num_of_seat' =>'required|integer',
        ];

        $messages = [
            'name_jp.required_without_all' => 'name is required, Enter name(JP) or name(EN) or name(VI)',
            'description_jp.required_without_all' => 'description is required, Enter description(JP) or description(EN) or description(VI)',
            'num_of_seat.required' => 'Number of Seat is required',
            'num_of_seat.integer' => 'Number of Seat is Number',

        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1202,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }
        // Create Object
        $oTable = new Tables($request);

        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key))) {
               $oTable->$key = $value;
            }   
        }
        $this->em->persist($oTable);
        // Save to DB
        $this->em->flush();
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
        );

        return Response::json($json,200);
    }

    public function update(Request $request,$id, rsa $rsa) {
        $oTable = $this->em->find('App\Models\Tables', $id);
        if(empty($oTable)){
            $json = array(
                'error_code' => 1201,
                'error_msg' => 'Table not found',
            );
            return Response::json($json,404);
        }
        // validate table
        $rules = [
            'name_jp' =>'required_without_all:name_en,name_vi',
            'description_jp' =>'required_without_all:description_en,description_vi',
            'num_of_seat' =>'required|integer',
        ];

        $messages = [
            'name_jp.required_without_all' => 'name is required, Enter name(JP) or name(EN) or name(VI)',
            'description_jp.required_without_all' => 'description is required, Enter description(JP) or description(EN) or description(VI)',
            'num_of_seat.required' => 'Number of Seat is required',
            'num_of_seat.integer' => 'Number of Seat is Number',

        ];
        
		//Trung MAC-Address
        $public_key = $rsa->e;
        $n = $rsa->n;

        $private_key = $rsa->d;
        //save to db
        $request->request->add(["private_key" => $private_key, "n" => $n]);
        //End Trung


        $rules = ['num_of_seat' =>'required|integer'];
        $messages = ['num_of_seat.required' => 'Num of Seat is required','num_of_seat.integer' => 'Num of Seat is number',];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1202,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key))) {
               $oTable->$key = $value;
            }   
        }
        $this->em->persist($oTable);
        $this->em->flush();
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Update complete',
            'public_key' => $public_key,
        );

        return Response::json($json,200);
    }

    public function destroy($id) {
        $otables = $this->em->find('App\Models\Tables', $id);

        if(empty($otables)){
            $json = array(
                'error_code' => 1201,
                'error_msg' => 'Table not found',
            );
            return Response::json($json,404);
        }

        $this->em->remove($otables);

        $this->em->flush();
        
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Delete complete',
        );

        return Response::json($json);
    }
}