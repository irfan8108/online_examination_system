<?php
namespace App\helpers;

// use Illuminate\Http\Request;

class Helper{

	/**
	 * USED TO RETUN A VIEW ACCORDINGLY
	 * from "inc" directory inside "view"
	 *-----------------------------------
     * @var file_name as file and cmd array as cmd
     * @var cmd looking for cmd_type and cmd_id
     * @var cmd['type'] used to load dynamic data
     * @var cmd['id'] also used to load dynamic data accordingly
     * @return View
     */
	public static function Load($file, $cmd = null){
		if( (!empty($cmd)) && (in_array('type', $cmd)) ){
			switch ($cmd['type']) {
				case 'value':
					$data = [];
					break;
				
				default:
					dd('view type is not defined, Hint: class NeK{ Load() }');
					break;
			}
		}
		else{
			$data = [];
		}
		return view('inc.'.$file)->with($data);
	}

	public static function test(){
		return "working..";
	}

}