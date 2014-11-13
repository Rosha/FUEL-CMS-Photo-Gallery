<input type="hidden" name="GalleryID" id="GalleryID" value="<?php echo isset($GalleryID) ? $GalleryID : ""; ?>" />
<input type="hidden" name="GroupID" id="GroupID" value="" />

<div class="inner_padding">
	<div id="main_content_inner">        
        <h2 class="short">Page </h2>
        <select name="select_gallery" id="select_gallery">            
            <option value="">Select Gallery</option>
            <?php foreach($Galleries as $Gallery) { ?>                    
            <option value="<?=$Gallery->ID; ?>"<?php echo (isset($GalleryID) && $GalleryID == $Gallery->ID) ? " selected" : ""; ?>><?=$Gallery->GalleryName; ?></option>
            <?php } ?>            
        </select>
        <div class="clear_10"></div>
        <div id="div_gallery" style="width: 500px; float: left; display: inline-block; margin: 0 30px 0 0;">
            <div id="ajax_galleries" class="ajax_overlay"><div class="ajax_loader"></div></div>
            <h2>Gallery Groups</h2>
            <div id="gallery_groups" class="supercomboselect" style="overflow-y: auto; -moz-user-select: none;">
                <ul class="supercomboselect_list" id="tests_left">
                    <?php if (isset($GalleryGroups) && count($GalleryGroups) > 0) { ?>
                        <?php foreach($GalleryGroups as $Gallery) { ?>
                        <li id="group_<?php echo $Gallery->GroupID; ?>">
                            <a href="javascript:void(0);" class="delete_gallery_group" title="<?php echo $Gallery->GroupTitle; ?>" data-groupid="<?php echo $Gallery->GroupID; ?>">x</a> - <a href="javascript:void(0);" class="btnSelectGallery" data-groupid="<?php echo $Gallery->GroupID; ?>"><strong><?php echo $Gallery->GroupTitle; ?></strong></a>
                            <div style="display: inline-block; float: right; text-align: center;"><a href="javascript:void(0);" class="btnSelectGallery" data-groupid="<?php echo $Gallery->GroupID; ?>">Select</a></div>
                        </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div id="div_edit_gallery" style="width: 500px; float: left; display: inline-block;">
            <div id="ajax_edit_gallery" class="ajax_overlay"><div class="ajax_loader"></div></div>
            <h2>Edit Gallery Group</h2>
            <div class="box_admin" style="height: 230px;">                
                <div class="float_left" style="margin: 0 20px 0 0;">
                    <input type="hidden" name="GalleryGroup_Folder" />
                    <p><label>Title:</label> <input type="text" name="GalleryGroup_Title" /></p>
                    <p><label>Active:</label> <input type="checkbox" name="GalleryGroup_Active" checked /></p>                    
                </div>
                <div class="float_left" style="width: 250px; text-align: center;"><img src="/assets/images/blank_cover_photo.jpg" id="cover_photo" class="cover_photo" width="250" height="180" alt="Cover Photo" /></div>
                <div class="clear_10"></div>                    
	            <div class="buttonbar">
		            <ul>
			            <li class="end"><a href="#" class="ico ico_yes" id="btn_Save_Gallery">Save</a></li>
		            </ul>
	            </div>
                <div class="clear_10"></div>
            </div>
        </div>

        <div class="clear_30"></div>

        <div>
            <h2>Gallery Photos</h2>
            <hr>
            <div class="clear_10"></div>
	        <div class="buttonbar align_right float_right">                
		        <ul>
                    <li class="end"><a href="#UploadPictures" class="ico ico_yes" id="btnUploadPicture">Upload Picture</a></li>
		        </ul>
	        </div>
            <div class="clear_10"></div>
            <div id="gallery_pictures_wrapper">
                <div id="ajax_gallery_pictures" class="ajax_overlay"><div class="ajax_loader"></div></div>
                <div id="gallery_pictures" style="padding: 10px 0;">                
                    <div class="align_center">There are no pictures uploaded.</div>
                </div>
                <div class="clear"></div>               
            </div>
        </div>
        </form>

        <div class="clear"></div>
        <div id="div_html_output"></div>        
        <?php // display: none; ?>
        <iframe name="iframe_upload_pics" width="800" style=""></iframe>

        <div id="UploadPictures" style="display: none; width: 880px; height: 550px; overflow-y: auto; ">
            <h2>Upload Picture</h2>
            <div id="div_upload_form">
                <form method="post" action="gallerymanager/upload_pics" enctype="multipart/form-data" target="iframe_upload_pics">
                    <input type="file" name="upload_picture" size="30" />
                    <input type="submit" value="upload" />
                </form>
            </div>
            <div id="div_upload_image" style="width: 600px; height: 400px; float: left;">
                <img id="upload_image" alt="" />
            </div>
            <div id="div_upload_thumb" style="width: 250px; float: left; text-align: right; display: none; padding: 0 0 0 10px;">
                <div id="preview-pane">
                    <div class="preview-container">
                        <img id="preview_thumb" class="jcrop-preview" alt="Preview" src="/assets/images/blank_photo.jpg" />
                    </div>
                </div>
                <div style="padding: 10px 0 0 0;">
		            <form name="image_crop"> 
			            <input type="hidden" id="x" name="x" />
			            <input type="hidden" id="y" name="y" />
			            <input type="hidden" id="w" name="w" />
			            <input type="hidden" id="h" name="h" />
			            <input type="hidden" name="file_name" id="file_name" value="" />
                        <input type="hidden" name="raw_name" id="raw_name" value="" />
                    
                        Title: <input type="text" name="title" id="title" value="" /><br/>
                        Active: <input type="checkbox" name="active" id="active" checked="checked" />                           

                        <div class="clear_10"></div>
                        <div>
                            <div id="upload_picture_button"><input type="button" id="btn_finalize_thumb" value="Finalize" /></div>
                            <div id="ajax_upload_picture"></div>                            
                        </div>
		            </form>		 
                </div>           
            </div>
        </div>
        

        <div id="modal_edit_gallery_photo" style="display:none;">
            <h2>Edit Picture</h2>
            <hr>
            <div style="width:250px; float:left;"><img id="photo_edit" /></div>
            <div style="width:250px; float:left; margin: 0 0 0 15px;">
                <form name="edit_photo">
                    <input type="hidden" name="photo_ID" id="photo_ID" />
                    <p><label>Title:</label> <input type="text" name="photo_Title" /></p>
                    <p><label>Active:</label> <input type="checkbox" name="photo_Active" /></p>                    
                </form>
                <div class="clear_10"></div>
	            <div class="buttonbar">                
		            <ul>
                        <li class="end"><a href="javascript:void(0);" class="ico ico_yes" id="save_picture">Save Picture</a></li>
		            </ul>
	            </div>
            </div>
        </div>

        <div class="align_right"><a href="#">Back to top</a></div>
	</div>   
    
         	            
</div>

    <link href="/fuel/modules/gallerymanager/assets/fancybox/jquery.fancybox.css" media="all" rel="stylesheet"/>
    <script src="/fuel/modules/gallerymanager/assets/fancybox/jquery.fancybox.js" type="text/javascript"></script>
    
    <link href="/fuel/modules/gallerymanager/assets/css/jquery.Jcrop.css" media="all" rel="stylesheet"/>
    <script src="/fuel/modules/gallerymanager/assets/js/jquery.Jcrop.js" type="text/javascript"></script>

    <script type="text/javascript" src="/fuel/modules/gallerymanager/assets/js/gallerymanager.js"></script>       

    
