<div class="col-lg-7 col-md-8"><?php
	
	//$this->context->module->thread->title .= (' – '.($model->isNewRecord ? 'добавление' : 'редактирование'));
	
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
		
		foreach ($model->attributes as $key=>$val)
			if ($model->isAttributeSafe($key)) {
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

		<div class="form-group">
			<input class="btn btn-primary btn-sm" type="submit" value="Сохранить" />
		</div>';
?>
	</form>
</div>
<div class="col-lg-4 col-md-4">
	<a href="<?php echo "/{$this->context->module->id}/{$this->context->id}" ?>" title="Назад">
		<h2><big><span class="glyphicon glyphicon-circle-arrow-left"></span></big></h2>
	</a>
</div>

<script>
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
		
		$(this).find('[type=text], [type=hidden], select, textarea').each(function() {
			var name = this.name.replace(/\[\d+\]/, '');
			if (typeof params[name] == 'string')
				params[name] = [params[name], $.trim(this.value)];
			else if (typeof params[name] == 'object')
				params[name].push($.trim(this.value));					
			else	
				params[name] = $.trim(this.value);
		});

		$(this).find(':radio, :checkbox').each(function() {
			var value = !this.checked ? 0 : this.value;
			var name = this.name.replace(/\[\d+\]/, '');
			if (typeof params[name] == 'string')
				params[name] = [params[name], $.trim(value)];
			else if (typeof params[name] == 'object')
				params[name].push($.trim(value));					
			else	
				params[name] = $.trim(value);
		});

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
						console.log(resp);
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