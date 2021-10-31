var fox_pager_prefix=undefined;
var fox_pager_callback=undefined;
var fox_pager_page=0;
var fox_pager_pages=0;
var selText = "";

$(document).ready(function(){
	 	
    	$('div.clicktohide').click(function () {
			hideAllMenus();
    	})
    	
		$('div.logo_menu').click(function () {
        $('div.hidden_menu').slideToggle('medium');
    	});
    	
    	$(".chevron .chevron").click(function () {
		$(".widget", $(this).closest("div.chevron")).slideToggle('fast');
		//$("i", this).toggle('fast');
		

		})
		
		$(document).keydown(function(e) {
			if (e.which == 27) {
				hideAllMenus();
			}
		});
		
		cts=new Date().getTime();
		lcts = sessionStorage.getItem("foxLastLDocCheckStamp");
		// check agreements
		if (cts > lcts+360000 && modInstance != 'core' && modFunction != 'myprofile') {
			jsonExec(sitePrefix+"/ajax/core/getmynackdocs", {}, function onAjaxSuccess(json,textStatus) {
				 if (json.count>0) {
					showInfoDialog(
					"Имеются непринятые соглашения или документы, требующие ознакомления. <br>Перейти к списку?<br>\
					<span style='color: var(--mxs-orange)'>В случае отсутствия подтверждения доступ к системе может быть ограничен", 
					"Внимание!", 
					{
						"Перейти": function() {
							//sessionStorage.setItem("foxLastLDocCheckStamp",cts);
							document.location.href=sitePrefix+"/core/myprofile#tab-docs";
							},
			   			"Отложить": function() {
							sessionStorage.setItem("foxLastLDocCheckStamp",cts);
							$("#dialogInfo").dialog("close");
						}
					}
					)
				} else {
					sessionStorage.setItem("foxLastLDocCheckStamp",cts);
				}
			},true,function onError() {
				sessionStorage.setItem("foxLastLDocCheckStamp",cts);
			});
		}
 });



function hideAllMenus() {
    	if ($('div.hidden_menu').is(":visible"))
		{
			 $('div.hidden_menu').slideToggle('medium');
		}
		if ($('div.contextMenu').is(":visible")) {
			$("div.contextMenu").slideToggle('fast');
		}
		$("div#contextMenu").hide();
}
 
function base64_decode( data ) {	// Decodes data encoded with MIME base64
	// 
	// +   original by: Tyler Akins (http://rumkin.com)


	var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	var o1, o2, o3, h1, h2, h3, h4, bits, i=0, enc='';

	do {  // unpack four hexets into three octets using index points in b64
		h1 = b64.indexOf(data.charAt(i++));
		h2 = b64.indexOf(data.charAt(i++));
		h3 = b64.indexOf(data.charAt(i++));
		h4 = b64.indexOf(data.charAt(i++));

		bits = h1<<18 | h2<<12 | h3<<6 | h4;

		o1 = bits>>16 & 0xff;
		o2 = bits>>8 & 0xff;
		o3 = bits & 0xff;

		if (h3 == 64)	  enc += String.fromCharCode(o1);
		else if (h4 == 64) enc += String.fromCharCode(o1, o2);
		else			   enc += String.fromCharCode(o1, o2, o3);
	} while (i < data.length);

	return enc;
}

 
 function dialogShow(title) {
 	$("#f_dialog").show();
  	$("#f_dialog h2.dialog").html(title);
 }
 
 function dialogHide() {
 	$("#f_dialog").hide();
 }
 
 function progressBar_init()
 {
	$('.progressbar').each(function() {
   	el = $(this);
   	val = parseInt(el.attr('value'));

   	el.progressbar({
   		value: 0
   	});
   progressBar_update(el, val);

	});
}	

function progressBar_update(el, val)
{
	el.attr("value",val);
	el.progressbar( "option", "value", val);
		if (val >= 90) {
   		el.children(".ui-widget-header").css({ 'background': 'var(--mxs-red)' });
   	} else if (val >= 80) {
   		el.children(".ui-widget-header").css({ 'background': 'var(--mxs-yellow)' });
   	} else {
   		el.children(".ui-widget-header").css({ 'background': 'var(--mxs-blue)' });
   	}
}

 function dialogAddHandler(handler)
{
	$('#b_dialog_green').unbind('click');
	$('#b_dialog_green').click(handler);
}

function on_valChanged(t_this) { 
	t_this.addClass('changed');
}

function markOnValChanged() {
	$(".i").change(function() {$(this).addClass('changed');});
}

function loadDialogForm (url,udesc) {
	$("#f_dialog div.widget").html("Loading...");
	$.post({
  		url: url,
  		data: { a: "get"+udesc},
  		success: function onAjaxSuccess(data,textStatus)
		{
		  // Здесь мы получаем данные, отправленные сервером и выводим их на экран.
		  $("#f_dialog div.widget").html(data);
		  $('.i').change(function() {on_valChanged($(this)); });
		},
		error: function onAjaxSuccess(data,textStatus)
		{
		  // Здесь мы получаем данные, отправленные сервером и выводим их на экран.
		  $("#f_dialog div.widget").html("Ошибка загрузки данных");
		},
		dataType: "html"
	});
}


function isset(val)
{
	return !(val === null || val == '' || val === undefined);
}
function setVal(id, value,text,href)
{
	if (isset(value))
	{
		if (text===undefined)
		{
			text = value;		
		}
		$("#"+id).show();
		$("#"+id+" .crm_entity_field_value span").text(text);
		$("#"+id+" .crm_entity_field_value a").text(text);
		$("#"+id+" .crm_entity_field_value a").attr('href',href);
		$("#"+id+" .crm_entity_field_value .i").val(value);
	}
}

function setMultiVal(id,value1,value2,text)
{
	if (isset(value1) || isset(value2))
	{
		$("#"+id).show();
		if (text ===undefined)
		{
			text = value1+"; "+value2;		
		}
		$("#"+id+" .crm_entity_field_value span").text(text);
		$("#"+id+" .crm_entity_field_value .i.half1").val(value1);
		$("#"+id+" .crm_entity_field_value .i.half2").val(value2);
	}
}

function getVal(id)
{
	if ($("#"+id+" .crm_entity_field_value .i").hasClass("changed"))
	{
		val = $("#"+id+" .crm_entity_field_value .i").val();
		if (isset(val)) { return val; } else {return null; }
	}
	else
	{ return false; }

}

function getMultiVal(id,subclass)
{
	if ($("#"+id+" .crm_entity_field_value .i."+subclass).hasClass("changed"))
	{
		val = $("#"+id+" .crm_entity_field_value .i").val();
		if (isset(val)) { return val; } else {return null; }
	}
	else
	{ return false; }

}


function AjaxLoader() {
    $('body').append('<div id="loadingDiv"></div>');

    $('#loadingDiv')
        .append('<p id="loadingText"></p>')
        .css('background', 'url(' + siteprefix + 'images/ajax.gif) no-repeat 50% 25%')
        .css('padding-top', '90px')
        .css('background-color', '#F5F5F5')
        .css('border', '3px solid #00008B')
        .css('height', '160px')
        .css('width', '300px')
        //.hide(); // изначально скрываем сообщение

    $('#loadingText')
        .css('text-align', 'center')
        .css('font', '20px bolder')
        .css('font-family', 'Segoe UI, Tahoma, Arial');
}

function lockInput(url, id, udesc)
{
	$("#b_submit"+udesc).hide();
	$("div.widget#"+udesc).html("Обновление данных");
	loadDesc(url, id, udesc);
}

function lockToggle(url, id, udesc)
{
	if ($("#"+udesc+" .i").prop('disabled')==true){
		$("#"+udesc+" .i").prop('disabled',false);
		$("#b_submit"+udesc).show();
		$('.i').change(function() {on_valChanged($(this)); }); 
		
	} else {
		lockInput(url, id, udesc);
	}
}



function loadDesc (url, id, udesc, var1, var2, var3, onSuccess,onError) {
	$.post({
  		url: url,
  		data: { a: "get"+udesc, id : id, dis : 1, var1 : var1, var2 : var2, var3 : var3},
  		success: function onAjaxSuccess(data,textStatus)
		{
		  // Здесь мы получаем данные, отправленные сервером и выводим их на экран.
		  $("#"+udesc).html(data);
		  if(typeof onSuccess == 'function') {onSuccess(udesc);}
		},
		error: function onAjaxSuccess(data,textStatus)
		{
		  // Здесь мы получаем данные, отправленные сервером и выводим их на экран.
		  $("#"+udesc).html("Ошибка загрузки данных");
		  if(typeof onError == 'function') {onError(data);}
		},
		dataType: "html"
	});
}

function jsonExec(url, data, onSuccess,noblank,onError)
{
	if (noblank==undefined || noblank==false) {$(".blanker").show();}
	$.post({
  		url: url,
  		data: data,
  		success: function onAjaxSuccess(data,textStatus)
		{
	 		try {
				var json = $.parseJSON(data);
				if (json.status=="OK")
				{
					$(".blanker").hide();
					onSuccess(json,textStatus);
				} else {
					$(".blanker").hide();
					if (typeof(onError) == 'function')
					{
						onError(json, json.message);
					} else {
						alert("ERROR: "+json.message)

					}
					
				}	 				
			} catch (err) {
				$(".blanker").hide();
				console.log(err);
			}; 
			$(".blanker").hide();
		},
		error: function (data,textStatus, errorText)
		{

			$(".blanker").hide();

			if (typeof(onError) == 'function')
			{
				onError({status: "FAIL", message: textStatus+": "+errorText, code: 0});
			} else {
	 		    alert("Ошибка выполнения запроса!\n"+textStatus+": "+errorText);
	 		}
		},
		dataType: "html"
	});	
}

function objectDump(object) {
    var out = "";
    if(object && typeof(object) == "object"){
        for (var i in object) {
            out += i + ": " + object[i] + "\n";
        }
    } else {
        out = object;
    }
        alert(out);
}

function saveDesc(url, id, udesc)
{
	// создадим пустой объект
	var $data = {};
	var ctr=0;
	$('#'+udesc+' .changed').each(function() {
	  $data[this.name] = $(this).val();
	  ctr++;
	});

	$data["a"] = "save"+udesc;
	$data["id"] = id;

	if (ctr > 0)
	{
		$.post({
		  url: url,
		  data: $data,
		  success: function onAjaxSuccess(data,textStatus)
			{
			  // Здесь мы получаем данные, отправленные сервером и выводим их на экран.
			  
				try {
			  var obj = jQuery.parseJSON(data);
			  
			  
			  if (obj.status == 'OK')
			  {
			  	lockInput(url, id, udesc);
				//alert("Данные успешно записаны");		  
			  } else {
			  		alert("Ошибка! "+ obj.message);
			  }
					
				} catch(err)
				{
					alert(data);			
				}			  
			},
			error: function onAjaxSuccess(data,textStatus)
			{
			  // Здесь мы получаем данные, отправленные сервером и выводим их на экран.
			  alert("Ошибка загрузки данных");
			},
			dataType: "html"
		});
	} else {
		lockInput(url, id, udesc);	
	}
}

function dialogFieldGroup_add(item)
{
	return $("<div>",{
		class: "crm_entity_block_group",
		append: item
	});
}


function dialogField_add(title, item, blockstyle, fieldstyle, type, args, onChange)
{
	if (type === undefined) { type = 'text'; }
	if (typeof(item)=="string")
	{
		name = item.replace(/^[a-z]*\_/,'');

		switch(type) {
  			case 'password':
  				item_id=item;
	    		item = $("<input>", {class: "i", id: item, name: name, type:'password',width: 'calc(100% - 32px)'})
	    		.add($("<div>",{class: "button short", style: "width: 25px; margin-right: 0; margin-left: 2; padding: 0; padding-top: 1; font-size: 13px;",append: $("<i>",{class: 'far fa-eye'})
	    		})
	    		.click(function() {
					if ($("#"+item_id).prop('type')==='password') {
						$(this).addClass("active");
						$("#"+item_id).prop('type', 'input');
					} else {
						$(this).removeClass("active");
						$("#"+item_id).prop('type', 'password');
					}
	    		}));
	    		break
  			case 'passwordNew':
  				item_id=item;
	    		item = $("<input>", {class: "i", id: item, name: name, width: 'calc(100% - 32px)'})
	    		.add($("<div>",{
	    			class: "button short", 
	    			style: "width: 25px; margin-right: 0; margin-left: 2; padding: 0; padding-top: 1; font-size: 13px;",
	    			append: $("<i>",{class: 'fas fa-retweet'}),
    			})
    			.click(function() {
    				jsonExec(sitePrefix+'/ajax/core/genpassw', {l: (args===undefined)?undefined:args.len, t: (args===undefined)?undefined:args.type}, function (data) {
    					$("#"+item_id).val(data.passwd);
						$("#"+item_id).change();
	
    				});
    			}));
    			
	    		break
	    	case 'select':
	    		item= $("<select>",	{class: "i", id: item, name: name});
	    		if (args !== undefined) {
	    			$.each(args, function(arg,val) {
	    				item.append($("<option>",{value: arg, text: val}));
	    			});
	    		}
	    		break;

    		case 'datetime':
    			if (typeof(args)!=='object') { args={curr: args}; }
    			if (args.step===undefined) { args.step = 10; }
    			if (args.format===undefined) { args.format = "Y-m-d H:i"; }
    			if (args.mask === undefined) { args.mask=true; }
    			
    			item_id=item;
    			item=$("<input>", {class: "i", id: item_id, name: name});
    			
    			item.datetimepicker(args);
    			item.val(args.curr);
    			break;

  			default:
	    		item = $("<input>", {class: "i", id: item, name: name});
	    		item.val(args);
	    		break
    	}
	}
	return $("<div>",{
			class: "crm_entity_field_block",
			style: blockstyle,
			append: $("<div>",{
				class: "crm_entity_field_title",
				
				append: $("<span>",{
					html: title,
				})
		
			})
			.add($("<div>",{
				class: "crm_entity_field_value",
				append: item,
				style: fieldstyle
			}))
		});	
}

function breadcrumbsUpdate(text) {
	$("#breadcrumbs_label").text(text);
}

function breadcrumbsUpdateSuffix(text) {
	breadcrumbsUpdate($("#breadcrumbs_label").text().replace(/ \/[^/]*$/,' / '+text))
}


function collectForm(formid, getall, withIDS, withREF, validate)
{
	
	// создадим пустой объект
	var data = {};
	var ctr=0;
	var cctr=0;
	var cerr=0;
	$('#'+formid+' .i'+((getall==true)?"":'.changed')).each(function() {
	  if (isset(this.name)) {key = this.name} else {key = this.id}
 	  if (getall) { 
		
		var reqx="false";
		var regx=".*";
		var rext="false";
		var px = $(this).closest('.crm_entity_field_block');
		if (px.length == 1) {
			if ($(px[0]).attr("regx") !== undefined) { regx = $(px[0]).attr("regx"); }
			if ($(px[0]).attr("reqx") !== undefined) { reqx = $(px[0]).attr("reqx"); }
			if ($(px[0]).attr("rext") !== undefined) { rext = $(px[0]).attr("rext"); }
		}
		data[key] = {val: $(this).val(), changed: $(this).hasClass("changed")};
		} else { data[key] = $(this).val(); }
		
		if (withIDS==true) {
			data[key].id=$(this).prop("id");
			data[key].regx=regx;
			data[key].reqx=reqx;
		}
		
		data[key].rext=rext;

		if (withREF==true) {
			data[key].refx=this;
		}

		if (validate==true) {
			var reqxOK = reqx!="true" || data[key].val.length>0;
			var regxOK = data[key].val.length==0 || data[key].val.match(new RegExp(regx, 'g' )) !== null;
			
			if (withIDS==true) {
				data[key].reqxOK = reqxOK;
				data[key].regxOK = regxOK;
			}	
			
			data[key].valOK = reqxOK && regxOK;
			
			if ((reqxOK !== true || regxOK !== true )) {
				$(this).addClass("alert");
				var ttlx ="";
				if (reqxOK !== true) { ttlx += "Обязательное поле. "};
				if (regxOK !== true) { ttlx += "Несовпадение формата - " + regx};
				$(this).attr("title",ttlx);
				cerr++;
				
			} else {
				$(this).removeClass("alert");
				$(this).attr("title","");
			}
		}
		
	   if ($(this).hasClass("changed")) { cctr++;}
	  ctr++;
	});
	data["elCount"]=ctr;
	data["changedCount"]=cctr;
	data["validateErrCount"]=cerr;
	try {
		data["id"] = id;
		} catch {
			
		}
	return data;
}

function traffToStr(val)
{
	
	if (val >= 1000000000000)
	{
		return (Math.round(val/100000000000,1)/10)+"T";	
	} else if (val >= 1000000000)
	{
		return (Math.round(val/100000000,1)/10)+"G";	
	} else if (val >= 1000000)
	{
		return (Math.round(val/100000,1)/10)+"M";
	} else if (val >= 1000)
	{
		return (Math.round(val/100,1)/10)+"K";
	}
	return Math.round(val);

}

function showInfoDialog(message, title, buttons, height) {

	
	msg = $("<span>",{class: "widget", style: "padding: 8px;height: "+height+";", id: "dialogInfoSpan", });
	if (typeof(message)=="string")
	{
		msg.html(message);
	} else {
		msg.append(message);
	}
	
	if (title===undefined) { title="Информация"; };
	if (buttons===undefined) {
		buttons = {
        "Закрыть": function() {
           dialogInfo.dialog("close");
        }}
	}
	dialogInfo = $("<div>",{
		id: "dialogInfo",
		title: title,
	}).appendTo("div#dialogs")
	.dialog({
		autoOpen: true,
 		height: 250,
      	width: 403,
		modal: true,
		position: {my: "center",at: "center",of: "body"},
        close: function () {
    		dialogInfo.dialog("destroy").remove();
    	},
		buttons: buttons
	})
	.append(msg);
	dialogInfo.dialog("option","height", $("#dialogInfoSpan").height()+150);
}

function createDialog(body, title, buttons, height, columns, id) {

	if (id==undefined) {id = 'dialogForm'; }
	if (columns=='undefined') { columns=1; }
	
	dialog = $("<div>",{
		id: id,
		title: title,
		style: "overflow: hidden;"
	}).appendTo("div#dialogs")
	.dialog({
		autoOpen: false,
 		height: height,
      	width: (350*columns),
		modal: true,
		position: {my: "center",at: "center",of: "body"},
        close: function () {
    		$("#"+id).dialog("destroy").remove();
    	},
		buttons: buttons
	})
	.append(body);
}

function openDialog(id) {
	if (id==undefined) {id = 'dialogForm'; }
	$("#"+id+' .i').on("change",function() {on_valChanged($(this)); });
	$("#"+id).dialog("open");
}

function closeDialog(id) {
	if (id==undefined) {id = 'dialogForm'; }
	$("#"+id).dialog("close");
}


function copySelText() {
	if (navigator.clipboard) {
		navigator.clipboard.writeText(selText);
		return true;
	} else {
		return false;
	}
}

function getSelectionText() {
    var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    return text;
}

function resetCopyHash() {
	sessionStorage.removeItem(modInstance+'.copyHash');
}

function setCopyHash(ref_type, ref_id,path) {
	sessionStorage.setItem(modInstance+'.copyHash', JSON.stringify({class: ref_type,id: ref_id, path: path}));
		
	hash="cfx::"+btoa(JSON.stringify({"uid":xInstance, "i":modInstance,"t": ref_type, "r":ref_id}));
	if (navigator.clipboard) {
		navigator.clipboard.writeText(hash);
	}
}

function decodeHash(val, ignoreModule) {
	if (ignoreModule==undefined) { ignoreModule=false; }
	if (res=val.match(/^cfx::(.*)/)) {
  		try {
  			j = JSON.parse(atob(res[1]));
			if (j.uid!=xInstance) { return false; };
			if (!ignoreModule && j.i!=modInstance) { return false; };

      		return j;
   		} catch(err) {
   			console.log(err);
  			return false;		
  		}
	} else {
		return false;
	}
}

(function($){
 	$.fn.extend({ 
		
 		foxPager: function(cmd, value) {
			if (cmd===undefined) { cmd = {}; }
			var mode = undefined;
			if (typeof(cmd) == 'object' && value===undefined) {
				// initialize
				mode='init';
				 
				var defaults={
					page: undefined,
					pages: 0,
					prefix: '',
					callback: function() {},
				}
				
				var options =  $.extend(defaults, cmd);
				
				
				if (!options.page) {
					options.page = (!sessionStorage.getItem(options.prefix+"pager") || sessionStorage.getItem(options.prefix+"pager").replace(/[^0-9]/g,'')=='')?1:sessionStorage.getItem(options.prefix+"pager");
				}
				if (options.page > options.pages && options.pages >0) { options.page = options.pages; }
				sessionStorage.setItem(options.prefix+"pager", options.page);
				
			} else {

				switch (cmd) {
					case "clear":
						mode='remove';
						break;
					case "getPage":
						mode='getPage';
						break;
					case "update":
						mode = 'update';
						if (value.page > value.pages) { value.page = value.pages; }
						var options = value;
						break;	
					default:
						return;
					
				}
			}							
			
			if (mode=='getPage') {
				return sessionStorage.getItem($(this).prop('foxPager_prefix')+"pager");c
			}
    		this.each(function(rid,ref) {
				
				if (mode!='remove') {
					if (options.page!==undefined) {
						$(ref).prop('foxPager_page', options.page);
					} else {
						options.page = $(ref).prop('foxPager_page');
					}
					
					if (options.pages!==undefined) {
						$(ref).prop('foxPager_pages', options.pages); 
					} else {
						options.page = $(ref).prop('foxPager_pages');
					}				
					
					
					if (mode =='init') {
						$(ref).empty();
						$(ref).prop('foxPager_prefix', options.prefix);
						$(ref).addClass('foxPager_'+options.prefix);
						
						$("<i>",{class: "fas fa-angle-double-left", css: {	padding: "0 10 0 10", cursor: 'hand' }}).click(function() {
							options.page = parseInt($(ref).prop('foxPager_page'));
							options.pages = parseInt($(ref).prop('foxPager_pages'));
							if (options.pages==0) { return; }
							if (options.page > 1) {
								options.page=1;
								sessionStorage.setItem(options.prefix+"pager", options.page);
								$(".foxPager_"+options.prefix).find(".foxPager_label").text("Стр: "+options.page+" из "+options.pages);
								$(".foxPager_"+options.prefix).prop('foxPager_page', options.page);
								options.callback();
							}
						})
						.appendTo(ref);
						$("<i>",{class: "fas fa-angle-left", css: { padding: "0 10 0 10", cursor: 'hand'} }).click(function() {
							options.page = parseInt($(ref).prop('foxPager_page'));
							options.pages = parseInt($(ref).prop('foxPager_pages'));
							if (options.pages==0) { return; }

							if (options.page > 1) {
								options.page=options.page-1;
								sessionStorage.setItem(options.prefix+"pager", options.page);
								$(".foxPager_"+options.prefix).find(".foxPager_label").text("Стр: "+options.page+" из "+options.pages);
								$(".foxPager_"+options.prefix).prop('foxPager_page', options.page);
								options.callback();
							}
						}).appendTo(ref);
						
						$("<span>",{text: "Стр: "+options.page+" из "+options.pages, class: 'foxPager_label', css: {	padding: "0 10 0 10" }}).appendTo(ref);
						
						$("<i>",{class: "fas fa-angle-right", css: {	padding: "0 10 0 10", cursor: 'hand' } }).click(function() {
							options.page = parseInt($(ref).prop('foxPager_page'));
							options.pages = parseInt($(ref).prop('foxPager_pages'));
							if (options.pages==0) { return; }

							if (options.page < options.pages) {
								options.page=options.page+1;
								sessionStorage.setItem(options.prefix+"pager", options.page);
								$(".foxPager_"+options.prefix).find(".foxPager_label").text("Стр: "+options.page+" из "+options.pages);
								$(".foxPager_"+options.prefix).prop('foxPager_page', options.page);
								options.callback();
							}
						}).appendTo(ref);
						$("<i>",{class: "fas fa-angle-double-right", css: { padding: "0 10 0 10", cursor: 'hand'	} }).click(function() {
							options.page = parseInt($(ref).prop('foxPager_page'));
							options.pages = parseInt($(ref).prop('foxPager_pages'));
							if (options.pages==0) { return; }

							if (options.page != options.pages) {
								options.page=options.pages;
								sessionStorage.setItem(options.prefix+"pager", options.page);
								$(".foxPager_"+options.prefix).find(".foxPager_label").text("Стр: "+options.page+" из "+options.pages);
								$(".foxPager_"+options.prefix).prop('foxPager_page', options.page);
								options.callback();
							}
						}).appendTo(ref);
						
					} else if (mode=='update') {
						prefix = $(ref).prop("foxPager_prefix");
						sessionStorage.setItem(prefix+"pager", options.page);
						$(ref).prop('foxPager_page',options.page);
						$(ref).prop('foxPager_pages',options.pages);
						$(ref).find(".foxPager_label").text("Стр: "+options.page+" из "+options.pages);
						
						return;
					}
					
					
				} else {
					$(ref).empty();
				}
			});

		}
	});
	
})(jQuery);
