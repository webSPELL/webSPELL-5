function Ajax() {
	this.requestUrl			= '';
	this.requestParams		= '';
	this.requestMethod		= 'GET';
	this.requestAsync		= true;
	this.onRequestSuccess	= null;
	this.onRequestError		= function(msg) {window.alert(msg);};
}

Ajax.prototype.request = function()
{
	if(!this.requestUrl) {
		this.onRequestError('No request URL given.');
		return false;
	}
	
	if (!this.requestMethod) {
		this.onRequestError('No request method given.');
		return false;
	} 
	this.requestMethod = this.requestMethod.toUpperCase();
	
	var xmlHttpRequest;
	if (window.XMLHttpRequest) {
        xmlHttpRequest = XMLHttpRequest();
    }
	else if(window.ActiveXObject) {
		try { xmlHttpRequest = ActiveXObject("Msxml2.XMLHTTP");}
		catch(e) {
			try { xmlHttpRequest = ActiveXObject("Microsoft.XMLHTTP"); }
			catch (e) { xmlHttpRequest = false; }
		}
	}
	
	if(xmlHttpRequest) {
		var _this = this;
		
		function handler()	{
			if (xmlHttpRequest.readyState < 4) {
                return false;
            }
			
			if (xmlHttpRequest.status == 200 || xmlHttpRequest.status == 304) {
				if (_this.onRequestSuccess) {
                    _this.onRequestSuccess(xmlHttpRequest.responseText, xmlHttpRequest.responseXML);
                }
			} 
			else {
				if (_this.onRequestError) {
                    _this.onRequestError(xmlHttpRequest.status + " " + xmlHttpRequest.statusText + ": Error while transfering data.");
                }
			}
		}
		
		switch (this.requestMethod) {
			case "GET":	
				xmlHttpRequest.open(this.requestMethod, this.requestUrl + '?' + this.requestParams, this.requestAsync);
				xmlHttpRequest.onreadystatechange = handler;
				xmlHttpRequest.send(null);
			break;
			
			case "POST": 
				xmlHttpRequest.open(this.requestMethod, this.requestUrl, this.requestAsync);
				xmlHttpRequest.onreadystatechange = handler;
				xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				xmlHttpRequest.send(this.requestParams);
			break;
		}
	}
	else {
		this.onRequestError('XML http request object creation failed.');
		return false;
	}
};
