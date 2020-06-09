var hall_ticket = null;

function prepareExamHall() {
	var metas = document.getElementsByTagName('meta');
	var i;
	for (i = 0; i < metas.length; i++) {
		if(metas[i].name == '_token'){
			hall_ticket = metas[i].content;
		}
	}
}

// INITIALIZE EXAM SESSION
var tickCount = setInterval(examSession, 1000);

// LOAD THE QUESTION
loadQuestions();

function examSession(){
	// CALL THE EXAMINER
	examiner.postMessage(hall_ticket);
}

var QnAs;
function loadQuestions(){	
	// GET/SET ALL QUESTIONS
	QnAs = JSON.parse(document.getElementById('QnA').value);
	console.log('loading all questions..');
	console.log(QnAs);

	// LOAD INITIAL QUESTION
	loadQnA();
}

function loadQuestion(srno, QnA, subject){
	console.log('loading selected question..');
	console.log(QnA);
	
	// BIND QUESTION SERIAL NO.
	document.getElementById('srNo').innerHTML = srno;

	// BIND QUESTION
	document.getElementById('QnA_Q').innerHTML = isItImage(QnA.title);

	// RESET ANSWERS
	document.getElementById('QnA_A').innerHTML = "";

	// BIND QUESTION SUBJECT
	document.getElementById('QnA_S').innerHTML = subject;

	// GET / SET POSTED ANSWER
	var postedAnswer = null;
	var isItPostedAnswer = null;
	if(Array.isArray(QnA['answer']) && QnA['answer'].length){
		postedAnswer = QnA['answer'][0]['answer'];
	}
	// console.log("loading posted answer");
	// console.log(postedAnswer);
	// console.log("loading right answer");
	// console.log(QnA['right_answer']);

	// BIND ANSWERS
	$.each(JSON.parse(QnA.available_answers), function(key, value) {

		// SET CHECKED IF IT IS POSTED ANSWER
		isItPostedAnswer = postedAnswer == key ? 'checked' : '';

		option = isItImage(value);
		optionHTML = "<div class='col-md-6 option'><div class='custom-control custom-radio'><input onclick='ClearRd(this)' type='radio' class='custom-control-input' id='option"+key+"' name='option' value='"+key+"' "+isItPostedAnswer+"><label class='custom-control-label' for='option"+key+"'>"+option+"</label></div></div>";

		$('#QnA_A').append(optionHTML);
	});
}

function isItImage(str){
	if( (str.includes('.png')) || (str.includes('.jpg')) || (str.includes('.jpeg')) || (str.includes('.gif')) )
		return "<img src='"+location.origin+"/uploads/"+str+"'>";
	return str;
}

function loadQnA(srno = 0){
	var isLoaded = false;

	for (var i = 0; i < QnAs.length; i++) {

		if(isLoaded){
			break;
		}
  	
  		$.each(QnAs[i], function(id, v) {

  			if(loadWithCondition(i, id, v, srno))
  				isLoaded = true;

		});

	}
}

function loadWithCondition(i, id, v, srno){
	// LOAD INITIAL QUESTION
	if(srno == 0){
		// if(v['qna']['qpState'] == ""){

			// BIND TAGS
			bindQuestionTags({srno: i, id: id});

			// LOAD QUESTION
			loadQuestion(srno+1, v['qna'], v['subject_name']);
			return true;
		// }
	}
	// LOAD SELECTED QUESTION
	else{
		if(i == srno){
			// BIND TAGS
			bindQuestionTags({srno: i, id: id});

			// LOAD QUESTION
			loadQuestion(srno+1, v['qna'], v['subject_name']);
			return true;
		}
	}

	return false;
}

function bindQuestionTags(dataArr){
	// BIND QUESTION NO.
	document.getElementById('QnA_N').value = dataArr['srno'];

	// BIND QUESTION ID.
	document.getElementById('QnA_ID').value = dataArr['id'];
}

var OMRsheet = null;
var answerSheet = {};
// COLLECT THE ANSWER
function collectAnswer(){
	OMRsheet = document.getElementById("OMRsheet");
	// console.log("selected option is: "+OMRsheet.elements["option"].value);
	answerSheet = {
		serial_number: document.getElementById('QnA_N').value,
		question_id: document.getElementById('QnA_ID').value,
		answer: OMRsheet.elements["option"].value
	}
}

function loadNextPrev(srno){

	// alert(QnAs.length);
	if(QnAs.length == srno)
		finishExam('Do you want to submit the exam?');

	// COLLECT THE ANSWER
	collectAnswer();

	// LOAD THE NEXT/PREV QUESTION
	loadQnA(srno);	


	// CALL VOLUNTEER TO POST THE ANSWER AND GET / SET THE UPDATED QNAs
	// volunteer.postMessage({url: 'postAnswer', method: 'post', data: answerSheet, token: hall_ticket});
	// QnAs = volunteer_data.QnAs;
	volunteer('postAnswer', 'post', answerSheet, hall_ticket);
	// QnAs = volunteer_data.QnAs;
}

function finishExam(msg){
	var confirmation = confirm(msg);
	if (confirmation == true) {
 		document.getElementById("OMRsheet").submit();
	}
}

function updateQP(qid, ans, is_marked){
	var clas = !(ans.length) ? 'not_answered' : 'answered';
	clas = is_marked ? marked : clas;
	$('#qp_'+qid).removeClass();
	$('#qp_'+qid).addClass(clas);
}

var checkedRadio;
function ClearRd(thiss){
	if (checkedRadio == thiss){
	    thiss.checked = false;
	    checkedRadio = null;
	}
	else{
	    checkedRadio = thiss; 
	}
}

// ++++++++++++++++++++++++++++++++++++++++++++++++

// DEFINE WEB WORKER
var examiner = new Worker('js/examiner.js');

// EXAMINER TAKING THE EXAM
examiner.addEventListener('message', function(e) {
	console.log('Examiner Watching...');
	if(e.data !== undefined)
		document.getElementById("time_left").innerHTML = e.data.time_left;
}, false);


var volunteer_data = null;
function volunteer(url, method, data, token){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			console.log('volunteer working...');
			volunteer_data = JSON.parse(this.responseText);
			QnAs = volunteer_data.QnAs;
			updateQP(data.question_id, volunteer_data.ans, volunteer_data.marked);
		}
	};
	xhttp.open(method, "/"+url, true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.setRequestHeader('X-CSRF-TOKEN', token);
	xhttp.send(JSON.stringify(data));
}

// VOLUNTEER AVAILABLE FOR EXAMINER CALL
volunteer.onmessage = function(e) {
	console.log('volunteer working...');
	// console.log(e.data);
	volunteer_data = e.data;
	test(volunteer_data);
};

function test(volunteer_data){
	console.log(volunteer_data)
	console.log('volunteer_data end')
}