<div id="editEmployeeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="/secret/adminPage/updateEmployee" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <input type="hidden" name="MAX_FILE_SIZE" value="1073741824" /> <!-- 1 * 1024 * 1024 * 1024 = 1073741824 = 1 Mib -->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="edit_myModalLabel">Изменение данных сотрудника</h4>
      </div>
      <div class="modal-body">
        <div class="imageWrapper">
        	<div class="image">
        		<span class="image_helper"></span><!-- 
        		-->@if(@$error['old_avatar'])<img class="avatar_source" src="{{@$error['old_avatar']['src']}}"><!-- 
                -->@else<img class="avatar_source" src="/img/avatars/source/noAvatar.png">
                @endif
        	</div>
        	<div class="image_buttons">
                <input type="file" name="avatar" class="avatar" accept="image/jpeg">
                <input type="hidden" name="old_avatar" class="old_avatar" value="{{@$error['old_avatar']['src']}}">
                <input type="hidden" name="employee_id" class="employee_id" value="{{@$error['edit_employee']['id']}}">
        		<button type="button" class="btn btn-default openImg">Загрузить фото</button>
        		<button type="button" class="btn btn-default pull-right remove_avatar">Удалить фото</button>
                @if(@$error['edit_image'])<div class="error" style="text-align: center;">{{@$error['edit_image']['error_msg']}}</div>@endif
                @if(@$error['db'])<div class="error" style="text-align: center;">{{@$error['db']['error_msg']}}</div>@endif
        	</div>
        </div>
        <div class="dataWrapper">
        	<div class="form-group">
        	    <label for="edit_employee_f">Фамилия</label>
                <input type="text" class="form-control" id="edit_employee_f" name="edit_employee_f" placeholder="Фамилия" value="{{@$error['edit_employee_f']['value']}}">
                @if(@$error['edit_employee_f'])<div class="error">{{@$error['edit_employee_f']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="edit_employee_i">Имя</label>
        	    <input type="text" class="form-control" id="edit_employee_i" name="edit_employee_i" placeholder="Имя" value="{{@$error['edit_employee_i']['value']}}">
                @if(@$error['edit_employee_i'])<div class="error">{{@$error['edit_employee_i']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="edit_employee_o">Отчество</label>
        	    <input type="text" class="form-control" id="edit_employee_o" name="edit_employee_o" placeholder="Отчество" value="{{@$error['edit_employee_o']['value']}}">
                @if(@$error['edit_employee_o'])<div class="error">{{@$error['edit_employee_o']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="edit_position">Должность</label>
        	    <input type="text" class="form-control" id="edit_position" name="edit_position" placeholder="Должность" value="{{@$error['edit_position']['value']}}">
                @if(@$error['edit_position'])<div class="error">{{@$error['edit_position']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="edit_salary">Зарплата</label>
        	    <input type="text" class="form-control" id="edit_salary" name="edit_salary" placeholder="Зарплата" value="{{@$error['edit_salary']['value']}}">
                @if(@$error['edit_salary'])<div class="error">{{@$error['edit_salary']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="editEmployeeModal_datetimepicker">Дата приёма на работу</label>
        	    <input type='text' class="form-control" id='editEmployeeModal_datetimepicker' name="edit_hiring_date" value="{{@$error['edit_hiring_date']['value']}}"/>
                @if(@$error['edit_hiring_date'])<div class="error">{{@$error['edit_hiring_date']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group selectpickerWrapper">
        	    <label for="edit_selectpicker">Начальник</label>
                <input id="edit_selectpicker" class="form-control" name="edit_boss_string" placeholder="Введи Фамилию или Имя или Отчество начальника" value="{{@$error['edit_boss']['value_boss_string']}}">
                @if(@$error['edit_boss'])<div class="error">{{@$error['edit_boss']['error_msg']}}</div>@endif
                <input type="hidden" id="edit_selectpicker_id" name="edit_boss_id" value="{{@$error['edit_boss']['value_boss_id']}}">
                <input type="hidden" id="edit_selectpicker_employee_id" name="edit_boss_employee_id" value="{{@$error['edit_boss']['value_boss_employee_id']}}">
                <input type="hidden" id="edit_selectpicker_deep_level" name="edit_boss_deep_level" value="{{@$error['edit_boss']['value_boss_deep_level']}}">
        	</div>

            
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger pull-left fire_employee">Уволить сотрудника</button>
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
      </div>
    </form>
    </div>
  </div>
</div>