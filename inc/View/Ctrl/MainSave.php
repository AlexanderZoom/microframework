<?php
$success = $params->get('success');
?>
<div class='form_news'>
<h2>Новость <?php if ($success == 'edit'): ?>сохранена<?php else: ?>добавлена<?php endif;?></h2>
<div id="link">
<a href='<?php echo Lib_Helper_Url::main(); ?>/main'>Перейти на главную страницу новостей</a>
<?php if ($success == 'add'): ?><a href='<?php echo Lib_Helper_Url::main(); ?>/main/add'>Добавить еще одну новость</a><?php endif;?>
</div>
</div>