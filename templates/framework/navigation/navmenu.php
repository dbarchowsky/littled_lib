<?php /** @var Littled\PageContent\Navigation\NavigationMenu $menu */ ?>
			<ul<?=(($menu->cssClass)?(" class=\"{$menu->cssClass}\""):(""))?>>
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
