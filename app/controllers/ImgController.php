<?php

class ImgController extends AdminController {

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

    public function getGen($imgid, $imgname)
    {

    }
}