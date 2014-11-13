<div class="inner_padding">
	<div id="main_content_inner">		
        <div id="div_edit_gallery" style="width: 500px; float: left; display: inline-block;">
            <h2>Add Gallery Group</h2>
            <p class="instructions">Create a new gallery group. Each gallery group's folder should be unique.</p>
            <div class="box_admin">
                <div class="float_left">
                    <div>
                        <label>Page:</label>
                        <select name="GalleryID" id="GalleryID">            
                            <option value="">Select Gallery</option>
                            <?php foreach($Galleries as $Gallery) { ?>                    
                            <option value="<?=$Gallery->ID; ?>"<?php echo (isset($GalleryID) && $GalleryID == $Gallery->ID) ? " selected" : ""; ?>><?=$Gallery->GalleryName; ?></option>
                            <?php } ?>            
                        </select>
                    </div>                    
                    <p><label>Title:</label> <input type="text" name="GroupTitle" /></p>
                    <div>
                        <div id="folder_status"></div>
                        <label>Folder:</label> <input type="text" name="GroupFolder" />           
                        <input type="hidden" name="FolderStatus" />                 
                    </div>
                    <p><label>Active:</label> <input type="checkbox" name="Active" checked /></p>                    
                </div>

                <div class="clear"></div>
                <div id="message_add_group"></div>
                <div class="clear_10"></div>
                    
	            <div class="buttonbar">
		            <ul>
			            <li class="end"><a href="#" class="ico ico_yes" id="btn_Add_Gallery">Add</a></li>
		            </ul>
	            </div>
                <div class="clear"></div>
            </div>
        </div>

        <div class="clear"></div>
        <div id="div_html_output"></div>
	</div>        	
</div>

<div style="display: none;">
    <img src="/assets/images/is_valid.gif" alt="Folder Status" />
    <img src="/assets/images/not_valid.gif" alt="Folder Status" />
</div>

    <script src="/fuel/modules/gallerymanager/assets/js/jquery.typewatch.js" type="text/javascript"></script>
    <script type="text/javascript">
        var ajax_folder = null;

        function ClearScreen() {
            $('#message_add_group').html('');
            $('#message_add_group').removeClass('error_message');
        }

        function CheckFolderName() {
            var GalleryID       = $('select[name=GalleryID] option:selected');
            var GroupFolder     = $('input[name=GroupFolder]');

            var formData = {
                GalleryID       : GalleryID.val(),
                GroupFolder     : GroupFolder.val()
            };

            if (ajax_folder) {
                ajax_folder.abort();
            }

            ajax_folder = $.ajax({
                url: "folder_valid",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    if (jsonData.FolderStatus == 1) {
                        GroupFolder.removeClass('input_error');
                        GroupFolder.addClass('input_valid');
                        $('input[name=FolderStatus]').val("1")
                        $('#folder_status').html('<img src="/fuel/modules/gallerymanager/assets/images/is_valid.gif" alt="Folder Status" />');
                        ClearScreen();
                    } else {
                        GroupFolder.removeClass('input_valid');
                        GroupFolder.addClass('input_error');
                        $('input[name=FolderStatus]').val("0");
                        $('#folder_status').html('<img src="/fuel/modules/gallerymanager/assets/images/not_valid.gif" alt="Folder Status" />');
                        $('#message_add_group').addClass("error_message");
                        $('#message_add_group').html(jsonData.Message);
                    }
                },
                error: function (xhr, testStatus, error) {
                    //console.log('$.ajax() error: ' + error);
                }
            });
        }



        $(function () {

            var options = {
                callback: function () { CheckFolderName(); },
                wait: 750,
                highlight: true,
                captureLength: 2
            }

            $("input[name=GroupFolder]").typeWatch(options);

            $("input[name=GroupFolder]").live('change', function () {
                CheckFolderName();
            });

            function IsAddGalleryValidated() {
                var form_pass = true;

                var GroupTitle = $('input[name=GroupTitle]');
                if (GroupTitle.val() == '') {
                    form_pass = false;
                    GroupTitle.removeClass('input_valid');
                    GroupTitle.addClass('input_error');
                } else {
                    GroupTitle.removeClass('input_error');
                    GroupTitle.addClass('input_valid');
                }

                var GroupFolder = $('input[name=GroupFolder]');
                if (GroupFolder.val() == '') {
                    form_pass = false;
                    GroupFolder.removeClass('input_valid');
                    GroupFolder.addClass('input_error');
                } else {
                    GroupFolder.removeClass('input_error');
                    GroupFolder.addClass('input_valid');
                }


                if ($('input[name=FolderStatus]').val() != '1') {
                    form_pass = false;

                    GroupFolder.removeClass('input_valid');
                    GroupFolder.addClass('input_error');

                    alert("You must enter a valid available folder.");
                }

                return form_pass;
            }


            $('#btn_Add_Gallery').live('click', function () {
                CheckFolderName();
                if (IsAddGalleryValidated() == true) {
                    $('#form').submit();
                }
            });

        });

    </script>
