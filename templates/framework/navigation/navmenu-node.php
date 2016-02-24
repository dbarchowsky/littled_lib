<?php /** @var $node Littled\PageContent\Navigation\NavigationMenuNode */ ?>
		<li><a<?
	if ($node->cssClass):
	    ?> class="<?=$node->cssClass?>"<?
	endif;
	if ($node->url) :
	    ?> href="<?=htmlentities($node->url) ?>"<?
	    if ($node->target) :
		?> target="<?=$node->target ?>"<?
	    endif;
	else:
	    ?> href="#" rel="nofollow"<?
	endif;
	if ($node->domId):
	    ?> id="<?=$node->domId?>"<?
	endif;
	if ($node->attributes):
	    print " ".$node->attributes;
	endif;
	?>><?=$node->label?></a></li>
