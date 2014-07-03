<?php
$newsItem = $params->get('news');
?>
<div class="news">
<div class="news_item">
    <div id="subject"><?php echo $newsItem->subject; ?></div>
    <div id="info"><span>дата: <?php echo $newsItem->datetime; ?></span>
                   <span><a href="<?php echo Lib_Helper_Url::main()?>/main/edit/<?php echo $newsItem->id; ?>">Редактировать</a></span>
                   <span><a href="<?php echo Lib_Helper_Url::main()?>/main/delete/<?php echo $newsItem->id; ?>">Удалить</a></span>
    </div>
    <div id="text"><?php echo nl2br($newsItem->fullText); ?></div>
</div>
</div>