<?php
if (strlen($label) > 0 && $input->displayPlaceholder===false):
	include (LITTLED_TEMPLATE_DIR."framework/forms/form-input-label.php");
endif;
?>
<div<?=$css_class ?>><input type="<?=$input->content_type ?>" name="<?=$input->param.((is_numeric($input->index))?("[{$input->index}]"):('')) ?>" id="<?=$input->param ?>" value="<?=htmlentities($input->value) ?>"<?php if ($input->display_placeholder): ?> placeholder="<?=htmlentities($label) ?>"<?php endif; ?> maxlength="<?=$input->sizeLimit ?>" /></div>