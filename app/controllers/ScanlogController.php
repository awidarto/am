<?php

class ScanlogController extends AdminController {

    public function __construct()
    {
        parent::__construct();

        $this->controller_name = str_replace('Controller', '', get_class());

        //$this->crumb = new Breadcrumb();
        //$this->crumb->append('Home','left',true);
        //$this->crumb->append(strtolower($this->controller_name));

        $this->model = new Scanlog();
        //$this->model = DB::collection('documents');

    }

    public function getTest()
    {
        $raw = $this->model->where('docFormat','like','picture')->get();

        print $raw->toJSON();
    }


    public function getIndex()
    {

        $this->heads = array(
            array('Timestamp',array('search'=>true,'sort'=>true,'datetimerange'=>true)),
            array('Activity',array('search'=>true,'sort'=>false)),
            array('Actor',array('search'=>true,'sort'=>true)),
            array('Asset Type',array('search'=>true,'sort'=>true)),
            array('Result',array('search'=>true,'sort'=>true)),
            array('Log Message',array('search'=>true,'sort'=>true))
        );

        //print $this->model->where('docFormat','picture')->get()->toJSON();

        $this->title = 'Scan Log';

        return parent::getIndex();

    }

    public function postIndex()
    {
        /*
        "action": "scan",
   "actor": "Andy Awidarto : andy.awidarto@gmail.com",
   "actorId": "50d433404325a24c04000000",
   "assetType": "asset",
   "extId": "55085ee871c950a64e8b458a",
   "logId": "F65146C9D58EEDE",
   "logMessage": "BCF0827O87",
   "result": "BCF0827O87",
   "timestamp": "2015-03-30 16:58:44",
   "uploaded": NumberLong(0),
    */

        $this->fields = array(
            array('mtimestamp',array('kind'=>'datetime','query'=>'like','pos'=>'both','show'=>true)),
            array('action',array('kind'=>'text','query'=>'like','pos'=>'both','show'=>true)),
            array('actor',array('kind'=>'text','query'=>'like','pos'=>'both','show'=>true)),
            array('assetType',array('kind'=>'text','query'=>'like','pos'=>'both','show'=>true)),
            array('result',array('kind'=>'text','query'=>'like','pos'=>'both','show'=>true)),
            array('logMessage',array('kind'=>'text','query'=>'like','pos'=>'both','show'=>true,'attr'=>array('class'=>'expander')))
        );

        $this->def_order_by = 'mtimestamp';
        $this->def_order_dir = 'desc';

        return parent::postIndex();
    }

    public function postAdd($data = null)
    {

        $this->validator = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'email'=> 'required|unique:agents',
            'pass'=>'required|same:repass'
        );

        return parent::postAdd($data);
    }

    public function beforeSave($data)
    {
        unset($data['repass']);
        $data['pass'] = Hash::make($data['pass']);
        return $data;
    }

    public function beforeUpdate($id,$data)
    {
        //print_r($data);

        if(isset($data['pass']) && $data['pass'] != ''){
            unset($data['repass']);
            $data['pass'] = Hash::make($data['pass']);

        }else{
            unset($data['pass']);
            unset($data['repass']);
        }

        //print_r($data);

        //exit();

        return $data;
    }

    public function postEdit($id,$data = null)
    {
        $this->validator = array(
            'firstname' => 'required',
            'lastname' => 'required',
            'email'=> 'required'
        );

        if($data['pass'] == ''){
            unset($data['pass']);
            unset($data['repass']);
        }else{
            $this->validator['pass'] = 'required|same:repass';
        }

        return parent::postEdit($id,$data);
    }

    public function makeActions($data)
    {
        $delete = '<span class="del" id="'.$data['_id'].'" ><i class="icon-trash"></i>Delete</span>';
        $edit = '<a href="'.URL::to('agent/edit/'.$data['_id']).'"><i class="icon-edit"></i>Update</a>';

        $actions = $edit.'<br />'.$delete;
        return $actions;
    }

    public function splitTag($data){
        $tags = explode(',',$data['docTag']);
        if(is_array($tags) && count($tags) > 0 && $data['docTag'] != ''){
            $ts = array();
            foreach($tags as $t){
                $ts[] = '<span class="tag">'.$t.'</span>';
            }

            return implode('', $ts);
        }else{
            return $data['docTag'];
        }
    }

    public function splitShare($data){
        $tags = explode(',',$data['docShare']);
        if(is_array($tags) && count($tags) > 0 && $data['docShare'] != ''){
            $ts = array();
            foreach($tags as $t){
                $ts[] = '<span class="tag">'.$t.'</span>';
            }

            return implode('', $ts);
        }else{
            return $data['docShare'];
        }
    }

    public function namePic($data)
    {
        $name = HTML::link('products/view/'.$data['_id'],$data['productName']);
        if(isset($data['thumbnail_url']) && count($data['thumbnail_url'])){
            $display = HTML::image($data['thumbnail_url'][0].'?'.time(), $data['filename'][0], array('id' => $data['_id']));
            return $display.'<br />'.$name;
        }else{
            return $name;
        }
    }

    public function pics($data)
    {
        $name = HTML::link('products/view/'.$data['_id'],$data['productName']);
        if(isset($data['thumbnail_url']) && count($data['thumbnail_url'])){
            $display = HTML::image($data['thumbnail_url'][0].'?'.time(), $data['filename'][0], array('style'=>'min-width:100px;','id' => $data['_id']));
            return $display.'<br /><span class="img-more" id="'.$data['_id'].'">more images</span>';
        }else{
            return $name;
        }
    }

    public function getViewpics($id)
    {

    }


}
