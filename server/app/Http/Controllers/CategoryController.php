<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Response,DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Validator;
use Intervention\Image\ImageManagerStatic as Image;

class CategoryController extends Controller
{
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function index(){
    	$result =  $this->em->getRepository('App\Models\Category')->findAll();
    	$json = array();
         if(empty($result)){
            $json = array(
                'error_code' => 1300,
                'error_msg' => 'List Category empty',
            );
            return Response::json($json,404);
        }
    	$json['error_code'] = 0;
		$json['error_msg'] = "";
		foreach ($result as $value) {
			$json['category_list'][$value -> id] = [
				"id" => $value->id,
				"name_jp" => $value->name_jp,
				"name_en" => $value->name_en,
				"name_vi" => $value->name_vi,
				"category_image" => url('/') . "/storage/images/category/" . $value->category_image,
				"updated_at" => $value->updated_at->format('c')
			];
		}

		
		return response::json($json, 200);
    }

    public function show($id) {
        $result = $this->em->find('App\Models\Category', $id);
        $json = array();
        if(empty($result)){
            $json = array(
                'error_code' => 1301,
                'error_msg' => 'Category not found',
            );
            return Response::json($json,404);
        }
        $json['error_code'] = 0;
		$json['error_msg'] = "";
		$json['category'] = [
			"id" => $result->id,
			"name_jp" => $result->name_jp,
			"name_en" => $result->name_en,
			"name_vi" => $result->name_vi,
			"category_image" => url('/') . "/images/category/" . $result->category_image,
			"updated_at" => $result->updated_at->format('c')
		];

	    return Response::json($json);
    }
    // add category
    public function store(Request $request) {
        // Check image base64
        Validator::extend('checkbase64',function($attribute, $value, $params, $validator) {
            $value = str_replace('data:image/jpeg;base64,', '', $value);
            $image = base64_decode($value);
            $f = finfo_open();
            $result = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
            if($result == 'image/jpeg' || $result == 'image/jpg' || $result == 'image/png' || $result == 'image/gif') {
                return true;
            }
            return false;
        });
        if ($request->hasFile('category_image')) {
            $rules['category_image'] = 'image|mimes:jpg,png,jpeg,gif|max:2048';
        }
        elseif (!empty($request->category_image)) {
            $rules['category_image'] = 'checkbase64';
        }

        // validate category
        $rules = [
            'name_jp' =>'required_without_all:name_en,name_vi',
        ];
        $messages = [
            'name_jp.required_without_all' => 'name is required, Enter name(JP) or name(EN) or name(VI)',
            'category_image.checkbase64' => 'The file is not image file',
            'category_image.image' => 'The file is not image file',
            'category_image.mimes' => 'The image file has to be jpg|png|jpeg|gif',
            'category_image.max' => 'The image file has to be 2MB or less',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1302,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,400);
        }
        
        // upload image
        if ($request->hasFile('category_image')) {
            if($request->file('category_image')->isValid()) {
                // save file to folder
                $path = $request->file('category_image')->store("public/images/category");
                if(!empty($path)) $filename = explode('public/images/category/',$path);
                $request->category_image = $filename[1];
            } 
        }elseif(!empty($request->category_image)) {
            try {
                $images_name  = $this->rand_string(40).".png";
                if(!Storage::exists('public/images/category/')) {
                    Storage::makeDirectory('public/images/category/');
                }
                $path = 'storage/images/category/' . $images_name;
                $image_string = 'data:image/png;base64,'.$request->category_image;
                $img = Image::make(file_get_contents($image_string))->save($path);
                $request->category_image = $images_name;
            }catch (\Exception $e) {

                $json = array(
                    'error_code' => 1302,
                    'error_msg' => 'Base64 Image not decoder',
                );
                return Response::json($json,500);

            }
        }
        
        // Create Object
        $oCategory = new Category($request);
        $this->em->persist($oCategory);

        // Save to DB
        $this->em->flush();
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Add complete',
        );

        return Response::json($json,200);
    }
    // update category
    public function update(Request $request,$id) {
        // validate category
        $oCategory = $this->em->find('App\Models\Category', $id);
        if(empty($oCategory)){
            $json = array(
                'error_code' => 1301,
                'error_msg' => 'Category not found',
            );
            return Response::json($json,404);
        }
        // Check image base64
        Validator::extend('checkbase64',function($attribute, $value, $params, $validator) {
            $value = str_replace('data:image/jpeg;base64,', '', $value);
            $image = base64_decode($value);
            $f = finfo_open();
            $result = finfo_buffer($f, $image, FILEINFO_MIME_TYPE);
            if($result == 'image/jpeg' || $result == 'image/jpg' || $result == 'image/png' || $result == 'image/gif') {
                return true;
            }
            return false;
        });
        if (!empty($request->category_image)) {
            $rules['category_image'] = 'checkbase64';
        }
        $rules = [
            'name_jp' =>'required_without_all:name_en,name_vi',
        ];

        $messages = [
            'name_jp.required_without_all' => 'name is required, Enter name(JP) or name(EN) or name(VI)',
            'category_image.checkbase64' => 'The file is not image file',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $json = array(
                'error_code' => 1302,
                'error_msg' => $validator->errors()->all(),
            );
            return Response::json($json,403);
        }
        
        // upload image
        if(!empty($request->category_image)) {
            try {
                $images_name  = $this->rand_string(40).".png";
                if(!Storage::exists('public/images/category/')) {
                    Storage::makeDirectory('public/images/category/');
                }
                $path = 'storage/images/category/' . $images_name;
                $image_string = 'data:image/png;base64,'.$request->category_image;
                $img = Image::make(file_get_contents($image_string))->save($path);
                $oCategory->category_image = $images_name;
            }catch (\Exception $e) {

                $json = array(
                    'error_code' => 1302,
                    'error_msg' => 'Base64 Image not decoder',
                );
                return Response::json($json,403);

            }
        }
    
        foreach ($request->all() as $key => $value) {
            if(!empty($request->input($key)) && $key != 'category_image') {
               $oCategory->$key = $value;
            }   
        }

        // fining Category
        // $oCategory->updateCategory($request);
        // $this->em->persist($oCategory);

        // Save to DB
        $this->em->flush();
      
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Update complete',
        );

        return Response::json($json,200);
    }
    // delete Category
    public function destroy($id) {
        $oCategory = $this->em->find('App\Models\Category', $id);
        if(empty($oCategory)){
            $json = array(
                'error_code' => 1301,
                'error_msg' => 'Category not found',
            );
            return Response::json($json,404);
        }
        $this->em->remove($oCategory);
        $this->em->flush();
		$image_old= $oCategory ->category_image;
        if(!empty($image_old)) {
		  Storage::delete("public/images/category/" . $image_old);
        }
        $json = array(
            'error_code' => 0,
            'error_msg' => 'Delete complete',
        );

        return Response::json($json,200);
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
