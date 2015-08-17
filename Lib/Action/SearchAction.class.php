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

    public function mediaGallery(){
        $this->assign('media', $this->mediaGalleryJson());
        $this->display();
    }

    public function ajaxMediaGalleryJson($page=1){
        print $this->mediaGalleryJson($page);
    }

    public function upvotePhoto($id){
        if(empty($id) || !is_numeric($id)) return;
        O('Media')->with('id', $id)->setInc('upvote');
        echo 'ok';
    }

    public function downvotePhoto($id){
        if(empty($id) || !is_numeric($id)) return;
        O('Media')->with('id', $id)->setInc('downvote');
        echo 'ok';
    }

    private function mediaGalleryJson($page=1, $per_page=20){
        $media = O('Media')->where("media.type='image' and event_id != 0 and media.user_id is not null")
                    ->join("event on event_id=event.id")->field("media.id id,media.url url,upvote,downvote,event.name name")
                    ->order('id desc')->limit(($page-1) * $per_page, $per_page)->select();
        
        return json_encode($media, true);
    }

}