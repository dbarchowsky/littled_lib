<?php /** @var $node Littled\PageContent\Navigation\NavigationMenuNode */ ?>
		<li><a<?php
			if ($node->cssClass): ?> class="<?=$node->cssClass?>"<?php
			endif; ?><?php
			if ($node->url): ?> href="<?=htmlentities($node->url) ?>"<?php
				if ($node->target): ?> target="<?=$node->target ?>"<?php
				endif;
			else: ?> href="#" rel="nofollow"<?php
			endif;
			if ($node->title): ?> title="<?=$node->title?>"<?php endif;
			if ($node->domId): ?> id="<?=$node->domId?>"<?php endif;
			if ($node->attributes):
				print " ".$node->attributes;
			endif; ?>><?php if($node->imgPath): ?><img src="<?=$node->imgPath?>" alt="<?=$node->title?>" /><?php else: print $node->label; endif; ?></a></li>
