<?php 

class Gallery_model extends CI_Model {

    private $Path = './assets/gallerymanager/';

    // gallery 
    public $ID;
    public $GalleryName;
    public $GalleryFolder;
    public $PrimaryGallery;
    
    // gallery_groups
    public $GroupID;
    public $GroupTitle;
    public $Folder;
    public $Active;

    // gallery_pics_bridge_groups
    public $BridgeID;
    public $bGroupID;
    public $bPictureID;

    // gallery_pics
    public $PictureID;
    public $PictureSRC;
    public $PictureThumb;
    public $PictureTitle;
    public $PictureActive;
    public $CoverPhoto;
    public $OrderID;
    

    function __construct()
    {
        parent::__construct();
    }



    /******************************************
                GALLERY 
    ******************************************/


    function GetGalleries() {        
        $this->load->database();
        $query = $this->db->get('gallery');

        $Galleries = array();
        foreach($query->result() as $row) {
            $Galleries[] = $row;
        }

        $this->db->close();
        $this->db->initialize();  

        return $Galleries;               
    }


    function GetGallery($GalleryID) {        
        $this->load->database();

        $this->db
            ->select('*')
            ->from('gallery g')
            ->join('gallery_groups gg', 'gg.GalleryID = g.ID', 'left')
            ->join('gallery_pics_bridge_groups b', 'b.bGroupID = gg.GroupID', 'left')
            ->join('gallery_pics p', 'p.PictureID = b.bPictureID', 'left')            
            ->where('g.ID', $GalleryID)            
            ->order_by('OrderID');

        $query = $this->db->get();

        $Gallery = array();
        foreach($query->result() as $row) {
            $Gallery[] = $row;
        }

        $this->db->close();
        $this->db->initialize();  
        
        return $Gallery;                          
    }

    
    function GetGalleryOnly($GalleryID) {        
        $this->load->database();

        $query = $this->db->get_where('gallery', array('ID' => $GalleryID), 1);

        $Gallery = array();
        foreach($query->result() as $row) {
            $Gallery[] = $row;
        }

        $this->db->close();
        $this->db->initialize();  
        
        return $Gallery;                          
    }

    /*****************************************
     *            GROUPS                     *
     *****************************************/


    function GetGroup($GroupID, $Active = NULL) {        
        $this->load->database();

        $this->db
            ->select('*')
            ->from('gallery_groups gg')
            ->join('gallery g', 'g.ID = gg.GalleryID')
            ->join('gallery_pics_bridge_groups b', 'b.bGroupID = gg.GroupID', 'left')
            ->join('gallery_pics p', 'p.PictureID = b.bPictureID', 'left')            
            ->where('gg.GroupID', $GroupID)            
            ->order_by('OrderID');

        if ($Active != NULL) {
            $this->db->where('gg.Active', $Active);
        }

        $query = $this->db->get();

        $Gallery = array();
        foreach($query->result() as $row) {
            $Gallery[] = $row;
        }

        $this->db->close();
        $this->db->initialize();  
        
        return $Gallery;                          
    }

    function GetGroups($GalleryID, $Active = NULL) {
        $this->load->database();

        $this->db->select('*');
        $this->db->from('gallery_groups');
        $this->db->join('gallery', 'gallery.ID = gallery_groups.GalleryID');
        if ($Active != NULL) {  
            $this->db->where('Active', $Active);
        }        
        $this->db->where('GalleryID', $GalleryID); 
        $this->db->order_by('GroupTitle', 'asc'); 
        $query = $this->db->get();

        $Groups = array();
        foreach($query->result() as $row) {
            $Groups[] = $row;
        }

        $this->db->close();
        $this->db->initialize();  

        return $Groups;
    }

    function CreateGalleryGroup($GalleryGroup) {    
            
        $return = array(
            "Status" => "1",
            "html"   => ""
        );  

        try {
            $data = array(
               'GalleryID'  => $GalleryGroup->GalleryID,
               'GroupTitle' => $GalleryGroup->GroupTitle,
               'Folder'     => strtolower($GalleryGroup->Folder),
               'Active'     => ($GalleryGroup->Active == 'on' ? '1' : '0')
            );

            $this->db->insert('gallery_groups', $data); 
            
            $this->db->close();            
        }
        catch (Exception $ex) {
            $return = array(
                "Status" => "0",
                "html"   => "Fail: " . $ex->Message
            );   
        }    
        
        return $return;
    }

    function rrmdir($dir) { 
        foreach(glob($dir . '/*') as $file) { 
            if(is_dir($file)) rrmdir($file); else @unlink($file); 
        } @rmdir($dir); 
    }

    function DeleteGalleryGroup($GroupID) {        
        try {
            $this->load->database();
            //$this->db->trans_start();

            $Gallery = $this->GetGroup($GroupID);

            $pictureIDs = array();
            foreach($Gallery as $Picture) {
                if ($Picture->PictureID != '') {
                    array_push($pictureIDs, $Picture->PictureID);   
                }                
            }

            if (count($pictureIDs > 0)) {
                $this->db->where_in('PictureID', $pictureIDs);
                $this->db->delete('gallery_pics');   
            }

            // remove group folder
            $Folder = $this->Path . $Gallery[0]->GalleryFolder . '/' . $Gallery[0]->Folder;
            $this->rrmdir($Folder);            

            // remove Group from db
            $this->db->where('GroupID', $GroupID);
            $this->db->delete('gallery_groups');    
            
            // remove Group-Pics Bridge records
            $this->db->where('bGroupID', $GroupID);
            $this->db->delete('gallery_pics_bridge_groups');    
            
            // complete transaction
            //$this->db->trans_complete();

            $this->db->close();
            $this->db->initialize();    
            
            $return = array(
                "Status" => "1",
                "html"   => "Success"
            );                                     
        }
        catch (Exception $ex) {
            $return = array(
                "Status" => "0",
                "html"   => "Fail: " . $ex->Message
            );              
        }

        return $return;
    }
    

    function SetCoverPhoto($PictureID) {
        $Gallery = array();

        try {
            $this->load->database();
            
            // remove cover photo from other pics
            $this->db->query('UPDATE gallery_pics p
	        LEFT JOIN gallery_pics_bridge_groups b ON b.bPictureID = p.PictureID
	        SET CoverPhoto = 0 WHERE b.bGroupID = (SELECT bGroupID FROM gallery_pics_bridge_groups g WHERE g.bPictureID = ' . $PictureID . ');');
                        
            // set current cover pic
            $data = array(
                'CoverPhoto'    =>  '1'
            );

            $this->db->where('PictureID', $PictureID);
            $this->db->update('gallery_pics', $data); 

            // return updated data
            $this->db->select('*');
            $this->db->from('gallery_pics p');
            $this->db->join('gallery_pics_bridge_groups b', 'b.bPictureID = p.PictureID');
            $this->db->join('gallery_groups gg', 'gg.GroupID = b.bGroupID');
            $this->db->join('gallery g', 'g.ID = gg.GalleryID');
            $this->db->where('p.PictureID', $PictureID); 
            $this->db->order_by('Folder', 'asc'); 
            $this->db->order_by('CoverPhoto', 'asc'); 
            $this->db->order_by('OrderID', 'asc');         
            $query = $this->db->get();

            $Gallery = array();
            foreach($query->result() as $row) {
                $Gallery[] = $row;
            }
            
            // close
            $this->db->close();
            $this->db->initialize();                                            
        }
        catch (Exception $ex) {

        }    

        return $Gallery;    
    }


    function UpdateGalleryGroup($GalleryGroup) {     
        $this->load->database();
        
        $data = array(
            'GroupTitle'    =>  $GalleryGroup->GroupTitle,
            'Folder'        =>  strtolower($GalleryGroup->Folder),
            'Active'        =>  $GalleryGroup->Active == "on" ? '1' : '0',
            'GroupTitle'    =>  $GalleryGroup->GroupTitle
        );

        $this->db->where('GroupID', $GalleryGroup->GroupID);
        $this->db->update('gallery_groups', $data); 
            
        $this->db->close();
        $this->db->initialize();     
                
        return $GalleryGroup;        
    }



    /******************************************
                PICTURES 
    ******************************************/

    function UpdatePhotoOrder($GroupId, $SortOrder) {
        $count = 1;
        foreach ($SortOrder as $Photo) {
            $data = array(
                'OrderID' => $count
            );

            $this->db->where('PictureID', $Photo);
            $this->db->update('gallery_pics', $data); 
            
            $count++;
        }      
    }


    function GetGalleryPics($GalleryID, $OnlyActive = NULL) {
        $this->load->database();
        
        $this->db->select('*');
        $this->db->from('gallery g');
        $this->db->join('gallery_groups gg', 'gg.GalleryID = g.ID');
        $this->db->join('gallery_pics_bridge_groups b', 'b.bGroupID = gg.GroupID');
        $this->db->join('gallery_pics p', 'p.PictureID = b.bPictureID');
        if ($OnlyActive != NULL) {  
            $this->db->where('gg.Active', '1');
            $this->db->where('p.PictureActive', '1');
        }      
        $this->db->where('g.ID', $GalleryID); 
        $this->db->order_by('Folder', 'asc'); 
        $this->db->order_by('CoverPhoto', 'asc'); 
        $this->db->order_by('OrderID', 'asc');         
        $query = $this->db->get();

        $Gallery = array();
        foreach($query->result() as $row) {
            $Gallery[] = $row;
        }

        $this->db->close();
        $this->db->initialize();  

        return $Gallery;                          
    }


    function GetPicture($PhotoID) {
        $query = $this->db->from('gallery_pics p')
            ->join('gallery_pics_bridge_groups b', 'b.bPictureID = p.PictureID', 'left')
            ->join('gallery_groups gg', 'gg.GroupID = b.bGroupID', 'left')
            ->join('gallery g', 'g.ID = gg.GalleryID', 'left')
            ->where('PictureID', $PhotoID)
            ->get();

        $Picture = $query->row();       
        return $Picture;        
    }


    function UpdatePhoto($Photo) {
                
        $data = array(
            'PictureTitle'  => $Photo['PictureTitle'],
            'PictureActive' => $Photo['PictureActive']
        );

        $this->db->where('PictureID', $Photo['PictureID']);
        $this->db->update('gallery_pics', $data); 
                           
        $this->db->close();
        $this->db->initialize();     
    }

    function ReOrderPhotos($GalleryID) {        
        $count = 0;        

        $this->db->select('p.PictureID, p.PictureSRC, p.PictureThumb, p.PictureTitle, p.PictureActive, p.CoverPhoto, p.OrderID');
        $this->db->from('gallery g');
        $this->db->join('gallery_groups gg', 'gg.GalleryID = g.ID');
        $this->db->join('gallery_pics_bridge_groups b', 'b.bGroupID = gg.GroupID');
        $this->db->join('gallery_pics p', 'p.PictureID = b.bPictureID');   
        $this->db->where('g.ID', $GalleryID); 
        $this->db->order_by('Folder', 'asc'); 
        $this->db->order_by('OrderID', 'asc');         
        $query = $this->db->get();

        $count = $this->db->affected_rows();

        $OrderID = 1;
        foreach ($query->result() as $row) {
            $row->OrderID = $OrderID;

            $this->db->where('PictureID', $row->PictureID);
            $this->db->update('gallery_pics', $row); 

            $OrderID++;
        }
        
        return $count;
    }

    function InsertPhoto($GalleryID, $GroupID, $file_name, $thumbnail, $Title, $Active) {       
        $return = array(
            "Status" => "1",
            "html"   => ""
        );  

        try {
            // start transaction
            $this->db->trans_start();
            
            // reorder photos
            $count = $this->ReOrderPhotos($GalleryID);
            $count++;

            // insert picture
            $data = array(
                'PictureSRC'    => strtolower($file_name),
                'PictureThumb'  => strtolower($thumbnail),
                'PictureTitle'  => $Title,
                'PictureActive' => $Active,
                'OrderID'       => $count
            );
                        
            $this->db->insert('gallery_pics', $data); 
            
            //echo $this->db->last_query();    
        
            // insert bridge record
            $data = array(
                'bGroupID'    => $GroupID,
                'bPictureID'  => $this->db->insert_id()
            );
            $this->db->insert('gallery_pics_bridge_groups', $data); 
            
            // complete transaction
            $this->db->trans_complete();
                        
            if ($this->db->trans_status() === FALSE)
            {
                // generate an error... or use the log_message() function to log your error
                $return = array(
                    "Status" => "0",
                    "html"   => "Transaction failed."
                );  
            }             
        }
        catch (Exception $ex) {
            $return = array(
                "Status" => "0",
                "html"   => $ex->getMessage()
            );  
        }

        $this->db->close();        
        $this->db->initialize();     

        return $return;               
    }

    function DeletePhoto($PictureID) {          
        // get picture first so we can delete the files
        $picture = $this->GetPicture($PictureID);

        // delete bridge records
        $this->db->where('bPictureID',$PictureID);
        $this->db->delete('gallery_pics_bridge_groups');

        // delete picture
        $this->db->where('PictureID',$PictureID);
        $this->db->delete('gallery_pics');

        return $picture; 
    }
}