const { createElement, useState, useEffect, Fragment, render,useRef} = wp.element;
const { dispatch, select } = wp.data;
import QuizTimer from './components/timer';
import Loading from './components/loading';
import '../_sass/main.scss'; 
const H5pQuiz = (props) => {


	const [isLoading,setLoading] = useState(true);


	const [quiz,setQuiz] = useState([]);
	
	const iframeref = useRef(null);

	let user = select( 'vibebp' ).getUser();
	let token = user['token'] = select('vibebp').getToken();


	useEffect(()=>{
		loadquiz();
		
	},[props.quiz_id]);

	useEffect(()=>{
		if(quiz.start){
			setTimeout(()=>{window.dispatchEvent(new Event('resize'));},100);
		}
		
	},[quiz.start]);

	const loadquiz = () => {
		setLoading(true);
		fetch(window.wplms_h5p_data.api.url+'/user/quiz?force' , {
		    method: 'POST',
		    headers: {
		      	'Accept': 'application/json',
      			'Content-Type': 'application/json'
		    },
		    body: JSON.stringify(
		    	{
		    		'quiz_id':props.quiz_id,
	            	
	            	'token':user.token
	            }
            )
		}).then( res => res.json())
      	.then((data) => {
      		if(data.status){
      			setQuiz(data.data);
      		}
      		setLoading(false);
      	});
	}

	const startQuiz =() =>{
		setLoading(true);
		
		let nquiz = {...quiz}; 
		nquiz.start = true;
		if(quiz.remaining && quiz.remaining > 0){
      		setLoading(false);
          	setQuiz(nquiz);
      	}else{
      		fetch(window.wplms_course_data.api_url+'/user/quiz/start?post' , {
			    method: 'POST',
			    body: JSON.stringify(
			    	{
			    		'quiz_id':nquiz.id,
			    		'token':token
		            }
	            )
			}).then( res => res.json())
	      	.then((data) => {
	      		
				
	      		setLoading(false);
	          	setQuiz(nquiz);

	      	});
      	}
	}

	const retakeQuiz = () => {
		setLoading(true);
		fetch(window.wplms_course_data.api_url+'/user/coursestatus/retake_single_quiz/'+props.quiz_id+'?post', {
		    method:'post',
		    body:JSON.stringify({
		    	'token':token
		    }),
		}).then( res => res.json())
      	.then(
          	(data) => {
          	if(data){
          		if(data.status){
          			loadquiz();
          			if(props.hasOwnProperty('update')){
          				props.update('retake_quiz');
          			}

          			var details = {"coursestatus":props.coursestatus,"action":'retake_quiz',"id":props.quiz_id,"course_id":props.course_id};
				
					var postevent1 = new CustomEvent('custom_quiz_action', { "detail":details});
			        document.dispatchEvent(postevent1);



          		}else{
          			if(data.message){
          				alert(data.message);
          				setLoading(false);
          			}
          		}
          		
          	}
        });

	}

	const submitQuiz = (scored_marks,total_marks) => {
		let nquiz = {...quiz};

		fetch(window.wplms_h5p_data.api.url+'/user/submitresult?post' , {
		    method: 'POST',
		    headers: {
		      	'Accept': 'application/json',
      			'Content-Type': 'application/json'
		    },
		    body: JSON.stringify(
		    	{
		    		'quiz_id':props.quiz_id,
	            	'course_id':props.course_id,
	            	'scored_marks':scored_marks,
	            	'total_marks':total_marks,
	            	'token':user.token
	            }
            )
		}).then( res => res.json())
      	.then((data) => {
          	if(data){
          		nquiz.submitted = true;
				nquiz.start = false;
				if(data.check_results_url){
					nquiz['check_results_url'] = data.check_results_url;
				}
				if(data.completion_message){
					nquiz['meta']['completion_message'] = data.completion_message;
				}
				if(data.retake_html){
					nquiz['retake_html'] = data.retake_html;
				}
				if(data.hasOwnProperty('max')){
					nquiz['meta']['max'] = data.max;
				}
				if(data.hasOwnProperty('marks')){
					nquiz['meta']['marks'] = data.marks;
				}
				setQuiz(nquiz);
				var event = document.createEvent("Event");
				event.initEvent("unit_traverse", false, true);
				if(window.wplms_h5p_data.hasOwnProperty('quiz_passing_score') && nquiz.quiz_passing_score){
					if(data.hasOwnProperty('continue') && data.continue){
						if(props.hasOwnProperty('update')){
							props.update('quizsubmitted');
						}
						
					}
				}else{
					if(props.hasOwnProperty('update')){
						props.update('quizsubmitted');
					}
				}

				var details = {"coursestatus":props.coursestatus,"action":'quizsubmitted',"id":props.quiz_id,"course_id":props.course_id};
				
				var postevent1 = new CustomEvent('custom_quiz_action', { "detail":details});
		        document.dispatchEvent(postevent1);


				if(document.querySelector('.unit_content') && document.querySelector('.unit_content')){
					
					document.querySelector('.unit_content').dispatchEvent(event);	

					var postevent = new CustomEvent('react_quiz_submitted', { "detail":{next_unit:data.next_unit}});
                	document.dispatchEvent(postevent);
				}
				
          	}

      	});
	}

	function listenXapi(event){
		if ((event.getVerb() === 'completed'  || event.getVerb() === 'answered' ) && !event.getVerifiedStatementValue(['context', 'contextActivities', 'parent'])) {
          var score = event.getScore();
          var maxScore = event.getMaxScore();
          if(maxScore){
          	submitQuiz(score,maxScore);
          }

        }
	}

	const setQuizExpire = (quiz_id,action) => {
		if(action == 'expired'){
			submitQuiz(0,0);
			//trigger submit quiz and end quiz send api call
		}
		
	}

	const addevent  = (event) =>{
		if(iframeref.current && iframeref.current.getAttribute('src').length) {
	      	//iframeref.current.contentWindow.H5P.externalDispatcher.off("xAPI",listenXapi);
	      	iframeref.current.contentWindow.H5P.externalDispatcher.on("xAPI",listenXapi);
	    }
	}


	let quiz_duration = 0;
	if(quiz.meta && quiz.meta.duration){
		quiz_duration = quiz.meta.duration;
	}
	if(quiz && quiz.remaining && quiz.remaining > 0){
		quiz_duration = quiz.remaining;
	}
	return (
		(isLoading)?
		<Loading />
		:
		<div className="h5pQuiz">
			<div className="quiz_header">
				{/*<div className="quiztimer_wrapper">
					{
				  		(quiz && quiz.meta && quiz.meta.duration && parseInt(quiz.meta.duration) < 863913600)?
			  			<QuizTimer 
			  				duration={quiz_duration} 
			  				update={setQuizExpire} 
			  				quiz_id={quiz.id} 
			  				start={quiz.start}
		  				/>
			  			:<strong>{window.wplms_course_data.translations.no_time_limit}</strong>
			  		}
		  		</div>*/}
		  		{
		  			(!isLoading && !quiz.start && !quiz.submitted)?
			  				
	  					(quiz.remaining && quiz.remaining > 0)?
		  					<a className="continue_quiz button is-primary" onClick={startQuiz}>
		  						{window.wplms_course_data.translations.continue}
		  					</a>
	  					:
		  					<a className="start_quiz button full is-primary" onClick={startQuiz}>
		  						{window.wplms_course_data.translations.start}
		  					</a>
		  			:''
		  		}
		  		{
		  			(!quiz.start && quiz.submitted && quiz.meta &&  quiz.meta.retakes > 0)?
		  				<div className="quiz_retake" onClick={()=>retakeQuiz()}>
			  				<a className=" button is-primary " >
				  				{window.wplms_course_data.translations.retake}
							</a>
							<strong>
								{window.wplms_course_data.translations.retakes_left} : {quiz.meta.retakes} 
							</strong>
		  				</div>
				  		
		  			:''
		  		}
		  		{
		  			(quiz.submitted && quiz.meta.hasOwnProperty('marks'))?
		  			<div className="results">
		  				 <span><i class="vicon vicon-medall"></i><strong>{quiz.meta.marks}</strong> / {quiz.meta.max}</span>
		  			</div>
		  			:''
		  		}
		  		
	  		</div>
	  		<div className="quiz_content">
	  		{
	  			(quiz.submitted && quiz.meta.hasOwnProperty('completion_message'))?
	  			<div dangerouslySetInnerHTML={{__html:quiz.meta.completion_message}}></div>
	  			:''
	  		}

	  		{
				(quiz.hasOwnProperty('content') && !quiz.submitted)?
		  		<div className="quiz_content" dangerouslySetInnerHTML={{ __html: (quiz && quiz.content)?quiz.content:'' }}></div>
		  		:''
		  	}
	  		{
	  			(quiz.start || (quiz.submitted && window.wplms_h5p_data.show_always))?
	  			<div className="new_quiz_h5p_wrapper wplms_iframe_wrapper">
					<iframe  onLoad={addevent} width="100%" height="100%" style={{'width':'100%','min-height':'500px'}} className="h5p_quiz" ref={iframeref} src={window.wplms_h5p_data.ajax_url+'?action=h5p_embed&id='+props.content_id}>
						
					</iframe>
				</div>
	  			:''
	  		}

	  		
			</div>
		</div>
	)
	
}

document.addEventListener("custom_quiz_type",(e)=>{
	if(e.detail.type === 'h5p_quiz'){
		setTimeout(()=>{
			render( <H5pQuiz quiz_id={e.detail.id} course_id={e.detail.course_id} content_id={e.detail.content_id} coursestatus={e.detail.coursestatus}/> ,
			   document.querySelector("#h5p_quiz")
			);
		},200);
	}
},false);

