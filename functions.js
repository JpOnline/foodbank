function showResult(str)
{
    var xmlhttp;
    if(str.length == 0) {
        if(document.getElementById("allclients")) {
            document.getElementById("allclients").style.display = '';
        }
        hideResult();
        return;
    } else if(str == 'fooditem' && document.getElementById('name').selectedIndex == 0 && document.getElementById('location').selectedIndex == 0) {
        hideResult();
        return;
    }
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            document.getElementById("txtResult").innerHTML = data;
            if(document.getElementById("allclients")) {
                document.getElementById("allclients").style.display = 'none';
            }
        }
    };
    
    if(document.getElementById('ajaxid').value == 'client') {
		var sel = document.getElementById('searchtype');
        var type = sel.options[sel.selectedIndex].value;
        
    	xmlhttp.open("GET","getclient.php?t="+type+"&q="+str,true);
    } else if(document.getElementById('ajaxid').value == 'fooditem') {
    	var sel = document.getElementById('name');
    	var cat = sel.options[sel.selectedIndex].value;
    	
        var sel = document.getElementById('location');
    	var loc = sel.options[sel.selectedIndex].value;
        
        xmlhttp.open("GET","getfooditem.php?cat="+cat+"&loc="+loc,true);
    }
    xmlhttp.send();
}
function hideResult() {
    document.getElementById("txtResult").innerHTML="";
    if(document.getElementById('ajaxid').value == 'client') {
        document.getElementById("clientinfo").value = "";
    }
    if(document.getElementById("allclients")) {
        document.getElementById("allclients").style.display = '';
    }
}
function getfoodparcelitems() {
    var xmlhttp;
    var sel = document.getElementById('foodparceltype');
    var id = sel.options[sel.selectedIndex].value;
    
    if(id.length == 0) {
        document.getElementById("foodparcelitems").innerHTML = '';
        document.getElementById("referencenumber").value = '';
        document.getElementById("tagcolour").value = '';
        document.getElementById("foodparcelitemsprint").style.display = 'none';
        return;
    } else {
        document.getElementById('submit').disabled = true;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText.split("[BRK]");
            
            document.getElementById("foodparcelitems").innerHTML = data[0];
            document.getElementById("referencenumber").value = data[1];
            document.getElementById("tagcolour").value = data[2];
            document.getElementById("foodparcelitemsprint").style.display = '';
            document.getElementById('submit').disabled = false;
        }
    };
    
    xmlhttp.open("GET","getfpitems.php?id="+id,true);
    xmlhttp.send();
}
function getplaces() {
    var xmlhttp;
    var sel = document.getElementById('placestype');
    var type = sel.options[sel.selectedIndex].value;
    
    if(type.length == 0) {
        document.getElementById("getplaces").innerHTML = '<select name=\'location\' disabled></select>';
        return;
    } else {
        var submit;
        if(submit = document.getElementById('submit'))
            submit.disabled = true;
        document.getElementById("getplaces").innerHTML = '<select name=\'location\' disable><option>Loading...</option></select>';
    }
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            var tmp = '<input type=\'hidden\' name=\'TESTE\' value=\'1\'>';
            document.getElementById("getplaces").innerHTML = data+tmp;
            
            var submit;
            if(submit = document.getElementById('submit'))
                submit.disabled = false;
        }
    };
    
    xmlhttp.open("GET","getplaces.php?t="+type,true);
    xmlhttp.send();
}
function fptitems() {
    var sel = document.getElementById('selectfptype');
    var typeid = sel.options[sel.selectedIndex].value;
    
    var inputs = document.getElementsByTagName('input');
    for(var i = 0; i < inputs.length; i++) {
        if(inputs[i].type == 'checkbox') {
            inputs[i].checked = false;
        }
    }
    
    var selects = document.getElementsByTagName('select');
    for(var i = 0; i < selects.length; i++) {
        if(selects[i].id != 'selectfptype') {
		    selects[i].selectedIndex = 0;
    	    selects[i].disabled = true;
        }
    }
    
    document.getElementById('fptname').value = '';
    document.getElementById('fptstartletter').value = '';
    document.getElementById('fptagcolour').value = '';
	document.getElementById('loading').style.display = '';
    
    if(typeid == '') {
		document.getElementById('loading').style.display = 'none';
        return;
    } else {
        document.getElementById('submit').disabled = true;
    }
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText.split("[BRK]");
            var items = data[0].split("[BRK2]");
            
            for(var i = 0; i < items.length; i++) {
                var itemInfo = items[i].split("[BRK3]");
                var id = itemInfo[0];
                var quantity = itemInfo[1];
                
                document.getElementById('item'+id).checked = 'true';
                document.getElementById('quantity'+id).disabled = false;
                if(parseInt(quantity) > 0)
                	document.getElementById('quantity'+id).selectedIndex = parseInt(quantity)-1;
            }
            
            document.getElementById('fptname').value = data[1];
            document.getElementById('fptstartletter').value = data[2];
            document.getElementById('fptagcolour').value = data[3];
			document.getElementById('loading').style.display = 'none';
	        document.getElementById('submit').disabled = false;
        }
    };
    
    xmlhttp.open("GET","getfptitems.php?id="+typeid,true);
    xmlhttp.send();
}
function activateSelectFPTitem(el) {
    var select = document.getElementById('quantity'+el.id.substring(4));
    if(el.checked) {
        select.disabled = false;
    } else {
        select.disabled = true;
    }
}
function checkothernature() {
    var sel = document.getElementById('othernatureinput');
    
    if(sel.checked) {
        document.getElementById('othernature').style.display = '';
        document.getElementById('othernaturefield').disabled = false;
    } else {
        document.getElementById('othernature').style.display = 'none';
        document.getElementById('othernaturefield').disabled = true;
    }
}
function printpackingform() {
    var sel = document.getElementById('foodparceltype');
    var type = sel.options[sel.selectedIndex].value;
    
    myWindow = window.open('printpackingform.php?t=' + type,'');
    myWindow.focus();
    myWindow.print();
}
function printIssuedVoucher(){
    var selClient = document.getElementById('clientFullName');
    var selAgency = document.getElementById('agencyreferrer');
    var clientName = selClient.options[selClient.selectedIndex].innerHTML;
    var date = document.getElementById('datevoucherissued').value;
    var agency = selAgency.options[selAgency.selectedIndex].innerHTML;

    myWindow = window.open('printissuedvoucher.php?n='+clientName+'&a='+agency+'&d='+date,'');
    myWindow.focus();
    myWindow.print();
}
function validateForm() {
    var remove;
    if(remove = document.getElementById('removeuser')) {
        if(remove.checked) {
			return true;
        }
    }
    for(var j = 0; j < document.forms.length; j++) {
        var forms = document.forms[j];
        var found = false;
	    for(var i = 0; i < forms.length; i++) {
    	    if(!forms[i].disabled && (forms[i].type == 'text' || forms[i].type == 'password')) {
        	    if((forms[i].value == '' || forms[i].value == null) && forms[i].id.indexOf('opt') == -1) {
            	    forms[i].style.borderColor = 'red';
                    found = true;
	            } else {
    	            forms[i].style.borderColor = '';
        	    }
	        }
        }
        if(found) {
            alert('Fields in red must not be empty.');
         	return false;
        }
    }
    var dob;
    if(dob = document.getElementById('optdob')) {
	    if(!isValidDate(dob.value) && dob.value != '') {
    	    alert('Invalid date.');
        	dob.style.borderColor = 'red';
            dob.focus();
	        return false;
    	}
    }
    var packingdate;
    if(packingdate = document.getElementById('packingdate')) {
	    if(!isValidDate(packingdate.value)) {
    	    alert('Invalid date.');
        	packingdate.style.borderColor = 'red';
            packingdate.focus();
	        return false;
    	}
    }
    var donationdate;
    if(donationdate = document.getElementById('donationdate')) {
	    if(!isValidDate(donationdate.value)) {
    	    alert('Invalid date.');
        	donationdate.style.borderColor = 'red';
            donationdate.focus();
	        return false;
    	}
    }
    var login;
    if(login = document.getElementById('login')) {
	    if(login.value.length < 4) {
            alert('login must contain at least 4 characters.');
            login.style.borderColor = 'red';
            login.focus();
	        return false;
    	}
    }
    var pass1;
    if(pass1 = document.getElementById('pass1')) {
        if(pass1.disabled) return true;
        var pass2 = document.getElementById('pass2');
        if(pass1.value != pass2.value) {
            alert('Password do not match.');
            pass1.style.borderColor = 'red';
            pass2.style.borderColor = 'red';
            pass1.focus();
            return false;
        } else if(pass1.value.length < 6) {
            alert('Password must contain at least 6 characters.');
            pass1.style.borderColor = 'red';
            pass2.style.borderColor = 'red';
            pass1.focus();
            return false;
        }
    }
}
function validateFormPackNewFP() {
    var foodparceltype;
    if(foodparceltype = document.getElementById('foodparceltype')) {
        if(foodparceltype.selectedIndex == 0) {
            alert('Please select a food parcel type.');
            foodparceltype.style.borderColor = 'red';
            return false;
        }
    }
    var placestype;
    if(placestype = document.getElementById('placestype')) {
        if(placestype.selectedIndex == 0) {
            alert('Please select a location.');
            placestype.style.borderColor = 'red';
            return false;
        }
    }
    var place;
    if(place = document.getElementById('location')) {
        if(place.disabled) {
            alert('Please select a location.');
            place.style.borderColor = 'red';
            document.getElementById('placestype').selectedIndex = 0;
            return false;
        }
    }
}
function verifyDate(el) {
	var dat = el.value;
    if(!isValidDate(dat)) {
        alert('Invalid date.');
        el.style.borderColor = 'red';
    } else {
        el.style.borderColor = '';
    }
}
function isValidDate(date)
{
    var matches = /^(\d{2})[-\/](\d{2})[-\/](\d{4})$/.exec(date);
    if (matches == null) return false;
    
    var d = matches[1];
    var m = matches[2]-1;
    var y = matches[3];
    
    var composedDate = new Date(y, m, d);
    
    return composedDate.getDate() == d &&
    composedDate.getMonth() == m &&
    composedDate.getFullYear() == y;
}

function activateDonationForms(check) {
    var num = check.id;
    var forms = document.getElementsByTagName('input');
    var wh = document.getElementById('warehouses'+num);
    
	if(check.checked) {
        wh.disabled = false;
        for(var i = 0; i < forms.length; i++) {
            if(forms[i].name.indexOf(num) != -1) {
                forms[i].disabled = false;
            }
        }
    } else {
        wh.disabled = true;
        for(var i = 0; i < forms.length; i++) {
            if(forms[i].name.indexOf(num) != -1) {
                forms[i].disabled = true;
            }
        }
    }
}
function edititem(id) {
    var item = document.getElementById('item'+id);
    var text = item.innerHTML;//.toUpperCase();
    var button = document.getElementById('edititem'+id);
    
    item.innerHTML = '<input type=\'text\' id=\'newvalue'+id+'\' value=\''+text+'\' style=\'width:70px;\' maxlength=\'32\'>';
    
    button.value = 'submit';
    button.setAttribute('onClick', 'javascript: updateediteditem('+id+');');
}
function updateediteditem(id) {
    var newitem = document.getElementById('newvalue'+id).value;
    if(newitem == '') {
        return;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            var errorform = document.getElementById('errorinserting');
            
            if(!isNaN(data)) {
	            var button = document.getElementById('edititem'+id);
			    button.value = 'edit';
			    button.setAttribute('onClick', 'javascript: edititem('+id+');');
                
		    	var item = document.getElementById('item'+id);
			    item.innerHTML = newitem;
                
                errorform.innerHTML = '<br />';
            } else {
                alert(data);
                errorform.innerHTML = '<strong><font color=\'red\' size=\'3\'>'+data+'</font><strong>';
            }
        }
    };
    
	xmlhttp.open("GET","updateitemdb.php?mode=update&t="+newitem+"&id="+id,true);
    xmlhttp.send();
}
function newitem() {
    var newitem = document.getElementById('newitem').value;
    
    if(newitem == '') {
        return;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            var errorform = document.getElementById('errorinserting');
            
            if(!isNaN(data)) {
                var row = 0;
                var table = document.getElementById('tableitems');
                var spans = table.getElementsByTagName('span');
                
                for(var i = 0; i < spans.length && spans[i].innerHTML.toLowerCase() < newitem.toLowerCase(); i++, row++);
                
                var table = document.getElementById('tableitems');
                var newRow = table.insertRow(row);
                var cell1 = newRow.insertCell(-1);
                var cell2 = newRow.insertCell(-1);
                var cell3 = newRow.insertCell(-1);
                
                document.getElementById('newitem').value = '';
	            
                cell1.innerHTML = '<h3><span id=\'item'+data+'\'>'+newitem+'</span></h3>';
    	        cell2.innerHTML = '<td><input class=\'form-input-button\' type=\'submit\' id=\'edititem'+data+'\' value=\'Edit\' onclick=\'edititem('+data+')\'></td>';
        	    //cell3.innerHTML = '<td><input class=\'form-input-button\' type=\'submit\' id=\'removeitem'+data+'\' value=\'Remove\' onclick=\'removeitem('+data+')\'></td>';
                
                errorform.innerHTML = '<br />';
                alert('Item added successfully.');
            } else {
                alert(data);
                errorform.innerHTML = '<strong><font color=\'red\' size=\'3\'>'+data+'</font><strong>';
            }
        }
    };
    
    xmlhttp.open("GET","updateitemdb.php?mode=add&t="+newitem,true);
    xmlhttp.send();
}
function removefptype() {
    var xmlhttp;
    var sel = document.getElementById('selectfptype');
    var fptype = sel.options[sel.selectedIndex].value;
    var r;
    
    if(fptype.length == 0) {
        return;
    }
    
    r = confirm("Are you sure you want to remove this Food Parcel Type?");
    
    if(r == true) {
	    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    	    xmlhttp = new XMLHttpRequest();
	    } else {// code for IE6, IE5
    	    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	    }
    	xmlhttp.onreadystatechange = function() {
        	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            	var data = xmlhttp.responseText;
            	if(data != 'true') {
                    alert(data);
                } else {
                    var x = document.getElementById('selectfptype');
                    x.remove(x.selectedIndex);
                    
                    var inputs = document.getElementsByTagName('input');
                    for(var i = 0; i < inputs.length; i++) {
                        if(inputs[i].type == 'checkbox') {
                            inputs[i].checked = false;
                        }
                    }
                    
                    var selects = document.getElementsByTagName('select');
                    for(var i = 0; i < selects.length; i++) {
                        if(selects[i].id != 'selectfptype') {
                            selects[i].selectedIndex = 0;
                            selects[i].disabled = true;
                        }
                    }
                    
                    document.getElementById('fptname').value = '';
                    document.getElementById('fptstartletter').value = '';
                    document.getElementById('fptagcolour').value = '';
                    document.getElementById('loading').style.display = 'none';
                }
        	}
    	};
        
    	xmlhttp.open("GET","removefptype.php?fpt="+fptype,true);
    	xmlhttp.send();
    } else {
        return;
    }
}
function showExchange(el) {
    var check = document.getElementById('exchange');
    if(el.checked) {
        check.style.display = '';
        
        document.getElementById('dateGiven').disabled = false;
        document.getElementById('placestype').disabled = false;
        document.getElementById('location').disabled = false;
        document.getElementById('foodparcels').disabled = false;
	document.getElementById('submit').disabled = false;
	document.getElementById('submitAndPrint').disabled = true;
	document.getElementById('submitAndPrint').style.display = 'none';
    } else {
        check.style.display = 'none';
        
        document.getElementById('dateGiven').disabled = true;
        document.getElementById('placestype').disabled = true;
        document.getElementById('location').disabled = true;
        document.getElementById('foodparcels').disabled = true;
	document.getElementById('submit').disabled = true;
	document.getElementById('submitAndPrint').disabled = false;
	document.getElementById('submitAndPrint').style.display = '';
    }
}
function newReportedProblem() {
    var problem = document.getElementById('problem').value;
    var user = document.getElementById('user').value;
    var iduser = document.getElementById('iduser').value;
    var submit = document.getElementById('submit');
    
    if(problem == '') {
        return;
    } else {
        submit.disabled = true;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            
            submit.disabled = false;
            if(data == '') {
                alert('Error: Unable to update reported problems database');
            } else {
                var table = document.getElementById('problemstable');
                var newRow = table.insertRow(1);
                newRow.style.height = '30px';
                var cell1 = newRow.insertCell(0);
                var cell2 = newRow.insertCell(1);
                var cell3 = newRow.insertCell(2);
                
                cell1.innerHTML = data;
    	        cell2.innerHTML = user;
        	    cell3.innerHTML = problem;
            }
        }
    };
    
    xmlhttp.open("POST","updateproblemsdb",true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send("problem="+problem+"&user="+iduser);
}
function validateVoucherForm() {
    var nature = 0;
    var checkboxes = 0;
    for(var j = 0; j < document.forms.length; j++) {
        var forms = document.forms[j];
        var found = false;
	    for(var i = 0; i < forms.length; i++) {
    	    if(forms[i].type == 'text' && !forms[i].disabled) {
        	    if(forms[i].value == '' || forms[i].value == null) {
            	    forms[i].style.borderColor = 'red';
                    found = true;
	            } else {
    	            forms[i].style.borderColor = '';
        	    }
	        } else if(forms[i].type == 'checkbox' && forms[i].id != 'exchangenow') {
                checkboxes++;
                if(forms[i].checked) {
                    nature++;
                }
            }
        }
    }
    if(nature == 0 && checkboxes > 0) {
        alert('You must select at least one nature of need.');
        return false;
    } else if(nature == 1) {
        var othernature = document.getElementById('othernaturefield');
        if(!othernature.disabled && othernature.value == '') {
            alert('You must specify the other nature of need.');
            return false;
        }
    }
    var help = document.getElementById('helping');
    if(help.value == '' || help.value == null) {
        help.style.borderColor = 'red';
        found = true;
    } else {
        help.style.borderColor = '';
    }
    if(found) {
        alert('Fields in red must not be empty.');
        return false;
    }
    var date;
    if(date = document.getElementById('datevoucherissued')) {
	    if(!isValidDate(date.value)) {
    	    alert('Invalid date.');
        	date.style.borderColor = 'red';
	        return false;
    	}
    }
    var exchanged;
    if(exchanged = document.getElementById('exchangenow') && exchanged.checked) {
        var dateexchange;
        if(dateexchange = document.getElementById('dateGiven')) {
            if(!isValidDate(dateexchange.value)) {
                alert('Invalid date.');
                dateexchange.style.borderColor = 'red';
                return false;
            }
        }
        var placestype = document.getElementById('placestype');
        if(placestype.selectedIndex == 0) {
            placestype.style.borderColor = 'red';
            alert('You must specify where the voucher was exchanged.');
            return false;
        } else {
            placestype.style.borderColor = '';
        }
        var foodparcels = document.getElementById('foodparcels');
        if(foodparcels.selectedIndex == -1) {
            foodparcels.style.borderColor = 'red';
            alert('You must specify which food parcels have been given.');
            return false;
        } else {
            foodparcels.style.borderColor = '';
        }
    }
}
function nofixaddr(checkbox) {
    if(checkbox.checked) {
        if(document.getElementById('addr1').value != '') {
        	r = confirm("Are you sure you want to erase all of the address fields?");
            if(r == true) {
            	document.getElementById('addr1').value = 'No fixed address';
                document.getElementById('addr1').readOnly = true;
                
        		document.getElementById('optaddr2').value = '';
                document.getElementById('optaddr2').disabled = true;
        		
                document.getElementById('postcode').value = '';
                document.getElementById('postcode').disabled = true;
                document.getElementById('postcode').id = 'optpostcode';
        		
                document.getElementById('town').value = '';
                document.getElementById('town').disabled = true;
                document.getElementById('town').id = 'opttown';
        	} else {
                checkbox.checked = false;
            }
        } else {
            document.getElementById('addr1').value = 'No fixed address';
            document.getElementById('addr1').readOnly = true;
            
            document.getElementById('optaddr2').value = '';
            document.getElementById('optaddr2').disabled = true;
            
            document.getElementById('postcode').value = '';
            document.getElementById('postcode').disabled = true;
        	document.getElementById('postcode').id = 'optpostcode'
            
            document.getElementById('town').value = '';
            document.getElementById('town').disabled = true;
            document.getElementById('town').id = 'opttown';
        }
    } else {
        document.getElementById('addr1').value = '';
        document.getElementById('addr1').readOnly = false;
        
        document.getElementById('optaddr2').value = '';
        document.getElementById('optaddr2').disabled = false;
        
        document.getElementById('optpostcode').id = 'postcode'
        document.getElementById('postcode').value = '';
        document.getElementById('postcode').disabled = false;
        
        document.getElementById('opttown').id = 'town'
        document.getElementById('town').value = '';
        document.getElementById('town').disabled = false;
    }
}
function DonationItems(id, showhide) {
    var items = document.getElementById('viewitems'+id);
    var button = document.getElementById('button'+id);
    
    if(showhide == 'show') {
    	items.style.display = '';
        
        showhide = 'hide';
        button.value = 'Hide';
    } else if(showhide == 'hide') {
        items.style.display = 'none';
        
        showhide = 'show';
        button.value = 'View';
    }
    button.setAttribute('onClick', "javascript: DonationItems("+id+", '"+showhide+"');");
}
function getClientAddress(el) {
    var idClient = el.options[el.selectedIndex].value;
    
    if(idClient == '') {
        return;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            
            document.getElementById('clientaddr').innerHTML = data;
        }
    };
    
	xmlhttp.open("GET","getclientaddr.php?id="+idClient,true);
    xmlhttp.send();
}
function getParcelsPerWeek(select) {
    var val = select.options[select.selectedIndex].value;
    
    if(val == '') {
        document.getElementById('parcelsperweek').innerHTML = '';
        return;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            
            document.getElementById('parcelsperweek').innerHTML = data;
        }
    };
    
	xmlhttp.open("GET","getparcelsperweek.php?t="+val,true);
    xmlhttp.send();
}
function clearLog() {
    var conf = confirm('Are you sure you want to clear the Audit Log?\nIt will be possible to save it before clearing.');
    if(conf) {
        alert('Please save the following page and click on \'Clear\'.');
        document.getElementById('clear').style.display = '';
        window.open('viewlog.php?clear=1');
    }
}
function enablePass(el) {
    if(el.checked) {
        document.getElementById('pass1').disabled = false;
        document.getElementById('pass2').disabled = false;
    } else {
        document.getElementById('pass1').disabled = true;
        document.getElementById('pass2').disabled = true;
    }
}
function removeUser(el) {
    if(el.checked) {
        var conf = confirm('Are you sure you want to remove this user?');
        if(conf) {
            el.checked = true;
        } else {
            el.checked = false;
        }
    }
}
function getUsers(query) {
    var xmlhttp;
    var sel = document.getElementById('searchtype');
    var type = sel.options[sel.selectedIndex].value;
    
    if(query.length == 0) {
        document.getElementById("result").innerHTML = '';
        document.getElementById("allusers").style.display = '';
        return;
    }
    
    if(window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var data = xmlhttp.responseText;
            
            document.getElementById("result").innerHTML = data;
            document.getElementById("allusers").style.display = 'none';
        }
    };
    
    xmlhttp.open("GET","getusers.php?query="+query+"&type="+type,true);
    xmlhttp.send();
}
function hideUsers() {
    document.getElementById('clientinfo').value = '';
    document.getElementById('result').innerHTML = '';
    document.getElementById("allusers").style.display = '';
}
