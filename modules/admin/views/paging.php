<?php
if ($pageCount > 1)
{
	$firstPage = $page ? floor(($page + 1) / 10) + 1 : 1;
	$lastPage = floor(($page + 1) / 10) + 10;
	if ($pageCount < $lastPage)
		$lastPage = $pageCount;
		
	if ($_SERVER['QUERY_STRING'])
		$queryStringSuffix = '?'.$_SERVER['QUERY_STRING'];
	else
		$queryStringSuffix = '';
	
	echo '
	<ul class="pagination pagination-md">';
		
		if (($firstPage - 1) > 0)
			echo '
			<li><a href="', "/{$this->context->module->id}/{$this->context->id}/page", ($firstPage - 1), $queryStringSuffix, '">&lt;&lt;</a></li>';			
		
		for ($i = $firstPage; $i <= $lastPage; $i++) {
			echo '
			<li', ($page == ($i-1) ? ' class="active"' : ''), '><a href="', "/{$this->context->module->id}/{$this->context->id}/page$i", $queryStringSuffix, '">', $i, '</a></li>';
		}
		
		if (($lastPage + 1) <= $pageCount)
			echo '
			<li><a href="', "/{$this->context->module->id}/{$this->context->id}/page", ($lastPage + 1), $queryStringSuffix, '">&gt;&gt;</a></li>';			
		
		
	echo '
	</ul>';
}
	
?> 