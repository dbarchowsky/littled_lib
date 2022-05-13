<?php
namespace Littled\Ajax;

use Littled\Validation\Validation;

abstract class APIRoute extends JSONResponse
{
	public ?object $request_data;

    function __construct()
    {
        session_start();
        parent::__construct();
    }

	/**
	 * Collect request data from the client.
	 * @param bool $bypass_csrf Optional flag to override CSRF validation. Default value is FALSE.
	 */
    public function collectRequestData(bool $bypass_csrf=false)
    {
		$json = file_get_contents('php://input');
		$this->request_data = json_decode($json);

        /** prevent cross-site attacks */
        if (!$bypass_csrf && !Validation::validateCSRF()) {
            $this->returnError("Invalid request.");
        }
    }

	public abstract function processRequest();
}