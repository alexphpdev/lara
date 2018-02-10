@extends('app')

@section('content')
<div class="container-fluid">
<div class="row">
	<div class="header">
		<div class="header_title">
			Фамилия Имя Отчество, Должность
		</div>
		@guest
			<a id='login' href="{{ route('login') }}">Войти</a>
		@else
			<a id='logout' href="{{ route('logout') }}"
			    onclick="event.preventDefault();
			             document.getElementById('logout-form').submit();">
			    Выйти
			</a>

			<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
			    {{ csrf_field() }}
			</form>
		@endguest
	</div>
	@foreach($employees as $employee)
		<div class="item lvl<?= $employee->deep_level ?>" >
			{{ $employee->f }} 
			{{ $employee->i }} 
			{{ $employee->o }}, 

			{{ $employee->position }}
		</div><br>
	@endforeach
</div>
</div>
@stop