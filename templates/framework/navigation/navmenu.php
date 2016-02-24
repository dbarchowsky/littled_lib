<?php /** @var $menu Littled\PageContent\Navigation\NavigationMenu */ ?>
			<ul>
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
