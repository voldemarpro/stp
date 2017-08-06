<?php 
	$this->title = \Yii::$app->name.' - '.(\Yii::$app->thread->title ? \Yii::$app->thread->title : $this->title);
	$formatter = \Yii::$app->formatter;
	$user = \Yii::$app->user->identity;
	$dtz = new \DateTimeZone('Europe/Moscow');
	$date = new \DateTime('now', $dtz);

	// Форматирование знака числовой величины
	$signPref = [-1 => '<em class="monosign">&ndash;</em>', 0 => '<em class="monosign">&nbsp;</em>', 1 => '<em class="monosign">+</em>'];
	
	// форматирование номера сотакарты
	$stpCardFormatted = \chunk_split(\Yii::$app->user->identity->sotacard, 4, ' ');
	if (\strlen(\Yii::$app->user->identity->sotacard) == 17)
		$stpCardFormatted = \substr($stpCardFormatted, 0, \strlen($stpCardFormatted) - 3).\substr($stpCardFormatted, -2, 1);
	
	$stpCardFormattedUnits = explode(' ', $stpCardFormatted);
	$stpCardFormattedUnits[3] = '<b>'.$stpCardFormattedUnits[3].'</b>';
	$stpCardFormatted = implode(' ', $stpCardFormattedUnits);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		
		<meta name="description" content="" />
		<meta name="keywords" content="" />	
		
		<title><?php echo $this->title ?></title>
		
		<link rel="shortcut icon" href="/favicon.png" type="image/png" />
		<link rel="apple-touch-icon" href="/favicon.png" type="image/png" />

		<link rel="stylesheet" href="/styles/bootstrap.min.css" type="text/css" />
		<link rel="stylesheet" href="/styles/jquery-ui.min.css" type="text/css" /><?php
		
		echo '
		<link rel="stylesheet" href="//sotabank.com/styles/common.css?dev=2" type="text/css" />';
		
		?> 
		<link rel="stylesheet" href="//sotabank.com/styles/gallery/magnific.css" type="text/css" />
		<link rel="stylesheet" href="/styles/tipped.css?dev=01" type="text/css" />
		
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;lang=en" />
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic|PT+Sans+Caption:400,700&subset=latin,cyrillic" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:200,400,700&subset=latin,cyrillic" type="text/css" />

		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script type="text/javascript" src="/js/jquery.magnific.min.js"></script>
		<script type="text/javascript" src="/js/jquery-ui.min.js?ru"></script>
		
		<!--[if lt IE 9]>
		<script type="text/javascript" src="/js/html5shiv.js"></script>
		<script type="text/javascript" src="/js/respond.js"></script>
		<![endif]-->
		<script>
			var config = {
				timeOrigin: new Date('<?php echo \gmdate('Y-m-d\TH:i:sP') ?>'),
				timeOffset: <?php echo ($dtz->getOffset($date))/60 ?>,
				timeOpen: <?php echo \Yii::$app->params['open_time'] ?>,
				timeClose: <?php echo \Yii::$app->params['close_time'] ?>,
				open: <?php echo (time() >= \Yii::$app->params['open_time']) && (time() < \Yii::$app->params['close_time']) ? 1 : 0 ?> 
			}
		</script>
		<script type="text/javascript" src="/js/stp.min.js?dev=04"></script>

		<style>	
			input, select, button, textarea {
				border-radius: 2px!important;
				-moz-border-radius: 2px!important;
			}
			textarea.input-lg, .input-lg {
				font-size: 18px!important;
			}				
			
			h2 {
				font-size: 1.4em;
				font-weight: 400;
				text-transform: uppercase;		
			}
			h2 > span {
				display: inline-block;
				vertical-align: middle;				
			}			
			h3 + .grey {
				font-family: 'PT Sans Caption';
			}

			hr + .small {
				margin-bottom: 0;
				font-size: 1.1em;
			}
			
			table {
				margin-top: 0;
			}
			table > tbody > tr.darkblue > td {
				padding-top: 4px;
				padding-bottom: 4px;
			}
			table > tbody > tr.darkblue + tr > td {
				padding-bottom: 20px;
			}
			
			dl + div > .btn {
				font-size: 1.2em;
				margin-right: 40px;
			}
			dl > dt, dl > dd {
				font-size: 1.3em;
			}			
			
			form p, label {
				font-size: 1.2em!important;
			}			
			label {
				color: #6d757b;
			}

			.grey {
				color: grey;
			}
			
			.small > p:last-child {
				margin-bottom: 0;
			}

			.message {
				padding: 20px 30px 10px 20px;
				border: 1px solid #da7e0e;
				background: #f6e9da;
				position: relative;
			}
			.message * {	
				color: #a6671a;
			}
			.message > .close, .message > .close:focus  {
				position: absolute;
				top: 0.8em;
				right: 15px;
				line-height: 0.8;
				text-decoration: none;
				float: none;
				z-index: 1;
				font-size: 1.8em;
				opacity: 0.7;
			}
			.message > .close:hover {
				color: #9E9089;
				opacity: 1.0;
			}
			.message + div {	
				margin-top: 1em;
			}
			.message.hidden + div {	
				margin-top: 0;
			}							
			.news h3 + p {
				margin-bottom: 0;
			}		
			.input-lg + .form-control-feedback {
				height: auto!important;
			}
			.input-group > .form-control {
				padding-right: 30px;
			}
			.form-control + .input-ok {
				position: absolute;
				right: 0;
				padding: 15px;
				z-index: 2;
			}
			.btn-md, .btn-group-md >.btn {
				padding: 10px 12px;
				font-size: 18px;
			}			
			.form-text:first-child {
				margin-top: 10px;
			}
			
			.toggle {
				line-height: 1.5;
			}
			.dl-horizontal.nobr dt {
				float: left;
				width: 160px;
				overflow: hidden;
				clear: left;
				white-space: nowrap;
			}
			
			.monosign {
				font-style: normal;
				width: 0.6em;
				text-align: right;
				display: inline-block;
			}
			
			.thumb:focus, .btn:focus {
				text-decoration: none;
				outline: none;
			}
			
			.modal-box.graph-box {
				background: none;
				border: none;
				box-shadow: none;
			}
			
			.head-row {
				margin-bottom: 6px;
			}				
			.head-row .h1 {
				margin: 0;
			}		
			.head-row .text, .head-row p {
				font-size: 1.4em;
			}
			.head-row p {
				line-height: 1.5;
			}
			.head-row td > .grey {
				display: block;
			}			

			.help-icon {
				display: inline-block;
				margin-left: 10px;
				vertical-align: middle;
				width: 24px;
				height: 24px;
				background: url('/ui/help.png') no-repeat center center;
				cursor: pointer;
				opacity: 0.3;
				filter: alpha(opacity=30);
			}
			.help-icon:hover, .help-icon:active, .help-icon:focus {
				opacity: 1;
				filter: alpha(opacity=100);
			}

			.darkblue {
				color: #aaa8a8;
				text-transform: uppercase;
				font-weight: 800;
			}
			.table-items td,
			.table-items th {
				border: 0!important;
			}
			.table-items tbody {
				border-top: 1px solid #ddd;
				border-bottom: 1px solid #ddd;
				border-collapse: separate;
			}
			
			.circle {
				font-size: 1.6em;
				line-height: 0.9em;
				position: relative;
				left: -4px;
				top: 1px;
			}	
			.circle-green {
				color: #68b19a;
			}
			.circle-red {
				color: #b16868;
			}
			
			.content > div {
				margin-bottom: 1em;
			}
			
			.nav-tabs {
				font-size: 18px;
			}
			
			.preview > li > a {
				border: 1px solid #9E9089;
				padding: 18px;
				margin-bottom: 12px;
				display: block;
			}
			.preview > li > div > .grey {
				margin: 6px 0;
			}			
			.preview > li h4 {
				margin-bottom: 0;
				font-weight: normal;
				height: 2.2em;
				overflow: hidden;
			}			
			.preview > li p {
				margin-bottom: 0;
			}			
			.preview > li p, .preview > li span {
				font-size: 90%;
			}
			.preview .grey + div {
				height: 3.8em;
				overflow: hidden;
				position: relative;
			}
			.preview .grey + div > p:first-child {
				height: 66%;
				overflow: hidden;
			}
			.preview .grey + div > p:only-child {
				height: 100%;
			}			
			.preview .blur {
				position: absolute;
				display: block;
				bottom: 20px;
				background: white;
				opacity: 0.6;
				height: 10px;
				z-index: 3;
				width: 100%;
			}
			.preview .blue {
				color: #428bca;
				font-size: 100%;
				font-weight: bold;
			}
			.preview .goto:hover .blue {
				color: #254b6c;
			}
			
			.card-info > li {
				vertical-align: middle;
			}

			.sota {
				font-size: 36px;
			}			
			.sota img {
				width: 2em;
			}
			.sota > *, #logout > span {
				display: inline-block;
				vertical-align: middle;
			}
			
			.nav > li > a {
				padding: 8px 12px;
			}
			
			.note {
				background: #e4e8e8;
				padding: 16px 16px 10px;
				min-width: 320px;
				font-size: 0.9em;
				width: 70%;
			}
			.note h4 {
				font-size: 1.1em;
				font-weight: normal;
			}			
			.note h4 + ul {
				padding-left: 20px;
				margin: 0;
			}
			.note li {
				list-style-type:square;
				list-style-position: inset;
				padding-bottom: 5px;
			}
			
			.graph-label {
				position: absolute;
				width: 6em;
				text-align: center;
				display: block;
				padding: 4px 6px;
				background: #f3f4f4;
				border: 1px solid lightgray;
			}
			 
			.inactive {
				opacity: 0.6;
				filter: alpha(opacity=60);
			}
			
			.text-box {
				max-width: 96%;
				padding: 24px 30px;
			}			
			.text-box .small {
				max-height: 510px;
				overflow: auto;
				font-size: 94%;
			}
			.text-box p:last-child {
				margin-bottom: 0;
			}
			.text-box img {
				max-width: 100%;
			}
			
			span.glyphicon-ruble {
				font-size: 75%;
			}
			
			#logout {
				line-height: 20px;
			}
			#logout:hover {
				opacity: 0.8;
				filter: alpha(opacity=80);
			}
			
			[type=radio] {
				margin-top: 0.4em!important;
			}
			
			.checkbox > label > *, .radio > label > * {
				vertical-align: middle;
				display: inline-block;
				position: static!important;
			}
			
			@media (max-width: 1400px) {
				body {
					font-size: 16px!important;
				}
				.sota {
					font-size: 28px;
				}
				.menu:first-child, .content, .head-row {
					font-size: 14px!important;
				}
				.head-row .text, .head-row p {
					font-size: 1.3em;
				}				
				.input-lg {
					font-size: 16px!important;
					padding: 8px 12px;
					line-height: auto!important;
				}
				.btn-md, .btn-group-md >.btn {
					font-size: 16px;
				}
				.form-control + .input-ok {
					padding: 10px;
				}
				.nav-tabs .small {
					font-size: 80%!important;		
				}
				
				input.input-lg {
					height: 36px!important;
				}
				select.input-lg {
					height: 36px!important;
					padding: 6px 12px;
				}
				form p, label, input, select {
					font-size: 1.2em!important;
				}				
			}			
			@media (max-width: 991px) {
				.head-row .auth {
					margin-top: 2em;
					border-top: 6px solid #e2e8e9;
					border-bottom: 6px solid #e2e8e9;
					padding-top: 12px;
					padding-bottom: 6px;				
				}
				.head-row .auth > div {
					margin-right: 2em;
				}				
				.head-row .auth > * {
					display: inline-block;
					vertical-align: middle;
					line-height: 20px;
					padding-bottom: 6px;
				}
				
				.head-row .sota {
					margin: 0;
					font-size: 200%;
				}
				.head-row .sota, .sota + span {
					display: inline-block;
					vertical-align: middle;	
				}
				
				.head-row + .row {
					display: none;
				}
				
				.container {
					margin-top: 40px;
				}

				.mobile {
					display: block!important;
				}
				.menu {
					margin: 0 auto;
					background: none;
					position: static;
					display: block;
				}				
				.menu > li {
					padding: 0;
					display: block;
				}
				.menu a {
					display: block;
					margin: 12px auto;
					padding: 6px;
					font-size: 24px;
					font-weight: 200;
					border: none;
				}	
				.menu > li > a:hover {
					color: white;
					border: 1px solid white;
					background: inherit;
					padding: 5px;
				}
				.opener {
					display: block!important;
				}
			}
			@media (max-width: 640px) {
				.head-row .text {
					font-size: 1.2em;
				}
				.head-row > div > *, .head-row > div > .auth {
					margin-top: 2em;
				}
				.head-row > div > .sota {
					margin-top: 1.1em!important;
				}				
				.head-row .auth > * {
					padding-bottom: 6px;
				}
				.head-row .auth > div:first-child {
					width: 75%;
					margin-right: 0;
				}
				.head-row .text.auth {
					font-size: 1.3em;
				}
				p {
					font-size: 125%;
				}				
			}
			@media (max-width: 400px) {
				body {
					font-size: 15px;
				}
				.section {
					padding-left: 0;
					padding-right: 0;
				}
				.head-row .text {
					font-size: 1.0em;
				}
				.head-row .sota {
					font-size: 160%;
				}				
				.head-row .text.auth {
					font-size: 1.0em;
				}
				.head-row > div > .sota {
					margin-top: 1.6em!important;
				}
				.head-row > div > * {
					margin-top: 2.5em!important;
				}
				.circle {
					font-size: 12px;
					top: -1px;
				}
				.modal-box {
					width: 96%;
					padding: 12px;
				}
				.text-box .small {
					max-height: 480px;
				}
			}			
			@media (min-width: 800px) {
				.text-box {
					width: 800px!important;
				}
			}
			@media (min-width: 1400px) {
				.btn-md, .btn-group-md >.btn {
					font-size: 20px;
				}
				.nav .small {
					font-size: 95%;
				}
				.message p {
					font-size: 0.99em;
				}					
				.message > .close, .message > .close:focus  {
					font-size: 1.5em;
				}				
			}			
		</style>
	</head>

	<body>
		<div class="container-fluid">
			<div class="row mobile">
				<div class="col-xs-4 col-sm-4 col-md-2 col-lg-2">
					<ul class="text-right list-unstyled list-inline">
						<li class="icon"><a href="http://<?php echo \str_replace(['stp.', 'stp1.', 'stp2.'], '', $_SERVER['HTTP_HOST']) ?>" title="На главную"><img src="/ui/home.png" /><img src="/ui/home-over.png" /></a></li>
					</ul>
				</div>				
				
				<div class="col-xs-4 col-sm-4 col-md-8 col-lg-8"></div>
				
				<div class="col-xs-4 col-sm-4 col-md-2 col-lg-2">
					<ul class="text-left list-unstyled list-inline opener">
						<li class="icon"><a href="/" title="Разделы"><img src="/ui/menu.png" /><img src="/ui/menu-over.png" /></a></li>
					</ul>
				</div>				
			</div>
		</div> 
		
		<div class="container">
			<div class="row section">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">					

					<div class="row head-row"><?php
						
					echo '
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
							<div class="sota"> 
								<a href="/"><img class="logo" src="/ui/logo.png" alt="SOTA (STP)" />
								<span style="color: black; font-size: 110%">SOTA</span></a>
							</div><span></span>
						</div>';

					if (!\Yii::$app->user->isGuest)
					{
						$hQuotes = app\models\Quotation::getheaderquotes();
						$circleClass = 'circle-green';
						if (time() >= \Yii::$app->params['close_time'] || time() < \Yii::$app->params['open_time'])
							$circleClass = 'circle-red';
						
						echo '
						<div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
							<div class="text">
								<span class="lightgrey">Сегодня</span> ', $formatter->asDate(date('Y-m-d', time() + 5*60*60), 'dd MMMM'), '</i><br/>
								
								<big id="time">', $date->format('H : i : s'), ' МСК</big>
								<span class="circle ', $circleClass, '">&#x25CF;</span>
							</div>
						</div>';
						
						echo '
						<div class="hidden-xs col-sm-4 col-md-3 col-lg-3">
							<p>
								', $stpCardFormatted, '<br/>
								до ', $formatter->asDate($user->end_date, "dd MMMM ''yy"), '<br/>
							</p>
						</div>';
						
						echo '
						<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
							<div class="text auth">
								<div>
									<span class="text-uppercase">', ($user->last_name && $user->first_name) ? ($user->last_name.' '.mb_substr($user->first_name, 0, 1, 'utf-8').'. ' . ($user->mid_name ? mb_substr($user->mid_name, 0, 1, 'utf-8').'.' : '') ) : 'Без имени', '</span>
									<small class="hidden-lg hidden-md"> до <strong>', $formatter->asDate($user->end_date, "dd MMM ''yy"), '</strong></small>
								</div>
								<a id="logout" class="grey" href="/logout"><img src="/ui/logout.png" /> <span class="hidden-lg hidden-md hidden-sm">Выйти</span><span class="hidden-xs">Выйти из системы</span></a>
							</div>
						</div>';
						
					}
					
					?> 
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">				
							<ul class="text-center list-unstyled list-inline menu"><?php
							
							if (!\Yii::$app->user->isGuest)
								foreach (\Yii::$app->thread->items as $_id=>$_it)
									if ($_it['active'] == 1)
										echo '
										<li', ($_id == \Yii::$app->thread->id ? ' class="active"' : ''), '><a href="', ($_it['vname'] ? "/{$_it['vname']}/" : $_it['redirect']), '">', $_it['name'], '</a></li>';						
							
							
							?> 							
							</ul>
						</div>
					</div> 	 
					
					<div class="row content"><?php

						if ($notices = $user->getNotices()->all()) {
							echo '
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 notices">';							
							
							foreach ($notices as $not) {
								echo '
								<div class="message">
									<a name="id', $not->id, '" href="" class="close">×</a>
									<p>', $not->message, '</p>
								</div>'	;					
							}
							
							echo '
							</div>';
						
						} else {
							echo '
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 hidden notices"></div>';						
						}
						
						echo $content;
							 
						?> 	
					</div>
					
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<hr style="color:#8c949a; border-width: 3px" />
							<p class="text-center small">© <?php echo \Yii::$app->params['company_name'] ?></p>
						</div>							
					</div>
				</div>
			</div>
		</div>
		
		<div class="message hidden">
			<a name="" href="" class="close">×</a>
			<p></p>
		</div>		
		
		<div class="modal-cover">
			<div class="modal-wrapper">
				<div class="modal-cell">

					<div class="modal-box" id="stp-status">
						<div class="group clearfix">
							<h3 class="text-uppercase">&nbsp;</h3><a href="" class="close">&times;</a>
						</div>
						<div class="group">
							<p class="text-center"></p>
						</div>
						<div class="group ctrl text-center">
							<button class="btn btn-primary btn-lg btn-close">&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box" id="stp-confirm">
						<div class="group clearfix">
							<h3 class="text-uppercase">&nbsp;</h3><a href="" class="close">&times;</a>
						</div>
						<div class="group">
							<p class="text-center">Вы уверены?</p>
						</div>
						<div class="group ctrl text-center">
							<button class="btn btn-info btn-lg btn-yes">&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;</button>
							<button class="btn btn-default btn-lg btn-close">&nbsp;&nbsp;Нет&nbsp;&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box graph-box" id="stp-proc">
						<div class="text-center">
							<img src="/ui/delay1.gif" />
						</div>
					</div>
					
					<div class="modal-box text-box" id="stp-text">
						<div class="group clearfix">
							<h3 class="text-uppercase">Новости</h3>
							<a href="" class="close">&times;</a>
						</div>
						<br/>
						
						<div class="small"></div>
						
						<br/>
						
						<div class="group ctrl text-center">
							<button class="btn btn-primary btn-lg btn-close">&nbsp;Закрыть&nbsp;</button>
						</div>
					</div>
					
					<div class="modal-box graph-box" id="stp-menu">
						<div class="text-uppercase"><?php
							if (!\Yii::$app->user->isGuest)
							{
								echo '					
								<ul class="text-center list-unstyled list-inline menu">';

								foreach (\Yii::$app->thread->items as $_id=>$_it)
									if ($_it['active'] == 1)
										echo '
										<li', ($_id == \Yii::$app->thread->id ? ' class="active"' : ''), '><a href="', ($_it['vname'] ? "/{$_it['vname']}/" : $_it['redirect']), '">', $_it['name'], '</a></li>';						
								
								echo '
								<li style="margin-top:2.5em"><a href="#">Закрыть</a></li>';							
								
								echo '
								</ul>';					
							}
							?> 
						</div>
					</div>	
					
					<div class="modal-box" id="sms_box">
						<div class="group clearfix">
							<h3 class="text-uppercase">Проверка номера</h3><a href="" class="close">&times;</a>
						</div>
						<br/>
						<form action="" role="form">	
							<div class="form-group">
								<p>Код подтверждения</p>
								<input id="sms_crc" type="text" class="form-control" name="sms_crc" value="" />
								<div class="form-control-feedback"></div>
							</div>
							
							<div class="group ctrl">
								<button class="btn btn-success btn-lg">Далее</button>
								<button class="btn btn-default btn-lg btn-close">Отмена</button>
							</div>
						</form>
					</div>	
				</div>
			</div>
		</div>
		
		<script>
			function getContentHeight() {
				var sumH = 0;
				$('.section').children().children().each(function(i) {
					if (i != 2 && $(this).is(':visible'))
						sumH += $(this).outerHeight(true);
				});
				return $(window).height() - 110 - sumH - ($(window).width() <= 991 ? 40 : 0);
			}
			if ($('.thumb').length) {
				$('.thumb').css({cursor: 'zoom-in'}).magnificPopup({
					type : 'image',
					gallery: { enabled: false }
				});
			}			
			if ($(window).height() > 600)
				$('.content').css({minHeight: (getContentHeight() + 'px')});
			$(window).resize(function() {
				if ($(window).width() > 991)
					$('#stp-menu a').last().click();
				if ($(window).height() > 600)
					$('.content').css({minHeight: (getContentHeight() + 'px')});
			});
			$(document).on('click', '.message > .close', function(e) {
				e.preventDefault();
				$.get('/thread/readnotice', {id: this.name.replace('id', '')});					
				if ($(this).parent().siblings().length)
					$(this).parent().remove();
				else
					$(this).parent().parent().addClass('hidden').children().remove();
			});
			
			$('#stp-menu a').last().click(function(e) {
				$('#stp-menu').hide();
				$('.modal-cover').fadeOut(400);
				return false;
			});			
			$('.opener a').click(function(e) {
				$('.modal-cover').fadeIn(400, function() {
					$('#stp-menu').show()
				});
				return false;
			});		
		</script>
	</body>
</html>