<?php
namespace Littled\App;


class LittledGlobals
{
	/** @var string ID request variable name. */
	const ID_PARAM = 'id';

	/** @var string Request variable name to commit operations. */
	const P_COMMIT = 'commit';
	/** @var string Request variable name to cancel operations. */
	const P_CANCEL = 'cancel';
	/** @var string Request variable name containing referring URLs. */
	const P_REFERER = 'ref';
}