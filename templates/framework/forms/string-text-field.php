<?php
/** @var Littled\Request\RequestInput $input */
/** @var string $label */
/** @var string $css_class */
if (strlen($label) > 0 && $input->displayPlaceholder===false):
	include (LITTLED_TEMPLATE_DIR."framework/forms/form-input-label.php");
endif;
?>
<div<?=$css_class ?>><input type="<?=$input->contentType?>" name="<?=$input->key.((is_numeric($input->index))?("[{$input->index}]"):('')) ?>" id="<?=$input->key ?>" value="<?=htmlentities($input->value) ?>"<?php if ($input->displayPlaceholder): ?> placeholder="<?=htmlentities($label) ?>"<?php endif; ?> maxlength="<?=$input->sizeLimit ?>" /></div>