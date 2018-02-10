<div class="itemWrapper">
	<div class="avatar">
		<span class="image_helper"></span><!-- 
		 --><img src="{{$employee->img}}">
	</div>
	<div class="item draggable @if($employee->deep_level < 4) droppable @endif lvl{{$employee->deep_level}}">
		#{{ $employee->employee_id }}, 

		{{ $employee->f }} 
		{{ $employee->i }} 
		{{ $employee->o }}, 

		{{ $employee->position }}, 

		{{ $employee->salary }},

		{{ $employee->hiring_date }}
	</div>
	<a href="#" data-toggle="modal" data-target="#editEmployeeModal" onclick="event.preventDefault();" data-item-id="{{$employee->id}}">
		<span class="glyphicon glyphicon-pencil pencil" aria-hidden="true"></span>
	</a>
</div>