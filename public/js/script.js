$(function(){

	// необходим для правильного вывода дочерних элементов после сортировки или сброса фильтров
	var globalSortType = {
		field: 'employee_id',
		type: 'asc'
	};

	$('#addEmployeeModal').on('scroll', function(){
		$('.ui-autocomplete').css('display', 'none');
	})

	$('#addEmployeeModal').on('click', function(){
		$('.ui-autocomplete').css('display', 'none');
	})


	$('#edit_selectpicker').on('input', function(){ 
		if($(this).val()=='') $('#edit_selectpicker_employee_id').val('')
	})


	var datetimepicker = {
		format: 'DD-MM-YYYY',
		locale: moment().locale('ru.js'),
	}

	$('#addEmployeeModal_datetimepicker')
		.datetimepicker(datetimepicker)
		.datetimepicker('date', new Date())
	$('#editEmployeeModal_datetimepicker')
		.datetimepicker(datetimepicker)
		.datetimepicker('date', $(this).val()) 



	$('#selectpicker').selectpicker({
	  noneSelectedText: 'Введи Фамалию или Имя или Отчество начальника'
	});


	$('#searchForm').on('submit', function(e){
		e.preventDefault();
		window.location.href = $(this).attr('action') + $(this).find('input').val();
	})


	$.ajaxSetup({
	    headers: {
	        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	    }
	});


	$('.headerLink').on('click', function(e){
		e.preventDefault();
		showSortedData(this);
	})

	$('.reset_filters').on('click', function(e){
		e.preventDefault();
		resetFilters(this);
	})

	$('.submitSearch').on('click', function(e){
		e.preventDefault();
		$form = $(this).parents('#searchForm');
		showFindedData($form.find('.searchField').eq(0).val());
	})


	// Открывает модальное окно, если произошла ошибка при добавлении нового работника
	if(window.location.href.split('/')[5] == 'addEmployee') {
		$('#addEmployeeModal').modal('show');
	}

	// Открывает модальное окно, если произошла ошибка при изменении данных работника
	if(window.location.href.split('/')[5] == 'updateEmployee') {
		$('#editEmployeeModal').modal('show');
	}

	$('.fire_employee').on('click', function(){

		var fio = $('#edit_employee_f').val() + ' ' + $('#edit_employee_i').val() + ' ' + $('#edit_employee_o').val();

		if(!confirm("Вы уверены, что нужно уволить " + fio + "?")) return;

		var employee_id = $('#editEmployeeModal').find(".employee_id").val();
		window.location = '/secret/adminPage/fired/' + employee_id;
	})

	$('#editEmployeeModal .remove_avatar').on('click', function(){
		var employee_id = $('#editEmployeeModal .employee_id').eq(0).val();
		$.ajax({
			type: 'post',
			data: {'employee_id': employee_id},
			url: '/secret/adminPage/remove_avatar',
			success: function(id){
				$(".itemWrapper a[data-item-id='"+id+"']")
					.parents(".itemWrapper")
					.eq(0)
					.find('.avatar img')
					.attr('src', '/img/avatars/thumbnails/noAvatar.png')
			}
		})
	})

	$( "#selectpicker" )
		.autocomplete({
			source: [{id: '', employee_id: '', label: '', deep_level: ''}], 
			position: { my : "left bottom", at: "left top" },
			select: function( event, ui ) {
		        $( "#selectpicker_id" ).val( ui.item.id );
		        $( "#selectpicker_employee_id" ).val( ui.item.employee_id );
		        $( "#selectpicker" ).val( ui.item.label );
		        $( "#selectpicker_deep_level" ).val( ui.item.deep_level );
		 
		        return false;
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" )
		        .append( "<div>" + item.label + "</div>" )
		        .appendTo( ul );
		};

	$( "#edit_selectpicker" )
		.autocomplete({
			source: [{id: '', employee_id: '', label: '', deep_level: ''}], 
			position: { my : "left bottom", at: "left top" },
			select: function( event, ui ) {
		        $( "#edit_selectpicker_id" ).val( ui.item.id );
		        $( "#edit_selectpicker_employee_id" ).val( ui.item.employee_id );
		        $( "#edit_selectpicker" ).val( ui.item.label );
		        $( "#edit_selectpicker_deep_level" ).val( ui.item.deep_level );
		 
		        return false;
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" )
		        .append( "<div>" + item.label + "</div>" )
		        .appendTo( ul );
		};

	$( "#selectpicker, #edit_selectpicker" ).on('input', function(){
		var $this = $(this);
		var searchString = $this.val();
		if(searchString.length < 3) return;

		$.ajax({
			type: 'post',
			data: {s: searchString},
			url: '/secret/adminPage/searchBosses',
			success: function(res){
				res = JSON.parse(res);
				
				var data = [];
				for(var i in res.bosses){

					var boss = res.bosses[i];
					var label = boss.f + ' ' + boss.i + ' ' + boss.o + ', ' + boss.position;

					data.push({
						id: boss.id,
						employee_id: boss.employee_id,
						label: label,
						deep_level: boss.deep_level,
					});

					$this.autocomplete( "option", "source", data );
				}

			}
		})
	})


	$('.openImg').on('click', openAddImgDialog);


	$('.avatar').on('change', showPreview)

	$('.remove_avatar').on('click', removeAvatar);

	$(document).on('click', '.itemWrapper a', showEditEmployeeForm);

	$(document).on('click', '.item.lvl1', loadTree)













	function displaySelection(employees) {
		var html = '';

		if ($.isEmptyObject(employees)){
			html = '<div class="notFound">Ничего не найдено!</div>';
		} else {
			for(var employee in employees){
				var employee = employees[employee];
				var droppable = employee.deep_level < 4 ? 'droppable' : ''; 


				html += '<div class="itemWrapper">';
					html += '<div class="avatar">';
						html += '<span class="image_helper"></span>';
						html += '<img src="' + employee.img + '">';
					html += '</div>';
					html += '<div class="item lvl' + employee.deep_level + ' '+droppable+' draggable">';
					html += '#' + employee.employee_id + ', ';
					html += employee.f + ' ' + employee.i + ' ' + employee.o + ', ';
					html += employee.position + ', ';
					html += employee.salary + ', ';
					html += employee.hiring_date;
					html += '</div>';
					html += '<a href="#" data-toggle="modal" data-target="#editEmployeeModal" onclick="event.preventDefault();" data-item-id="'+employee.id+'">';
						html += '<span class="glyphicon glyphicon-pencil pencil" aria-hidden="true"></span>'
					html += '</a>';
				html += '</div>'; 
			}
		}

		return html;
		// $(".items").empty().append(html);
	}

	function resetFilters(self) {
		var $self = $(self);
		var url = '/secret/adminPage/reset_filters';
		$.ajax({
			url: url,
			type: 'post',
			beforeSend: function(){
			    $('#preloader, #dark_bg').fadeIn( "slow" );
			},
			success: function(res){
				globalSortType = {
					field: 'employee_id',
					type: 'asc'
				};

				$('#preloader, #dark_bg').fadeOut( "slow" );

				res = JSON.parse(res);

				$(".header .caret").remove();
				$(".headerLink").each(function(){
				    var href = $(this).attr('href');
				    href = href.split('/');
				    href[5] = 'asc';
				    href = href.join('/');
				    $(this).attr('href', href);
				});


				var html = displaySelection(res.employees);
				$(".items").empty().append(html);

				dnd();

				$("#searchForm input").val('');
			}
		})
	}


	function showSortedData(self) {
		var $self = $(self);
		var url = $self.attr('href');
		
		var howSort = {
			field: url.split('/')[4],
			type: url.split('/')[5]
		};

		$("#searchForm .searchField").val('');

		$.ajax({
			url: url,
			type: 'post',
			beforeSend: function(){
			    $('#preloader, #dark_bg').fadeIn( "slow" );
			},
			success: function(res){
				globalSortType = howSort;

				$('#preloader, #dark_bg').fadeOut( "slow" );

				res = JSON.parse(res);

				// меняет урл для сортировки в противоположную сторону при повторном нажатии
				var href = url.split('/');
				href[5] = res.setType;
				href = href.join('/');
				$self.attr('href', href);
				// --


				// добавляет треугольничек для индикации сортировки
				$(".header .caret").remove();
				var text = $self.text();
				var caretClass = 'caret';
				if(res.setType == 'desc'){
					caretClass += ' caret_up';
				}
				var caret = "<span class='"+caretClass+"'></span> ";
				text = caret + text;
				$self.html(text); 
				// --


				var html = displaySelection(res.employees);
				$(".items").empty().append(html);

				dnd();

			}
		})
	}


	function showFindedData(searchString){
		
		if(!searchString) return;

		var url = '/secret/adminPage/search/' + searchString;

		var data = {
			'field': globalSortType.field,
			'type': globalSortType.type
		};

		//$(".header .caret").remove();
		$.ajax({
			type: 'post',
			url: url,
			data: data,
			beforeSend: function(){
			    $('#preloader, #dark_bg').fadeIn( "slow" );
			},
			success: function(res){
				$('#preloader, #dark_bg').fadeOut( "slow" );

				res = JSON.parse(res);

				var html = displaySelection(res.employees);
				$(".items").empty().append(html);

				dnd();
			}
		});
	}


	function openAddImgDialog(){

		$(this).siblings('.avatar').eq(0).trigger('click');
	}


	function showPreview(){
		var input = this;
		if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $(input).parents('.imageWrapper').eq(0).find("img.avatar_source").attr('src', e.target.result);
            }
            
            reader.readAsDataURL(input.files[0]);
        }
	}

	function removeAvatar(){
		$('.avatar').val('');
		$('.avatar_source').attr('src', '/img/avatars/source/noAvatar.png');
	}

	function showEditEmployeeForm(){
		var $this = $(this);
		var item_id = $this.data('item-id');

		$.ajax({
			type:'post',
			data: {'item_id' : item_id},
			url: '/secret/adminPage/getDataForEditForm',
			success: function(res){
				res = JSON.parse(res);
				var item = res.item_info;
				
				$('.employee_id').val(item.employee_id);

				$('#editEmployeeModal .avatar_source').attr('src', item.img);
				$('#editEmployeeModal .old_avatar').val(item.img);

				$('#edit_employee_f').val(item.f);
				$('#edit_employee_i').val(item.i);
				$('#edit_employee_o').val(item.o);

				$('#edit_position').val(item.position);
				$('#edit_salary').val(item.salary);

				$('#editEmployeeModal_datetimepicker').val(item.hiring_date);

				var bossString = item.boss_f + ' ' + item.boss_i + ' ' + item.boss_o + ', ' + item.boss_position;
				$('#edit_selectpicker').val(bossString);

				$('#edit_selectpicker_id').val(item.boss_id);
				$('#edit_selectpicker_employee_id').val(item.boss_employee_id);
				$('#edit_selectpicker_deep_level').val(item.boss_deep_level);

			}
		})
	}


	function dnd(){
		$( ".draggable" ).draggable({
		  cursor: "move",
		  opacity: 0.3,
		  helper: function( event ) {
		    return $(this).parents('.itemWrapper').clone();
		  }
		});
		$( ".droppable" ).droppable({
		  accept: ".draggable",
		  over: function(event, ui) {
	        $(this).parents('.itemWrapper').css('background-color', 'rgba(69,177,69, .45)');
	      },
	      out: function(event, ui) {
	        $(this).parents('.itemWrapper').css('background-color', 'inherit');
	      },
		  drop: function( event, ui ) {
		  	$(this).parents('.itemWrapper').css('background-color', 'inherit');

		    var id = ui.draggable.siblings('a').eq(0).data('item-id');
		    var boss_id = $(this).siblings('a').eq(0).data('item-id');

		    changeBoss(id, boss_id);
		  }
		});
	}

	dnd();


	function changeBoss(id, boss_id) {
		var data = {
			'id' : id,
			'boss_id' : boss_id
		};

		$.ajax({
			type: 'post',
			data: data,
			url: '/secret/adminPage/changeBoss',
			success: function(res){
				res = JSON.parse(res);

				if(!res) return;
				
				// если перетаскиваем суперБосса
				if(res.new_super_boss_id) {

					// удаляем старого босса
					$("div.lvl0").parents('.itemWrapper').remove()

					var $superBoss = $("a[data-item-id='"+res.new_super_boss_id+"']");
					var $bossItem = $superBoss.siblings('.item');
					if($bossItem.hasClass('opened')) $bossItem.trigger('click');

					$bossItem.removeClass(function (index, className) {
				    	return (className.match (/(^|\s)lvl\d+/g) || []).join(' ');
					});
					$bossItem.addClass('lvl0');
					$superBoss.parents('.itemWrapper').prependTo('.items');
				}

				var $dragParentLvl1;
				var $dragItem = $("a[data-item-id='"+data.id+"']").parents('.itemWrapper');

				var $dropParentLvl1;
				var $dropItem = $("a[data-item-id='"+res.new_boss_id+"']").parents('.itemWrapper');

				if($dragItem.has(".item.lvl1").size()){
					$dragParentLvl1 = $dragItem;

					// закрыть и удалить
					if($dragParentLvl1.find(".item.lvl1.opened").size()) $dragParentLvl1.find(".item.lvl1.opened").trigger('click');
					$dragParentLvl1.remove();
				} else {
					$dragParentLvl1 = $dragItem.prevAll('.itemWrapper').has(".item.lvl1").eq(0);


					// если перемещяем работника на 1ый уровень, т.е. в подчинение суперБоссу
					if($dropItem.has(".item.lvl0").size()){
						
						$dragItem.insertAfter($("div.lvl0").parents('.itemWrapper'));
						$dragItem.find('.item').removeClass(function (index, className) {
				    		return (className.match (/(^|\s)lvl\d+/g) || []).join(' ');
						});
						$dragItem.find('.item').addClass('lvl1');

					}



					// закрыть открыть
					$dragParentLvl1.find(".item.lvl1").trigger('click');
					$dragParentLvl1.find(".item.lvl1").trigger('click');
				}

				if($dropItem.has(".item.lvl1").size()){
					$dropParentLvl1 = $dropItem;
				} else {
					$dropParentLvl1 = $dropItem.prevAll('.itemWrapper').has(".item.lvl1").eq(0);
				}

				if($dragParentLvl1 != $dropParentLvl1){
					// закрыть открыть
					if($dropParentLvl1.find(".item.lvl1.opened").size() == 1){
						$dropParentLvl1.find(".item.lvl1").trigger('click');
						$dropParentLvl1.find(".item.lvl1").trigger('click');
					}else{
						// открыть
						$dropParentLvl1.find(".item.lvl1").trigger('click');
					}
				}
			}
		})
	}



	function loadTree() {
		var $parent = $(this);

		if($parent.hasClass('opened')) {


			var startIndex = $parent.parents('.itemWrapper').index();
			var endIndex = $('.itemWrapper').eq(startIndex).nextAll('.itemWrapper').has(".item.lvl1").eq(0).index();

			if(endIndex == -1) endIndex = $('.itemWrapper').size();

			$('.itemWrapper').each(function(i, el){
				if(i <= startIndex || i >= endIndex) return;

				$(el).remove();
			});

		 	$parent.toggleClass('opened');


		 	return;
		 }

		var id = $parent.siblings('a').eq(0).data('item-id');

		var data = {
			'id': id,
			'field': globalSortType.field,
			'type': globalSortType.type
		}; 
		
		$.ajax({
			type: 'post',
			data: data,
			url: '/secret/adminPage/getTree',
			beforeSend: function(){ $('#preloader, #dark_bg').fadeIn(  );},
			success: function(res) {

				$('#preloader, #dark_bg').fadeOut(  );

				res = JSON.parse(res);
				if(!res.employees) return;

				var html = displaySelection(res.employees);

				$(html).insertAfter($parent.parents('.itemWrapper'));

				dnd();

				$parent.toggleClass('opened');

			}
		})
	}

})