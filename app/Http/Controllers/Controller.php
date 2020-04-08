<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
    * UPLOAD DOCUMENT/IMAGE
    *-------------------------------
    * Can Accept Request / String / Array via fileRequest
    * if @var type = b64, means it it an base64 encoded file
    * @return filename
    */
    protected function uploadDoc($fileRequest, $type = null){

    	// dd($fileRequest);

    	// EMPTY FILE
    	$file = [];
    	// UPLOAD PATH
    	$path = public_path(). '/uploads/';

    	switch ($type) {
    		
    		case 'b64':
    			// GET FILE, EXTENSION AND TYPE 
    			$file['ext'] = explode(';', (explode('/', $fileRequest))[1])[0];
    			$tempFile = explode(',', $fileRequest);
    			$file['type'] = $tempFile[0];
    			$file['file'] = $tempFile[1];
    			break;
    		
    		default:
    			return false;
    			break;
    	}

    	// CREATE A UNIQUE FILE NAME 
		$fileName = rand(1000,99999).'.'.$file['ext'];
		
		// CREATE UPLOAD DIRECTORY, IF NOT EXIST
		if(!\File::isDirectory($path)){
	        \File::makeDirectory($path, 0774, true, true);
	    }
	    // UPLOAD IT TO AN PUBLIC FOLDER
		$upload = \File::put($path . $fileName, base64_decode($file['file']));	

		return $fileName;
    }

}
