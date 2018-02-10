@extends('app')

@section('content')
<div class="container-fluid">
<div class="row">
	<div class="header">
		{!! $menu !!}
		<form id="searchForm" class="form-inline" action="/secret/adminPage/search/">
		  <div class="form-group">
		    <input type="text" class="form-control searchField" placeholder="Поиск">
		  </div>
		  <button type="submit" class="btn btn-default submitSearch"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
		</form>

		<a href="/secret/adminPage" class="reset_filters"> Сбросить фильтры </a>

		<button 
			type="button" 
			id="showAddEmployeeModal" 
			class="btn btn-success" 
			data-toggle="modal" 
			data-target="#addEmployeeModal">
		Добавить работника
		</button>

		@include('addEmployeeModal')
		@include('editEmployeeModal')

		<a id='logout' href="{{ route('logout') }}"
		    onclick="event.preventDefault();
		             document.getElementById('logout-form').submit();">
		    Выйти
		</a>

		<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
		    {{ csrf_field() }}
		</form>

		<div class="items">
			@each('employeeItem', $employees, 'employee', 'employeesEmpty')
		</div>
</div>
</div>
@stop