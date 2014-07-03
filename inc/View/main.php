<!DOCTYPE html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <title>Welcome</title>
    <style type="text/css">
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
	margin: 0;
	padding: 0;
	border: 0;
	font-size: 100%;
	font: inherit;
	vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
	display: block;
}
body {
	line-height: 1;
}
ol, ul {
	list-style: none;
}
blockquote, q {
	quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after {
	content: '';
	content: none;
}
table {
	border-collapse: collapse;
	border-spacing: 0;
}

        body {
        	width:100%;
        }
        
        .container {
        	width:100%;
        }
        
        .form_news{
        	width:600px;
        	margin:0 auto;
        }
        
        .form_news > h2 {
        	text-align:center;
        	font-size: 21px;
            font-weight: bold;
        }
        
        .form_news > div {
        	margin-top: 15px;
        }
        
        .form_news label {
        	font-size:12px;
        	font-weight: bold;
        	display:block;
        }
        
        .form_news input {
        	width: 100%;
        }
        
        .form_news > #datetime > input {
        	text-align:right;
        }
        
        .form_news > #text > textarea {
        	width: 100%;
        	height: 350px;
        }
        
        .form_news #save {
        	text-align: center;
        }
        
        .form_news #save input[type=submit]{
        	width: 250px;
        	height: 45px;
        	font-size:14px;
        	font-weight: bold;
        }
        
        .form_news #warning {
        	color:#FF0000;
        	
        	font-size:14px;
        	
        }
        
        .form_news #link {
        	text-align: center;
        }
        
        .form_news #error {
        	font-weight:bold;
        	color:#FF0000;
        	font-size:16px;
        }
        
        .news {
        	width:800px;
        	margin:0 auto;
        }
        
        
        .news .pager {
        	color: #888;
            font-size: 12px;
            line-height: 18px;
            overflow: hidden;
            text-align: center;
        }
        
        .pager > ul{
        	margin-bottom: 24px;
        	padding: 5px;
            margin: 0;
            list-style: none;
        }
        
        .pager > ul > li {
        	display: inline;
            list-style: none;
        }
        
        .pager > ul > li > a{
        	text-decoration: none;
        	background: #e7e7e7;
            border: 1px solid #d7d7d7;
            color: #666666;
            margin-right: 4px;
            padding: 3px 6px;
            text-align: center;
        }
        
        .pager > ul > li > a:hover {
        	background: #d7d7d7;
	        color: #888888;
        }
        
        .pager > ul > li > span{
        	color: #666666;
            background: #f7f7f7;
            border: 1px solid #e7e7e7;
            margin-right: 4px;
            padding: 3px 6px;
        }
        
        
        
         .news_item {
         	margin-bottom:25px;
         }
         
         .news_item > #subject{
         	color: #000;
            font-size: 21px;
            font-weight: bold;
            line-height: 1.3em;
            margin-bottom: 0;
         }
          
         .news_item > #info{
         	color: #888;
            font-size: 12px;
         }
          
         .news_item > #text{
         	color: #333;
            font-size: 16px;
            line-height: 24px;
         	font-family: Georgia, "Bitstream Charter", serif;
         }
          
         .news_item > #read_more{
         	
         }
         
         .menu {
         	width:800px;
        	margin:0 auto;
         	background: #f7f7f7;
         	text-align:right;
         	margin-bottom: 30px;
         }
         
        .menu > ul{
        	margin-bottom: 24px;
        	padding: 5px;
            margin: 0;
            list-style: none;
        }
        
        .menu > ul > li {
        	display: inline;
            list-style: none;
        }
    </style>
  </head>

  <body>
    <div class='container'>
    <div class="menu">
        <ul>
        <li><a href="<?php echo Lib_Helper_Url::main()?>/">Главная</a></li>
        <li><a href="<?php echo Lib_Helper_Url::main()?>/main/add">Добавить Новость</a></li>
        </ul>
    </div>
<?php echo $content; ?>
    </div>
</body>
</html>