<?php


class BNBPage{
    protected $htmlLeftArrow = '<span class="fa fa-caret-left pager-prev"></span>';
    protected $htmlRightArrow = '<span class="fa fa-caret-right pager-next"></span>';
    protected $htmlGap = '<span class="pager-gap">...</span>';
    protected function htmlPageNumber($p){
        if($p == $this->page){
            $active = 'active';
        }
        else{
            $active = '';
        }
        return '<span class="pager-number '.$active.'">'.$p.'</span>';
    }


    public $count;
    public $rowsPerPage;
    public $firstRow;
    public $page;
    public $totalPages;

    public function build($count, $listRows=20, $page=1){
        $this->rowsPerPage = $listRows;
        $this->count = $count;
        $totalPages = ceil($count / $listRows);
        $this->totalPages = $totalPages;
        if($page > $totalPages) $page = $totalPages;
        if($page < 1) $page = 1;
        $this->firstRow = ($page - 1) * $listRows;
        $this->page = $page;
        return $this;
    }

    public function show(){
        $build = '';
        $page = $this->page;
        if($page != 1){ 
            // (<-prev)
            $build .= $this->htmlLeftArrow;
        }

        if($page < 5){
            // (1) (2) (3) 4<-page
            for($i=1;$i<$page;$i++){
                $build .= $this->htmlPageNumber($i);
            }
        }
        else{
            // (1) (...) (7) 8<-page
            $build .= $this->htmlPageNumber(1);
            $build .= $this->htmlGap;
            $build .= $this->htmlPageNumber($page-1);
        }


        //  (page) 
        $build .= $this->htmlPageNumber($page);

        if($page > $this->totalPages-4){
            // 34<-page (35) (36) (37<-totalPages)
            for($i=$page+1; $i<=$this->totalPages; $i++){
                $build .= $this->htmlPageNumber($i);
            }
        }
        else{
            // page (page+1) ... (totalPages)
            $build .= $this->htmlPageNumber($page+1);
            $build .= $this->htmlGap;
            $build .= $this->htmlPageNumber($this->totalPages);
        }

        if($page != $this->totalPages){ 
            // (next->)
            $build .= $this->htmlRightArrow;
        }

        return $build;
    }
}