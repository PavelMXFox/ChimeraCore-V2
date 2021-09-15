pwr_mode="";

function doLogin()
{
	 username=$("#username").val();
	 password=$("#password").val();	 
	 if (username =='' || password=='') {return; }
	 jsonExec(sitePrefix+'/ajax/auth/login', {username: username, password: password}, function (data) {
	 	document.cookie= data.cookie_name+'='+data.cookie+'; path=/; max-age='+data.cookie_expire;
	 	if (pg_module=='login') {
	 		window.location.href=sitePrefix+"/";
	 	} else {
	 		window.location.reload(false);
	 	} 
	 });
}
function restorePasswd(args) {
	pwr_mode="restore";
	//$(".blanker").show();
	dialogReply = $("<div>",{
		id: "restorePasswdForm",
		title: "Восстановление доступа",
		style: "overflow: hidden;"
	}).appendTo("div#dialogs")
	.dialog({
		autoOpen: false,
 		height: 240,
      	width: 350,
		modal: true,
		position: {my: "center",at: "center",of: "body"},
        close: function () {
		    //reloadGenDesc();
    		dialogReply.dialog("destroy").remove();
    	},
		buttons: {
        "Восстановить": restorePasswd_click,
        "Закрыть": function() {
           dialogReply.dialog("close");
        }}
	})

	.append(
		dialogFieldGroup_add(
			dialogField_add("Логин <span style='color: var(--mxs-red);'>*</span>","pwr_login")
			.add(dialogField_add("E-Mail <span style='color: var(--mxs-red);'>*</span>","pwr_email"))
		))
	/*	
		// fill data
		jsonExec(sitePrefix+"/ajax/"+modInstance+"/getaddress", {id: id}, function onAjaxSuccess(json,textStatus) {
			$("#ied_address").val(json.data.address);
		});
	*/
	
	if (args) {
		$("#pwr_login").val(args.login);
		$("#pwr_code").val(args.code);
	};
	markOnValChanged();
	dialogReply.dialog("open");
	
}



function userRegister(code) {

	//$(".blanker").show();
	dialogReply = $("<div>",{
		id: "userRegisterForm",
		title: "Регистрация пользователя",
		style: "overflow: hidden;"
	}).appendTo("div#dialogs")
	.dialog({
		autoOpen: false,
 		height: 500,
      	width: 350,
		modal: true,
		position: {my: "center",at: "center",of: "body"},
        close: function () {
		    //reloadGenDesc();
    		dialogReply.dialog("destroy").remove();
    	},
		buttons: {
        "Регистрация": userRegister_click,
        "Закрыть": function() {
           dialogReply.dialog("close");
        }}
	})

	.append(
		dialogFieldGroup_add(
			dialogField_add("Логин","pwr_login")
			.add(dialogField_add("ФИО","pwr_fullName"))
			.add(dialogField_add("EMail","pwr_email"))
			.add(dialogField_add("Пароль",
				$("<input>", {class: "i", type: "password", id: "new_password", name: "new_password"})
			))
			.add(dialogField_add("Пароль (еще раз)",
				$("<input>", {class: "i", type: "password", id: "new_password2", name: "new_password2"})
			))
			.add(dialogField_add("Код регистрации (при наличии)","pwr_code"))
			.add(dialogField_add("Согласие на обработку персональных данных",
				$("<select>", {class: "i", id: "pwr_accept", name: "pwr_accept"})
					.append($("<option>",{value: "decline", text: "Не согласен"}))
					.append($("<option>",{value: "accept", text: "Согласен"}))
			))
		))
	/*	
		// fill data
		jsonExec(sitePrefix+"/ajax/"+modInstance+"/getaddress", {id: id}, function onAjaxSuccess(json,textStatus) {
			$("#ied_address").val(json.data.address);
		});
	*/
	
	if (code) {
		$("#pwr_code").val(code);
	}
	markOnValChanged();
	dialogReply.dialog("open");
}

function userRegister_click() {
	login = $("#pwr_login").val();
	fullname = $("#pwr_fullName").val();
	code = $("#pwr_code").val();
	email = $("#pwr_email").val();
	passwd=$("#new_password").val();
	passwd2=$("#new_password2").val();
	accept = $("#pwr_accept").val();

	$(".i.alert").removeClass("alert");

	err=0;
	
	if (passwd.length < 8) {
		$("#new_password").addClass("alert").prop("title","Пароль не может быть меньше 8 символов");
		err++;
	}
	
	if (passwd2 !== "" && passwd2 != passwd) {
		$("#new_password2").addClass("alert").prop("title","Пароли не совпадают");
		err++;
	}
	
	if (err>0) {
		return;
	}

	jsonExec(sitePrefix+'/ajax/auth/register', {login: login, email: email, fullname: fullname, password:passwd, code:code, accept:accept}, function onAjaxSuccess(json,textStatus) {

	showConfirmDialog("Запрос на регистрацию отправлен на электронную почту.</br> Для завершения регистрации введите полученный код.", "Подтверждение регистрации",
	userRegConfirm, {login: login});
	
	}, false, function onAjaxFailed(json, textStatus) {
		switch (json.code) {
			case 701:
				showInfoDialog("EMail уже зарегистрирован","Ошибка");
				break;
			case 702:
				showInfoDialog("Пользователь уже зарегистрирован","Ошибка");
				break;
			case 703:
				showInfoDialog("EMail некорректный","Ошибка");
				break;
			case 704:
				showInfoDialog("Код некорректный","Ошибка");
				break;
			case 705:
				showInfoDialog("Согласие на обработу перс.данных обязательно","Ошибка");
				break;
			case 707:
				showInfoDialog("Пользователь не приглашен. Регистрация без приглашения запрещена.","Ошибка");
				break;
			default:
				showInfoDialog("Ошибка "+json.code+":"+json.message,"Ошибка");
				break;
		}
	});
	
}

function restorePasswd_click() {

	login = $("#pwr_login").val();
	email = $("#pwr_email").val();

	$(".i.alert").removeClass("alert");
	
	if (login != "" && email != ""){
		jsonExec(sitePrefix+"/ajax/auth/recover", {login: login, email:email}, function onAjaxSuccess(json,textStatus) {
			dialogReply.dialog("close");
			showConfirmDialog("Запрос на восстановление пароля отправлен.</br> Если указанные Вами данные верны -"
			+"то на указанную почту будет отправлен код восстановления. Введите его в поле \"Код восстановления\" для продолжения процедуры либо перейдите по ссылке из письма.", "Подтверждение восстановления",
	recoverComplete, {login: login});
		
		},false, function onAjaxError(json,textStatus) {
		switch (json.code) {
			case 7504:
				showInfoDialog("Пользователь с такими данными в системе не зарегистрирован. Проверьте данные и повторите попытку или обратитесь в службу поддержки","Ошибка");
				break;
			default:
				showInfoDialog("Ошибка "+json.code+":"+json.message,"Ошибка");
				break;

			
			}
		});
	} else {
		if (login =='') { $("#pwr_login").addClass("alert").prop("title","Поле Логин обязательно для заполнения"); }
		if (email =='') { $("#pwr_email").addClass("alert").prop("title","Поле Email или Code должно быть заполнено"); }
	}
}

function recoverComplete(code, args) {
	if (!(isset(code) && isset(args) && isset(args.login))) {
		return;
	}
	dialogReply = $("<div>",{
		id: "restorePasswdForm2",
		title: "Восстановление доступаs",
		style: "overflow: hidden;"
	}).appendTo("div#dialogs")
	.dialog({
		autoOpen: true,
 		height: 290,
      	width: 350,
		modal: true,
		position: {my: "center",at: "center",of: "body"},
        close: function () {
		    //reloadGenDesc();
    		dialogReply.dialog("destroy").remove();
    	},
		buttons: {
        "Восстановить": restorePasswdComplete_click,
        "Закрыть": function() {
           dialogReply.dialog("close");
        }}
	})

	.append(
		dialogFieldGroup_add(
			dialogField_add("Логин","pwr_login")
			.add(dialogField_add("Пароль",
				$("<input>", {class: "i", type: "password", id: "new_password", name: "new_password"})
			))
			.add(dialogField_add("Пароль (еще раз)",
				$("<input>", {class: "i", type: "password", id: "new_password2", name: "new_password2"})
			))
			.add($("<input>",{value: code, id: "pwr_code", type: "hidden"}))
		));		
		markOnValChanged();
		
		$("#pwr_login")
			.val(args.login)
			.prop('disabled','true');
}

function restorePasswdComplete_click() {
		login = $("#pwr_login").val();
		code = $("#pwr_code").val();
		passwd=$("#new_password").val();
		passwd2=$("#new_password2").val();
		err=0;
		
		if (passwd.length < 8) {
			$("#new_password").addClass("alert").prop("title","Пароль не может быть меньше 8 символов");
			err++;
		}
		
		if (passwd2 !== "" && passwd2 != passwd) {
			$("#new_password2").addClass("alert").prop("title","Пароли не совпадают");
			err++;
		}
		
		if (err>0) {
			return;
		}
		
		jsonExec(sitePrefix+"/ajax/auth/change", {login: login, code:code, passwd: passwd}, function onAjaxSuccess(json,textStatus) {
			showInfoDialog("Пароль успешно обновлен. Теперь можно войти в систему.");
			dialogReply.dialog("close");
			$("#username").val(login);
			
		}, false, function onAjaxFailed(json, textStatus) {
		switch (json.code) {
			case 711:
				showInfoDialog("Код регистрации или логин не верны. Попробуйте запросить код повторно. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			case 712:
				showInfoDialog("Код регистрации или логин не верны. Попробуйте запросить код повторно. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			case 713:
				showInfoDialog("Код регистрации или логин не верны. Попробуйте запросить код повторно. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			case 714:
				showInfoDialog("Внутренняя ошибка сервера. Попробуйте повторить запрос позднее. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			default:
				showInfoDialog("Ошибка "+json.code+":"+json.message,"Ошибка");
				break;
		}
	});

}

function userRegConfirm(code, args) {
	if (isset(code) && isset(args) && isset(args.login)) {
		jsonExec(sitePrefix+"/ajax/auth/regconfirm", {code: code, login:args.login}, function onAjaxSuccess(json,textStatus) {
			showInfoDialog("Регистрация завершена успешно. Теперь можно войти в систему.");
			$("#userRegisterForm").dialog("close");
			$("#username").val(args.login);
		}, false, function onAjaxFailed(json, textStatus) {
			switch (json.code) {
			case 721:
				showInfoDialog("Код регистрации или логин не верны. Попробуйте запросить код повторно. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			case 722:
				showInfoDialog("Код регистрации или логин не верны. Попробуйте запросить код повторно. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			case 723:
				showInfoDialog("Код регистрации или логин не верны. Попробуйте запросить код повторно. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			case 724:
				showInfoDialog("Внутренняя ошибка сервера. Попробуйте повторить запрос позднее. Если проблема сохраняется - обратитесь, пожалуйста, в службу поддержки.","Ошибка");
				break;
			default:
				showInfoDialog("Ошибка "+json.code+":"+json.message,"Ошибка");
				break;
			}
		});
	}
	
}

function showConfirmDialog(message, title, callback, args) {
	
	formName="dialogConfirm";
	$("<div>",{
		id: formName,
		title: title,
		style: "overflow: hidden;"
	}).appendTo("div#dialogs")
	.dialog({
		autoOpen: true,
 		height: 250,
      	width: 350,
		modal: true,
		position: {my: "center",at: "center",of: "body"},
        close: function () {
		    //reloadGenDesc();
    		$("#"+formName).dialog("destroy").remove();
    	},
		buttons: {
        "Подтвердить": function() {
        	dcv_val =$("#dcf_code").val();
        	$("#"+formName).dialog("close"); 
        	callback(dcv_val, args);
        },
        "Отменить": function() {
           $("#"+formName).dialog("close");
        }}
	})

	.append($("<div>",{id: "div_"+formName, class: "widget", style: " background-color: inherit", html: message})
	.add(
		dialogFieldGroup_add(
			dialogField_add("Код подтверждения","dcf_code")
		)))

	$("#"+formName).dialog("option","height", $("#div_"+formName).height()+210);
	
}