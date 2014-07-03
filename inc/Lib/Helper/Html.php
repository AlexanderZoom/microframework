<?php
class Lib_Helper_Html {
    
    public static function paginator($total, $limit, $page = 1,
            $showPagesLeft = 10, $showPagesRight = 10){
    
        $showPages = $showPagesRight + $showPagesLeft;
        $pages = ceil( $total / $limit );
    
        if ($page < 1)
            $page = 1;
    
        $beginPage = $page > 1 ? $showPagesLeft - $page : 1;
        $endPage = 0;
    
        if ($beginPage < 1)
        {
            $beginPage = 1;
            $endPage = $showPages + $beginPage;
    
        } else
        {
            $endPage = $showPages + $beginPage;
        }
    
        if ($endPage > $pages)
            $endPage = $pages;
    
        if ($endPage == $pages && ($endPage - $beginPage) < $showPages)
            $beginPage = $endPage - $showPages;
    
        if ($beginPage < 1)
            $beginPage = 1;
    
        if ($page > $endPage)
            $page = $beginPage;
    
        $prevPage = $page - 1;
        if ($prevPage < 1)
            $prevPage = 1;
    
        $nextPage = $page + 1;
        if ($nextPage > $pages)
            $nextPage = $pages;
    
        $result = array ('first' => $beginPage,
        'prev' => $prevPage,
        'pages' => array (),
        'next' => $nextPage,
        'last' => $pages,
        'current' => $page );
    
        for($i = $beginPage; $i <= $endPage; $i ++)
        {
            $result ['pages'] [] = $i;
    }
    
    		return $result;
    
    	}
}