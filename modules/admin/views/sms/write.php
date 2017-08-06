<div class="col-lg-6 col-md-6"><?php
	echo '
	<h2><big>', $title, '</big></h2>
	
	<form class="write" action="/', "{$this->context->module->id}/{$this->context->id}/save", ($model->isNewRecord ? '' : '?id='.$model->id), '" method="post">
		
		<input type="hidden" name="', \Yii::$app->getRequest()->csrfParam, '" value="', \Yii::$app->getRequest()->getCsrfToken(), '" />';

		echo '
		<div class="alert alert-success hidden">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			', ($model->isNewRecord ? 'Запись добавлена' : 'Изменения сохранены'), '
		</div>';
	
		$fields = [];
		$formats = app\modules\admin\models\Param::getFormats($model);
		$labels = $model->attributeLabels();
		$unsafeAttr = ['id', 'date_time', 'filter', 'users'];

		foreach ($model->attributes as $key=>$val)
			if (!in_array($key, $unsafeAttr)) {
				$fields[] = [
					'format' => $formats[$key],
					'value'  => isset($attributes[$key]) ? $attributes[$key] : $val,
					'name'   => $key,
					'label'  => isset($labels[$key]) ? $labels[$key] : $model->generateAttributeLabel($key),
					'options'=> isset($options[$key]) ? $options[$key] : false,
				];
			}
			
		if ($fields)
			echo $this->render(
				'/formfield',
				['fields'=>$fields]
			);
		
		echo '
		<div class="form-group row">
			<div class="col-lg-9 col-md-10">
				
				<label>Целевая аудитория</label>
				<div class="radio">
					<label for="t1"><input type="radio" name="_filter" id="t1"', ($model->filter >= 0 ? ' checked="checked"' : ''), '/><span>Категории трейдеров</span></label>
					<div class="form-row">
						<div class="checkbox">
							<label><input type="checkbox" name="filter[grade]" value="0"', ($model->filter == 0 ? ' checked="checked"' : ''), ' id="filter-all" /><span>Все</span></label>
						</div>					
						<div class="checkbox">
							<label><input type="checkbox" name="filter[grade]" value="1"', ($model->filter & 1 ? ' checked="checked"' : ''), ' />Новые</label>
						</div>
						<div class="checkbox">		
							<label class="radio-inline"><input type="radio" name="filter[deposit]" value="2"', ($model->filter & 2 ? ' checked="checked"' : ''), ' /><span>Бесплатные счета</span></label>
							<label class="radio-inline"><input type="radio" name="filter[deposit]" value="4"', ($model->filter & 4 ? ' checked="checked"' : ''), ' /><span>Счета с депозитами</span></label>
						</div>
						<div class="checkbox">		
							<label class="radio-inline"><input type="radio" name="filter[opt]" value="8"', ($model->filter & 8 ? ' checked="checked"' : ''), ' /><span>Без выкупа сделок</span></label>
							<label class="radio-inline"><input type="radio" name="filter[opt]" value="16"', ($model->filter & 16 ? ' checked="checked"' : ''), ' /><span>С выкупом сделок</span></label>
						</div>
					</div>
				</div>
			
				<div class="radio">
					<label for="t2"><input type="radio" name="_filter" id="t2"', ($model->filter < 0 ? ' checked="checked"' : ''), '/><span>Отдельные трейдеры</span></label>
					<div class="form-row">
						<div class="input-group">
							<input type="text" class="autocomplete form-control input-sm" value=""', (!$users ? ' disabled="disabled"' : ''), ' />
							<div class="input-group-addon"><span class="glyphicon glyphicon-search"></span></div>
						</div>
						<ul class="list-unstyled multiple-input">';
						
							foreach ($users as $u) echo '
							<li>', "{$u->last_name} {$u->first_name} {$u->mid_name}", '  <a class="close" href="">&times;</a></li>';
							
						echo '
						</ul>
						<input type="hidden" name="users" value="', ($users ? implode(',', array_keys($users)) : ''), '" />
					</div>
				</div>
			
			</div>
		</div>';		
			
		echo '
		<br/>
		<div class="form-group">
			<input class="btn btn-primary btn-sm" type="submit" value="Сохранить" />
		</div>';
?>
	</form>
</div>
<div class="col-lg-6 col-md-6">
	<a href="<?php echo "/{$this->context->module->id}/{$this->context->id}" ?>" title="Назад">
		<h2><big><span class="glyphicon glyphicon-circle-arrow-left"></span></big></h2>
	</a>
</div>

<script>
	if ($('#filter-all').prop('checked'))
		$('#filter-all').parents('.form-row').find('input').not('#filter-all').prop({disabled: true});
	var formRows = $('.radio > .form-row').toArray();

	var itemList = <?php echo $users ? '['.implode(',', array_keys($users)).']' : '[]' ?>;
	var cancelItem = function(e) {
		e.stopPropagation();
		e.preventDefault();
		itemList.splice($(this).parent().index(), 1);
		$('[name=users]').val(itemList.join());	
		$(this).parent().remove();
	};
	
	$('.multiple-input a').click(cancelItem);
	
	$('.autocomplete').each(function() {
		$(this).autocomplete({
			source: '/secret/users/search',
			select: function(e, ui) {
				if (ui.item && $.inArray(ui.item.id, itemList) < 0) {
					var a = $('<a class="close" href="">&times;</a>').click(cancelItem);
					itemList.push(ui.item.id);
					$('<li>' + ui.item.label + '</li>').append(a).addBack().appendTo($('.multiple-input'));			
					$('[name=users]').val(itemList.join());	
				}
				return false;
			},
			close: function(e, ui) {
				$('.autocomplete').val('');
			}			
		});
	});
	
	$('#filter-all').click(function(e) {
		$(this).parents('.form-row').find('input').not(this).prop({disabled: this.checked, checked: false});		
	});	
	$('[name=_filter]').each(function(index) {
		$(this).click(function(e) {
			e.stopPropagation();
			$(formRows[1 -index]).find('input').prop({disabled: true});
			$(formRows[index]).find('input').prop({disabled: false});
			if (index == 0)
				$('#filter-all').click().prop({checked: true});
		});
	});
	
	$('[name=_filter]').filter(':checked').click();
	
	// Form serialization
	// Validation and saving request
	$('form').submit(function(e) {
		var params = {};
		var formValid = true;
		var form = this;
		
		e.preventDefault();
		$(form).find(':submit').prop({disabled: true});
		
		$(this).find('.has-error').removeClass('has-error').removeClass('has-feedback');
		$(this).find('.form-control-feedback').text('');
		
		$(this).find('[type=hidden], textarea').each(function() {
			params[this.name] = $.trim(this.value);
		});

		$(this).find(':radio, :checkbox').not(':disabled').each(function() {
			var value = !this.checked ? 0 : parseInt(this.value);
			var name = this.name.replace(/\[\D+\]/, '');
			var key = this.name.replace(name, '').replace(/[\[\]]+/, '');
			if (!key)
				params[name] = value;
			else {
				if (!params[name])
					params[name] = {};
				if (!params[name][key])
					params[name][key] = value;
			}
		});
		//console.log(params); return;
		if (formValid) {
			window.Dialog.showProc();
			$.post($(this).attr('action'), params)
			 .done(function(resp) {
				if (resp == 1) {
					$('.alert-success').removeClass('hidden');
					window.Dialog.close(function() {
						$('body, html').animate({scrollTop: 0});
					});
				} else {
					try {
						resp = $.parseJSON(resp);
						$(form).find(':submit').prop({disabled: false});
						window.Dialog.close(function() {
							$('body, html').animate({scrollTop: 0});
						});
						console.log(params);
						var errMessage = '';
						for (var key in resp) {
							errMessage = resp[key].substr(0, 60);
							if (errMessage.substr(-1) == '.')
								errMessage = errMessage.substr(0, (errMessage.length - 1));
							$(form).find('[name^=' + key + ']').first().parents('.form-group').addClass('has-error').addClass('has-feedback').find('.form-control-feedback').text(errMessage);
						}
					} catch (e) {
						window.Dialog.close();
						alert($.trim(resp) ? resp : 'Произошла ошибка');
						console.log(resp);
					}
				}
			 })
			 .fail(function() {
					window.Dialog.close();
					alert('Удаленный сценарий не отвечает');
			 });
		}
	});
</script>