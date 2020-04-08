<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontController extends \App\Http\Controllers\ProcessController
{
    public function index(){
    	// GET THE AVILABLE EXAMS
    	$data['exams'] = $this->availableExams(\Auth::user()->id);
    	return view('user.welcome')->with($data);
    }

    public function examInstruction($exam_id){
    	// GET THE SPECIFIC EXAMS INSTRUCTIONS
    	$exam = $this->availableExams(\Auth::user()->id);
    	$data['exam']['id'] = $exam_id;
    	$data['exam']['instructions'] = "Instruction Not Found";
    	return view('user.exam_instruction')->with($data);	
    }

    public function exam(Request $request){
    	// RETURN BACK, IF INSTRUCTION NOT BEEN READ
    	if(!$request->has('read')){
    		return back()->with('error', 'Please check to read the instructions.');
    	}

    	// VALIDATE THE EXAM
    	if(!$this->validateExamSession($request->exam_id)){
    		return back()->with('error', 'Your Examination Session is Expired.');
    	}

    	// GET THE QUESTION PALETTE
    	$data['qp'] = $this->questionPalette($request->exam_id);

    	// GET EXAM BASIC DETAILS
		$data['exam'] = $this->examDetails($request->exam_id);
    	
    	// GET THE REMAINING TIME
    	$data['time_left'] = $this->remainingTime();

    	\Session::put('exam', $this->exam);
    	\Session::put('examSession', $this->examSession);
    	\Session::put('questions', $this->questions);
    	\Session::put('question_palette', $this->question_palette);
    	// \Session::flush();
    	// dd(session()->all());

    	$data['qna'] = json_encode($this->questions);
    	// dd($data['qp']);
    	// dd($this->questions);

    	return view('user.exam')->with($data);
    }

    public function finishExam(){
    	$this->submitExam();
    	return view('user.exam_submitted');
    }

    public function examDesign(){
    	return view('user.exam_design');
    }

    // https://ration.jantasamvad.org/ration/app/FRSVMXYC/

}
