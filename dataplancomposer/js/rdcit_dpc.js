/**
 * Starts the application and gives the concepts field it's starting values
 */
function startDPC(){

	//action, parameter1,parameter2
	fetchWSData("getConcepts",null,null,null);
	
}

function conceptChange(){
	
	var itemListDivField = jQuery("#item_list_div");
	itemListDivField.empty();
	
	var itemField = jQuery('#iteminput')
	
	if(!itemField.val()){
		var concept = jQuery('#conceptselect').children(":selected").val();
		fetchWSData("getGroups",concept,null,null);
	}
	else{
		var concept = jQuery('#conceptselect').children(":selected").val();
		fetchWSData("getGroups",concept,null,null);
		searchItem();
	}
}

function groupChange(){
	var itemField = jQuery('#iteminput')
	
	if(!itemField.val()){
		var concept = jQuery('#conceptselect').children(":selected").val();
		var group = jQuery('#groupselect').children(":selected").val();
		
		fetchWSData("getItems",concept,group,null);
	}
	else{
		
		searchItem();
	}
}


function searchItem(){
	
	var concept = jQuery('#conceptselect').children(":selected").val();
	var group = jQuery('#groupselect').children(":selected").val();
	var item = jQuery('#iteminput').val();
	
	fetchWSData("getItems",concept,group,item);
	
	
}


/**
 * Creates an ajax post request to the server with given parameters
 * 
 * @param action defines the action to perform
 * @param p1 additional parameter 1
 * @param p2 additional parameter 2
 * @param p3 additional parameter 3
 * @returns mixed (json/html)
 */
function fetchWSData(action, p1, p2, p3){
	
	if (action=="getConcepts"){
		
		var ajaxrequest = jQuery.ajax({
			url : "ws/restful.php",
			type : "post",
			data : {
				action : "getConcepts"
			}
		}).done(function(data) {
			if (data.errorCode == 0) {
				
				updateConceptOptions(data.data);
				
			} else {
				console.log("Error: " + data.message);
			}
		}).fail(function() {
			console.log("Error");
		});
	}
		
	else if (action=="getGroups"){
		
		var ajaxrequest = jQuery.ajax({
			url : "ws/restful.php",
			type : "post",
			data : {
				action : "getGroups",
				concept: p1
			}
		}).done(function(data) {
			if (data.errorCode == 0) {
				
				updateGroupOptions(data.data);
				
			} else {
				console.log("Error: " + data.message);
			}
		}).fail(function() {
			console.log("Error");
		});
	}
	
	
	else if (action=="getItems"){
		
		var ajaxrequest = jQuery.ajax({
			url : "ws/restful.php",
			type : "post",
			data : {
				action : "getItems",
				concept: p1,
				group: p2,
				item: p3
			}
		}).done(function(data) {
			if (data.errorCode == 0) {
				
				updateItemsTable(data.data);
				
			} else {
				console.log("Error: " + data.message);
			}
		}).fail(function() {
			console.log("Error");
		});
	}
	else if(action == "getItemTable"){
		
		var datatype = jQuery("#datatype").prop("checked");
		var dataunit = jQuery("#dataunit").prop("checked");
		var multisingle = jQuery("#multisingle").prop("checked");
		var responseoptions = jQuery("#responseoptions").prop("checked");
		var codedvalues = jQuery("#codedvalues").prop("checked");
		var required = jQuery("#required").prop("checked");
		
		
		var ajaxrequest = jQuery.ajax({
			url : "ws/restful.php",
			type : "post",
			data : {
				action : "getItemBasket",
				datatype: datatype,
				dataunit: dataunit,
				multisingle: multisingle,
				responseoptions: responseoptions,
				codedvalues: codedvalues,
				required: required
			}
		}).done(function(data) {
			if (data.errorCode == 0) {
				
				displayDPCPreview(data.data);
				
			} else {
				console.log("Error: " + data.message);
			}
		}).fail(function() {
			console.log("Error");
		});
		
		
		
		
		
		
		
	}
	
		
}
	

function updateConceptOptions(data){
	
	var select = jQuery('#conceptselect');
	select.empty();
	
	select.append('<option>(ALL CONCEPTS)</option>');
	
	for(i=0;i<data.length;i++){
		if(data[i].concept != null && data[i].concept != ""){
			select.append('<option>'+data[i].concept+'</option>');
			
		}
		
	}
}


function updateGroupOptions(data){
	
	var select = jQuery('#groupselect');
	select.empty();
	
	select.append('<option>(ALL GROUPS)</option>');
	
	for(i=0;i<data.length;i++){
		if(data[i].concept_group != null && data[i].concept_group != ""){
			select.append('<option>'+data[i].concept_group+'</option>');
		}
	}
}



function updateItemsTable(data){

	
	var itemListDivField = jQuery("#item_list_div");
	itemListDivField.empty();
	
	var itemListTable = jQuery('<table id="itemListTable"/>');
	var itemListTableHead = jQuery("<thead><tr><th>Select</th><th>Item</th>" +
		"<th>Description</th><th>Concept</th><th>Group</th></tr></thead>");

		itemListTable.append(itemListTableHead);
	var itemListTableBody = jQuery("<tbody/>");
	
	if(data.length>0){
		
	
	
	for (var i=0;i<data.length;i++){
		
			
			var itemListRow = jQuery('<tr/>');
			
			var itemCBID = data[i].data_item_id;
			itemListRow.append('<td><input type="checkbox" class="cb-item" id="'+itemCBID+'"/></td>'+
				'<td>'+data[i].data_item_name+'</td>'+
				'<td class="descrlabel1">'+data[i].description+'<div id="details__'+itemCBID+'" class="description"></div></td>'+
				'<td>'+data[i].concept+'</td>' +
				'<td>'+data[i].concept_group+'</td>');
			itemListTableBody.append(itemListRow);
		

	}
	itemListTable.append(itemListTableBody);
	
	var itemListForm = jQuery('<form id="itemListForm"/>');
	var navTable = jQuery('<table/>');
	navTable.addClass("noborder");
	navTable.append('<tr><td><input type="button" id="submitItems" onclick="addItems()" value="Add to basket"></input></td>'+
			'<td><input type="button" id="selectdeselect" onclick="selectDeselect()" value="Select all"></input></td></tr>');

	var navTable_bottom = jQuery('<table/>');
	navTable_bottom.addClass("noborder");
	navTable_bottom.append('<tr><td><input type="button" id="submitItems" onclick="addItems()" value="Add to basket"></input></td>'+
			'<td><input type="button" id="selectdeselect" onclick="selectDeselect()" value="Select all"></input></td></tr>');

	
		itemListForm.append(navTable);
		itemListForm.append(itemListTable);
		itemListForm.append(navTable_bottom);
		
	//itemListDivField.append(itemListTable);
		itemListDivField.append(itemListForm);
		
		jQuery(".descrlabel").hover(function (e) {

		    var target = jQuery(this).children(".description");
		    var dataitem = target.attr("id")
		    var dataitemid = dataitem.split("__");
		    var description = target.html();
		    //console.log(target.attr("id")+" DIV content:"+description);
		    // if no data was fetched from the server yet
		    if(description == "" ){
		    	description = "Fetching data from server...";
		    	target.html(description);
		    	target.show();
				fetchWSData(null,null,dataitemid[1]).done(function(data){
					//alert(JSON.stringify(data));
					//console.log(JSON.stringify(data.data));
					var response=data.data;
					description="";
					for(i=0;i<response.length;i++){
						description+="<b>Option name:</b> "+response[i].option_name+"<br/>";	
					}
					
					target.html(description);
					
					
				});
		    	
		    	
		    }
		    
		    
		    target.show();
		    moveLeft = (jQuery(this).outerWidth() / 2);
		    moveDown = (target.outerHeight() / 2);
		}, function () {
		    var target = jQuery(this).children(".description");
		    if (!(target.hasClass("show"))) {
		        target.hide();
		    }
		});
		
		jQuery(".descrlabel").mousemove(function (e) {
		    var target = jQuery(this).children(".description");

		    leftD = e.pageX + parseInt(moveLeft);
		    maxRight = leftD + target.outerWidth();
		    windowLeft = jQuery(window).width() - 40;
		    windowRight = 0;
		    maxLeft = e.pageX - (parseInt(moveLeft) + target.outerWidth() + 20);

		    if (maxRight > windowLeft && maxLeft > windowRight) {
		        leftD = maxLeft;
		    }

		    topD = e.pageY - parseInt(moveDown);
		    maxBottom = parseInt(e.pageY + parseInt(moveDown) + 20);
		    windowBottom = parseInt(parseInt(jQuery(document).scrollTop()) + parseInt(jQuery(window).height()));
		    maxTop = topD;
		    windowTop = parseInt(jQuery(document).scrollTop());
		    if (maxBottom > windowBottom) {
		        topD = windowBottom - target.outerHeight() - 20;
		    } else if (maxTop < windowTop) {
		        topD = windowTop + 20;
		    }

		    target.css('top', topD).css('left', leftD);
		});
		
		jQuery('.cb-item').click(function(){
			  if(jQuery(this).prop('checked'))
				    jQuery(this).parent().parent().addClass('selected');
				   else 
				    jQuery(this).parent().parent().removeClass('selected');
				});
		
		
		
		
		
	}
	else{
		itemListDivField.append("No data was returned.");
	}
}



function addItems(){
	var items = [];
	
	jQuery('.cb-item').each(function(){
		if (this.checked==true){
			var name = jQuery(this).closest('td').next('td').text();
			
			var descr = jQuery(this).closest('td').next('td').next('td').html();
			descr = descr.split("<div");
			var concept = jQuery(this).closest('td').next('td').next('td').next('td').text();
			var group = jQuery(this).closest('td').next('td').next('td').next('td').next('td').text();
			
			
			var item = {"id":this.id,"name":name,"descr":descr[0],"concept":concept,"group":group};
			items.push(item);
			//addedItems.push(this.id);
			//var rowToDel = jQuery(this).closest('td').parent();
			//rowToDel.remove();
		}
	});
	
	if (items.length != 0) {
		var requestData = {"action_type":"add", "list":items}; 
		var ajaxrequest = jQuery.ajax({
			url : "ws/basket_action.php",
			type : "post",
			contentType: 'application/json',
			data: JSON.stringify(requestData)
		}).done(function(data) {
				searchItem();
		}).fail(function() {
			console.log("Error");
		});
	}
}


function removeItems(){
	var items = [];
	
	jQuery('.cb-item').each(function(){
		if (this.checked==true){
			
			items.push(this.id);
		}
	});
	
	if (items.length != 0){
		var requestData = {"action_type":"remove", "list":items}; 
		//alert(JSON.stringify(requestData));
		jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		}).done(function (data) {
			location.reload(); 
		});
	}
}

function emptyBasket(){
	var perm = confirm("Are you sure you would like to remove all items from the basket?");
	if (perm){
		var requestData = {"action_type":"empty"}; 
		
		var request = jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		});
		
		request.done(function (data) {
			location.reload(); 
		});
		
	}
}

function saveItemOrder(){
	var items = [];
	
	jQuery('.cb-item').each(function(){
		items.push(this.id);
	});
	
	if (items.length != 0){
		var requestData = {"action_type":"reorder", "list":items}; 
		//alert(JSON.stringify(requestData));
		jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		}).done(function (data) {
			location.reload(); 
		});
		
		
	}
}


function selectDeselect(){
	var selectDeselectButton = jQuery('#selectdeselect');
	
	if(selectDeselectButton.val() == "Select all"){
		jQuery('.cb-item').each(function() { 
			jQuery(this).parent().parent().addClass('selected');
            this.checked = true;               
        });
		selectDeselectButton.val("Deselect all");
	}
	else if(selectDeselectButton.val() == "Deselect all"){
		jQuery('.cb-item').each(function() { 
            this.checked = false;
            jQuery(this).parent().parent().removeClass('selected');
        });
		selectDeselectButton.val("Select all");
		
	}
	
	
}


function loadItemsTableFromServer(){
	
	fetchWSData("getItemTable",null,null,null);
}


function displayDPCPreview(data){
	
	var targetdiv = jQuery("#dataplan_preview");
	
	targetdiv.empty();
	targetdiv.append(data);
}


/*

*//**
 * Updates the categories select field
 * +++
 *//*
function updateCategories(){
	
	var categorySelectField = jQuery("#category_select");
	categorySelectField.empty();
	
	var startingOptions ='<option class="selectoption" value="_select_one_">=== SELECT ONE CATEGORY ===</option>';
	var options;
	var cat;
	var type;
	//alert(JSON.stringify(dataArray))
	//startingOptions+='<option value="_All_">===== ALL CATEGORIES =====</option>';
	categorySelectField.append(startingOptions);
	
    	for (var i=0;i<dataArray.data.length;i++){
    		options+='<option class="selectoption" value="'+dataArray.data[i].CAT_ID+'">'+dataArray.data[i].Category+'</option>';
    	}
    	
    	if (typeof(options) !== 'undefined' && options !==""){
    		categorySelectField.append(options);
    	}
	
}	
	
*//**
 * Updates the subcategories select field
 * +++
 *//*

function updateSubCategories(){

	var categorySelectField = jQuery("#category_select");
	var cat = "";
	cat = categorySelectField.val();
		
	if (cat != "_select_one_" && cat != "_All_"){
	
			fetchWSData(cat,null,null).done(function(data){
				//alert(JSON.stringify(data));
				
				subcategories = data.data;
				displaySubCategories();
				filteredArray = data.data;
				//updateTypes();
				//displayItemTable();
				//displayTypes();
			});
	}
	
}

*//**
 * +++
 *//*
function displaySubCategories(){
	
	var subcatSelectField = jQuery("#subcat_select");
	subcatSelectField.empty();
	var subcatOptions = '<option class="selectoption" value="_select_one_">=== SELECT A SUBCATEGORY ===</option>';
	
	for (var i=0;i<subcategories.length;i++){
		subcatOptions+='<option class="selectoption" value="'+subcategories[i].SUBCAT_ID+'">'+subcategories[i].Sub_Category+'</option>';
	}
	subcatSelectField.append(subcatOptions);

	
}



*//**
 * Displays the item table and applies the filter on it
 * +++
 *//*
function displayItemTable(){
//	console.log(JSON.stringify(filteredArray));
	var itemListDivField = jQuery("#item_list_div");
	itemListDivField.empty();
	
	var itemListTable = jQuery('<table id="itemListTable"/>');
	var itemListTableHead = jQuery("<thead><tr><td>Select</td><td>Type</td>" +
		"<td>Description label</td><td>Relevance</td><td>Response</td><td>unit</td></tr></thead>");

		itemListTable.append(itemListTableHead);
	var itemListTableBody = jQuery("<tbody/>");
	
	if(filteredArray.length>0){
		
	
	
	for (var i=0;i<filteredArray.length;i++){
		if( addedItems.indexOf(filteredArray[i].data_item_id)==-1){
		
			if(i%2==0){
				var itemListRow = jQuery('<tr class="odd"/>');
			}
			else{
				var itemListRow = jQuery('<tr class="even"/>');
			}
			var itemCBID = filteredArray[i].data_item_id;
			itemListRow.append('<td><input type="checkbox" class="cb-item" id="'+itemCBID+'"/></td>'+
				'<td>'+filteredArray[i].CONCEPT_TYPE+'</td>'+
				'<td class="descrlabel">'+filteredArray[i].description_label+'<div id="details__'+itemCBID+'" class="description"></div></td>'+
				'<td>'+filteredArray[i].relevance+'</td>'+
				'<td>'+filteredArray[i].response+'</td>'+
				'<td>'+filteredArray[i].unit+'</td>');
			itemListTableBody.append(itemListRow);
		
		}
	}
	itemListTable.append(itemListTableBody);
	
	var itemListForm = jQuery('<form id="itemListForm"/>');
	var navTable = jQuery('<table/>');
	navTable.append('<tr><td><input type="button" id="submitItems" onclick="addItems()" value="Add to basket"></input></td>'+
			'<td><input type="button" id="selectdeselect" onclick="selectDeselect()" value="Select all"></input></td></tr>');

	var navTable_bottom = jQuery('<table/>');
	navTable_bottom.append('<tr><td><input type="button" id="submitItems" onclick="addItems()" value="Add to basket"></input></td>'+
			'<td><input type="button" id="selectdeselect" onclick="selectDeselect()" value="Select all"></input></td></tr>');

	
		itemListForm.append(navTable);
		itemListForm.append(itemListTable);
		itemListForm.append(navTable_bottom);
		
	//itemListDivField.append(itemListTable);
		itemListDivField.append(itemListForm);
		
		jQuery(".descrlabel").hover(function (e) {

		    var target = jQuery(this).children(".description");
		    var dataitem = target.attr("id")
		    var dataitemid = dataitem.split("__");
		    var description = target.html();
		    //console.log(target.attr("id")+" DIV content:"+description);
		    // if no data was fetched from the server yet
		    if(description == "" ){
		    	description = "Fetching data from server...";
		    	target.html(description);
		    	target.show();
				fetchWSData(null,null,dataitemid[1]).done(function(data){
					//alert(JSON.stringify(data));
					//console.log(JSON.stringify(data.data));
					var response=data.data;
					description="";
					for(i=0;i<response.length;i++){
						description+="<b>Option name:</b> "+response[i].option_name+"<br/>";	
					}
					
					target.html(description);
					
					
				});
		    	
		    	
		    }
		    
		    
		    target.show();
		    moveLeft = (jQuery(this).outerWidth() / 2);
		    moveDown = (target.outerHeight() / 2);
		}, function () {
		    var target = jQuery(this).children(".description");
		    if (!(target.hasClass("show"))) {
		        target.hide();
		    }
		});
		
		jQuery(".descrlabel").mousemove(function (e) {
		    var target = jQuery(this).children(".description");

		    leftD = e.pageX + parseInt(moveLeft);
		    maxRight = leftD + target.outerWidth();
		    windowLeft = jQuery(window).width() - 40;
		    windowRight = 0;
		    maxLeft = e.pageX - (parseInt(moveLeft) + target.outerWidth() + 20);

		    if (maxRight > windowLeft && maxLeft > windowRight) {
		        leftD = maxLeft;
		    }

		    topD = e.pageY - parseInt(moveDown);
		    maxBottom = parseInt(e.pageY + parseInt(moveDown) + 20);
		    windowBottom = parseInt(parseInt(jQuery(document).scrollTop()) + parseInt(jQuery(window).height()));
		    maxTop = topD;
		    windowTop = parseInt(jQuery(document).scrollTop());
		    if (maxBottom > windowBottom) {
		        topD = windowBottom - target.outerHeight() - 20;
		    } else if (maxTop < windowTop) {
		        topD = windowTop + 20;
		    }

		    target.css('top', topD).css('left', leftD);
		});
		
		jQuery('.cb-item').click(function(){
			  if(jQuery(this).prop('checked'))
				    jQuery(this).parent().parent().addClass('selected');
				   else 
				    jQuery(this).parent().parent().removeClass('selected');
				});
		
		
		
		
		
	}
	else{
		itemListDivField.append("No data was returned.");
	}
		

}



*//**
 * Connects to the restful service and reads the dataArray values
 * @param category
 * @param subcat
 * @returns array of items
 * +++
 *//*
function fetchWSData(category,subcat, dataitem) {

	var urlToService;
	if (category == undefined && subcat == undefined && dataitem == undefined){
		urlToService	= "http://ocdw.medschl.cam.ac.uk/dataplancomposer/ws/restful.php";

	}
	else if(category != undefined && subcat == undefined){
		urlToService	= "http://ocdw.medschl.cam.ac.uk/dataplancomposer/ws/restful.php?catid="+category;
	}
	else if (subcat!=undefined){
		urlToService	= "http://ocdw.medschl.cam.ac.uk/dataplancomposer/ws/restful.php?subcatid="+subcat;
	}
	else if (dataitem!=undefined){
		urlToService	= "http://ocdw.medschl.cam.ac.uk/dataplancomposer/ws/restful.php?dataitemid="+dataitem;
	}

	return jQuery.ajax({
        type: "GET",
        url: urlToService,
        async: true,
        dataType: "json",
        success: function(data){
        	//process pedigree data here and populate versions
        	//alert(data.data);
        }
        
    });		
}

*//**
 * 
 * +++
 *//*

function updateDataItems(){
	var subcatSelectField = jQuery("#subcat_select");
	var subcat = subcatSelectField.val();
	
	if (subcat!="" && subcat!="_select_one_"){
		fetchWSData(null,subcat, null).done(function(data){
			//alert(JSON.stringify(data));
			
			dataArray = data.data;
			
			filteredArray = data.data;
			updateTypes();
			displayTypes();
			displayItemTable();

		});
		
	}
	
	
}


*//**
 * 
 * +++
 *//*
function updateTypes(){
	types = [];
	for (i=0;i<dataArray.length;i++){
	     if(jQuery.inArray(dataArray[i].CONCEPT_TYPE, types)<0){
	            //add to array
	    	 	types.push(dataArray[i].CONCEPT_TYPE);
	        }
	}
	types.sort();
	console.log(types);
}

*//**
 * Displays the types
 * +++
 *//*
function displayTypes(){
	
	var typeSelectField = jQuery("#type_select");
	typeSelectField.empty();
	
	var typeOptions = '<option class="selectoption" value="_all_types_">All</option>';
	
	for (var i=0;i<types.length;i++){
		typeOptions+='<option class="selectoption" value="'+types[i]+'">'+types[i]+'</option>';
	}
	typeSelectField.append(typeOptions);
}

*//**
 * Updates the dataArray and calls the displaying functions
 *//*
function updateItemList(){
	
	var categorySelectField = jQuery("#category_select");
	var cat = "";
	cat = categorySelectField.val();
		
	if (cat != "_select_one_" && cat != "_All_"){
	
			fetchWSData(cat,null,null).done(function(data){
				//alert(JSON.stringify(data));
				
				dataArray = data;
				filteredArray = data;
				updateTypes();
				displayItemTable();
				displayTypes();
			});
	}
}


function filterItemsByType(){
	var typeSelectField = jQuery("#type_select");
	var selectedType = typeSelectField.val();
	
	console.log("In filter.");
	filteredArray=[];
	filteredArray = JSON.parse(JSON.stringify(dataArray));
	console.log("FilteredArray:"+filteredArray.length+" DataArray:"+dataArray.length);
	console.log("Selected:"+selectedType);
	if (selectedType != "_all_types_"){
		
		for (var i=filteredArray.length-1;i>=0;i--){
			
			
			if (filteredArray[i].CONCEPT_TYPE != selectedType){
				filteredArray.splice(i,1);
			}
		}
		
	}
	else{
		console.log("In else");
		filteredArray=[];
		filteredArray = JSON.parse(JSON.stringify(dataArray));
	}
	
	//alert(JSON.stringify(filteredArray));
	//alert(log + filteredArray.data.length-1);
	console.log("FilteredArray after:"+filteredArray.length+" DataArray after:"+dataArray.length);
	displayItemTable();
	
}



function filterItems(itemArray){
	var filter = JSON.parse(JSON.stringify(filteredArray));
	if(itemArray != undefined && itemArray.length>0){
		for (var i=0;i<itemArray.length;i++){
			var index = filter.indexOf(itemArray[i]);
			if (index > -1){
				filter.splice(index,1);
			}
		}
	}
	filteredArray=JSON.parse(JSON.stringify(filter));
}



function selectDeselect(){
	var selectDeselectButton = jQuery('#selectdeselect');
	
	if(selectDeselectButton.val() == "Select all"){
		jQuery('.cb-item').each(function() { 
			jQuery(this).parent().parent().addClass('selected');
            this.checked = true;               
        });
		selectDeselectButton.val("Deselect all");
	}
	else if(selectDeselectButton.val() == "Deselect all"){
		jQuery('.cb-item').each(function() { 
            this.checked = false;
            jQuery(this).parent().parent().removeClass('selected');
        });
		selectDeselectButton.val("Select all");
		
	}
	
	
}


function addItems(){
	var items = [];
	
	
	jQuery('.cb-item').each(function(){
		if (this.checked==true){
			var name = jQuery(this).closest('td').next('td').next('td').html();
			console.log(name);
			name= name.split("<div");
			
			var type = jQuery(this).closest('td').next('td').text();
			var item = {"id":this.id,"name":name[0],"type":type};
			items.push(item);
			addedItems.push(this.id);
			var rowToDel = jQuery(this).closest('td').parent();
			rowToDel.remove();
		}
	});
	
	
	if (items.length != 0) {
		
		var basketImage = document.getElementById('basketstate');
		if (basketImage.src.match("_empty")) {
	        basketImage.src = "./images/basket_notempty.png";
	    }
		
		
		var requestData = {"action_type":"add", "list":items}; 
		
		jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		});
	}

}

function removeItems(){
	var items = [];
	
	jQuery('.cb-item').each(function(){
		if (this.checked==true){
			
			items.push(this.id);
		}
	});
	
	if (items.length != 0){
		var requestData = {"action_type":"remove", "list":items}; 
		//alert(JSON.stringify(requestData));
		jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		}).done(function (data) {
			location.reload(); 
		});
		
		
	}
}

function emptyBasket(){
	var perm = confirm("Are you sure you would like to remove all items from the basket?");
	if (perm){
		var requestData = {"action_type":"empty"}; 
		
		var request = jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		});
		
		request.done(function (data) {
			location.reload(); 
		});
		
	}
}

function saveItemOrder(){
	var items = [];
	
	jQuery('.cb-item').each(function(){
		items.push(this.id);
	});
	
	if (items.length != 0){
		var requestData = {"action_type":"reorder", "list":items}; 
		//alert(JSON.stringify(requestData));
		jQuery.ajax({
		    url: "ws/basket_action.php"
		,   type: 'POST'
		,   contentType: 'application/json'
		,   data: JSON.stringify(requestData)
		}).done(function (data) {
			location.reload(); 
		});
		
		
	}
}


function switchToCollections(){
	
	var categorymenu = jQuery('#selectbycategory');
		categorymenu.removeClass('active');

	var fromallmenu = jQuery('#searchfromall');
		fromallmenu.removeClass('active');

		
	var collectionmenu = jQuery('#selectbycollection');
		collectionmenu.addClass('active');
		
	var categoryorcollection = jQuery('#categoryorcollection');
	categoryorcollection.empty();
	
	var itemListDivField = jQuery("#item_list_div");
	itemListDivField.empty();
	
	var collectionSearchTable = jQuery('<table id="collectionsearchtable"/>');
	collectionSearchTable.append('<tr><td>Collection</td><td><select id="collselect"></select></td></tr>');
	
	categoryorcollection.append(collectionSearchTable);
}


function switchToSearchAll(){
	
	var categorymenu = jQuery('#selectbycategory');
	categorymenu.removeClass('active');

	var fromallmenu = jQuery('#searchfromall');
	fromallmenu.addClass('active');

	
	var collectionmenu = jQuery('#selectbycollection');
	collectionmenu.removeClass('active');
	
	var categoryorcollection = jQuery('#categoryorcollection');
	categoryorcollection.empty();

	var itemListDivField = jQuery("#item_list_div");
	itemListDivField.empty();

	var allSearchTable = jQuery('<table id="searchfromalltable"/>');
	allSearchTable.append('<tr><td>All items</td><td><input type="text" id="searchFromAll"/></td></tr>');

	categoryorcollection.append(allSearchTable);
}

*/