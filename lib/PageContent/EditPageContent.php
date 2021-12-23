<?php

namespace Littled\PageContent;


class EditPageContent extends PageContent
{
	/** @var string URL to use to redirect to another page after completing an edit */
	public $url;
	/** @var string Status of edit operation to be displayed in page content. */
	public $status;

	function __construct()
	{
		parent::__construct();
		$this->url = "";
		$this->status = "";
	}
}