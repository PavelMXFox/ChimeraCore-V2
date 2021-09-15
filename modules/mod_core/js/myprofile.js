$(document).ready(function(){

	// pager
	$( "#accordion" ).accordion({heightStyle: "content", collapsible: false});

	$( "#item_tabs").tabs({
		create: tabSwitch,
		activate: tabSwitch	
	});

	//$( "#item_tabs").tabs("disable","#tab-power");
	//$( "#item_tabs").tabs("disable","#tab-events");
	$("#tab_buttons .button_block").hide();
	$("#buttons_objects").show();
	
	$( "#item_tabs").show();
	reloadGenDesc();
});


function tabSwitch (event, ui) {
	if (event.type == "tabscreate") { panel = ui.panel; }
	else if (event.type == "tabsactivate" ) { panel = ui.newPanel}
	else return;
	$("#tab_buttons .button_block").hide();
	$(panel.prop("id").replace(/^[^\-\_]*[\-\_]/,"#buttons_")).show();
	try {
		switch (panel.prop("id")) {
			case "tab-notifications":
				//drawMailLists();
				break;
			case "tab-docs":
				reloadDocs();
				break;
		}
	} catch (e) {
		console.log(e);
	}	
}

function reloadGenDesc() {

	jsonExec(sitePrefix+"/ajax/"+modInstance+"/getmyprofile", {}, function onAjaxSuccess(json,textStatus) {
		drawGenDesc(json.data);
	}, true, function onFail(a,b) {
		$("#gendesc").html("<p>Ошибка загрузки данных</p>");
		console.log("Unable lo load gendesc:",a);
	});	
	
}

function drawGenDesc(data) {
	$("#gendesc").empty();
	
	$("#b_gdescedit").unbind('click',user_editGenDesc_click).hide();
	if (data==undefined) { 
		// empty block or company info.
		r_units=0;
		return; }
		
		thistitle = data.fullPath;
		//breadcrumbsUpdate("Инвентаризация / "+data.fullPath);
		
		dialogFieldGroup_add(
			dialogField_add("Идентификатор",$("<span>",{text: data.invCodePrint }))	
			.add(dialogField_add("Логин",$("<span>",{text: data.login })))
			.add(dialogField_add("ФИО",$("<span>",{text: data.fullName })))	
			.add(dialogField_add("eMail",$("<span>",{text: data.eMail })))
			.add(dialogField_add("Тема интерфейса",$("<span>",{text: data.uiTheme })))	
		).appendTo("#gendesc");

		if (data.settings) {
			if (data.settings.pagesize) {
				dialogFieldGroup_add(
					dialogField_add("Размер страницы (строк)",$("<span>",{text: data.settings.pagesize }))	
				).appendTo("#gendesc");
			}
		}
		
		//$("#b_gdescedit").bind('click',user_editGenDesc_click).show();
}

function user_editGenDesc_click() {
	$("#gendesc").empty();
	$("#b_gdescedit").unbind('click',user_editGenDesc_click).hide();
	
	jsonExec(sitePrefix+"/ajax/"+modInstance+"/getmyprofiledit", {}, function onAjaxSuccess(json,textStatus) {
		//drawGenDesc(json.data);
		dialogFieldGroup_add(
			dialogField_add("Идентификатор",$("<span>",{text: data.invCodePrint }))	
			.add(dialogField_add("Логин",$("<span>",{text: data.login })))
			.add(dialogField_add("ФИО","uxe_model"))
			.add(dialogField_add("eMail","uxe_email"))
			.add(dialogField_add("Тема интерфейса","uxe_theme"))
		).appendTo("#gendesc");

		if (data.settings) {
			if (data.settings.pagesize) {
				dialogFieldGroup_add(
					dialogField_add("Размер страницы (строк)","uxe_pagesize")	
				).appendTo("#gendesc");
			}
		}

		
		$("#b_gdescedit").bind('click',user_editGenDesc_click).show();
	}, true, function onFail(a,b) {
		$("#gendesc").html("<p>Ошибка загрузки данных</p>");
		console.log("Unable lo load gendesc:",a);
	});	
}

function reloadDocs() {
	jsonExec(sitePrefix+"/ajax/"+modInstance+"/getmydocs", {}, function onAjaxSuccess(json,textStatus) {
		w = $("#tab-docs .widget");
		w.empty();
		t = $("<table>",{class: "datatable sel"}).appendTo(w);
		el = $("<tr>").appendTo(t);
		$('<th>', { text: "#", class: "idx" })
			.add($('<th>', { class: "icon", append: $("<i>",{ class: "far fa-check-circle"  }).prop("title","Статус") }))
			.add($('<th>', { class: "icon", append: $("<i>",{ class: "fas fa-book-reader"  }).prop("title","Открыть документ") }))						
			.add($('<th>', { id: "th_desc", text:  "Наименование",fld: "desc", }))
		.appendTo(el);

		// fas fa-exclamation - Required, NACKed
		// far fa-check-circle - ACKed or non-ACKable
		$.each(json.data, function(key, row) {
			icolor='inherit';
			iclass='far fa-check-circle';
			ititle='Документ не требует подтверждения';
			
			if (row.ackRequired && row.ack!==true) {
				icolor='var(--mxs-red)';
				iclass='fas fa-exclamation-circle';
				ititle='Необходимо ознакомиться и подтвердить документ';
			} else if (row.ackRequired && row.ack==true) {
				icolor='var(--mxs-green)';
				ititle='Подтверждение получено';
			}
			
			el = $("<tr>",{
				id: "userdoc_"+row.id,
				oncontextmenu: "userDocContextMenuOpen("+row.id+",this,"+0+"); return false;"
				})
				.appendTo(t)
				.prop("title","Клик правой кнопкой откроет меню")
				.prop("ackReq",row.ackRequired)
				.prop("ack",row.ack)
				.prop("href",row.href)
				
				;
			$('<td>', { text: key+1, class: "idx" })
			.add($('<td>', { class: "icon", append: $("<i>",{ class: iclass, style: "color: "+icolor  }).prop("title",ititle) }))
			.add($('<td>', { class: "icon", append: $("<i>",{ class: "fas fa-book-reader"  }).prop("title","Открыть документ"), onclick: "openDocFile("+row.id+")" }))			
			.add($('<td>', { class: "ud_desc", text:  row.title,fld: "desc", }))
			.appendTo(el);
		})
	
	}, true, function onFail(a,b) {
		w = $("#tab-docs .widget");
		w.html("<p>Ошибка загрузки данных</p>");
		console.log("Unable lo load gendesc:",a);
	});	
	
}

function doAck(row_id) {
	showInfoDialog(
		"Вы подтверждаете, что ознакомились с документом <i>"+$("#userdoc_"+row_id+" .ud_desc").text()+" </i> и принимаете все его положения?", 
		"Подтвердите действие", 
		{
			"Подтверждаю": function() {
				$("#dialogInfo").dialog("close");
					jsonExec(sitePrefix+"/ajax/"+modInstance+"/ackmydoc", {id: row_id}, function onAjaxSuccess(json,textStatus) {
						reloadDocs();
					});
				},
   			"Отмена": function() {$("#dialogInfo").dialog("close");}
		}
		)
	
}

function openDocFile(row_id) {
	href=$("#userdoc_"+row_id).prop("href");
	if (href.length > 0) {
		window.open(href);
	}
}

function userDocContextMenuOpen(row_id, el,xx) {
	selText = getSelectionText();
	$("div#contextMenu").show();
	$("div#contextMenu").offset({top:$(window).scrollTop()+event.clientY, left:$(window).scrollLeft()+event.clientX});
	$("#contextMenu .title").text($(el).find(".ud_desc").text());
	$("#contextMenu .items").empty();

	ack=$(el).prop("ack");
	ackReq=$(el).prop("ackReq");
	
	if (selText.length > 0) {
		$("<p>",{class: "item", text: "Скопировать выделенное", onclick: "copySelText()"}).appendTo("#contextMenu .items");	
	}
	$("<p>",{class: "item", text: "Открыть документ", onclick: "openDocFile("+row_id+")"}).appendTo("#contextMenu .items");

	if (ackReq && !ack) {
		$("<p>", {class: "item", text: "Подтвердить документ", onclick: "doAck(\""+row_id+"\")"}).appendTo("#contextMenu .items");
	}
	return false;
}



