<?php

namespace Littled\PageContent\Navigation;


use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;

class NavigationMenuBase
{
	/** @var string Path to template used to display the breadcrumbs. */
	protected static string $menu_template_path='';
	/** @var string Class name of the class used to render the breadcrumb nodes. */
	protected static string $node_type='';

	/** @var NavigationNodeBase Pointer to first node in the list of breadcrumbs. */
	public NavigationNodeBase $first;
	/** @var NavigationNodeBase Pointer to last node in the list of breadcrumbs. */
	public NavigationNodeBase $last;
	/** @var string CSS class to apply to the breadcrumb menu parent element */
	public string $css_class='';

    /**
     * Class constructor.
     */
    function __construct()
    {
        /** placeholder */
    }

	/**
	 * Adds menu item to navigation menu and sets its properties.
	 * @param string $label
	 * @param string $url
	 */
	public function addNode(string $label, string $url='')
	{
		/** placeholder for children classes */
	}

	/**
	 * Remove and delete all nodes on the tree.
	 */
	public function clearNodes()
	{
		while(isset($this->last)) {
			$node = null;
			if (isset($this->last->prev_node) && is_object($this->last->prev_node)) {
				$node = $this->last->prev_node;
			}
			unset($this->last);
			if (is_object($node)) {
				$this->last = $node;
			}
		}
		unset($this->first);
	}

	/**
     * @deprecated Use popNode() instead.
	 */
	function dropLast ( )
	{
		if (!isset($this->last)) {
			return;
		}

		if (isset($this->last->prev_node)) {
			$node = $this->last->prev_node;
			unset($node->next_node);
			$this->last = $node;
		}
		else {
			unset($this->last);
		}
	}

	/**
	 * @return string Navigation menu template path.
	 */
	public static function getMenuTemplatePath(): string
	{
		return (static::$menu_template_path);
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
	 * @return string Returns the type set for the navigation menu nodes.
	 */
	public static function getNodeType(): string
	{
		return static::$node_type;
	}

	/**
	 * Returns true/false depending on whether the menu current contains any nodes.
	 * @return bool True if the menu has nodes, false otherwise
	 */
	public function hasNodes (): bool
	{
		return (isset($this->first));
	}

	/**
	 * Sets initial pointers to child nodes of the menu.
	 * @param NavigationNodeBase $node
	 * @return void
	 */
	protected function initializeChildren(NavigationNodeBase $node)
	{
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
                // a single node remaining in the list; the link to a previous node should not be set anymore
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

	public function removeByLabel(string $label)
	{
		if (isset($this->first)) {
			$node = $this->first;
			while ($node) {
				if ($label == $node->label) {
					if (isset($node->prev_node) && isset($node->next_node)) {
						// matching node is in the middle of the list
						$node->prev_node->next_node = $node->next_node;
						$node->next_node->prev_node = $node->prev_node;
					}
					elseif (isset($node->prev_node)) {
						// matching node found at the end of the list
						$this->last = $node->prev_node;
						unset($this->last->next_node);
					}
					elseif (isset($node->next_node)) {
						// matching node found at the start of the list
						$this->first = $node->next_node;
						unset($this->first->prev_node);
					}
					else {
						// matching node was the only node on the list
						unset($this->first);
						unset($this->last);
					}
					// clean up the removed node
					unset($node);
				}
				$node = ((isset($node->next_node))?($node->next_node):(null));
			}
		}
	}

	/**
	 * Outputs navigation menu markup.
	 * @throws ResourceNotFoundException
	 */
	function render ()
	{
		ContentUtils::renderTemplate(static::getMenuTemplatePath(), array(
			'menu' => &$this
		));
	}

	/**
	 * Sets the CSS class of the breadcrumbs parent element.
	 * @param string $css_class
	 */
	public function setCSSClass(string $css_class)
	{
		$this->css_class = $css_class;
	}

	/**
	 * Sets the path to the navigation template.
	 * @param string $path Path to the navigation menu template.
	 */
	public static function setMenuTemplatePath(string $path)
	{
		static::$menu_template_path = $path;
	}

	/**
	 * Sets the type of the breadcrumb nodes.
	 * @param string $type Name of the class to use as breadcrumb nodes.
	 */
	public static function setNodeType(string $type)
	{
		static::$node_type = $type;
	}
}