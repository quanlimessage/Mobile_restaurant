<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NamesController extends Controller
{
    /**
	 * Display a listing of the resource
	 *
	 * @return Response
	 */
	public function index()
	{
		// echo "aaaa";
	 //  	return array(
	 //      	1 => "John",
	 //      	2 => "Mary",
	 //      	3 => "Steven"
	 //    );
	    return view("welcome",compact('name'));
	}

	public function index1()
	{
	  	echo "<h1>1</h1>";
	  	return view("welcome",compact('name'));
	}

	public function index2()
	{
	  	return 2;
	}

	public function index3()
	{
	  	return 3;
	}

	public function show()
	{
	  	echo "show";
	}
}
