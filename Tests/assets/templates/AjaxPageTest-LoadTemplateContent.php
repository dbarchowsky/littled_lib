<?php
/** @var string $custom_var */
?>
<div class="test-container">
    <div>custom context value: <?=$custom_var ?></div>
    <div>default context value: <?php if(!isset($content)): ?>undefined<?php endif; ?></div>
</div>
