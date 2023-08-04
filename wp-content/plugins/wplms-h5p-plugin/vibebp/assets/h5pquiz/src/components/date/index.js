import React, {Component} from 'react';

export default class DateTimestamp extends Component {

	constructor(props){
		super(props);

		this.state={
			time:0,
			details:{
				hours:0,
				minutes:0,
				seconds:0,
				month:0,
				year:0,
				day:0
			}
		}
	}

	componentWillMount(){


		let time = ( parseInt(window.wplms_commissions.timestamp,10) - parseInt(this.props.date,10) );

		
		this.setState({time});

		let date = new Date(new Date().getTime() - time*1000);
		
		let details = {
			day:date.getDate(),
			month:(date.getMonth()+1),
			year:date.getYear()+1900,
			hour:date.getHours(),
			minutes:date.getMinutes()
		};
		this.setState({details});
	}

	render(){
		return (
			<div className="course">
				<span>{this.state.details.month}-{this.state.details.day}-{this.state.details.year} {this.state.details.hour}:{this.state.details.minutes}</span>
			</div>
		);
	}
}
