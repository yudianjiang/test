    var Ajax = {
        get:function(url, queryStr, callback, type) {
            var request = new XMLHttpRequest();
            if(queryStr) {
                // 判断url 中是否含有问号
                if(url.indexOf('?')>-1) {
                    url += '&'+queryStr;
                } else {
					// 判断参数中第一个字符位置是否为?
					if(queryStr.indexOf('?')==0){
						url +=queryStr;
					}else{
						url += '?'+queryStr;
					}                    
                }
                
            }
            request.open('get', url, true);
            request.onreadystatechange = function() {
                if(request.readyState==4 && request.status==200) {
                    if(type && type.toLowerCase()=='xml') {
						var responseData = request.responseXML;
					} else {
						var responseData = request.responseText;
                    }
					
                    if(type && type.toLowerCase()=='json') {
                        responseData = eval('('+responseData+')');
                    }
                    callback(responseData);
                }
            }
            request.send(null);
        },
        post:function(url, queryStr, callback, type) {
            var request = new XMLHttpRequest();
            request.open('post', url, true);
            request.onreadystatechange = function() {
                if(request.readyState==4 && request.status==200) {
					if(type && type.toLowerCase()=='xml') {
						var responseData = request.responseXML;
					} else {
						var responseData = request.responseText;
                    }
					if(type && type.toLowerCase()=='json') {
                        responseData = eval('('+responseData+')');
                    }
                    callback(responseData);
                }
            }
            request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            request.send(queryStr);
        },
    };