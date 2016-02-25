<?php /** @var $breadcrumbs Littled\PageContent\Navigation\Breadcrumbs */ ?>
			<ul>
<?php
$node = $breadcrumbs->first;
if (isset($node)) {
	while(isset($node)) {
		$node->render();
		$node = $node->nextNode;
	}
}
?>
			</ul>
