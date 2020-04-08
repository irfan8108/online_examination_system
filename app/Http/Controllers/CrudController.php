<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CrudController extends Controller
{
    private function validation($request, $modelObj, $additionalValidationRules){
    	// PROCEED, IF FILLABLES AVAILABLE
    	if(empty($this->getFillables($modelObj))){
    		return ['status'=>false, 'is_validated'=>false, 'error'=>'Fillables has been not defined.'];
    	}


		foreach ($this->getFillables($modelObj) as $value) {
            $validateArray[$value] = 'required';
        }

        if(!empty($additionalValidationRules)){
        	
        	foreach ($additionalValidationRules as $k => $v) {
				foreach ($v as $key => $value) {
					if($k === 'include'){
						// LOGIC WILL BE DEFINED HERE
					}
					elseif($k === 'exclude'){
						// EXCLUDE FROM VALIDATION RULE, IF EXIST
						if(array_key_exists($value, $validateArray)){
							unset($validateArray[$value]);
						}
					}
				}
			}

        }

        $validation = \Validator::make($request->all(), $validateArray);
        if ($validation->fails()) {
            return ['status'=>false, 'is_validated'=>true, 'error'=>$validation->errors()];
        	dd($validateArray);
        } 

        return ['status'=>true];
    }

    protected function store($modelObj, $request, $additionalValidationRules = null){
    	$validation = $this->validation($request, $modelObj, $additionalValidationRules);
    	if(!$validation['status']){
    		return $validation;
    	}

        foreach ($request->all() as $key => $value) {
            if(in_array($key, $this->getFillables($modelObj))){
        		$modelObj->$key = $value;
            }
        }
        if($modelObj->save()){
        	return ['status'=>true, 'is_validated'=>true, 'data_id'=>$modelObj->id];
        }
        return ['status'=>false, 'is_validated'=>true];
    }

    private function getFillables($modelObj){
    	return $modelObj->getFillable();
    }

    // protected function view($model, $withArr = []){
	protected function view($model){
		// return $model::with($withArr)->orderBy('id', 'desc')->get();
		return $model::orderBy('id', 'desc')->get();
    }

  //   protected function filter($model, $cmd){
		// // PREPARE ORDER_BY
  //   	$orderBy = is_null($cmd['orderBy']) ? 'desc' : $cmd['orderBy'];
    	
  //   	// PREPARE WHERE ARRAY
  //   	$whereArr = [];
  //   	foreach ($cmd['where'] as $key => $value) {
  //   		if(!is_null($value))
  //   			$whereArr[$key] = $value;
  //   	}

  //   	// dd($whereArr);
  //   	// dd($orderBy);

		// return $model::where($whereArr)->orderBy('id', $orderBy)->get();
  //   }
    protected function filter($model, $cmd){
		// PREPARE ORDER_BY
    	$orderBy = is_null($cmd->orderBy) ? 'desc' : $cmd->orderBy;
    	
    	// PREPARE WHERE ARRAY
    	$whereArr = [];
    	foreach ($cmd->where as $key => $value) {
    		if(!is_null($value))
    			$whereArr[] = [$key, $value];
    	}

		return $model::where($whereArr)->orderBy('id', $orderBy)->get();
    }

    protected function delete($item){
    	if($item->delete()){
    		return back()->with('success', 'Successfully deleted.');
    	}else{
    		return back()->with('error', 'Whoops, something went wrong? please try after sometime.');
    	}
    }

    protected function update($itemModel, $request){
    	// CRAETE AN MODEL OBJECT
    	$itemObject = $itemModel::find($request->id);
    	// GET REQUEST ATTRIBUTES
    	$data = $request->all();
    	// GET MODEL/TABLE FILLABLES
    	$fillables = $this->getFillables($itemObject);
    	// REMOVE ITEMS EXCEPT FILLABLES
    	foreach ($request->all() as $key => $value) {
    		if(in_array($key, $fillables))
    			// unset($data[$key]);
    			$itemObject->$key = $value;
    	}

    	// UPDATE ITEM
		if($itemObject->save()){
			return back()->with('success', 'Successfully updated.');
		}else{
			return back()->with('error', 'Whoops, something went wrong? please try after sometime.');
		}
    }

}
