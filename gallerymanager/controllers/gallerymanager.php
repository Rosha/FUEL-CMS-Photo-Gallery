<?php  
require_once(FUEL_PATH.'/libraries/Fuel_base_controller.php');

class gallerymanager extends Fuel_base_controller 
{
    public  $view_location = 'gallerymanager';

    private $gallery_path;
	public  $nav_path;
    public  $nav_title;
    	
    private $firephp;
    private $page_title;
    private $vars;

	function __construct()
	{
		parent::__construct();

        $this->load->helper('ajax');
        $this->load->model('Gallery_model');
        $this->load->library('session');     
                
        //require_once(APPPATH.'/libraries/phpfire/FirePHP.class.php');
        //$this->firephp = FirePHP::getInstance(true);
        //$this->firephp->log('FirePHP Loaded');      
        
        $this->page_title = "Gallery Manager - ";   

        $this->gallery_path = $this->fuel->gallerymanager->config('path');
        $this->nav_path     = $this->fuel->gallerymanager->config('nav_path');
        $this->nav_title    = $this->fuel->gallerymanager->config('nav_title');
        
        $this->vars['nav_selected'] = $this->nav_path;
	}

	function help()
    {
        $vars = $this->vars;

        // load actions
        $vars['current_page'] = 'help';
        $actions = $this->load->module_view(GALLERYMANAGER_FOLDER, '_blocks/list_actions', $vars, TRUE);
        $vars['actions'] = $actions; 

        $crumbs = array($this->nav_path => $this->nav_title, '' => 'Gallery Manager');
        $this->fuel->admin->set_titlebar($crumbs, 'ico_gallerymanager');
        $this->fuel->admin->render('help', $vars);        
    }

	function index()
	{        
        $vars = $this->vars;

        $vars['page_title']     = $this->page_title."Gallery Manager";                
        $vars['Galleries']      = $this->Gallery_model->GetGalleries();
        
        $GalleryID = $this->session->userdata('GalleryID');

        if ($GalleryID != "") {
            $vars['GalleryID']      = $GalleryID;
            $GalleryGroups          = $this->Gallery_model->GetGroups($GalleryID);          

            $vars['GalleryGroups']  = $GalleryGroups;
        }
		
        // load actions
        $vars['current_page'] = '';
        $actions = $this->load->module_view(GALLERYMANAGER_FOLDER, '_blocks/list_actions', $vars, TRUE);
        $vars['actions'] = $actions; 

        $crumbs = array($this->nav_path => $this->nav_title, '' => 'Gallery Manager');
        $this->fuel->admin->set_titlebar($crumbs, 'ico_gallerymanager');
        $this->fuel->admin->render('gallery_manager', $vars);
    }

        function refresh()
        {   
            $GalleryID = $this->input->post('GalleryID');     
            $GroupID   = $this->input->post('GroupID');     
            
            //$this->firephp->log('Gallery Refresh - GalleryID: ' . $GalleryID);   
            //$this->firephp->log('Gallery Refresh - GroupID: '   . $GroupID);   

            // make sure a gallery id is being passed and it selected.
            if ($GalleryID == '') {
                $html = "<p>Please select a 'Gallery Page'.</p>";
                $this->session->set_userdata('GalleryID',       NULL);  
                $this->session->set_userdata('GalleryFolder',   NULL);
                echo $html;
                return;              
            }

            // default message if there aren't any 'Gallery Groups'.
            $html = "<p>Please add 'Gallery Groups' to this gallery.</p>";
            
            $Gallery = $this->Gallery_model->GetGalleryOnly($GalleryID);

            if ($Gallery) {
                $this->session->set_userdata('GalleryID',       $Gallery[0]->ID);  
                if (isset($GroupID)) {
                    $this->session->set_userdata('GroupID', $GroupID);    
                }         
            }   
            
            $GalleryGroups = $this->Gallery_model->GetGroups($GalleryID);            
            if (count($GalleryGroups) > 0) {
                $html = '<ul class="supercomboselect_list" id="tests_left">';
                foreach ($GalleryGroups as $Gallery) {
                    if ($GroupID == $Gallery->GroupID) {                        
                        $html .= '<li id="group_' . $Gallery->GroupID . '" class="gallery_selected">';
                    } else {
                        $html .= '<li id="group_' . $Gallery->GroupID . '">';   
                    }                    
                    $html .= '<a href="javascript:void(0);" class="delete_gallery_group" title="' . $Gallery->GroupTitle . '" data-groupid="' . $Gallery->GroupID . '">x</a> - <a href="javascript:void(0);" class="btnSelectGallery" data-groupid="' . $Gallery->GroupID . '"><strong>' . $Gallery->GroupTitle . '</strong></a>';
                    $html .= '<div style="display: inline-block; float: right; text-align: center;"><a href="javascript:void(0);" class="btnSelectGallery" data-groupid="' . $Gallery->GroupID . '">Select</a></div>';
                    $html .= '</li>';
                }
                $html .= '</ul>';            
            }
        
            echo $html; 
        }

        function reorder_photos()
        {
            $sort_order = $this->input->post("sort_order");
            $GroupId    = $this->input->post("GroupId");

            $this->Gallery_model->UpdatePhotoOrder($GroupId, $sort_order);      
        }

    function add_group() 
    {
        $vars = $this->vars;

        $GalleryID = $this->session->userdata('GalleryID');

        if ($GalleryID == '') {
            // send back to gallery manager with error of not selecting a gallery page.
            // needs temp data error message
            redirect(fuel_url($this->nav_path), 'refresh');
        }

        $vars['GalleryID']  = $GalleryID;
        $vars['Galleries']  = $this->Gallery_model->GetGalleries();
        $vars['page_title'] = $this->page_title."Add Gallery Group";                

        // load actions
        $vars['current_page'] = 'add_gallery_group';
        $actions = $this->load->module_view(GALLERYMANAGER_FOLDER, '_blocks/list_actions', $vars, TRUE);
        $vars['actions'] = $actions;    

        $return = array(
            "Status" => "0",
            "html"   => ""
        );  

        if ($this->input->post("GroupFolder") != "") {
            try {
                $GalleryID = $this->input->post("GalleryID");
                $Gallery = $this->Gallery_model->GetGallery($GalleryID);
                
                $GroupTitle  = $this->input->post("GroupTitle");                
                $GroupActive = $this->input->post("Active");

                $GroupFolder = $this->input->post("GroupFolder");
                $GroupFolder = strtolower(trim($GroupFolder));
                $GroupFolder = str_replace(' ', '-', $GroupFolder);

                $GalleryGroup = new Gallery_model();
                $GalleryGroup->GalleryID    = $GalleryID;
                $GalleryGroup->GroupTitle   = $GroupTitle;
                $GalleryGroup->Folder       = $GroupFolder;
                $GalleryGroup->Active       = $GroupActive;

                // gallery manager
                $path = './assets/gallerymanager/';
                if (!is_dir($path)) {
                    mkdir($path);    
                }  
                
                // gallery folder
                $path = './assets/gallerymanager/'.strtolower($Gallery[0]->GalleryFolder);
                if (!is_dir($path)) {
                    mkdir($path);    
                }  
                
                // group folder
                $path = './assets/gallerymanager/'.strtolower($Gallery[0]->GalleryFolder.'/'.$GroupFolder);
                if (!is_dir($path)) {
                    mkdir($path);    
                }  
                    
                $result = $this->Gallery_model->CreateGalleryGroup($GalleryGroup);

                if ($result['Status'] == '1') {
                    redirect(fuel_url($this->nav_path), 'refresh');
                }
            } catch (Exception $ex) {
                $return = array(
                    "Status" => "0",
                    "html"   => "" . $ex->Message
                );            
            }            
        }
         
        $crumbs = array($this->nav_path => $this->nav_title, '' => 'Gallery Manager');
        $this->fuel->admin->set_titlebar($crumbs, 'ico_gallerymanager');
        $this->fuel->admin->render('add_group', $vars);
    }


    function ajax_add_gallery()
    {
        if (!is_ajax()) { die("AJAX CALLS ONLY"); }

        $return = array(
            "Status" => "0",
            "html"   => ""
        );

        try {
            $GroupTitle  = $this->input->post("form_GroupTitle");
            $GroupFolder = $this->input->post("form_GalleryFolder");
            $GroupActive = $this->input->post("form_Active");

            $GalleryGroup = new Gallery_model();
            $GalleryGroup->GroupTitle = $GroupTitle;
            $GalleryGroup->Folder = $GroupFolder;
            $GalleryGroup->Active = $GroupActive;

            $this->Gallery_model->CreateGalleryGroup($GalleryGroup);

            mkdir('./assets/explore/'+$GroupFolder);

        } catch (Exception $ex) {
            $return = array(
                "Status" => "0",
                "html"   => "" . $ex->Message
            );            
        }

        echo json_encode($return);
    }


    function folder_valid() 
    {
        $GalleryID      = strtolower($this->input->post("GalleryID"));
        $FolderPath     = strtolower($this->input->post("GroupFolder"));        

        $Gallery = $this->Gallery_model->GetGallery($GalleryID);

        // RegEx for spaces and illegal characters
        if (!preg_match("/^[a-z]+$/i", $FolderPath)) {
            $return = array(
                "Status"        => "0",
                "FolderStatus"  => "0",
                "Message"       => "Folder name must be letters only. No spaces or special characters.",
                "FolderPath"    => $FolderPath
            );  
            
            echo json_encode($return); 
            return;
        }

        if ($FolderPath != "") {
            $FolderPath = $_SERVER["DOCUMENT_ROOT"] . "\assets\\" . GALLERYMANAGER_FOLDER . "\\" . $Gallery[0]->GalleryFolder . "\\".$FolderPath;
           
            if (is_dir($FolderPath)) {
                $return = array(
                    "Status"        => "1",
                    "FolderStatus"  => "0",
                    "html"          => "Folder already in use.",
                    "FolderPath"    => $FolderPath
                );                    
            } else {
                $return = array(
                    "Status"        => "1",
                    "FolderStatus"  => "1",
                    "html"          => "",
                    "FolderPath"    => $FolderPath
                );
            }
        } else {
            $return = array(
                "Status"        => "0",
                "FolderStatus"  => "0",
                "html"          => "No data passed.",
                "FolderPath"    => $FolderPath
            );            
        }

        echo json_encode($return);
    }


    function save_group()
    {
        $GalleryGroup = new Gallery_model();
        $GalleryGroup->GroupID      = $this->input->post("GroupID");
        $GalleryGroup->GroupTitle   = $this->input->post("GalleryGroup_Title");               
        $GalleryGroup->Active       = $this->input->post("GalleryGroup_Active");
        $GalleryGroup->Folder       = strtolower($this->input->post("GalleryGroup_Folder")); 

        $result = $this->Gallery_model->UpdateGalleryGroup($GalleryGroup);           

        $return = array(
            "Status" => "0",
            "html"   => ""
        );

        if ($result) {
            $return = array(
                "Status" => "1",
                "html"   => ""
            );               
        }
        
        echo json_encode($return);        
    }


    function _group() 
    {       
        if (!is_ajax()) { die("AJAX CALLS ONLY"); }
    
        $return = array(
            "Status" => "1",
            "html"   => "Success"
        );

        echo json_encode($return);
    }


    function set_cover_photo() {

        if (!is_ajax()) { die("AJAX CALLS ONLY"); }

        $PictureID = $this->input->post("PictureID");
  
        $query = $this->Gallery_model->SetCoverPhoto($PictureID);

        $return = array(
            "Status" => "0",
            "html"   => "",
        );

        if ($query != NULL) {
            $return = array(
                "Status"        => "1",
                "html"          => "Success",
                "GalleryFolder" => $query[0]->GalleryFolder,
                "Folder"        => $query[0]->Folder,
                "PictureThumb"  => $query[0]->PictureThumb
            );           
        }

        echo json_encode($return);
    }


    function delete_group()
    {
        $return = array(
            "Status" => "0",
            "html"   => "Fail"
        );

        try {
            $GroupID = $this->input->post("GroupID");
            $return = $this->Gallery_model->DeleteGalleryGroup($GroupID);      
        }
        catch (Exception $ex) {
            $return = array(
                "Status" => "0",
                "html"   => "Fail"
            );            
        }        
        
        echo json_encode($return);
    }

    function get_picture() 
    {
        $PhotoID = $this->input->post("PhotoID");

        $Photo = array();
        $Photo = $this->Gallery_model->GetPicture($PhotoID);      

        $return = array(
            "Status"        => "1",
            "PhotoID"       => $Photo[0]->PictureID,
            "Title"         => $Photo[0]->PictureTitle,
            "Active"        => $Photo[0]->PictureActive
        );
        
        echo json_encode($return);
    }

    function get_gallery()
    {
        $GroupID = $this->input->post("GroupID");
        
        $dataGroup = array();
        $dataGroup = $this->Gallery_model->GetGroup($GroupID, NULL);      

        $html = ''; $photo_count = 0; $CoverPhoto = '';
        if (count($dataGroup) >1 || (isset($dataGroup[0]) && $dataGroup[0]->PictureThumb != '' && $dataGroup[0]->PictureSRC != '')) {
            $html = '<ul class="sortable" data-groupid="' . $GroupID . '">';
            foreach($dataGroup as $Gallery) {
                if ($Gallery->PictureThumb != '') {
                    $photo_count++;
                    $PhotoPath = '/assets/gallerymanager/'.$Gallery->GalleryFolder.'/'.$Gallery->Folder.'/'.$Gallery->PictureThumb;

                    $html .= '<li data-pictureid="' . $Gallery->PictureID . '">';
                    $html .= '<div class="gallery_picture">';
                    $html .= '<div class="ajax_overlay ajax_photo"></div>';
                    $html .= '<div class="photo_reorder grab handle"><img src="/fuel/modules/' . GALLERYMANAGER_FOLDER . '/assets/images/icon_drag_drop.png" /></div>';
                    $html .= '<div class="photo_delete"><button data-pictureid="' . $Gallery->PictureID . '" aria-hidden="true" data-groupid="' . $Gallery->GroupID . '" data-dismiss="alert" class="close delete_photo" type="button">×</button></div>';
                    $html .= '<div class="photo"><a class="edit_photo" PhotoID="' . $Gallery->PictureID . '" PhotoPath="' . $PhotoPath . '" rel="' . $Gallery->Folder . '" href="javascript:void(0);"><img src="' . $PhotoPath . '" /></a></div>';
                    $html .= '<div class="commands">' . $Gallery->PictureTitle . '<br><a href="javascript:void(0);" data-pictureid="' . $Gallery->PictureID . '" class="make_cover_photo">Make Cover Photo</a></div>';
                    $html .= '</div>';
                    $html .= '</li>';

                    if ($Gallery->CoverPhoto == 1) {
                        $CoverPhoto = $PhotoPath;
                    }
                }
            }
            $html .= '</ul>';
        } else {
            $html = '<div class="align_center">There are no pictures uploaded.</div>'; 
        }
        
        $return = array(
            "Status"        => "1",            
            "GalleryHTML"   => $html,

            "GroupID"       => $dataGroup[0]->GroupID,
            "Title"         => $dataGroup[0]->GroupTitle,
            "GalleryFolder" => $dataGroup[0]->GalleryFolder,
            "Folder"        => $dataGroup[0]->Folder,
            "Active"        => $dataGroup[0]->Active,
            "GalleryObject" => $dataGroup,
            "CoverPhotoPath"=> $CoverPhoto
        );
          
        echo json_encode($return);
    }




    function save_picture() {
        $Photo = array(
            'PictureID'     => $this->input->post("photo_ID"),
            'PictureTitle'  => $this->input->post("photo_Title"),
            'PictureActive' => $this->input->post("photo_Active") == "on" ? '1' : '0',               
        );

        $dataGroup = $this->Gallery_model->UpdatePhoto($Photo);      

        $return = array(
            "Status" => "1"            
        );
        
        echo json_encode($return);        
    }



    function upload_pics() {
        ob_start();
        
        if (!isset($_FILES['upload_picture'])) {
            header("Location: /index.php/fuel/" . GALLERYMANAGER_FOLDER . "/");
        }

        //$this->firephp->log("Max Upload Size: " . ini_get("upload_max_filesize"));

        /*********************************/
        /*          UPLOAD               */
        /*********************************/

        $this->load->library('image_lib');

        $path = WEB_ROOT.'/tempupload';
        if (!is_dir($path)) {
            mkdir($path);    
        }  

        $upload_path = realpath(APPPATH . '../../tempupload');

		$config = array(
			'allowed_types' => 'jpg|jpeg|gif|png',
			'upload_path'   => $upload_path,
            'remove_spaces' => TRUE,
			'max_size'      => 5120
		);
		
		$this->load->library('upload', $config);
        
        if (!$this->upload->do_upload('upload_picture')) {
            //$this->firephp->log('Upload Error: ' . $this->upload->display_errors());

            $return = array(
                "Status"        => 0,
                "Message"       => $this->upload->display_errors() . " - MAX Upload File Size: " . ini_get("upload_max_filesize"),
            );

            var_dump($return);
            
            echo "<script type='text/javascript'>";
            echo "window.parent.PictureUploaded(" . json_encode($return) . ")";
            echo "</script>";    

            return;
        }
        		
		$image_data = $this->upload->data();
        
        echo "<pre>";
        print_r($image_data);
        
        //die("");


        /*********************************/
        /*    RESIZE FOR CROPPING        */
        /*********************************/

        // make sure image is not wider than 850px..
        if ($image_data['image_width'] > 850) {            
            //$this->firephp->log('Image Width > 850 (W:'. $image_data['image_width'] . ' H: ' . $image_data['image_height'] . ')');

            $width  = (int)$image_data['image_width'];
            $height = (int)$image_data['image_height'];

            $ratio = ($width - 850) / $width;
            //$this->firephp->log('Image Resize Ratio: ' . $ratio);

            $config['source_image'] = $image_data['full_path'];
            $config['width']  = 850;
            $config['height'] = $height - ($height * $ratio);
		
		    $this->load->library('image_lib', $config);
            $this->image_lib->initialize($config);

            if (!$this->image_lib->resize())
            {
                echo $this->image_lib->display_errors();
            }            
        }


        /*********************************/
        /*    RESIZE THUMBNAIL WINDOW    */
        /*********************************/

        // resize for thumbnail maker
        $FileName = $image_data['file_name'];
        $ext      = end(explode(".", $FileName));   

        $resize_name =  $image_data['raw_name'].'.'.$ext;
        $resize_path = './tempupload/' . $resize_name;
        
        if ($image_data['image_width'] > 600) {                                    
            //$this->firephp->log('Image Width: > 600');

            $resize_name = strtolower($image_data['raw_name'].'.resize.'.$ext);
            $resize_path = './tempupload/' . $resize_name;

            $width  = (int) $image_data['image_width'];
            $height = (int) $image_data['image_height'];

            $ratio = ($width - 600) / $width;

            $config['new_image']    = $resize_path;
            $config['source_image'] = $image_data['full_path'];            
            $config['width']        = 600;
            $config['height']       = $height - ($height * $ratio);
		
		    $this->load->library('image_lib', $config);
            $this->image_lib->initialize($config);

            if (!$this->image_lib->resize())
            {
                echo $this->image_lib->display_errors();
            }
        } else {
            $new_file = './tempupload/' . $image_data['raw_name'] . '.resize.'.$ext;
            //$this->firephp->log('Image Width: <= 600 so copy to '.$new_file);
            copy($resize_path, $new_file);
        }

        $return = array(
            "Status"        => 1,
            "Message"       => "Success",
            "file_name"     => $image_data['file_name'],
            "image_path"    => $image_data['full_path'],
            "raw_name"      => $image_data['raw_name'],
            "resize_path"   => $resize_name
        );

        echo "</pre>";

        echo "<script type='text/javascript'>";
        echo "window.parent.PictureUploaded(" . json_encode($return) . ")";
        echo "</script>";         
    }


    function thumbnail_image() {
        
        $return = array(
            "Status" => "0"
        );

        //$this->firephp->log('-> Thumbnail Image');

        $x = $this->input->post("x");
        $y = $this->input->post("y");
        $w = $this->input->post("w");
        $h = $this->input->post("h");
            
        $GalleryID      = $this->input->post("GalleryID");
        $GroupID        = $this->input->post("GroupID");
        $FileName       = $this->input->post("FileName");
        $raw_name       = $this->input->post("RawName"); 
        $ext            = end(explode(".", $FileName));   
        $Title          = $this->input->post("Title");    
        $Active         = $this->input->post("Active") == "on" ? 1 : 0;    
               
        $ImgFileName    = strtolower($raw_name.".".$ext); 
        $ImgThumbName   = strtolower($raw_name.".thumb.".$ext);
        $ImgResizeName  = strtolower($raw_name.".resize.".$ext);
        $ImgCropName    = strtolower($raw_name.".crop.".$ext);

        //$this->firephp->log('Source File Name: ' . $FileName);
        //$this->firephp->log('File Name: ' . $ImgFileName);
        //$this->firephp->log('Thumb Name: ' . $ImgThumbName);
        //$this->firephp->log('Resize Name: ' . $ImgResizeName);
        //$this->firephp->log('Crop Name: ' . $ImgCropName);

        // get folder data or thorw error            
        $GalleryGroup = $this->Gallery_model->GetGroup($GroupID);

        // crop image
		$config = array(
			'source_image'  => './tempupload/'.$ImgResizeName,
            'new_image'     => './tempupload/'.$ImgCropName,
            'maintain_ratio' => FALSE,
			'width'         => $w,
			'height'        => $h,
            'x_axis'        => $x,
            'y_axis'        => $y
		);
		    
		$this->load->library('image_lib', $config);
        $this->image_lib->initialize($config);

        //$this->firephp->log('Crop: ' . $config['source_image']);
        if (!$this->image_lib->crop())
        {
            // error
            $return = array(
                "Status" => "0",
                "Message" => $this->image_lib->display_errors()
            );
            echo json_encode($return);
            return;
        } else {
            // resize to 255x188
		    $config = array(
			    'source_image'  => './tempupload/'.$ImgCropName,
                'new_image'     => './tempupload/'.$ImgThumbName,
                'maintain_ratio'=> FALSE,
			    'width'         => 255,
			    'height'        => 188,
                'overwrite'     => TRUE
		    );
		    
		    $this->load->library('image_lib', $config);
            $this->image_lib->initialize($config);

            //$this->firephp->log('Resize');
            if (!$this->image_lib->resize()) {
                $return = array(
                    "Status"  => "0",
                    "Message" =>  "Line: " . $ex->getLine() . " " . $this->image_lib->display_errors()
                );
                echo json_encode($return);
                return;                    
            }
        }

            
        // move images
        try {
            //$this->firephp->log('Move Images');

            // is gallery manager folder setup?
            $path = './assets/gallerymanager';
            if (!is_dir($path)) {
                //$this->firephp->log('Creating GalleryManager folder.');
                mkdir($path);    
            }  

            // check directory first
            $path = './assets/gallerymanager/'.strtolower($GalleryGroup[0]->GalleryFolder);
            if (!is_dir($path)) {
                //$this->firephp->log('Creating Gallery Folder.');
                mkdir($path);    
            }  

            if (!file_exists('./assets/gallerymanager/'.strtolower($GalleryGroup[0]->GalleryFolder.'/'.$GalleryGroup[0]->Folder))) { 
                //$this->firephp->log('Creating Group Folder.');                   
                mkdir('./assets/' . GALLERYMANAGER_FOLDER . '/'.strtolower($GalleryGroup[0]->GalleryFolder.'/'.$GalleryGroup[0]->Folder), 0777);
            }

            $full_path = strtolower('./assets/' . GALLERYMANAGER_FOLDER . '/'.$GalleryGroup[0]->GalleryFolder.'/'.$GalleryGroup[0]->Folder.'/');
            
            //$this->firephp->log('Copy Path: ' . $full_path);

            copy('./tempupload/'.$FileName,     $full_path.$ImgFileName);
            copy('./tempupload/'.$ImgThumbName, $full_path.$ImgThumbName);

            if (file_exists('./tempupload/'.$FileName))                 { unlink('./tempupload/'.$FileName); }
            if (file_exists('./tempupload/'.$ImgResizeName))            { unlink('./tempupload/'.$ImgResizeName); }
            if (file_exists('./tempupload/'.$ImgCropName))              { unlink('./tempupload/'.$ImgCropName); }
            if (file_exists('./tempupload/'.$ImgThumbName))             { unlink('./tempupload/'.$ImgThumbName); }
        }
        catch(Exception $ex) {
            $return = array(
                "Status" => "0",
                "Message" => "Line: " . $ex->getLine() . " " . $ex-getMessage()
            );
            echo json_encode($return);
            return;
        }

        // insert into database            
        $this->Gallery_model->InsertPhoto($GalleryID, $GroupID, $ImgFileName, $ImgThumbName, $Title, $Active);      

        $return = array(
            "Status" => "1"
        );
        
        echo json_encode($return);
    }


    function delete_photo()
    {
        $PhotoID = $this->input->post("PictureID");
        
        // remove from database
        $Photo = $this->Gallery_model->DeletePhoto($PhotoID);

        // delete file
        try {
            $path = './assets/' . GALLERYMANAGER_FOLDER . '/' . $Photo[0]->GalleryFolder . '/'.$Photo[0]->Folder.'/';

            if (!@unlink(strtolower($path.$Photo[0]->PictureSRC))) {
                echo "Could not delete: " . $path.$Photo[0]->PictureSRC . "<br>";
            }

            if (!@unlink(strtolower($path.$Photo[0]->PictureThumb))) {
                echo "Could not delete: " . $path.$Photo[0]->PictureThumb . "<br>";
            }
            
            $return = array(
                "Status" => "1"
            );
        }
        catch (Exception $ex) {
            $return = array(
                "Status" => "0",
                "Message" => $ex->getMessage()
            );            
        }
       

        echo json_encode($return);
    }

}