const { Component,createElement, render, useState,useEffect,Fragment} = wp.element;
import CircularProgress from '../circularprogress';
const QuizTimer = (props) =>{
	const [time,setTime] = useState(props.duration);
	const [timer,setTimer] = useState({d:0,h:0,m:0,s:0});


	useEffect(()=>{
		if(props.start){
			let stimer = setTimeout(()=>{
				let ntime = time-1;
				if(ntime <= -1){
					props.update(props.quiz_id,'expired');
				}else{
					if(ntime >= 0){
						setTime(ntime);
						Timer();
					}
				}
			},1000);
		}else{
			setTime(props.duration);
			Timer();
		}
		
	},[time,props.start,props.duration]);	


	const Timer = () =>{
		let ntimer = {...timer};
		let duration = time;
		if( duration > 86400){
			ntimer.d = Math.floor(duration/86400);
			duration = duration - ntimer.d*86400;
		}else{
			ntimer.d =0;
		}

		if( duration > 3600){
			ntimer.h = Math.floor(duration/3600);
			duration = duration - ntimer.h*3600;
		}else{
			ntimer.h =0;
		}

		if( duration > 60){
			ntimer.m = Math.floor(duration/60);
			duration = duration - ntimer.m*60;
		}else{
			ntimer.m =0;
		}

		ntimer.s = duration;
		setTimer(ntimer);
	}


	let circle_per = 'c100 p0 big';
	let progress = 0;
	if(time > -1){
		progress = Math.floor(((((props.duration-time))/props.duration)*100));
		if(progress <= 0){
			progress = 1;
		}
	}

	return (
		<div className="quiztimer">
			<div className="circle_timer">
				{
                	progress?<CircularProgress size={window.innerWidth < 480?'xs':'sm'} progress={progress} />
                	:''
                }
                <span>
                	<span className="timer_amount">
						{
							timer.d?
								<Fragment><span>{timer.d}</span><span>:</span></Fragment>
							:''
						}
						{
							timer.h?
								<Fragment><span>{timer.h}</span><span>:</span></Fragment>
							:''
						}
						{
							
							timer.m?
								<Fragment><span>{timer.m}</span><span>:</span></Fragment>
							:''
							
						}
						<span>{timer.s}</span>
					</span>

					<span className="timer_unit">
						{
						timer.d?
							<Fragment><span>{window.wplms_course_data.translations.days}</span><span></span></Fragment>
							:''
						}
						{
							timer.h?
								<Fragment><span>{window.wplms_course_data.translations.hours}</span><span></span></Fragment>
							:''
						}
						{
							timer.m?
								<Fragment><span>{window.wplms_course_data.translations.minutes}</span><span></span></Fragment>
							:''
						}
						<span>{window.wplms_course_data.translations.seconds}</span>
					</span>
                	
                </span>
			</div>
		</div>
	)
}


export default QuizTimer;