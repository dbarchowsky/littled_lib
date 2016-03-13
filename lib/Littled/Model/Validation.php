<?php
namespace Littled\Model;

require_once ("./Utilities.php");
require_once( "../Forms/RequestInput.php" );

use Littled\Forms\RequestInput;

class Validation extends Utilities
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Checks if the class property is an input object and should be used for
	 * various operations such as updating or retrieving data from the database,
	 * or retrieving data from forms.
	 * @param string $key Name of the class property.
	 * @param mixed $item Value of the class property.
	 * @param array $used_params Array containing a list of the objects that
	 * have already been listed as input properties.
	 * @return boolean True if the object is an input class and should be used to update the database. False otherwise.
	 */
	protected function is_input(&$key, &$item, &$used_params)
	{
		$is_input = ( ( $item instanceof RequestInput) &&
		              ($key != "id") &&
			($key != "index") &&
			($item->db_field==true));
		if ($is_input) {
			/* Check if this item has already been used as in input property.
			 * This prevents references used as aliases of existing properties
			 * from being included in database queries.
			 */
			if (in_array($item->param, $used_params)) {
				$is_input = false;
			}
			else {
				/* once an input property is marked as such, track it so it
				 * can't be included again.
				 */
				$used_params[] = $item->param;
			}
		}
		return ($is_input);
	}

}