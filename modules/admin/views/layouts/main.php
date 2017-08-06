<?php 
	$this->title = \Yii::$app->name.' - '.($this->context->module->thread->title ? $this->context->module->thread->title : $this->title);
	$user = \Yii::$app->user->identity;
	
	$count = [];
	$count['users'] = \app\models\Traider::find()->where('(`grade` & 1024) = 0')->count();
	$count['requests'] = \app\models\Request::find()->where('`status` = 0')->count();
	$count['support'] = \app\models\SupportTicket::find()->where('`response` IS NULL OR `response` = ""')->count();
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
		<link rel="stylesheet" href="/styles/jquery-ui.min.css" type="text/css" />
		<link rel="stylesheet" href="/styles/chartist.min.css" type="text/css" />
		<link rel="stylesheet" href="/styles/eD.css" type="text/css" />
		
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;lang=en" />
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic|PT+Sans+Caption:400,700&subset=latin,cyrillic" />

		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script type="text/javascript" src="/js/jquery-ui.min.js?dev=02"></script>
		<script type="text/javascript" src="/js/chartist.min.js"></script><?php

		echo '
		<script type="text/javascript" src="/js/eD.min.js?dev=max"></script>';
		
		?> 
		
		<!--[if lt IE 9]>
		<script type="text/javascript" src="/js/html5shiv.js"></script>
		<script type="text/javascript" src="/js/respond.js"></script>
		<![endif]-->
		
		<style>
			html {
				font-size: 15px;
			}	
			
			body {
				margin-top: 0;
				background: white;
				font-family: 'Open Sans', sans-serif;
				background: #cdd8da;
			}
			
			h1 {
				color: #9E9089;
				margin-bottom: 0;
				font-size: 1.8em;
			}
			h2 {
				font-size: 1.3em;
				font-weight: 400;
				text-transform: uppercase;		
			}
			h3 {
				font-weight: 800;
				color: #4c5358;
			}

			h2 + form {
				margin-top: 1.5em;
			}
			
			table .btn {
				font-size: 0.8em;
			}
			
			dl > dt, dl > dd {
				font-size: 1.1em;
			}
			
			dl.tbl > dt, dl.tbl > dd {
				font-size: 1.3em;
				padding-bottom: 6px;
			}
			dl.tbl > dt {
				color: #7e838a;
				text-align: left!important;
				min-width: 30%!important;
			}			
						
			label {
				font-size: 1.2em;
				color: #6d757b;
			}
			input, select, .btn, .alert {
				border-radius: 2px!important;
				-moz-border-radius: 2px!important;
			}
			th.name {
				width: 33%;
				max-width: 390px;
			}

			#root > div {
				display: inline-block;
			}
			#workspace {
				background: white;
			}
			
			td.actions {
				min-width: 80px;
			}
			.grey {
				color: grey;
			}
			.lightgrey {
				color: #aeb3b7;
			}
			.monosign {
				font-style: normal;
				width: 0.6em;
				text-align: right;
				display: inline-block;
			}
			.toggle-info, .toggle-info:hover, .toggle-info:focus  {
				color: #316492;
				border-bottom: 1px dashed;
			}
			
			.alert {
				font-size: 1.1em;
				padding: 0.9em;
			}
			.alert > .close {
				font-size: 1.4em;
			}
			
			.form-group > .row {
				margin-bottom: 0.5em;
			}
			
			.message {
				padding: 20px 20px 10px;
				border: 1px solid #da7e0e;
				background: #f6e9da;				
			}
			.message * {	
				color:#a6671a;	
			}
			.message .close {
				position: absolute;
				top: 15px;
				right: 30px;
				line-height: 0.8;
				text-decoration: none;
				float: none;
				z-index: 1;
				font-size: 1.8em;
				opacity: 0.7;
			}
			.news h3 + p {
				margin-bottom: 0;
			}
			.input-lg + .form-control-feedback {
				height: auto!important;
			}
			.form-text:first-child {
				margin-top: 10px;
			}	
			.toggle {
				line-height: 1.5;
			}
			
			.red {
				color: red;
			}
			.green {
				color: #4db973;
			}			
			
			.btn-sm {
				font-size: 1.1em;
			}
			.input-sm, .input-group-addon:first-child {
				padding: 6px;
				font-size: 1.1em;
			}
			.input-sm, select.input-sm {
				height: 36px;
			}
			.input-number {
				width: 36%;
			}
			
			select.input-sm {
				height: 38px;
			}			
			
			label + .checkbox, label + .radio {
				margin-top: 0;
			}

			form p {
				font-size: 1.0em!important;
			}
			label + .form-row {
				margin: 0 0 1em 1em;
			}				
			
			.form-row {
				margin: 1em 0;
			}
			.form-row > .checkbox:first-child, .form-row > .radio:first-child {
				margin-top: 5px;
			}
			.form-row > .input-group {
				margin-top: 0.5em;
			}			
			
			a.h1 {
				font-size: 3.0em;
				margin-top: 1.0em;
			}
			a.h1, a.h1 > * {	
				display: inline-block;
				vertical-align: middle;
			}
			a.h1 {	
				line-height: 2.5em;
			}
			a, a:hover, a:visited {	
				text-decoration: none!important;
			}
			
			*:focus, *:active {
				outline: none!important;
				text-decoration: none!important;
			}
			
			.menu {
			    margin: 40px auto;
				font-weight: normal;
				font-size: 1.3em;
				padding-left: 25px;
			}
			.menu li {
			    margin-top: 1.0em;
			}
			.menu a {
				display: inline-block;
				padding: 4px 8px;
				background: none;
				color: #4a4c4f;
			}
			.menu a:hover {
				color: #9E9089;
			}
			
			.menu .active > a {
				color: grey;
				opacity: 0.5!important;
			}
			.menu .active > a:hover {
				color: grey;
				cursor: default;
			}

			.logo {
				height: 70px;
			}
			.logo + span {
				color: black;
			}
			
			.content {
				margin: 5em 3em 0;
			}
			
			.actions {
				text-align: right;
			}
			.actions a+a {
				margin-left: 0.5em;
			}
			.inactive > td {
				opacity: 0.5;
				filter: alpha(opacity=50);
			}

			.done > td {
				background-color: #ecf1ef!important;			
			}
			
			.content.row > div {
				margin-bottom: 1.5em;
			}	
			
			.del, .times {
				color: #c16c6e;			
			}
			.del:hover, .times:hover {
				color: #f18083;
			}			
			.times {
				font-size: 200%;
			}
			
			.pagination-md > li >a, .pagination-lg > li > span {
				padding: 8px 14px;
				font-size: 1em;
				line-height: 1.3333333;
			}			

			.checkbox-inline > input,
			.checkbox > label > input,
			.radio > label > input,
			.radio-inline > input {
				margin-top: 0.4em;
			}

			.crumbs a {
				display: inline-block;
				padding: 2px 8px;
				border: 1px solid white;
				width: 28px;
				text-align: center;				
			}
			.crumbs a:hover, .crumbs .active {
				color: #428bca;
				border: 1px solid #428bca;
			}
			
			.modal-cover {
				top: 0;
				left: 0;
				z-index: 10;
				background: rgba(27, 25, 25, 0.8);
				position: fixed;		
				width: 100%;
				height: 100%;
				display: none;
			}

			.modal-wrapper {
				display: table;
				height: 100%;
				width: 100%;
			}

			.modal-cell {
				display: table-cell;
				vertical-align: middle;
				text-align: center;
			}

			.modal-box {
				width: 400px;
				margin: 0 auto;
				padding: 20px;
				-moz-box-shadow: 0px 0px 8px 2px #5a6a6a;
				-moz-border-radius: 3px;
				box-shadow: 0px 0px 8px 2px #5a6a6a;
				border-radius: 3px;
				background: #fefffd;
				text-align: left;
				overflow: auto;
				display: none;
			}

			.modal-box .close {
				color: #777;
				float: right;
				line-height: 0.8;
				text-decoration: none;
			}

			.modal-box .close:hover {
				color: #565787;/*#56abd8*/
			}

			.modal-box h3 {
				margin: 0;
				max-width: 90%;
				font-size: 1.3em;
				font-weight: 400;
				float: left;
			}
			
			.graph-box {
				background: none;
				border: none;
				box-shadow: none;
			}
			
			.form-control-feedback {
				top: 0;
				position: static!important;
				width: auto!important;
				height: auto!important;
				text-align: inherit;
			}
			
			.ui-widget-content {
				border: 1px solid #aaaaaa;
				background: #ffffff;
				color: #222222;
			}
			.ui-menu {
				list-style: none;
				padding: 0;
				margin: 0;
				display: block;
				outline: none;
			}
			.ui-autocomplete {
				position: absolute;
				top: 0;
				left: 0;
				cursor: default;
			}
			.ui-front {
				z-index: 100;
			}
			.ui-menu .ui-menu-item {
				position: relative;
				margin: 0;
				cursor: pointer;
				min-height: 0;
			}
			.ui-menu-item > div {
				display: block;
				padding: 3px 1em 3px 0.4em;
				color: #212121;
				text-decoration: none;
			}
			.ui-state-focus {
				border: 1px solid #999999;
				background: #dadada;
				font-weight: normal;
				color: #212121;
			}

			.ui-menu .ui-state-focus,
			.ui-menu .ui-state-active {
				margin: -1px;
			}
			.ui-helper-hidden-accessible {
				display: none!important
			}

			.ui-helper-hidden-accessible {
				display: none!important
			}
			
			.multiple-input {
				margin-top: 1em;
				margin-bottom: 1em;
			}
			.multiple-input li {
				background: #fff;
				box-shadow: 0 1px 1px #aaa;
				cursor: default;
				display: block;
				margin-right: 10px;
				margin-bottom: 10px;
				padding: 6px 10px;
			}
			.multiple-input li > .close {
				color: #f00;
				position: static;
				margin-left: 4px;
				font-size: 150%;
				text-decoration: none;
			}

			.toggle-info {
				position: relative;
				display: inline-block;
			}
			.toggle-info > .tooltip {
				position: absolute;
				width: 360px;
				color: #303030;
				background: #FFFCF0;
				padding: 10px 14px;
				font-size: 0.8em;
				border: 1px solid #D6D6D0;
				display: none;
				border-radius: 2px;
				box-shadow: 2px 2px 6px #D4D4D4;
				z-index: 3;
			}
			.toggle-info > .tooltip > dl > dt {
				text-align: left;
			}
			.toggle-info > .tooltip > dl {
				margin-bottom: 0;
			}
			.toggle-info > .tooltip:before {
				content: '';
				position: absolute;
				bottom: 100%;
				left: 50%;
				margin-left: -12px;
				width: 0; height: 0;
				border-bottom: 12px solid #D6D6D0;
				border-right: 12px solid transparent;
				border-left: 12px solid transparent;
			}
			.toggle-info > .tooltip:after {
				content: '';
				position: absolute;
				bottom: 100%;
				left: 50%;
				margin-left: -8px;
				width: 0; height: 0;
				border-bottom: 8px solid #FFFBE6;
				border-right: 8px solid transparent;
				border-left: 8px solid transparent;
			}
			.toggle-info:hover .tooltip {
				opacity: 1;
				top: 30px;
				left: 0;
				margin-left: 0;
				z-index: 999;
				display: block;
			}
			
			.redactor {
				min-height: 460px;
				max-width: 800px;
				padding: 15px !important;
			}
			
			.mobile {
				display: none;
			}
			
			@media (max-width: 1400px) {
				body {
					font-size: 12px;
				}
				h1 {
					margin-top: 0.5em;
				}
				.input-lg, .btn-lg, .btn-group-lg >.btn {
					padding: 6px 12px;
					font-size: 1.2em;
					height: 36px!important;
					line-height: 1.5!important;
				}
				.input-group-addon {
					padding: 2px 12px;
					font-size: 1.0em;
					height: 36px!important;
				}
				.content {
					margin-left: 1.5em;
					margin-right: 1.5em;
				}		
			}
			@media (min-width: 1400px) {
				body {
					font-size: 16px!important;
				}
				.container {
					width: 1340px!important;
				}
				.about > li > div {
					min-height: 170px;
				}
				img.logo {
					height: 90px;
				}
				.menu {
					padding-left: 35px;
				}
				[type=checkbox], [type=radio] {
					-ms-transform: scale(1.2);
					-moz-transform: scale(1.2);
					-webkit-transform: scale(1.2);
					-o-transform: scale(1.2);
				}		
			}			
			@media (min-width: 1000px) {
				.text-lead {
					font-size: 120%!important;
				}
			}
			
			@media (max-width: 600px) {
				.mobile {
					width: 100%;
					display: block;
					position: fixed;
					background: #cdd8da;
					box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.2);
					-webkit-box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.2);
					z-index: 10;
					height: 3.3em;
				}
				.mobile a {
					text-decoration: none;
					display: inline-block;
					padding: 6px;
					font-size: 200%;
					vertical-align: top;
					line-height: 1.0;
					color: #93a6ab;
				}
				.mobile a:hover {
					color: #697173;
				}
				.content {
					margin-left: 0;
					margin-right: 0;
				}
				.menu {
					padding-left: 0;
				}
				.menu a {
					color: #d1d3c8;
					display: block;
					text-align: center;
					margin: 6px 0;
					padding: 4px 6px;
					font-size: 16px;
					font-weight: 200;
					border: 1px solid #7a7b74;
				}
				.menu > li {
					padding: 0;
					display: inline-block;
					width: 42%;
					margin-right: 6px;
				}

				.menu > li > a:hover {
					color: white;
					border-color: white;
					background: inherit;
				}
				.menu > li.active > a, .menu > li.active > a:hover {
					border-color: transparent;
					color: #ffffff;
					cursor: default;
				}				
			}
		</style>
	</head>

	<body>
		<div class="container-fluid">
			
			<div class="row mobile">
				<div class="col-xs-2 col-xs-offset-1">
					<div><a href=""><big class="glyphicon glyphicon-align-justify"></big></a></div>
				</div>
				<div class="col-xs-8"></div>		
			</div>
			
			<div class="row">

				<div class="hidden-xs col-sm-4 col-md-3 col-lg-3 text-center" id="root"> 
					<div>
						<a class="h1" href="/"><img class="logo" src="/ui/logo.png" alt="SOTA (STP)" /> 
						<span>STP-1</span></a>
						
						<ul class="text-left list-unstyled menu"><?php

						foreach ($this->context->module->thread->items as $_id=>$_it)
							if ($_it['active'] == 1 && $_it['precedence'] >= 0)
								echo '
								<li', ($_id == $this->context->module->thread->id ? ' class="active"' : ''), '>
									<a href="', "/{$this->context->module->id}/{$_it['vname']}/", '">', $_it['name'], '
										', (!empty($count[$_it['vname']]) ? "<sup class=\"grey\">[{$count[$_it['vname']]}]</sup>" : ''), ' 
									</a>
								</li>';					
						
						?> 
						</ul> 
						
						<ul class="text-left list-unstyled menu"><?php

						foreach ($this->context->module->thread->items as $_id=>$_it)
							if ($_it['active'] == 1 && $_it['precedence'] < 0 && ($_it['vname'] != 'format' || $user->login == 'dev'))
								echo '
								<li', ($_id == $this->context->module->thread->id ? ' class="active"' : ''), '><a href="', "/{$this->context->module->id}/{$_it['vname']}/", '">', $_it['name'], '</a></li>';					
						
						?> 
						</ul> 
						
						<ul class="text-left list-unstyled menu">
							<li><a href="<?php echo "/{$this->context->module->id}/logout" ?>">Выход</a></li>					
						</ul>
					
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-8 col-md-9 col-lg-9" id="workspace">
					<div class="row content table-responsive">
						<div class="col-lg-12 col-md-12">
							<h1><?php echo $this->context->module->thread->title ?></h1>
						</div><?php				

						echo $content;
							 
						?> 
					</div>
				</div>
				
			</div>
		</div>
		
		<div class="modal-cover">
			<div class="modal-wrapper">
				<div class="modal-cell">
					
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
					
					<div class="modal-box graph-box" id="stp-menu" style="display: block;">
						<div class="text-uppercase">					
							
							<ul class="text-left list-unstyled menu"><?php

							foreach ($this->context->module->thread->items as $_id=>$_it)
								if ($_it['active'] == 1 && $_it['precedence'] >= 0)
									echo '
									<li', ($_id == $this->context->module->thread->id ? ' class="active"' : ''), '>
										<a href="', "/{$this->context->module->id}/{$_it['vname']}/", '">', $_it['name'], '
											', (!empty($count[$_it['vname']]) ? "<sup >[{$count[$_it['vname']]}]</sup>" : ''), ' 
										</a>
									</li>';					
							
							?> 
							</ul> 
							
							<ul class="text-left list-unstyled menu"><?php

							foreach ($this->context->module->thread->items as $_id=>$_it)
								if ($_it['active'] == 1 && $_it['precedence'] < 0 && ($_it['vname'] != 'format' || $user->login == 'dev'))
									echo '
									<li', ($_id == $this->context->module->thread->id ? ' class="active"' : ''), '><a href="', "/{$this->context->module->id}/{$_it['vname']}/", '">', $_it['name'], '</a></li>';					
							
							?> 
								<li><a href="#" id="close-menu">Закрыть</a></li>
							</ul> 
	
							<ul class="text-left list-unstyled menu">
								<li><a href="/">SOTA-1</a></li>
								<li><a href="<?php echo "/{$this->context->module->id}/logout" ?>">Выход</a></li>			
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
		
	<script>
		$('#root').css({minHeight: ($(window).height() + 'px')});
		$('#workspace').css({minHeight: ($('#root').height() + 'px')});
		$('.alert > .close').click(function() {
			return $(this).parent().remove() && false;
		});
		$('.del').click(function() {
			return confirm('Подтвердите удаление');
		});
		$('.toggle-info').parent().css({opacity:1});		
		window.Dialog = {
			fadeIn: function(sel, callback) {
				$(sel).fadeIn(300, function() {
					if (callback && typeof callback == 'function')
						callback.call(this);
				});						
			},
			
			open: function(sel, callback) {
				$(sel).parent().parent().parent().show();
				if ($(sel).siblings(':visible').length)
					$(sel).siblings(':visible').hide();
				$('.form-control-feedback').text('');
				$('.has-error').removeClass('has-error');
				Dialog.fadeIn(sel, callback);
			},
			
			close: function(callback) {
				$('.modal-box').filter(':visible').fadeOut(300, function() {
					$('.modal-cover').hide();
					if (callback && typeof callback == 'function')
						callback.call(this);
				});
			},
			
			stopEvent: function(e) {
				e.preventDefault();
				e.stopPropagation();
			},
			
			showProc: function(message) {
				Dialog.open('#stp-proc');
			}							
		};
		
		$(window).resize(function() {
			if ($(window).width() > 600)
				$('#close-menu').click();
			if ($(window).height() > 600) {
				$('#root').css({minHeight: ($(window).height() + 'px')});
				$('#workspace').css({minHeight: ($('#root').height() + 'px')});
			}
		});		
		$('#close-menu').last().click(function(e) {
			$('#stp-menu').hide();
			$('.modal-cover').fadeOut(400);
			return false;
		});
		$('.mobile a').click(function(e) {
			$('.modal-cover').fadeIn(400, function() {
				$('#stp-menu').show()
			});
			return false;
		});	
	</script>
</html>