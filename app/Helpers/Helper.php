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
	
	/**
	* CHECK FOR DOC/IMAGE
	* @return image with full path
	* or @return string as it is
	*/
	public static function textOrImage($string){
		$isImage = false;
		$DocImgformats = ['jpg','jpeg','png','gif'];
		foreach ($DocImgformats as $format) {
			if(strpos($string, $format)){
				$isImage = true;
			}
		}

		if($isImage){
			$fileWithPath = asset('uploads').'/'.$string;
			// return $fileWithPath;
			// RETURN HTML ELEMENT, IF FILE EXIST
			if(\File::exists(public_path().'/uploads/'.$string)){
		        return "<img src='$fileWithPath'>";
		    }

		    // RETURN WARNING WITH MESSAGE
		    return "<span class='badge badge-danger'><i class='fa fa-ban'></i> File Not Found! Please Update The Question</span>";
		}

		return $string;
	}

}