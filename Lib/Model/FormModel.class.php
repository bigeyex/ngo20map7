<?php

class FormModel extends BaseModel{
    protected $accepted_fields = array(
         'org_name'=>250,
         'org_province'=>50,
         'org_city'=>50,
         'org_street'=>50,
         'org_type'=>50,
         'org_contact'=>50,
         'org_mobile'=>50,

         'org_phone'=>50,
         'org_email'=>50,
         'org_weibo'=>50,
         'org_wechat'=>250,
         'org_website'=>250,
        );

    public function find($id){
        $obj = parent::find($id);
        if(!$obj){
            return $obj;
        }

        $obj['form_data'] = json_decode($obj['form'], true);
        return $obj;
    }

    public function save($data){
        $org_data = $this->find($data['id']);
        $org_form_data = $org_data['form_data'];
        if(!empty($data['form_data']) && is_array($org_data['form_data'])){
            $form_data = $data['form_data'];
            foreach($form_data as $fkey=>$fvalue){
                if(isset($this->accepted_fields[$fkey]) && $this->accepted_fields[$fkey]>=mb_strlen($fvalue)){
                    $org_form_data[$fkey] = $fvalue;
                }
            }
            $data['form'] = json_encode($org_form_data, true);
        }
        
        parent::save($data);
    }
}