<?php
/**
 * Typical fields render to write corresponding model attributes
 */
if (empty($fields))
	return false;

foreach ($fields as $field)
{	
	$format = $field['format'];
	$value = $field['value'];
	$label = $field['label'];
	$name = $field['name'];
	if (isset($field['options']))
		$options = (array)$field['options'];

	switch($format)
	{
		case 0:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<input class="form-control input-sm input-number" type="text" name="', $name, '" value="', $value, '" />
				<div class="form-control-feedback"></div>
			</div>';
			break;
			
		case 1:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<input class="form-control input-sm input-number" type="text" name="', $name, '" value="', $value, '" />
				<div class="form-control-feedback"></div>
			</div>';
			break;
			
		case 2:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<input class="form-control input-sm" type="text" name="', $name, '" value="', $value, '" />
				<div class="form-control-feedback"></div>
			</div>';
			break;
			
		case 3:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<textarea rows="5" class="form-control input-sm" name="', $name, '">', $value, '</textarea>
				<div class="form-control-feedback"></div>
			</div>';
			break;
			
		case 4:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<textarea class="redactor form-control input-sm" name="', $name, '">', $value, '</textarea>
			</div>
			<br/>
			<script>
				$(".redactor").eD({
					
					buttons: ["html", "|", "formatting", "|", "bold", "italic", "deleted", "|", "unorderedlist", "orderedlist", "outdent", "indent", "|",
						"image", "table", "link", "|", "alignment"
					],
					
					convertDivs: true,
					
					emptyHtml: "<p><br/></p>",
					
					imageUpload: "/secret/upload/",

					translate: {
						html: "Код",
						video: "Видео",
						image: "Изображение",
						table: "Таблица",
						link: "Ссылка",
						link_insert: "Вставить ссылку ...",
						unlink: "Удалить ссылку",
						formatting: "Форматирование",
						paragraph: "Обычный текст",
						quote: "Цитата",
						code: "Код",
						header1: "Заголовок 1",
						header2: "Заголовок 2",
						header3: "Заголовок 3",
						header4: "Заголовок 4",		
						bold:  "Полужирный",
						italic: "Наклонный",
						fontcolor: "Цвет текста",
						backcolor: "Заливка текста",
						unorderedlist: "Обычный список",
						orderedlist: "Нумерованный список",	
						outdent: "Уменьшить отступ",
						indent: "Увеличить отступ",
						cancel: "Отменить",	
						insert: "Вставить",
						save: "Сохранить",	
						_delete: "Удалить",
						insert_table: "Вставить таблицу",
						insert_row_above: "Добавить строку сверху",
						insert_row_below: "Добавить строку снизу",
						insert_column_left: "Добавить столбец слева",
						insert_column_right: "Добавить столбец справа",									
						delete_column: "Удалить столбец",									
						delete_row: "Удалить строку",									
						delete_table: "Удалить таблицу",
						rows: "Строки",
						columns: "Столбцы",	
						add_head: "Добавить заголовок",
						delete_head: "Удалить заголовок",	
						title: "Подсказка",
						image_position: "Обтекание текстом",
						none: "Нет",	
						left: "Cлева",
						right: "Cправа",
						image_web_link: "Cсылка на изображение",
						text: "Текст",
						mailto: "Эл. почта",
						web: "URL",
						video_html_code: "Код видео ролика",
						file: "Файл",
						upload: "Загрузить",
						download: "Скачать",
						choose: "Выбрать",
						or_choose: "Или выберите",
						drop_file_here: "Перетащите файл сюда",
						align_left:	"По левому краю",	
						align_center: "По центру",
						align_right: "По правому краю",
						align_justify: "Выровнять текст по ширине",
						horizontalrule: "Горизонтальная линейка",
						fullscreen: "Во весь экран",
						deleted: "Зачеркнутый",
						anchor: "Якорь",
						link_new_tab: "Открывать в новой вкладке",
						underline: "Подчеркнутый",
						alignment: "Выравнивание"			
					}
				});
			</script>';
			break;
			
		case 5:
			$value = date('H:i:s', strtotime($value) + $this->params['dto']);
			$valAsArray = $value ? explode(':', $value) : array_fill(0, 3, '');
			
			echo '
			<div class="form-group row">
				
				<!--<input type="hidden" name="dto" value="', $this->params['dto'], '" />-->
				
				<label class="col-lg-12 col-md-12">', $label, '</label>
				<div class="col-lg-3 col-md-3">
					<div class="input-group">
						<span class="input-group-addon"><i>час</i></span>
						<input class="form-control input-sm" type="text" name="', $name, '[0]" maxlength="2" value="', $valAsArray[0], '" />
					</div>
				</div>

				<div class="col-lg-3 col-md-3">
					<div class="input-group">
						<span class="input-group-addon"><i>мин</i></span>							
						<input class="form-control input-sm" type="text" name="', $name, '[1]" maxlength="2" value="', $valAsArray[1], '" />
					</div>
				</div>

				<div class="col-lg-3 col-md-3">	
					<div class="input-group">
						<span class="input-group-addon"><i>сек</i></span>								
						<input class="form-control input-sm" type="text" name="', $name, '[2]" maxlength="2" value="', $valAsArray[2], '" />
					</div>						
				</div>
				
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="form-control-feedback"></div>
				</div>
			</div>';
			break;
			
		case 6:
			echo '
			<div class="form-group row">
				
				<!--<input type="hidden" name="dto" value="', $this->params['dto'], '" />-->
				
				<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12">', $label, '</label>
				<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
					<input type="text" name="', $name, '" maxlength="10" class="form-control input-sm" value="', ($value ? date('d.m.Y', strtotime($value)) : ''), '" />
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="form-control-feedback"></div>
				</div>
			</div>
			<script>
				$("[name=', $name, ']").datepicker({
					hideIfNoPrevNext: true,
					changeYear: true,
					changeMonth: true,
					showWeek: false,
					firstDay: 1,
					yearRange: "1970:', date('Y') + 1, '",
					dateFormat: "dd.mm.yy"
				});
			</script>';
			break;
			
		case 7:
			
			$valueDt = $value ? explode(' ', $value) : ['', ''];
			$valAsArray = $valueDt[1] ? explode(':', date('H:i:s', strtotime($valueDt[1]) + $this->params['dto'])) : array_fill(0, 3, '');
								
			echo '
			<div class="form-group row">
				<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12">', $label, '</label>
				<div class="col-lg-3 col-md-3">
					<input type="text" name="', $name, '[-1]" maxlength="10" class="form-control input-sm" value="', ($value ? date('d.m.Y', strtotime($value)) : ''), '" />
				</div>
				
				<div class="col-lg-3 col-md-3">
					<div class="input-group">
						<span class="input-group-addon"><i>час</i></span>
						<input class="form-control input-sm" type="text" name="', $name, '[0]" maxlength="2" value="', $valAsArray[0], '" />
					</div>
				</div>

				<div class="col-lg-3 col-md-3">
					<div class="input-group">
						<span class="input-group-addon"><i>мин</i></span>							
						<input class="form-control input-sm" type="text" name="', $name, '[1]" maxlength="2" value="', $valAsArray[1], '" />
					</div>
				</div>

				<div class="col-lg-3 col-md-3">	
					<div class="input-group">
						<span class="input-group-addon"><i>сек</i></span>								
						<input class="form-control input-sm" type="text" name="', $name, '[2]" maxlength="2" value="', $valAsArray[2], '" />
					</div>						
				</div>						
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="form-control-feedback"></div>
				</div>
			</div>
			<script>
				$("[name^=', $name, ']").first().datepicker({
					hideIfNoPrevNext: true,
					changeYear: true,
					changeMonth: true,
					showWeek: false,
					firstDay: 1,
					yearRange: "1970:', date('Y') + 1, '",
					dateFormat: "dd.mm.yy"
				});
			</script>';
			break;
		
		case 8:
			echo '
			<div class="form-group">				
				<label>', $label, '</label><br/>';
				
				foreach (app\modules\admin\models\Param::$weekDays as $k=>$w) 
					echo '
					<label class="checkbox-inline"><input type="checkbox" value="', $k, '" name="', $name, '[', $k, ']"', (!$value[$k] ? '' : ' checked="checked"'), ' /><span>', $w, '</span></label>';
					
			echo '
			</div>';
			break;
			
		case 9:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<select class="form-control input-sm" name="', $name, '">';
					
				foreach (\Yii::$app->thread->items as $t) 
					if ($t['vname']) echo '
					<option value="', $t['id'], '"', ($t['id'] != $value ? '' : ' selected="selected"'), '>', $t['name'], '</option>';
					
			echo '
				</select>
			</div>';
			break;
			
		case 10:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<select class="form-control input-sm">';

				foreach (\app\models\MoneyTransfer::$recipients as $i=>$t) 
					echo '
					<option value="', $i, '"', ($i != $value ? '' : ' selected="selected"'), '>', $t, '</option>';
					
			echo '
				</select>
			</div>';
			break;
			
		case 12:
			echo '
			<div class="form-group">				
				<label>', $label, '</label>';
				
				foreach (\Yii::$app->user->identity->grades as $k=>$g) 
					if ($k) echo '
					<div class="checkbox">
						<label for="', "{$name}_$k", '"', ($k == 10 ? ' class="lightgrey" style="opacity: 0.5"' : ''), '><input id="', "{$name}_$k", '" type="checkbox" value="', \pow(2, $k), '" name="', $name, '[', $k, ']"', (!($value & pow(2,$k)) ? '' : ' checked="checked"'), ' /><span>', $g, '</span></label>
					</div>';
			echo '
			</div>
			<script>
				$(".lightgrey").last().children().first().click(function(e) {
					e.stopPropagation();
					return confirm("Подтвердите действие");
				});
			</script>';
			break;
			
		case 13:
			echo '
			<div class="form-group">				
				<label for="', "{$name}_chbx", '">', $label, '</label>
				<div class="checkbox">
					<label>
						<input type="hidden" value="0" name="', $name, '" />
						<input type="checkbox" value="1" name="', $name, '"', ($value ? ' checked="checked"' : ''), ' id="', "{$name}_chbx", '" /><span>&nbsp;</span>	
					</label>
				</div>
			</div>';
			break;
			
		case 14:
			if ($value) {
				$pay_card_splitted = \explode("\n", \chunk_split($value, 4, "\n"));
				if (isset($pay_card_splitted[4]))
					$pay_card_splitted[3] = $pay_card_splitted[3] . $pay_card_splitted[4];
			 } else
				$pay_card_splitted = \array_fill(0, 4, '');
			
			echo '
			<div class="form-group row">
				<label class="col-xs-12 col-sm-12 col-md-12 col-lg-12">', $label, '</label>
				
				<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
					<input type="text" class="form-control input-sm" name="', $name, '[0]" maxlength="4" value="', $pay_card_splitted[0], '" />
				</div>
				<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
					<input type="text" class="form-control input-sm" name="', $name, '[1]" maxlength="4" value="', $pay_card_splitted[1], '" />
				</div>
				<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
					<input type="text" class="form-control input-sm" name="', $name, '[2]" maxlength="4" value="', $pay_card_splitted[2], '" />
				</div>
				<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
					<input type="text" class="form-control input-sm" name="', $name, '[3]" maxlength="6" value="', $pay_card_splitted[3], '" />
				</div>
				
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<div class="form-control-feedback"></div>
				</div>
			</div>';
			break;
		
		case 15:	
			echo '
			<div class="form-group">
				<label for="phone">', $label, '</label>
				<div class="input-group">
					<span class="input-group-addon text">+7</span>
					<input type="text" class="form-control input-sm" name="', $name, '" value="', $value, '" placeholder="например, 905-148-2314" />
				</div>
				<div class="form-control-feedback"></div>
			</div>';
			break;
			
		case 17:	
			$pp = $value ? \explode('|', \app\models\Trader::myAESdecrypt($value)) : [];
			echo '
			<div class="form-group">
				<label for="phone">', $label, '</label>
				<div class="row">
					<div class="col-xs-4 col-sm-4 col-md-3 col-lg-3">
						<small class="text-uppercase">Серия</small><br/>
						<input type="text" class="form-control input-sm" maxlength="4" name="passport" value="', !empty($pp[0]) ? $pp[0] : '', '" />
					</div>
					<div class="col-xs-4 col-sm-4 col-md-3 col-lg-3">
						<small class="text-uppercase">Hомер</small><br/>
						<input type="text" class="form-control input-sm" maxlength="6" name="passport" value="', !empty($pp[1]) ? $pp[1] : '', '" />
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="form-control-feedback"></div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
						<small class="text-uppercase">Дата выдачи</small><br/>
						<input id="issue_date" type="text" name="passport" maxlength="10" class="form-control input-sm" name="passport" value="', !empty($pp[2]) ? $pp[2] : '', '" />
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="form-control-feedback"></div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<small class="text-uppercase">Кем выдан</small><br/>
						<input type="text" class="form-control input-sm" name="passport" value="', !empty($pp[3]) ? $pp[3] : '', '" />
						<div class="form-control-feedback"></div>
					</div>
				</div>
			</div>
			
			<script>
				$("#issue_date").datepicker({
					hideIfNoPrevNext: true,
					changeYear: true,
					changeMonth: true,
					showWeek: false,
					firstDay: 1,
					yearRange: "1970:', date("Y"), '",
					dateFormat: "dd/mm/yy"
				});
			</script>';
			break;
			
		case 18:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<select name="', $name, '" class="form-control input-sm input-number">';

				foreach ($options as $i=>$o) 
					echo '
					<option value="', $i, '"', ($i != $value ? '' : ' selected="selected"'), '>', $o, '</option>';
					
			echo '
				</select>
			</div>';
			break;
			
		case 19:
			echo '
			<div class="form-group">
				<label for="">', $label, '</label>
				<select class="form-control input-sm">';

				foreach (\app\models\MoneyTransfer::$grades as $i=>$g) 
					echo '
					<option value="', $i, '"', ($i != $value ? '' : ' selected="selected"'), '>', $g, '</option>';
					
			echo '
				</select>
			</div>';
			break;

		default:
			break;

	}
}
?> 