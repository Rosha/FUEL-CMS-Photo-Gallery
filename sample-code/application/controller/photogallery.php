<?php
class PhotoGallery extends CI_Controller {

	function __construct()
	{
		parent::__construct();
        $this->load->library('session');
	}

	function index()
	{
        $vars = array();
        $vars['heading'] = 'Photo Gallery';
        
        // load model and retrieve gallery pics
        $this->load->model('Gallery_model');
        $vars['Gallery'] = $this->Gallery_model->GetGalleryPics(2, 1);      

		$this->fuel->pages->render('photogallery', $vars);  
    }

}

