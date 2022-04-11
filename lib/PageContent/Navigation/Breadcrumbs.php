<?php
namespace Littled\PageContent\Navigation;


/**
 * Class Breadcrumbs
 * @package Littled\PageContent\Navigation
 */
class Breadcrumbs extends NavigationMenuBase
{
	/** @var string */
	protected static string $menu_template_path = '';
	/** @var string */
	protected static string $node_type = 'Littled\PageContent\Navigation\BreadcrumbsNode';

	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label Text to display for this item within the navigation menu.
	 * @param string $url (Optional) URL where the menu item will link to.
	 * @param string $dom_id (Optional) value for the breadcrumb node's id attribute.
	 * @param string $css_class (Optional) value for the breadcrumb node's class attribute.
	 */
	function addNode (string $label, string $url='', string $dom_id='', string $css_class='')
	{
		/** @var $node BreadcrumbsNode */
		$node_type = static::getNodeType();
		$node = new $node_type($label, $url, $dom_id, $css_class);
		if (isset($this->first)) {
			$this->last->next_node = $node;
			$node->prev_node = $this->last;
		}
		else {
			$this->first = $node;
		}
        $this->last = $node;
	}

	/**
	 * @param string $label
	 * @return BreadcrumbsNode|null
	 */
	public function find(string $label): ?BreadcrumbsNode
	{
		/** @var BreadcrumbsNode $node */
		if (isset($this->first)) {
			$node = $this->first;
			while ($node) {
				if ($label === $node->label) {
					return $node;
				}
				$node = ((isset($node->next_node))?($node->next_node):(null));
			}
		}
		return null;
	}

	/**
	 * @return string Breadcrumbs template path.
	 */
	public static function getBreadcrumbsTemplatePath(): string
	{
		return static::getMenuTemplatePath();
	}

    /**
     * Returns the current number of nodes in the breadcrumb menu.
     * @return int
     */
    public function getNodeCount(): int
    {
        $count = 0;
        if (isset($this->first)) {
            $node = $this->first;
            while($node) {
                $count++;
                $node = ((isset($node->next_node))?($node->next_node):(null));
            }
        }
        return $count;
    }

    /**
     * Pops last node off the breadcrumb list.
     * @return void
     */
    public function popNode()
    {
        if (!isset($this->first)) {
            // no nodes currently in the list; nothing to do
            return;
        }
        if ($this->last === $this->first) {
            // only one node in the list; delete it
            unset($this->first);
            unset($this->last);
            return;
        }
        $node = $this->last;
        if (isset($node->prev_node)) {
            if ($node->prev_node===$this->first) {
                // two nodes on the list; the link to a previous node should not be set anymore
                $this->last = $this->first;
                unset($this->first->next_node);
            }
            else {
                // two or more nodes will remain; make sure link to previous node is maintained
                $this->last = $node->prev_node;
                unset($this->last->next_node);
            }
        }
        // destroy what was the last node in the list
        unset($node);
    }

    /**
     * Pops n nodes off the end of the breadcrumb list
     * @param int $count Number of nodes to remove.
     * @return void
     */
    public function popNodes(int $count)
    {
        if (!isset($this->first)) {
            return;
        }
        $node = $this->last;
        $i = 0;
        while($node && $i < $count) {
            $this->popNode();
            $node = ((isset($this->last))?($this->last):(null));
            $i++;
        }
    }

    /**
     * Breadcrumbs template path setter.
     * @param string $path
     * @return void
     */
	public static function setBreadcrumbsTemplatePath(string $path)
	{
		static::setMenuTemplatePath($path);
	}
}
