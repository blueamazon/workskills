const { createElement, useState, useEffect, Fragment, render } = wp.element;
//const { dispatch, select } = wp.data;


const CircularProgress = ({ progress, size }) => {

  	let appliedRadius;
  	let appliedStroke;

  	switch (size) {
    case 'xs':
      appliedRadius = 10;
      appliedStroke = 1;
      break;
    case 'sm':
      appliedRadius = 25;
      appliedStroke = 2.5;
      break;
    case 'med':
      appliedRadius = 50;
      appliedStroke = 5;
      break;
    case 'lg':
      appliedRadius = 75;
      appliedStroke = 7.5;
      break;
    case 'xl':
      appliedRadius = 100;
      appliedStroke = 10;
      break;
    default:
      appliedRadius = 50;
      appliedStroke = 5;
  }
  const normalizedRadius = appliedRadius - appliedStroke * 2;
  const circumference = normalizedRadius * 2 * Math.PI;
  const strokeDashoffset = 
    circumference - (progress / 100) * circumference;


return (
    <div className="react-progress-circle">
      	<svg height={appliedRadius * 2} width={appliedRadius * 2}>
	        <circle
	          className='ReactProgressCircle_circleBackground'
	          strokeWidth={appliedStroke}
	          style={{ strokeDashoffset }}
	          r={normalizedRadius}
	          cx={appliedRadius}
	          cy={appliedRadius}
	        />
	        <circle
	          className='ReactProgressCircle_circle'
	          strokeWidth={appliedStroke}
	          strokeDasharray={circumference + ' ' + circumference}
	          style={{ strokeDashoffset }}
	          r={normalizedRadius}
	          cx={appliedRadius}
	          cy={appliedRadius}
	      />
      	</svg>
    </div>
  );
};
export default CircularProgress;