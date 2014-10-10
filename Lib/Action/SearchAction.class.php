<?php

class SearchAction extends BaseAction{
    public function result($q){
        $total_count = OO('XSearch')->count($q);
        $record_per_page = C('LIST_RECORD_PER_PAGE');
        if(isset($_GET['p'])){
            $start_from = ($_GET['p']-1) * $record_per_page;
        }
        else{
            $start_from = 0;
        }
        $results = OO('XSearch')->search($q, $record_per_page, $start_from);

        // generate url for results
        for($i=0;$i<count($results);$i++){
            if(substr($results[$i]['pid'], 0, 4) == 'user'){
                $results[$i]['url'] = U('User/view') . '/id/' . substr($results[$i]['pid'], 5);
            }
            elseif(substr($results[$i]['pid'], 0, 5) == 'event'){
                $results[$i]['url'] = U('Event/view') . '/id/' . substr($results[$i]['pid'], 6);
            }
        }

        // generae the pager
        import("@.Classes.TBPage");
        $pager = new TBPage($total_count, $record_per_page);


        $this->assign('pager_html', $pager->show());
        $this->assign('results', $results);

        $this->display();
    }

}