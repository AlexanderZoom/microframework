<?php
$news = $params->get('news');

$news_on_page = $params->get('news_on_page');
$news_count = $params->get('news_count');
$news_page = $params->get('news_page');


$pager_url = Lib_Helper_Url::main() . '/main/index';
$pager = Lib_Helper_Html::paginator($news_count, $news_on_page, $news_page);
$pager_html = '';

?>

<?php if(count($pager['pages'])>1): ob_start();?>
<div class="pager">
<ul>
<li><a href="<?php echo $pager_url . $pager['first']; ?>.html">Первая</a></li>
<li><a href="<?php echo $pager_url . $pager['prev']; ?>.html">Пред.</a></li>
<?php foreach ($pager['pages'] as $p):?>
<li><?php if($p != $pager['current']):?>
     <a href="<?php echo $pager_url . $p; ?>.html"><?php echo $p?></a>
    <?php else:?>
     <span><?php echo $p?></span>
    <?php endif;?>
</li>
<?php endforeach;?>
<li><a href="<?php echo $pager_url . $pager['next']; ?>.html">След.</a></li>
<li><a href="<?php echo $pager_url . $pager['last']; ?>.html">Последняя</a></li>

</ul>
</div>
<?php $pager_html = ob_get_clean(); endif;?>

<?php if(count($news)):?>
<div class="news">
<?php echo $pager_html;?>
<?php foreach ($news as $newsItem):?>
<div class="news_item">
    <div id="subject"><?php echo $newsItem->subject; ?></div>
    <div id="info"><span>дата: <?php echo $newsItem->datetime; ?></span>
                   <span><a href="<?php echo Lib_Helper_Url::main()?>/main/edit/<?php echo $newsItem->id; ?>">Редактировать</a></span>
                   <span><a href="<?php echo Lib_Helper_Url::main()?>/main/delete/<?php echo $newsItem->id; ?>">Удалить</a></span>
    </div>
    <div id="text"><?php echo nl2br($newsItem->shortText); ?></div>
    <div id="read_more"><a href="<?php echo Lib_Helper_Url::main()?>/main/item<?php echo $newsItem->id;?>.html">Читать далее</a></div>
</div>
<?php endforeach;?>
<?php echo $pager_html;?>
</div>
<?php endif;?>