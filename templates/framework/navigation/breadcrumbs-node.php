/** @var $menu Littled\PageContent\Navigation\NavigationMenu */
<? if ($this->url): ?>
		<li<? if ($this->next_node===null): ?> class="page-title"<? endif; ?>><a href="<?=htmlentities($this->url) ?>"<? if ($this->id): ?> id="<?=$this->id?>"<? endif; ?><? if ($this->class): ?> class="<?=$this->class?>"<? endif; ?>><?=$this->label?></a></li>
<? else: ?>
		<li<? if ($this->next_node===null): ?> class="page-title"<? endif; ?><? if ($this->id): ?> id="<?=$this->id?>"<? endif; ?><? if ($this->class): ?> class="<?=$this->class?>"<? endif; ?>><?=$this->label?></li>
<? endif; ?>