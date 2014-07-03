<?php
$data = $params->get('data');
$error = $params->get('error');
$warning = $params->get('warning');
?>
<form action="<?php echo Lib_Helper_Url::main(); ?>/main/save" method="POST">
<?php if ($data['id']): ?>
<input type='hidden' name='id' value="<?php echo $data['id'];?>">
<?php endif;?>
<div class='form_news'>
<h2><?php if ($data['id']): ?>Редактирование<?php else: ?>Добавление<?php endif;?> новости</h2>
<?php if ($error): ?>
<div id='error'>
    <?php echo $error; ?>
</div>
<?php else: ?>
    <?php if ($warning): ?>
    <div id='warning'>
        Ошибка: <?php echo $warning; ?>
    </div>
    <?php endif;?>
    
    <div id='subject'>
        <label>Заголовок</label>
        <input type="text" name="subject" value="<?php echo $data['subject']; ?>">
    </div>
    
    <div id='text'>
        <label>Текст</label>
        <textarea name="text"><?php echo $data['text']; ?></textarea>
    </div>
    
    <div id='datetime'>
        <label>Дата-Время</label>
        <input type="text" name="datetime" value="<?php echo $data['datetime']; ?>">
    </div>
    
    <div id='save'>
        <input type="submit" value="<?php if ($data['id']): ?>Сохранить<?php else: ?>Добавить<?php endif;?>">
    </div>
<?php endif;?>
</div>
</form>