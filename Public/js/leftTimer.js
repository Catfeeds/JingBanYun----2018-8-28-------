var leftTimer = function(baseTime,startTime,endTime,beforeCallback,ingCallback,afterCallback){
	this.timeDelta = new Date() - new Date(baseTime.replace(/\s/g,'T')); //ms
	this.startTime = startTime;
	this.endTime = endTime;
	this.beforeCallback =  beforeCallback;
	this.ingCallback =  ingCallback;
	this.afterCallback = afterCallback;
	this.lastState = -1;
	this.timerStart = function(){
		var currentDateTime = new Date();  
		var startDateTime = new Date(this.startTime.replace(/\s/g,'T'));
		var endDateTime = new Date(this.endTime.replace(/\s/g,'T'));
        var fixedCurrentDateTime = currentDateTime - this.timeDelta;
        if (fixedCurrentDateTime < startDateTime.getTime() && this.lastState !=0) {
		    if(typeof(this.beforeCallback) == "function")
             this.beforeCallback();
            this.lastState = 0;		
        }   
        else if(fixedCurrentDateTime <= endDateTime.getTime() && fixedCurrentDateTime >= startDateTime.getTime() && this.lastState !=1){
		   if(typeof(this.ingCallback) == "function")
            this.ingCallback();             
		   this.lastState = 1;
        }
		else if(fixedCurrentDateTime > endDateTime.getTime() && this.lastState !=2){
			
			if(typeof(this.afterCallback) == "function")
			 this.afterCallback();
		    this.lastState = 2;
		}
		setTimeout(this.timerStart.bind(this),1000);    			
		
	}
	return this;
	
}
