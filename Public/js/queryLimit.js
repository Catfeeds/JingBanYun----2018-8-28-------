var queryLimitCount = 3;
var interval = 10;
var currentCount = 0;
function canQuery()
{
	if(queryLimitCount == currentCount)
	{
		setTimeout("clearCount()",interval * 1000);
		return false;
	}
	else
	{
		currentCount ++;
		setTimeout("minusOne()",parseInt(interval*1000.0 / queryLimitCount));
		return true;
	}	  
}
function minusOne()
{
	if(currentCount!=0)
	currentCount--;
}
function clearCount()
{
	currentCount = 0;
}