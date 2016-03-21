<?php /** @var $menu Littled\PageContent\Navigation\NavigationMenu */ ?>
			<ul<?=(($breadcrumbs->cssClass)?(" class=\"{$breadcrumbs->cssClass}\""):(""))?>
<?php
$node = $menu->first;
if (isset($node)) {
	while(isset($node)) {
		$node->render();
		$node = $node->nextNode;
	}
}
?>
			</ul>
