			<ul>
<?
$node = $breadcrumbs->first;
if (isset($node))
{
	while(isset($node)) 
	{
		$node->render();
		$node = $node->next_node;
	}
}
?>
			</ul>
