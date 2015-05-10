<?php

class FormModel extends BaseModel{
    public function find($id){
        $obj = parent::find($id);
        if(!$obj){
            return $obj;
        }

        $obj['form_data'] = json_decode($obj['form'], true);
        return $obj;
    }

    public function save($data){
        if(!empty($data['form_data'])){
            $org_data = $this->data['form'];
            if(!is_array($org_data)){
                $org_data = array();
            }
            foreach($data['form_data'] as $fkey=>$fvalue){

            }
        }
    }
}