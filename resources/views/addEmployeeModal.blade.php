<div id="addEmployeeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="/secret/adminPage/addEmployee" method="post" enctype="multipart/form-data">
      {{ csrf_field() }}
      <input type="hidden" name="MAX_FILE_SIZE" value="1073741824" /> <!-- 1 * 1024 * 1024 * 1024 = 1073741824 = 1 Mib -->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Добавление нового работника</h4>
      </div>
      <div class="modal-body">
        <div class="imageWrapper">
        	<div class="image">
        		<span class="image_helper"></span><!-- 
        		--><img class="avatar_source" src="/img/avatars/source/noAvatar.png" max-width="100%">
        	</div>
        	<div class="image_buttons">
                <input type="file" name="avatar" class="avatar" accept="image/jpeg">
        		<button type="button" class="btn btn-default openImg">Загрузить фото</button>
        		<button type="button" class="btn btn-default pull-right remove_avatar">Удалить фото</button>
                @if(@$error['image'])<div class="error" style="text-align: center;">{{@$error['image']['error_msg']}}</div>@endif
                @if(@$error['db'])<div class="error" style="text-align: center;">{{@$error['db']['error_msg']}}</div>@endif
        	</div>
        </div>
        <div class="dataWrapper">
        	<div class="form-group">
        	    <label for="employee_f">Фамилия</label>
                <input type="text" class="form-control" id="employee_f" name="employee_f" placeholder="Фамилия" value="{{@$error['employee_f']['value']}}">
                @if(@$error['employee_f'])<div class="error">{{@$error['employee_f']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="employee_i">Имя</label>
        	    <input type="text" class="form-control" id="employee_i" name="employee_i" placeholder="Имя" value="{{@$error['employee_i']['value']}}">
                @if(@$error['employee_i'])<div class="error">{{@$error['employee_i']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="employee_o">Отчество</label>
        	    <input type="text" class="form-control" id="employee_o" name="employee_o" placeholder="Отчество" value="{{@$error['employee_o']['value']}}">
                @if(@$error['employee_o'])<div class="error">{{@$error['employee_o']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="position">Должность</label>
        	    <input type="text" class="form-control" id="position" name="position" placeholder="Должность" value="{{@$error['position']['value']}}">
                @if(@$error['position'])<div class="error">{{@$error['position']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="salary">Зарплата</label>
        	    <input type="text" class="form-control" id="salary" name="salary" placeholder="Зарплата" value="{{@$error['salary']['value']}}">
                @if(@$error['salary'])<div class="error">{{@$error['salary']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group">
        	    <label for="addEmployeeModal_datetimepicker">Дата приёма на работу</label>
        	    <input type='text' class="form-control" id='addEmployeeModal_datetimepicker' name="hiring_date" value="{{@$error['hiring_date']['value']}}"/>
                @if(@$error['hiring_date'])<div class="error">{{@$error['hiring_date']['error_msg']}}</div>@endif
        	</div>
        	<div class="form-group selectpickerWrapper">
        	    <label for="selectpicker">Начальник</label>
                <input id="selectpicker" class="form-control" name="boss_string" placeholder="Введи Фамилию или Имя или Отчество начальника" value="{{@$error['boss']['value_boss_string']}}">
                @if(@$error['boss'])<div class="error">{{@$error['boss']['error_msg']}}</div>@endif
                <input type="hidden" id="selectpicker_id" name="boss_id" value="{{@$error['boss']['value_boss_id']}}">
                <input type="hidden" id="selectpicker_employee_id" name="boss_employee_id" value="{{@$error['boss']['value_boss_employee_id']}}">
                <input type="hidden" id="selectpicker_deep_level" name="boss_deep_level" value="{{@$error['boss']['value_boss_deep_level']}}">
        	</div>

            
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Нанять работника</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
      </div>
    </form>
    </div>
  </div>
</div>