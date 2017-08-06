<?php
	$variants = \app\models\Quotation::$candles;
?> 
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<ul class="nav nav-tabs"><?php 

	foreach ($variants as $i=>$v) {
		echo '
		<li', ($i == $id ? ' class="active"' : ''), '><a href="', "/{$this->context->id}/{$i}", '">', $v['title'], '</a></li>';
	}

	unset($variants[$id]['title']);
	
	?> 
	</ul>
</div>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<h2>Динамика курса USD/RUB (MOEX)</h2>
	<a id="usdrub" class="thumb" href="<?php echo $variants[$id]['usdrub'] ?>"><img class="img-responsive" src="<?php echo $variants[$id]['usdrub'] ?>" /><img src="//:0" class="img-responsive hidden" /></a></p>
</div>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<h2>Динамика курса BRENT (ICE)</h2>
	<a id="brent" class="thumb" href="<?php echo $variants[$id]['brent'] ?>"><img class="img-responsive" src="<?php echo $variants[$id]['brent'] ?>" /><img src="//:0" class="img-responsive hidden" /></a></p>
</div>

<script>
	var i = 0;
	var variant = $.parseJSON('<?php echo json_encode($variants[$id]) ?>');
	var graphUpdate = function() {
		if (i == 30)
			window.location.reload();
		for (var item in variant) {
			$('#' + item).children('.hidden').first().clone().attr({alt:item}).on('load', function() {
				$(this).prependTo('#' + $(this).attr('alt')).removeClass('hidden').next().remove();
				$(this).parent().attr({href: $(this).attr('src')});
				window.setTimeout(graphUpdate, 59500);
			}).attr({src: variant[item] + '&index=' + i});
		}
		i++;
	};
	window.setTimeout(graphUpdate, 59500);
</script>