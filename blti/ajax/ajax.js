var http_request = false;
var el_id;

function doApprove(id, url, ci, cid) {
  getHTTPRequest()
  if (http_request) {
    var el = document.getElementById('button' + id);
    url += '?do=' + el.value + '&ci=' + escape(ci) + '&cid=' + escape(cid);
		el_id = id;
		http_request.onreadystatechange = alertApprove;
		http_request.open('GET', url, true);
		http_request.send(null);
  }
	return false;
}

function alertApprove() {
  if (http_request.readyState == 4) {
    if (http_request.status == 200) {
      var button = document.getElementById('button' + el_id);
      var label = document.getElementById('label' + el_id);
			if (button.value == 'Approve') {
				button.value='Suspend';
				label.innerHTML = 'Yes';
			} else {
				button.value='Approve';
				label.innerHTML = 'No';
			}
    }
  }
}

function doGenerateKey(url) {
  getHTTPRequest()
  if (http_request) {
    //var url = '/elgg/mod/blti/lib/generate.php';
    var cg = document.getElementsByName('consumer_guid');
        url += '?consumer_guid=' + cg[0].value;
    var cid = document.getElementsByName('context_id');
        url += '&context_id=' + cid[0].value;
    var em = document.getElementsByName('email');
        url += '&email=' + em[0].value;
    var fem = document.getElementsByName('fromemail');
        url += '&fromemail=' + fem[0].value;    
    var el = document.getElementsByName('life');
    	url += '&life=' + el[0].value;
    var ao = document.getElementsByName('auto_approve[]');
    if (ao[0].checked > 0) {
            url += '&auto_approve=true';
        }
        http_request.onreadystatechange = alertGenerateKey;
        http_request.open('GET', url, true);
        http_request.send(null);
    }
    return false;
}

function alertGenerateKey() {
  if (http_request.readyState == 4) {
    if (http_request.status == 200) {
            var key = http_request.responseText;
            document.getElementById("myDiv").innerHTML=key;
            return;
            if (key.length > 0) {
        window.prompt('Send this share key string to the other instructor:', 'share_key=' + key);
            } else {
                alert('Sorry an error occurred in generating a new share key; please try again');
            }
        } else {
            alert('Sorry unable to generate a new share key');
    }
  }
}


function getHTTPRequest() {
	http_request = false;
	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
}
