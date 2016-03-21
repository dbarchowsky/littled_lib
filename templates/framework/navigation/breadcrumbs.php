<?php /** @var $breadcrumbs Littled\PageContent\Navigation\Breadcrumbs */ ?>
			<ul<?=(($breadcrumbs->cssClass)?(" class=\"{$breadcrumbs->cssClass}\""):(""))?>
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
