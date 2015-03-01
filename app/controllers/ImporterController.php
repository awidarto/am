<?php

class ImporterController extends AdminController {

    public $controller_name;

    public $form_framework = 'TwitterBootstrap';

    public $upload_dir;

    public $input_name;

    public function __construct()
    {
        parent::__construct();

        $this->controller_name = str_replace('Controller', '', get_class());

        //$this->crumb = new Breadcrumb();
        //$this->crumb->append('Home','left',true);
        //$this->crumb->append(strtolower($this->controller_name));
        $this->title = 'Data Import';

        $this->model = new Asset();
        //$this->model = DB::collection('documents');

    }

    public function getIndex()
    {
        $controller_name = strtolower($this->controller_name);

        $this->title = ($this->title == '')?Str::plural($this->controller_name):Str::plural($this->title);

        Breadcrumbs::addCrumb($this->title,URL::to($controller_name));

        return View::make('importer.importinput')
            ->with('title', 'Assets Data')
            //->with('input_name',$this->input_name)
            ->with('importkey', $this->importkey)
            ->with('back',strtolower($this->controller_name))
            ->with('submit',strtolower($this->controller_name).'/uploadimport');
    }

    public function postUploadimport()
    {
        $file = Input::file('inputfile');

        $headindex = Input::get('headindex');

        $firstdata = Input::get('firstdata');

        $importkey = (!is_null($this->importkey))?Input::get('importkey'):$this->importkey;

        //$importkey = $this->importkey;

        $rstring = str_random(15);

        $destinationPath = realpath('storage/upload').'/'.$rstring;

        $filename = $file->getClientOriginalName();
        $filemime = $file->getMimeType();
        $filesize = $file->getSize();
        $extension =$file->getClientOriginalExtension(); //if you need extension of the file

        $filename = str_replace(Config::get('kickstart.invalidchars'), '-', $filename);

        $uploadSuccess = $file->move($destinationPath, $filename);

        $fileitems = array();

        if($uploadSuccess){

            $xlsfile = realpath('storage/upload').'/'.$rstring.'/'.$filename;

            //$imp = Excel::load($xlsfile)->toArray();

            $imp = array();

            Excel::load($xlsfile,function($reader) use (&$imp){
                $imp = $reader->toArray();
            })->get();

            $headrow = $imp[$headindex - 1];

            //print_r($headrow);

            $firstdata = $firstdata - 1;

            $imported = array();

            $sessobj = new Importsession();

            $sessobj->heads = array_values($headrow);
            $sessobj->isHead = 1;
            $sessobj->sessId = $rstring;
            $sessobj->save();

            for($i = $firstdata; $i < count($imp);$i++){

                $rowitem = $imp[$i];

                $imported[] = $rowitem;

                $sessobj = new Importsession();

                $rowtemp = array();
                foreach($rowitem as $k=>$v){
                    $sessobj->{ $headrow[$k] } = $v;
                    $rowtemp[$headrow[$k]] = $v;
                }
                $rowitem = $rowtemp;

                $sessobj->sessId = $rstring;
                $sessobj->isHead = 0;
                $sessobj->save();

            }

        }

        $this->backlink = strtolower($this->controller_name);

        $commit_url = $this->backlink.'/commit/'.$rstring;

        return Redirect::to($commit_url);

    }

    public function getCommit($sessid)
    {
        $heads = Importsession::where('sessId','=',$sessid)
            ->where('isHead','=',1)
            ->first();

        $heads = $heads['heads'];

        $imports = Importsession::where('sessId','=',$sessid)
            ->where('isHead','=',0)
            ->get();

        $headselect = array();

        foreach ($heads as $h) {
            $headselect[$h] = $h;
        }

        $title = $this->controller_name;

        $submit = strtolower($this->controller_name).'/commit/'.$sessid;

        $controller_name = strtolower($this->controller_name);

        $this->title = ($this->title == '')?Str::plural($this->controller_name):Str::plural($this->title);

        Breadcrumbs::addCrumb($this->title,URL::to($controller_name));

        Breadcrumbs::addCrumb('Import '.$this->title,URL::to($controller_name.'/import'));

        Breadcrumbs::addCrumb('Preview',URL::to($controller_name.'/import'));

        return View::make('shared.commitselect')
            ->with('title',$title)
            ->with('submit',$submit)
            ->with('headselect',$headselect)
            ->with('heads',$heads)
            ->with('back',$controller_name.'/import')
            ->with('imports',$imports);
    }

    public function postCommit($sessid)
    {
        $in = Input::get();

        $importkey = $in['edit_key'];

        $selector = $in['selector'];

        $edit_selector = isset($in['edit_selector'])?$in['edit_selector']:array();

        foreach($selector as $selected){
            $rowitem = Importsession::find($selected)->toArray();

            $do_edit = in_array($selected, $edit_selector);

            if($importkey != '' && !is_null($importkey) && isset($rowitem[$importkey]) && $do_edit ){
                $obj = $this->model
                    ->where($importkey, 'exists', true)
                    ->where($importkey, '=', $rowitem[$importkey])->first();

                if($obj){

                    foreach($rowitem as $k=>$v){
                        if($v != ''){
                            $obj->{$k} = $v;
                        }
                    }

                    $obj->save();
                }else{

                    unset($rowitem['_id']);
                    $rowitem['createdDate'] = new MongoDate();
                    $rowitem['lastUpdate'] = new MongoDate();

                    $rowitem = $this->beforeImportCommit($rowitem);

                    $this->model->insert($rowitem);
                }


            }else{

                unset($rowitem['_id']);
                $rowitem['createdDate'] = new MongoDate();
                $rowitem['lastUpdate'] = new MongoDate();

                $rowitem = $this->beforeImportCommit($rowitem);

                $this->model->insert($rowitem);

            }


        }

        $this->backlink = strtolower($this->controller_name);

        return Redirect::to($this->backlink);

    }

    public function postExtract()
    {
        $heads = Input::get('ext');

        unset($heads[0]);
        unset($heads[1]);

        file_put_contents(realpath($this->upload_dir).'/heads.json', json_encode($heads));

        return Response::json(array('status'=>'OK'));
    }

    public function missingMethod($param = array())
    {
        //print_r($param);
    }

}