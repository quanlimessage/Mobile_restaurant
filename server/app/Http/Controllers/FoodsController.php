<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Foods;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Response,DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManagerStatic as Image;

class FoodsController extends Controller
{
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function index(Request $request){
        if(isset($request->category_id)) {
            Validator::extend('category',function($attribute, $value, $params, $validator) {
                if(isset($value) && empty($this->em->find('App\Models\Category', $value))){
                    return false;
                }
                else {
                    return true;
                }
            });
             $rules = [
                'category_id'=>'category',
            ];

            $messages = [
                'category_id.category' => 'Category id does not exist',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $json = array(
                    'error_code' => 1202,
                    'error_msg' => $validator->errors()->all(),
                );
                return Response::json($json,403);
            }
            $result =  $this->em->getRepository('App\Models\Foods')->findBy(array('category_id' => $request->category_id));
        }else {
            $result =  $this->em->getRepository('App\Models\Foods')->findAll();
        }
        $json = array();
        // validate food
        if(empty($result)){
            $json = array(
                'error_code' => 1200,
                'error_msg' => 'List Food empty',
            );
            return Response::json($json,404);
        }
        $json['error_code'] = 0;
        $json['error_msg'] = "";
        foreach ($result as $value) {
            $json['foods_list'][$value->id] = [
                "id" => $value->id,
                "name_jp" => $value->name_jp,
                "name_en" => $value->name_en,
                "name_vi" => $value->name_vi,
                "description_jp" => $value->description_jp,
                "description_en" => $value->description_en,
                "description_vi" => $value->description_vi,
                'cost_price' => $value->cost_price,
                'sale_price' => $value->sale_price,
                'time_to_prepare' => $value->time_to_prepare,
                'category_id' => $value->category_id,
                "image_url" => url('/') . "/storage/images/foods/" . $value->image_url,
                "updated_at" => $value->updated_at->format('c')
            ];
        }
        
        return response::json($json, 200);
    }

    public function show($id) {
        $result = $this->em->find('App\Models\Foods', $id);
        $json = array();
        if(empty($result)){
            $json = array(
                'error_code' => 1201,
                'error_msg' => 'Food not found',
            );
            return Response::json($json,404);
        }
        $json['error_code'] = 0;
		$json['error_msg'] = "";
		$json['food'] = [
			"id" => $result->id,
            "name_jp" => $result->name_jp,
            "name_en" => $result->name_en,
            "name_vi" => $result->name_vi,
            "description_jp" => $result->description_jp,
            "description_en" => $result->description_en,
            "description_vi" => $result->description_vi,
            'cost_price' => $result->cost_price,
            'sale_price' => $result->sale_price,
            'time_to_prepare' => $result->time_to_prepare,
            'category_id' => $result->category_id,
            "image_url" => url('/') . "/storage/images/foods/" . $result->image_url,
            "updated_at" => $result->updated_at->format('c')
		];

	    return Response::json($json);
    }
    // add food
    public function store(Request $request) {
        // Check image base64
        Validator::extend('checkbase64',function($attribute, $value, $params, $validator) {
            $value = str_replace('data:image/jpeg;base64,', '', $value);
            $value = str_replace('data:image/jpg;base64,', '', $value);
            $value = str_replace('data:image/png;base64,', '', $value);
            $value = str_replace('data:image/gif;base64,', '', $value);
            $image = base64_decode($value);
            $f = finfo_open();
            $result = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
            if($result == 'image/jpeg' || $result == 'image/jpg' || $result == 'image/png' || $result == 'image/gif') {
                return true;
            }
            return false;
        });
        if ($request->hasFile('image_url')) {
            $rules['image_url'] = 'image|mimes:jpg,png,jpeg,gif|max:2048';
        }
        elseif (!empty($request->image_url)) {
            $rules['image_url'] = 'checkbase64';
        }
        // validate food
        $rules = [
            'name_jp' =>'required_without_all:name_en,name_vi',
            'cost_price' =>'required|integer',
            'sale_price' =>'integer|nullable',
            'category_id'=>'integer|nullable',
        ];

        $messages = [
            'name_jp.required_without_all' => 'name is required, Enter name(JP) or name(EN) or name(VI)',
            'cost_price.required' => 'Cost price is required',
            'cost_price.integer' => 'Cost price is number',
            'sale_price.integer' => 'Sale price is number',
            'category_id.integer' => 'Category is number',
            'image_url.image' => 'The file is not image file',
            'image_url.mimes' => 'The image file has to be jpg|png|jpeg|gif',
            'image_url.max' => 'The image file has to be 2MB or less',

        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function($validator) use($request) {
            // Call back validate
            if($request->sale_price > $request->cost_price) {
                $validator->errors()->add('sale_price', 'Sale price is not greater than Cost price');
            }
            if(isset($request->category_id) && empty($this->em->find('App\Models\Category', $request->category_id))){
                $validator->errors()->add('category_id', 'Category id does not exist');
            }
        });
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1202,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }
        // upload image
        if ($request->hasFile('image_url')) {
            if($request->file('image_url')->isValid()) {
                // save file to folder
                $path = $request->file('image_url')->store("public/images/foods");
                if(!empty($path)) $filename = explode('public/images/foods/',$path);
                $request->image_url = $filename[1];
            } 
        }elseif (!empty($request->image_url)) {
            try {
                $images_name  = $this->rand_string(40).".png";
                if(!Storage::exists('public/images/foods/')) {
                    Storage::makeDirectory('public/images/foods/');
                }
                $path = 'storage/images/foods/' . $images_name;
                $image_string = 'data:image/png;base64,'.$request->image_url;
                $img = Image::make(file_get_contents($image_string))->save($path);
                $request->image_url = $images_name;
            }catch (\Exception $e) {

                $json = array(
                    'error_code' => 1202,
                    'error_msg' => 'Base64 Image not decoder',
                );
                return Response::json($json,403);

            }
        }
        // Create Object
        $oFood = new Foods($request);
        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key)) && $key !='image_url') {
               $oFood->$key = $value;
            }   
        }
        $this->em->persist($oFood);

        // Save to DB
        $this->em->flush();
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
        );

        return Response::json($json);
    }
    // update food
    public function update(Request $request,$id) {
        // validate food
        $oFood = $this->em->find('App\Models\Foods', $id);
        if(empty($oFood)){
            $json = array(
                'error_code' => 1201,
                'error_msg' => 'Food not found',
            );
            return Response::json($json);
        }
         // Check image base64
        Validator::extend('checkbase64',function($attribute, $value, $params, $validator) {
            $value = str_replace('data:image/jpeg;base64,', '', $value);
            $value = str_replace('data:image/jpg;base64,', '', $value);
            $value = str_replace('data:image/png;base64,', '', $value);
            $value = str_replace('data:image/gif;base64,', '', $value);
            $image = base64_decode($value);
            $f = finfo_open();
            $result = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
            if($result == 'image/jpeg' || $result == 'image/jpg' || $result == 'image/png' || $result == 'image/gif') {
                return true;
            }
            return false;
        });
        if (!empty($request->image_url)) {
            $rules['image_url'] = 'checkbase64';
        }
        $rules = [
            'name_jp' =>'required_without_all:name_en,name_vi',
            'cost_price' =>'required|integer',
            'sale_price' =>'integer|nullable',
            'category_id'=>'integer|nullable',
        ];

        $messages = [
            'name_jp.required_without_all' => 'name is required, Enter name(JP) or name(EN) or name(VI)',
            'cost_price.required' => 'Cost price is required',
            'cost_price.integer' => 'Cost price is number',
            'sale_price.integer' => 'Sale price is number',
            'category_id.integer' => 'Category is number',
            'image_url.checkbase64' => 'The file is not image file',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function($validator) use($request) {
            // Call back validate
            if($request->sale_price > $request->cost_price) {
                $validator->errors()->add('sale_price', 'Sale price is not greater than Cost price');
            }
            if(isset($request->category_id) && empty($this->em->find('App\Models\Category', $request->category_id))){
                $validator->errors()->add('category_id', 'Category id does not exist');
            }
        });
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1202,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }

        // upload image
        if(!empty($request->image_url)) {
            try {
                $images_name  = $this->rand_string(40).".png";
                if(!Storage::exists('public/images/foods/')) {
                    Storage::makeDirectory('public/images/foods/');
                }
                $path = 'storage/images/foods/' . $images_name;
                $image_string = 'data:image/png;base64,'.$request->image_url;
                $img = Image::make(file_get_contents($image_string))->save($path);
                $oFood->image_url = $images_name;
            }catch (\Exception $e) {

                $json = array(
                    'error_code' => 1302,
                    'error_msg' => 'Base64 Image not decoder',
                );
                return Response::json($json,403);

            }
        }
        
        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key)) && $key !='image_url') {
               $oFood->$key = $value;
            }   
        }

        $this->em->persist($oFood);

        // Save to DB
        $this->em->flush();
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Update complete',
        );

        return Response::json($json);
    }
    // delete food
    public function destroy($id) {
        $oFood = $this->em->find('App\Models\Foods', $id);
        if(empty($oFood)){
            $json = array(
                'error_code' => 1201,
                'error_msg' => 'Food not found',
            );
            return Response::json($json,404);
        }
        $this->em->remove($oFood);
        $this->em->flush();
        $image_old= $oFood ->image_url;
        if(!empty($image_old)) {
          Storage::delete("public/images/foods/" . $image_old);
        }
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Delete complete',
        );

        return Response::json($json);
    }

    public function rand_string( $length ) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $str = '';
        $size = strlen( $chars );
        for( $i = 0; $i < $length; $i++ ) {
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }
        return $str;
    }
}
