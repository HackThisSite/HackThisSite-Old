function getRequest(){
  try{return new XMLHttpRequest();}catch(e){}
  try{return new ActiveXObject("Msxml2.XMLHTTP.6.0");}catch(e){}
  try{return new ActiveXObject("Msxml2.XMLHTTP.3.0");}catch(e){}
  try{return new ActiveXObject("Msxml2.XMLHttp");}catch(e){}
  try{return new ActiveXObject("Microsoft.XMLHTTP");}catch(e){}
return null;}

function ajaxGet(url, method, fn, failFn){
  request=getRequest();
  request.open(method, url);
  request.setRequestHeader("Content-type","application/x-www-form-urlencoded;");
//request.setRequestHeader("Connection","close");
  request.onreadystatechange = 
  function()
  {if(request.readyState==4) 
  {if(request.status==200) fn(request); 
   else if(failFn!=null) failFn(request);}}
request.send(null);}
