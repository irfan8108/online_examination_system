<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\CrudController;

class FrontController extends CrudController
{

	/**
	* Include Authentication Middleware
	* @return void 
	*/
	public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
    	return view('admin.welcome');
    }
    
    public function crud(Request $request, $type, $cmd = null, $cmd_id = null){
    	switch ($cmd) {
			case 'add':
				return $this->create($request, $request->method(), $type);
				break;

			case 'delete':
				return CrudController::delete(('\App\\'.ucfirst($type))::find($cmd_id));
				break;

			case 'update':
				return $this->create($request, $request->method(), $type, true, $cmd_id);
			
			default:
				return $this->read($type, $request);
				break;
		}
    }

    private function create(Request $request, $method, $type, $isEditMode = false, $cmd_id = null){
		
		switch ($method) {
			case 'GET':

				$data['isEditMode'] = $isEditMode;

				if($isEditMode){
					$tempItem = ('\App\\'.ucfirst($type))::whereId($cmd_id)->get();
					$data['data'] = $tempItem[0];
				}

				// GET ADDITIONAL DATA FOR SPECIFIC VIEW
				switch ($type) {
					case 'exam':
						break;
					case 'subject':
						break;
					case 'topic':
						$data['subjects'] = CrudController::view('\App\Subject');
						break;
					case 'question':
						$data['subjects'] = CrudController::view('\App\Subject');
						$data['topics'] = CrudController::view('\App\Topic');
						break;
					case 'user':
						break;
					
					default:
						return back()->with('error', 'Whoops, create() type on GET request has been not defined. Please try with available options!');
						break;
				}
				return view('admin.'.$type.'_add')->with($data);
				break;
			
			case 'POST':

				$optionalValidationRules = [];

				// UPDATE THE ITEM, IF EDIT AVAILABLE
				if($isEditMode){
					return CrudController::update('\App\\'.ucfirst($type), $request);
				}

				switch ($type) {
					case 'exam':
						$optionalValidationRules = ['exclude'=>['subjects','status']];
						break;
					
					case 'question':
						$request = $this->serialize($request);
						$optionalValidationRules = ['exclude'=>['exam_id','topic_id']];
						break;

					case 'user':
						$request->request->add(['password' => \Hash::make($request->password)]);
						// dd($request->all());
						break;

					default:
						return back()->with('error', 'Whoops, create() type on POST request has been not defined. Please try with available options!');
						break;
				}

				return $this->store($type, $request, $optionalValidationRules);

				break;	

			default:
				return back()->with('error', 'Whoops, create() method not defined. Please try with available options!');
				break;
		}
    }

    protected function store($modelName, $request, $optionalValidationRules = null){
    	$objString = '\App\\'.ucfirst($modelName);
    	$obj = new $objString;

		$result = CrudController::store(new $obj, $request, $optionalValidationRules);

		if($result['status']){

			if($result['is_validated']){
				return redirect()->route('crud',[$modelName])->with('success', 'Successfully added');	
			}

		}
		else{

			if($result['is_validated']){

				if(in_array('error', $result)){
					return back()->with('errors', $result['error'])->withInput();
				}
				else{
					return back()->with('error', 'Whoops, something is wrong? Please try after sometime.');
				}			

			}
			else{
				return back()->with('error', $result['error'])->withInput();	
			}

		}
    }

    private function read($type, $request){
    	switch ($type) {
			case 'exam':
				break;
			case 'subject':
				break;
			case 'topic':
				break;
			case 'question':
				// GET THE VIEW'S REQUIRED DATA
				$result['subjects'] = CrudController::view('\App\Subject');
				$result['topics'] = CrudController::view('\App\Topic');
				break;
			case 'user':
				break;
			
			default:
				return back()->with('error', 'Whoops, type is not defined. Please try with available options!');
				break;
		}
		
		// IF VIEW NEED FILTERED DATA
		if($request->has('filter')){
			// GET FILTERED DATA USING FILTER CMD
	    	$result[$type.'s'] = CrudController::filter('\App\\'.ucfirst($type), $request);

	    	// SET FLASH SESSION FOR INPUT JUST RECEIVED 
	    	session()->flashInput($request->input());
		}
		else{
			// GET DATA AS NORMAL
			$result[$type.'s'] = CrudController::view('\App\\'.ucfirst($type));
		}

		// LOAD THE VIEW WITH DATA
		return view('admin.'.$type)->with($result);
    }

  	// ITERATE TO SERIALIZE WITH ALL AVAILABLE OPTIONS
    private function serialize($request){
	
		$answers = [];
	
		foreach ($request->all() as $key => $value) {
			if( (strpos($key, 'option') !== false) || (strpos($key, 'title') !== false) ) {

			    if(strpos($key, 'type') !== false){
		    		
		    		// QUESTION TITLE
			    	if($key == 'title_type'){

			    		$k = 'title_'.$request->$key;

			    		switch ($request->$key) {

	    					case 'txt':
	    						// INPUT IS IN TEXT FORMAT
	    						$request->request->add(['title' => $request->$k]);
	    						break;

							case 'img':
								// INPUT IS IN IMAGE FILE FORMAT
								// UPLOAD FILE & SET TITLE INTO REQUEST
								$request->request->add(['title' => $this->uploadDoc($request->$k, 'b64')]);
	    						break;
	    					
	    					default:
	    						break;
	    				}
			    		// DONT BIND WITHIN ANSERS, JUST SEPRATE KEY
			    		// THATs WHY SKIPPING THE SWITCH
			    		continue;
			    	}
			    	// AVAILABLE ANSWERs 
			    	else{
			    		// value For KEY AND VALUE
			    		$v = substr($key, 6, 1);
			    		$k = 'option'.substr($key, 6, 1)."_".$request->$key;
			    	}

    				switch ($request->$key) {

    					case 'txt':
    						// INPUT IS IN TEXT FORMAT
    						$answers[$v] = $request->$k;
    						break;

						case 'img':
							// INPUT IS IN IMAGE FILE FORMAT
							// UPLOAD FILE
							$answers[$v] = $this->uploadDoc($request->$k, 'b64');
    						break;
    					
    					default:
    						break;
    				}
			    }
			}
		}

		$request->request->add(['available_answers' => json_encode($answers)]);

		return $request;
    }

    public function makeExamLive(Request $request){
    	if($request->isMethod('get')){
    		$data['exams'] = CrudController::view('\App\Exam');
    		$data['subjects'] = CrudController::view('\App\Subject');
    		// dd($data);
    		return view('admin.make_exam_live')->with($data);
    	}
    }

}
