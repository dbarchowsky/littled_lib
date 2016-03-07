<div class="alert alert-error clearfix<?=((isset($form_error_message) && $form_error_message)?(""):(" hidden"))?>">
<?php
if (isset($error_header_template_path) && $error_header_template_path) {
	include ($error_header_template_path);
}
if (isset($form_error_message) && $form_error_message): ?>
	<p><?=preg_replace("/\n/", "<br />\n", $form_error_message) ?></p>
<?php endif; ?>
</div>
