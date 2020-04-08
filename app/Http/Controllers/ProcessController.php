<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProcessController extends Controller
{

	protected $exam = null;
	protected $examSession = null;
	protected $questions = null;
	protected $question_palette = null;

    
	public function makeExamLive(Request $request){
		// return response()->json(['status' => true, 'data'=> $request->data], 200);

		// ITERATE TO CHECK QUESTION STOCK FOR EVERY PARTICULAR SUBJECT
		// CHECK FOR STOCK
		// return $request->data;
		foreach ($request->data['subjects'] as $subjectID => $questionNeed) {
			$stock = $this->checkQuestionStock($subjectID, $questionNeed);
			if($stock !== true){
				return response()->json(['status' => false, 'message' => $stock], 200);
			}
		}

		// UPDATE EXAM
		if($this->updateExam($request->data)){
			return response()->json(['status' => true, 'message' => 'Exam is Live'], 200);
		}
		else{
			return response()->json(['status' => false, 'message' => 'Whoops, Something went wrong? Please try after sometime!'], 200);
		}
	}

	public function checkQuestionStock($subjectID, $stockNeed){
		// GET ALL QUESTION
		$availableQuestions = \App\Question::where('subject_id', $subjectID)->get();
		// GET AVAILABLE STOCK
		$availableStock = count($availableQuestions);
		if($availableStock < $stockNeed){
			// GET SUBJECT NAME USING MODEL RELATION
			$subjectName = $availableQuestions[0]->subject->title;
			// LESS STOCK ACCORDING TO NEED IN PARTICULAR EXAM
			$lessStock = $stockNeed - $availableStock;
			// LESS STOCK INFORMATION
			return 'Exam has less questions in subject('.$subjectName.'). Please add atleast '.$lessStock.' more question in this subject.';
		}
		else{
			return true;
		}
	}


	public function updateExam($data){
		return \App\Exam::where('id', $data['exam_id'])->update([
				'exam_date' => $data['exam_date'],
				'subjects' => $data['subjects'],
				'status' => 1
			]);
	}

	/**
	* GET THE ALL AVAILABLE OR SPECIFIC EXAM
	* EXAM ACCORDING TO PARTICULAR USER
	* FEATURE NOT AVAILABLE BASED ON UID*
	*/
	public function availableExams($UID, $exam_id = null){
		if(empty($exam_id)){
			return \App\Exam::whereStatus(true)->get();
		}
		return \App\Exam::whereStatus(true)->whereId($exam_id)->get();
	}


	/**
	* GET THE QUESTION PALETTE
	* ACCORDING TO PARTICULAR EXAM
	* SERIALIZED BY SUBJECTS, QUANTITY, ANSWERED, NOT_ANSWERED, MARKED, UN_ATTEMPTED
	* RANDOMLY QUESTION SELECTION IS NOT AVAILABLE* 
	*/
	public function questionPalette($exam_id){
		// if(empty($this->exam)){
		// 	$exam = \App\Exam::whereId($exam_id)->get();
		// 	$this->exam = $exam[0];
		// }

		// CREATE BLANK SUBJECT NAMES
		$subjectNames = [];

		// GET SUBJECTS AND QUESTION ACCOEDINGLY
		$subjects = json_decode($this->exam->subjects);
		foreach ($subjects as $subjectId => $questionQuantity) {
			
			// GET SUBJECT WITH QUESTIONS, QUESTIONS ALONG WITH POSTED ANSWER
			$temp = \App\Question::whereSubject_id($subjectId)->with(['answer' => function($query){
			    $query->where('user_id', \Auth::user()->id)->where('exam_id', $this->exam->id);
			}])->limit($questionQuantity)->get();

			$subjectNames[] = $temp[0]->subject->title;	

			// GET EXISTING QUESTION PALETTE STATE
			foreach ($temp as $key => $question) {
				// SET QUESTION PALETTE STATES
				$temp[$key]['qpState'] = $this->isQuestionAttempted($temp[$key]['answer']);

				// SET QUESTION TO SUBJECTS ACCORDINGLY FOR QP
				$data['subjects'][$temp[0]->subject->title] = $temp;

				// BIND SELECTED SUBJECTS
				$this->questions[] = [ $temp[$key]->id => ['qna' => $question, 'subject_name' => $temp[0]->subject->title] ];
				// $this->questions[][$temp[$key]->id]['subject_name'] = $temp[0]->subject->title;
			}
			
		}

		// SET/OVERWRITE SUBJECTS JSON BY ARRAY OF SUBJECT NAMES
		$this->exam->subjects = [];
		$this->exam->subjects = array_merge((array)$this->exam->subjects, $subjectNames);

		// dd($this->questions);
		// dd($data);

		$this->question_palette = $data;
		return $data;
	}

	private function isQuestionAttempted($answerArr){
		if($answerArr->isEmpty()){
			// return ['Unattempted' => true];
			return "";
		}
		else{
			$result['answer'] = $answerArr[0]['answer'];
			$result['marked_for_review'] = $answerArr[0]['marked_for_review'];

			if(is_null($result['answer'])){
				if( (is_null($result['marked_for_review'])) || (!$result['marked_for_review']) ) {
					// return ['not_answered' => true, 'answer'=>false, 'marked' => false, 'Unattempted' => false];
					return 'not_answered';
				}else{
					// return ['not_answered' => true, 'answer'=>false, 'marked' => true, 'Unattempted' => false];
					return "marked";
				}
			}
			else{
				if( (is_null($result['marked_for_review'])) || (!$result['marked_for_review']) ) {
					// return ['not_answered' => false, 'answer'=>true, 'marked' => false, 'Unattempted' => false];
					return "answered";
				}else{
					// return ['not_answered' => false, 'answer'=>true, 'marked' => true, 'Unattempted' => false];
					return "marked";
				}
			}
		}
	}

	/**
	* VALIDATE EXAM SESSION
	* @return boolean
	*/
	public function validateExamSession($exam_id){
		$this->getSetExamSession($exam_id);
		// CHECK FOR VALID EXAM SESSION
		if( ($this->examSession->remaining_time > 0) && ($this->examSession->status) ){
			return true;
		}
		return false;
	}

	/**
	* SET THE EXAM AND USERs EXAM SESSION 
	*/
	private function getSetExamSession($exam_id){

		// SET EXAMNIATION
		if(empty($this->exam)){
			$exam = \App\Exam::whereId($exam_id)->get();
			$this->exam = $exam[0];
		}

		// CHECK FOR USERs EXAM SESSION
		if(empty($this->examSession)){

			// GET THE USERs EXAM SESSION, IF EXIST
			$where = [ 'exam_id' => $this->exam->id, 'user_id' => \Auth::user()->id ];
			$obj = \App\ExamSession::where($where)->first();

			// USERs EXAM SESSION NOT EXIST
			if(!$obj){
				// CREATE A FRESH USERs EXAM SESSION
				$this->examSession = \App\ExamSession::create(['exam_id' => $this->exam->id, 'user_id' => \Auth::user()->id, 'remaining_time' => $this->exam->exam_duration]);
			}
			// USERs EXAM SESSION EXIST
			else{
				$this->examSession = $obj;
			}
		}

		return $this->examSession;
	}

	/**
	* @param boolean status
	* @return string "Remaining Exam Time" as HH:MM:SS
	*/
	private function updateExamSession($status = true){
		$status = ($this->examSession->remaining_time == 1) ? false : $status;

		$session = \App\ExamSession::find($this->examSession->id);
		$session->remaining_time--;
		$session->status = $status;
		$session->save();

		$this->examSession = $session;
		return $this->readableTime($session->remaining_time);
	}

	public function examSession(){
		$this->setMemberVariables();
		
		// return response()->json(['status' => true, 'message' => 'Exam is Live'], 200);
		return response()->json(['status' => true, 'time_left' => $this->updateExamSession(), 'exam' => $this->exam], 200);
	}

	private function setMemberVariables(){
		// SET SESSION INTO MEMBER VARIABLES
		$this->exam = is_null($this->exam) ? \Session::get('exam') : $this->exam;
		$this->examSession = is_null($this->examSession) ? \Session::get('examSession') : $this->examSession;
		$this->questions = is_null($this->questions) ? \Session::get('questions') : $this->questions;
		$this->question_palette = is_null($this->question_palette) ? \Session::get('question_palette') : $this->question_palette;
	}

	public function remainingTime(){
		return $this->readableTime($this->examSession->remaining_time);
	}

	/**
	* @param integer seconds
	* @return HH:MM:SS
	*/
	private function readableTime($seconds){
		// USE str_pad() TO PLACE 0 BEFORE AN VALUE, IF VALUE AS IN SINGLE DIGIT
		$hours = str_pad(floor($seconds / 3600), 2, 0, STR_PAD_LEFT);
		$minutes = str_pad(floor(($seconds / 60) % 60), 2, 0, STR_PAD_LEFT);
		$seconds = str_pad($seconds % 60, 2, 0, STR_PAD_LEFT);
		return("$hours:$minutes:$seconds");
	}

	public function examDetails(){
		$data['name'] = $this->exam->title;
		$data['date'] = $this->exam->exam_date;
		$data['marks'] = $this->exam->marks;
		$data['subjects'] = $this->exam->subjects;
		$data['duration'] = floor($this->exam->exam_duration / 3600);
		return (object)$data;
	}

	public function postAnswer(Request $request){
		// SET THE MEMBER DATA TO ACCESS IT
		$this->setMemberVariables();

		// CREATE OR UPDATE THE ANSWER
		$result = $this->updateAnswer($request->question_id, $request->answer);
		// return $result;
		
		if($result){

			// UPDATE QUESTIONS OBJECT
			// return response()->json(['status' => true, 'ans' => $this->questions[$request->serial_number][$request->question_id]['qna']['answer']], 200);
			
			// GET UPDATE ANSWER & BIND ACCORDINGLY
			$ansArr = $this->questions[$request->serial_number][$request->question_id]['qna']['answer'];
			
			$answer = \App\Answer::where('user_id', \Auth::user()->id)->where('question_id', $request->question_id)->where('exam_id', $this->exam->id)->get();
				
			// UPDATE QUESTIONS
			$this->questions[$request->serial_number][$request->question_id]['qna']['answer'][0] = $answer[0];

			// return response()->json(['status' => false, 'answer' => $answer[0]], 200);

			// UPDATE QUESTION PALETTE
			$subject = $this->questions[$request->serial_number][$request->question_id]['subject_name'];
			
			// return response()->json(['status' => true, $subject => $this->questions[$request->serial_number][$request->question_id]], 200);

			// foreach ($this->question_palette[$subject] as $key => $question) {
			// 	if($question['id'] == $request->question_id){
			// 		$this->question_palette[$subject][$key]['answer'][0] = $answer[0];
			// 	}
			// }

			// return response()->json(['status' => true, 'qp' => $this->question_palette, 'QnAs' => $this->questions], 200);
			$temp = $this->questions[$request->serial_number][$request->question_id]['qna']['answer'][0];
			return response()->json(['status' => true, 'QnAs' => $this->questions, 'ans' => $temp['answer'], 'marked' => $temp['marked_for_review']], 200);

		}
		return response()->json(['status' => false], 200);
	}

	private function updateAnswer($qid, $answer){
		// CHECK IF ANSWER EXIST
		$exist = \App\Answer::where('user_id', \Auth::user()->id)->where('question_id', $qid)->where('exam_id', $this->exam->id)->count();

		// UPDATE ANSWER, IF EXIST
		if($exist > 0){
			return \App\Answer::where('user_id', \Auth::user()->id)->where('question_id', $qid)->where('exam_id', $this->exam->id)->update(['answer' => $answer]);
		}

		// CREATE ANSWER
		return \App\Answer::create(['exam_id' => $this->exam->id, 'user_id' => \Auth::user()->id, 'question_id' => $qid, 'answer' => $answer]);
	}

	protected function submitExam(){
		$this->setMemberVariables();
		return \App\ExamSession::whereExam_id($this->exam->id)->whereUser_id(\Auth::user()->id)->update(['status' => false]);
	}

}
