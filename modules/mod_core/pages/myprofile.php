<style>
#item_tabs div.widget {
    margin-top: 4px;
    min-height: 50px;
}

#accordion div.widget {
    margin-top: 5px;
    min-height: 50px;
}

</style>
<div class="widget_panel_left">


<div id="accordion" class="accordion">
	<h3 class="chevron">Профиль
	<div class="button short super hide" id="b_gdescedit" title="Редактировать описание"><i class="fas fa-edit"></i></div>
	<div class="button short super hide" id="b_gdesceditsave" title="Сохранить"><i class="fas fa-save"></i></div>
	<div class="button short super hide" id="b_gdesceditcancel" title="Отменить"><i class="fas fa-undo"></i></div>
	</h3>
	<div class="widget lock c_contacts" id="gendesc"><p>Информация загружается</p></div>
	
</div>
</div>

<div class="widget_panel_right">

<div id="item_tabs" class="ui-tabs-main-div" style="display: none">
<div class="ui-tabs-title-line">
<div style="display: inline-block; float: left;">
  <ul>
    <li><a href="#tab-notifications">Уведомления</a></li>
     <li><a href="#tab-docs">Документы и бланки</a></li>	
 </ul>
</div>

  <div id="tab_buttons" style="display: inline-block; float: right">
	<span class="button_block" id="buttons_notifications">
<?php /*  <div class="button short super" id="b_addnewobject" title="Добавить объект" onclick="itemEdit_gendesc_click('add')"><i class="fas fa-plus" ></i><i class="fab fa-buromobelexperte" ></i></div>
      <div class="button short super" id="b_paste" onclick="pasteHash()" title="Перенести сюда объект по фокс-ссылке"><i class="fas fa-paste"></i></div>
      <div class="button short super" id="b_objtogglehidden" onclick="objtoggleHidden()" title="Показать/скрыть пустые строки"><i class="fas fa-eye"></i></div>
*/?>
    </span>	
</div>  
</div>

<div id="tab-notifications" buttons="buttons_notifications">
	<div class="widget">
	<p>Уведомлений пока нет</p>
	</div>
	
</div>

<div id="tab-docs" buttons="buttons_docs">
	<div class="widget">
			<p>Данные загружаются</p>
	</div>
	
</div>
</div>
</div>
