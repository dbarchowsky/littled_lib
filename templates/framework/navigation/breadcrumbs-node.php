/** @var $node Littled\PageContent\Navigation\BreadcrumbsNode */
<?php if ($node->url): ?>
		<li<?php if ($node->nextNode===null): ?> class="page-title"<?php endif; ?>><a href="<?=htmlentities($node->url) ?>"<?php if ($node->domId): ?> id="<?=$node->domId?>"<?php endif; ?><?php if ($node->cssClass): ?> class="<?=$node->cssClass?>"<?php endif; ?>><?=$node->label?></a></li>
<?php else: ?>
		<li<?php if ($node->nextNode===null): ?> class="page-title"<?php endif; ?><?php if ($node->domId): ?> id="<?=$node->domId?>"<?php endif; ?><?php if ($node->cssClass): ?> class="<?=$node->cssClass?>"<?php endif; ?>><?=$node->label?></li>
<?php endif; ?>