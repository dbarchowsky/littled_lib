<?php
/** @var Littled\Request\RequestInput $input */
?>
<input type="text"<?php if ($input->cssClass): ?> class="<?=$input->cssClass ?>"<?php endif; ?>name="<?=$input->key.(($input->index===null)?(""):("[{$input->index}]"))?>" id="<?=$input->key.$input->index ?>" value="<?=((strlen($input->error)>0)?($input->error):($input->value))?>" maxlength="<?=$input->sizeLimit?>" />
