<?php
$user = \Yii::$app->user->identity;

if ($user->pay_card) {
	$pay_card_splitted = \explode("\n", \chunk_split($user->pay_card, 4, "\n"));
	if (isset($pay_card_splitted[4]))
		$pay_card_splitted[3] = $pay_card_splitted[3] . $pay_card_splitted[4];
 } else
	$pay_card_splitted = \array_fill(0, 4, '');
?> 
<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">

	<h1><?php echo \Yii::$app->thread->title ?></h1>

	<p></p>
	
	<form role="form" id="profile-form" method="post" action="/<?php echo yii\helpers\Url::to("{$this->context->id}/save") ?>">
		
		<input type="hidden" name="<?php echo \Yii::$app->getRequest()->csrfParam ?>" value="<?php echo \Yii::$app->getRequest()->getCsrfToken() ?>" />
		
		<h2>Личные данные</h2>	
		<div class="form-group required">
			<label for="last_name">Фамилия<sup class="grey">*</sup></label>
			<input id="last_name" type="text" class="form-control input-md" name="last_name" value="<?php echo $user->last_name ?>" />
			<div class="form-control-feedback"></div>
		</div>
		<div class="form-group required">
			<label for="first_name">Имя<sup class="grey">*</sup></label>
			<input id="first_name" type="text" class="form-control input-md" name="first_name" value="<?php echo $user->first_name ?>" />
			<div class="form-control-feedback"></div>
		</div>
		<div class="form-group required">
			<label for="mid_name">Отчество<sup class="grey">*</sup></label>
			<input id="mid_name" type="text" class="form-control input-md" name="mid_name" value="<?php echo $user->mid_name ?>" />
			<div class="form-control-feedback"></div>
		</div>
		
		<div class="form-group row">
			<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12" for="issue_date">Дата рождения<sup class="grey">*</sup></label>
			<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4 required">
				<input id="birth_date" type="text" class="form-control input-md" name="birth_date" maxlength="10" value="<?php echo $user->birth_date ? \date('d/m/Y', strtotime($user->birth_date)) : '' ?>" />
			</div>
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="form-control-feedback"></div>
			</div>
		</div>
		<script>
			$('#birth_date').datepicker({
				hideIfNoPrevNext: true,
				changeYear: true,
				changeMonth: true,
				showWeek: false,
				firstDay: 1,
				yearRange: '1970:<?php echo (date('Y') - 16) ?>',
				dateFormat: 'dd/mm/yy'											
			});
		</script>										
		
		<br/>
		
		<h2>Контактная информация</h2>	
		<div class="group">
			<label for="phone">Мобильный телефон<sup class="grey">*</sup></label>
			<input type="hidden" name="phone" value="<?php echo $user->phone ?>" />
			<div class="input-group required">
				<span class="input-group-addon text">+7</span>
				<input disabled="disabled" id="phone" type="text" class="form-control input-md" name="phone" value="<?php echo $user->phone ?>" placeholder="например, 905-111-2233" />
				<?php if ($user->grade & pow(2, 3)) echo '<small class="input-ok glyphicon glyphicon-ok"></small>' ?>
			</div>

			<div style="margin-top: 6px" class="text-left">
				<a href="" style="color:green" onclick="$(this).prop({disabled: true}); $('#phone').prop({disabled: false}).focus(); return false;">Изменить</a>
			</div>
			
			<div class="form-control-feedback"></div>
		</div>
		
		<div class="form-group">
			<label for="email">Эл. почта<sup class="grey">*</sup></label>
			<div class="input-group col-lg-12 required">
				<input id="email" type="text" class="form-control input-md" name="email" value="<?php echo $user->email ?>" />
				<?php if ($user->grade & pow(2, 4)) echo '<small class="input-ok glyphicon glyphicon-ok"></small>' ?>
			</div>
			<div class="form-control-feedback"></div>
		</div>
		
		<?php
		
		// Вывод средств на карту доступен только для трейдеров с депозитом
		if (Yii::$app->user->identity->deposit) echo '
		<br/>

		<h2>Платежная информация</h2>	
		<div class="form-group row">
			<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12">Номер карты</label>
			
			<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
				<input type="text" class="form-control input-md" name="pay_card" maxlength="4" value="', $pay_card_splitted[0], '" />
			</div>
			<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
				<input type="text" class="form-control input-md" name="pay_card" maxlength="4" value="', $pay_card_splitted[1], '" />
			</div>
			<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
				<input type="text" class="form-control input-md" name="pay_card" maxlength="4" value="', $pay_card_splitted[2], '" />
			</div>
			<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
				<input type="text" class="form-control input-md" name="pay_card" maxlength="6" value="', $pay_card_splitted[3], '" />
			</div>
			
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="form-control-feedback"></div>
			</div>
		</div>
		
		<div class="form-group">
			<label for="pay_bank">Наименование банка</label>
			<input id="pay_bank" type="text" class="form-control input-md" name="pay_bank" value="', $user->pay_bank, '" />
			<div class="form-control-feedback"></div>
		</div>';
		
		// Изменение пароля только для проверенных трейдеров
		if (Yii::$app->user->identity->grade && 4) echo '
		<br/><br/>
		
		<div class="form-group row">
			<label for="pwd" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">Новый пароль</label>
			
			<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
				<input id="pwd" type="text" class="form-control input-md" name="pwd" maxlength="20" value="" />
			</div>
			
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="form-control-feedback"></div>
			</div>
		</div>';
		
		echo '
		<br/><br/>
		
		<div class="form-group row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="note" style="width:auto">
					<h4 class="text-uppercase">СОГЛАШЕНИЕ</h4>
					<p class="small">', trim( str_replace("\r\n", '</p><p class="small">', \Yii::$app->params['privacy_terms']) ) , '</p>
				</div>
			</div>			
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="checkbox">
					<label>
						<input type="checkbox" id="privacy-check"> <span>С правилами обработки персональных данных согласен</span>
					</label>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="form-control-feedback"></div>
			</div>
		</div>';

		?> 
		
		<br/><br/>
		
		<div class="form-group">
			<input type="submit" class="btn btn-primary btn-md" disabled="disabled" value="Сохранить" />
		</div>									

	</form>

</div>
<script>
	if (window.STP) {
		var params = {};
		$('#phone').change(function() {
			$(this).next().hide();
			params['phone'] = this.value;
			params['crc'] = '';
		});
		$('#profile-form').submit(function(e) {
			var formValid = true;
			var form = this;

			if (typeof params['phone'] != 'undefined') {
				var phoneExp = new RegExp('^9\\d{9}$');
				if (phoneExp.test(params['phone'].replace(/D+/g, '')) == false) {
					$('[name=phone]').parents('.form-group').addClass('has-error').addClass('has-feedback').find('.form-control-feedback').text('Неправильный телефон');
					return false;
				}
				if (params['crc'] == '') {
					$.get('/settings/confirm', {phone: params['phone']} )
					 .done(function(resp) {
						if (parseInt(resp)) {
							STP.dialog.open('#popup-number');
							$('#popup-number').find('.btn-success').click(function(e) {
								delete params['phone'];
								$('[name=phone]').filter(':hidden').val(params['phone']);
								$('#phone').prop({disabled: true});
								params['crc'] = $.trim($('#sms_crc').val());
								STP.dialog.stopEvent(e);
								$(form).submit();
							});
						} else {
							STP.dialog.showStatus(resp);
						}
					 })
					 .fail(function() {
							STP.dialog.showStatus('Произошла ошибка');
					 });						
					return false;
				}
			}			
			
			STP.dialog.stopEvent(e);
			
			$(this).find('.has-error').removeClass('has-error').removeClass('has-feedback');
			$(this).find('.form-control-feedback').text('');
			
			$(this).find('[type=text], [type=hidden]').each(function() {
				if (!$(this).prop('disabled')) {
					if (!$.trim(this.value) && $(this).parent().hasClass('required')) {
						formValid = false;
						$(this).parents('.form-group').addClass('has-error').addClass('has-feedback').find('.form-control-feedback').text('Обязательное поле');
					
					} else {
						if (typeof params[this.name] == 'string')
							params[this.name] = [params[this.name], $.trim(this.value)];
						else if (typeof params[this.name] == 'object')
							params[this.name].push($.trim(this.value));					
						else	
							params[this.name] = $.trim(this.value);
					}
				}
			});

			if (formValid) {
				STP.dialog.showProc(); console.log(params);
				$.post($(this).attr('action'), params)
				 .done(function(resp) {
					if (resp == 1) {
						STP.dialog.showStatus('Изменения сохранены');
						$(form).find(':submit').prop({disabled: true});
					} else {
						try {
							resp = $.parseJSON(resp);
							STP.dialog.close();
							for (var key in resp)
								$(form).find('[name=' + key + ']')
									.parents('.form-group')
									.addClass('has-error')
									.addClass('has-feedback')
									.find('.form-control-feedback')
									.text(resp[key].substr(0,40));
						} catch (e) {
							console.log(resp);
							STP.dialog.showStatus($.trim(resp) ? resp : 'Произошла ошибка');
						}
					}
				 })
				 .fail(function() {
						STP.dialog.showStatus('Произошла ошибка');
				 });
			}
		});
	}
	$('#privacy-check').click(function() {
		$('#profile-form :submit').prop({disabled: !this.checked});
	});	
</script>