<?php

class XSearch{
    function index($prefix, $id, $subject, $content, $date){
        if(file_exists(C('SEARCH_API_PATH'))){
            require_once C('SEARCH_API_PATH');
            $xs = new XS(C('APP_NAME'));
            $xsIndex = $xs->index;
            $data = array(
                'pid'=> "$prefix_$id",
                'subject' => $subject,
                'content' => $content,
                'chrono' => date($date),
                );
            $doc = new XSDocument;
            $doc->setFields($data);
            
            $xsIndex->update($doc);
        }
    }

    function search($key, $limit=5, $pass=0){
        if(file_exists(C('SEARCH_API_PATH'))){
            require_once C('SEARCH_API_PATH');
            $xs = new XS(C('APP_NAME'));
            $xsSearch = $xs->search;
            $docs = $xsSearch->setQuery($key)->setLimit($limit, $pass)->search();
        }
    }

    function count($key){
        if(file_exists(C('SEARCH_API_PATH'))){
            require_once C('SEARCH_API_PATH');
            $xs = new XS(C('APP_NAME'));
            $xsSearch = $xs->search;
            $docs = $xsSearch->setQuery($key)->count();
        }
    }
}