<?php

class XSearch{
    function index($prefix, $id, $subject, $content, $date=null){
        if(file_exists(C('SEARCH_API_PATH'))){
            require_once C('SEARCH_API_PATH');
            try{
                $xs = new XS(C('APP_NAME'));
                $xsIndex = $xs->index;
                if($date===null){
                    $date = time();
                }
                $data = array(
                    'pid'=> $prefix.'_'.$id,
                    'subject' => $subject,
                    'message' => $content,
                    'date' => strtotime($date),
                    );
                $doc = new XSDocument;
                $doc->setFields($data);
                
                $xsIndex->update($doc);
            }
            catch(XSException $e){

            }
        }
    }

    function search($key, $limit=5, $pass=0){
        if(file_exists(C('SEARCH_API_PATH'))){
            require_once C('SEARCH_API_PATH');
            $xs = new XS(C('APP_NAME'));
            $xsSearch = $xs->search;
            $docs = $xsSearch->setQuery($key)->setLimit($limit, $pass)->search();
            $result = array();
            foreach($docs as $doc){
                $data = array(
                    'pid' => $doc->pid,
                    'subject' => $xsSearch->highlight($doc->subject),
                    'message' => $xsSearch->highlight($doc->message),
                    'date' => date("Y-m-d", $doc->chrono)
                    );
                $result[] = $data;
            }
            return $result;
        }
    }

    function delete($prefix, $key){
        if(file_exists(C('SEARCH_API_PATH'))){
            try{
                require_once C('SEARCH_API_PATH');
                $xs = new XS(C('APP_NAME'));
                $xsIndex = $xs->index;
                $xsIndex->del($prefix.'_'.$id);
            }
            catch(XSException $e){

            }
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