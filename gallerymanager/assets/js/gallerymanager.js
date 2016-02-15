
    var jcrop_api;
    var ajax_reorder_photos;

    var crop_width  = 250;
    var crop_height = 100;

    var GalleryManager = {

        ShowAllAjax: function () {
            $('#ajax_galleries').show();
            $('#ajax_edit_gallery').show();

            var pictures_height = $("#gallery_pictures_wrapper").outerHeight(true);
            $('#ajax_gallery_pictures').height(pictures_height);
            $('#ajax_gallery_pictures').show();
        },

        ShowPictureAjax: function () {
            var pictures_height = $("#gallery_pictures_wrapper").outerHeight(true);
            $('#ajax_gallery_pictures').height(pictures_height);
            $('#ajax_gallery_pictures').show();
        },

        ShowUploadAjax: function () {
            $('#ajax_upload_picture').show();
        },

        HideAllAjax: function () {
            $('#ajax_galleries').hide();
            $('#ajax_edit_gallery').hide();
            $('#ajax_gallery_pictures').hide();
            $('#ajax_upload_picture').hide();
        },


        ClearGallerySelection: function () {
            $('input[name=form_Folder]').val();
            $('#gallery_pictures').html("<div class='align_center'>There are no pictures uploaded.</div>");
            $('#cover_photo').attr('src', site_url + 'fuel/modules/gallerymanager/assets/images/blank_cover_photo.jpg');

            $('.btnSelectGallery').each(function (index) {
                var id = $(this).data("groupid");
                $('#GroupID').val(id);
                $('#group_' + id).removeClass('gallery_selected');
            });
        },


        ClearAll: function () {
            // crop box
            $('#title').val('');
            $('#active').attr('checked', '');

            // edit gallery
            $('input[name=GalleryGroup_Title]').val('');
            $('input[name=GalleryGroup_Folder]').val('');
            $('input[name=GalleryGroup_Active]').prop('checked', true);

            $('#cover_photo').attr('src', site_url + 'fuel/modules/gallerymanager/assets/images/blank_cover_photo.jpg');
            $('#gallery_pictures').html('');
            $('#gallery_groups').html('');
        },


        RefreshAll: function (GalleryID, GroupID) {
            this.ClearAll();

            if (GalleryID == null || GalleryID == '') {
                GalleryID = $('#GalleryID').val();
            }

            if (GroupID == null || GroupID == '') {
                GroupID = $('#GroupID').val();
            }

            this.ShowAllAjax();
            $('#gallery_pictures').html('');

            var formData = {
                GalleryID: GalleryID,
                GroupID: GroupID
            };

            $.ajax({
                url: "gallerymanager/refresh/",
                datatype: 'json',
                data: formData,
                type: 'post',
                success: function (data) {
                    $('#gallery_groups').html(data);
                    // load current gallery
                    if (GroupID != null) {
                        GalleryManager.LoadGalleryGroup(GroupID);
                    }
                    GalleryManager.HideAllAjax();
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                }
            });

        },


        /*******************************************/
        /*                  GALLERY                */
        /*******************************************/


        LoadGallery: function (GalleryID) {
            this.RefreshAll(GalleryID);
        },


        ValidateEditGallery: function () {
            var form_pass = true;
            var GroupID = $('#GroupID').val();

            if (GroupID == '') {
                alert("Select a gallery to edit.");
                return form_pass = false;
            }

            var GroupTitle = $('input[name=GalleryGroup_Title]');
            if (GroupTitle.val() == '') {
                form_pass = false;
                GroupTitle.removeClass('input_valid');
                GroupTitle.addClass('input_error');
            } else {
                GroupTitle.removeClass('input_error');
                GroupTitle.addClass('input_valid');
            }

            var GroupActive = $('input[name=GalleryGroup_Active]');
            if (GroupActive.val() == '') {
                form_pass = false;
                GroupActive.removeClass('input_valid');
                GroupActive.addClass('input_error');
            } else {
                GroupActive.removeClass('input_error');
                GroupActive.addClass('input_valid');
            }

            return form_pass;
        },


        /*******************************************/
        /*                  GROUPS                 */
        /*******************************************/


        LoadGalleryGroup: function (GroupID) {
            this.ShowAllAjax();
            var formData = { GroupID: GroupID };

            $.ajax({
                url: "gallerymanager/get_gallery",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    if (jsonData != null) {
                        // page data
                        $('#GroupID').val(jsonData.GroupID);

                        $('#title').val('');
                        $('#active').attr('checked', '');

                        // edit gallery
                        $('input[name=GalleryGroup_Title]').val(jsonData.Title);
                        $('input[name=GalleryGroup_Folder]').val(jsonData.Folder);


                        if (jsonData.Active == 1) {
                            $('input[name=GalleryGroup_Active]').prop('checked', true);
                        } else {
                            $('input[name=GalleryGroup_Active]').prop('checked', false);
                        }

                        // pictures    
                        $('#gallery_pictures').html(jsonData.GalleryHTML);
                        $("#group_" + GroupID).addClass('gallery_selected');


                        // cover photo
                        $('#cover_photo').attr('src', jsonData.CoverPhotoPath);

                        if (!jQuery().sortable) {
                            alert("'jQuery Sortable' Not Working!");
                        }

                        GalleryManager.Sortable();
                        GalleryManager.HideAllAjax();
                    } else {
                        GalleryManager.HideAllAjax();
                        alert("Error retrieving 'Gallery Group'");
                    }
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                }
            });
        },


        DeleteGalleryGroup: function (id) {
            this.ShowAllAjax();
            var formData = { GroupID: id };

            $.ajax({
                url: "gallerymanager/delete_group",
                datatype: 'JSONP',
                data: formData,
                type: 'post',
                success: function (data) {
                    GalleryManager.RefreshAll();
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                }
            });
        },


        SaveGalleryGroup: function (formData) {
            if (this.ValidateEditGallery() == true) {
                this.ShowAllAjax();
                $.ajax({
                    url: "gallerymanager/save_group/",
                    datatype: 'json',
                    type: 'post',
                    data: formData,
                    success: function (data) {
                        var jsonData = $.parseJSON(data);
                        GalleryManager.RefreshAll();
                        GalleryManager.LoadGalleryGroup(ID);
                        GalleryManager.HideAllAjax();
                    },
                    error: function (xhr, testStatus, error) {
                        GalleryManager.HideAllAjax();
                    }
                });
            }
        },

        /*******************************************/
        /*                  PHOTOS                 */
        /*******************************************/

        LoadPictures: function (GroupID) {

            this.ShowPictureAjax();
            var formData = { GroupID: GroupID };

            $.ajax({
                url: "gallerymanager/get_gallery",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    if (jsonData != null) {
                        // page data
                        $('#GroupID').val(jsonData.GroupID);

                        // pictures    
                        $('#gallery_pictures').html(jsonData.GalleryHTML);

                        if (!jQuery().sortable) {
                            alert("'jQuery Sortable' Not Working!");
                        }

                        GalleryManager.Sortable();
                        GalleryManager.HideAllAjax();
                    } else {
                        GalleryManager.HideAllAjax();
                        alert("Error retrieving 'Gallery Group'");
                    }
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                }
            });
        },

        MakeCoverPhoto: function (PictureID) {
            this.ShowAllAjax();
            var formData = { PictureID: PictureID };

            $.ajax({
                url: "gallerymanager/set_cover_photo/",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    $('#cover_photo').attr('src', '/assets/gallerymanager/' + jsonData.GalleryFolder + '/' + jsonData.Folder + '/' + jsonData.PictureThumb);
                    GalleryManager.HideAllAjax();
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                }
            });
        },


        ShowPhotoEdit: function (PhotoID, PhotoPath) {
            $('#photo_edit').prop('src', PhotoPath);
            var formData = { PhotoID: PhotoID };

            $.ajax({
                url: "gallerymanager/get_picture/",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    $('#photo_ID').val(jsonData.PhotoID);
                    $('input[name=photo_Title]').val(jsonData.Title);

                    if (jsonData.Active == '1') {
                        $('input[name=photo_Active]').prop('checked', true);
                    } else {
                        $('input[name=photo_Active]').prop('checked', false);
                    }

                    $.fancybox.open({
                        'href': '#modal_edit_gallery_photo'
                    });
                }
            })
        },


        SavePhoto: function (formData) {
            $.ajax({
                url: "gallerymanager/save_picture/",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    if (jsonData.Status == 1) {
                        $.fancybox.close();
                    } else {
                        alert(jsonData.Message);
                    }
                }
            });
        },


        DeletePhoto: function (PhotoID) {
            this.ShowPictureAjax();

            var formData = { PictureID: PhotoID };
            var GroupID = $('#GroupID').val();

            $.ajax({
                url: "gallerymanager/delete_photo/",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    GalleryManager.LoadPictures(GroupID);
                    GalleryManager.HideAllAjax();
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                }
            });
        },


        UpdatePhotoOrder: function () {
            var pictureOrder = new Array();
            var GroupId = $('.sortable').data('groupid');

            this.ShowPictureAjax();

            $('.sortable li').each(function () {
                var pictureid = $(this).data('pictureid');
                if (pictureid != null && pictureid != undefined) {
                    pictureOrder.push(pictureid);
                }
            });

            if (ajax_reorder_photos != null) {
                ajax_reorder_photos.abort();
            }

            ajax_reorder_photos = $.ajax({
                url: "gallerymanager/reorder_photos/",
                datatype: 'json',
                type: 'post',
                data: {
                    sort_order: pictureOrder,
                    GroupId: GroupId
                },
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    GalleryManager.HideAllAjax();
                },
                failure: function (data) {
                    GalleryManager.HideAllAjax();
                }
            });
        },


        Sortable: function () {
            $(".sortable").sortable(
                {
                    handle: '.handle',
                    opacity: 1,
                    stop: function (event, ui) {
                        GalleryManager.UpdatePhotoOrder();
                    }
                }
            );
            $(".sortable").disableSelection();
        },

        /*******************************************/
        /*              UPLOAD PHOTO               */
        /*******************************************/

        ClearUpload: function () {
            this.HideAllAjax();

            if (jcrop_api) {
                jcrop_api.release();

                // clear images
                $('input[name=upload_picture]').val('');
                $('#preview_thumb').attr('src', '/assets/images/blank_photo.jpg');

                // clear thumbnail coordinates
                $('#x').val('');
                $('#y').val('');
                $('#w').val('');
                $('#h').val('');

                // reset elements
                $('#div_upload_form').show();
                $('#div_upload_image').html('<img id="upload_image" />');
                $('#preview_thumb').hide();
                $('#div_upload_thumb').hide();
            }
        },

        FinalizeThumb: function (formData) {
            this.ShowUploadAjax();
            
            $.ajax({
                url: "gallerymanager/thumbnail_image/",
                datatype: 'json',
                type: 'post',
                data: formData,
                success: function (data) {
                    var jsonData = $.parseJSON(data);
                    if (jsonData.Status == "1") {
                        GalleryManager.LoadGalleryGroup(formData.GroupID);
                        $.fancybox.close();
                    } else {
                        alert(jsonData.Message);
                    }
                    GalleryManager.HideAllAjax();
                },
                error: function (xhr, testStatus, error) {
                    GalleryManager.HideAllAjax();
                    alert("Error: " + error);
                }
            });
        }
    };

    function PictureUploaded(jsonObj) {
        if (jsonObj.Status == 0) {
            alert(jsonObj.Message);
            $.fancybox.close();
            return;
        }

        $('#div_upload_form').hide();
        $('#div_upload_thumb').show();
        $('#upload_image').attr('src', site_url + '/tempupload/' + jsonObj.resize_path);
        $('#preview_thumb').attr('src', site_url + '/tempupload/' + jsonObj.resize_path);
        $('#upload_image').show();
        $('#preview_thumb').show();
        $('#file_name').val(jsonObj.file_name);
        $('#raw_name').val(jsonObj.raw_name);

        $.fancybox.update();

        $('#upload_image').Jcrop({
            onChange: ShowPreview,
            onSelect: ShowPreview,
            aspectRatio: 250 / 188
            /* aspectRatio: crop_width / crop_height */
            }, function () {
                jcrop_api = this;
            }
        );
    }

    function UpdateCoords(c) {
        $('#x').val(c.x);
        $('#y').val(c.y);
        $('#w').val(c.w);
        $('#h').val(c.h);
    }
    
    function ShowPreview(coords) {
        UpdateCoords(coords);

        if (parseInt(coords.w) > 0) {
            var rx = 250 / coords.w;
            var ry = 188 / coords.h;

            var img_height = $("#upload_image").height();
            var img_width  = $("#upload_image").width();

            jQuery('#preview_thumb').css({
                width: Math.round(rx * img_width) + 'px',
                height: Math.round(ry * img_height) + 'px',
                marginLeft: '-' + Math.round(rx * coords.x) + 'px',
                marginTop: '-' + Math.round(ry * coords.y) + 'px'
            });
        }
    }


    function CallDebugger() {
        debugger;
    }

    /***********************************************/
    /*                     BINDINGS                */
    /***********************************************/

    (function( $ ) {

        $('body').on('click', '.edit_photo', function () {
            var PhotoID   = $(this).attr("PhotoID");
            var PhotoPath = $(this).attr("PhotoPath");
            GalleryManager.ShowPhotoEdit(PhotoID, PhotoPath);
        });

        $('body').on('click', '#save_picture', function () {            
            var formData = $('form[name=edit_photo]').serialize();
            GalleryManager.SavePhoto(formData);
        });

        $('body').on('click', '.delete_gallery_group', function () {
            if (confirm("Are you sure you want to delete '" + $(this).attr('title') + "'?")) {                    
                var id = $(this).data("groupid");                    
                GalleryManager.DeleteGalleryGroup(id);
            }
        });

        $('body').on('click', '.btnSelectGallery', function () {
            var id = $(this).data("groupid");
            GalleryManager.ClearGallerySelection();
            $("#group_" + id).addClass('gallery_selected');
            GalleryManager.LoadGalleryGroup(id);
        });

        $('body').on('click', '#btn_Save_Gallery', function () {
            var formData = $('#form').serialize();
            GalleryManager.SaveGalleryGroup(formData);
        });
            
        $('body').on('click', '#btn_finalize_thumb', function () {
            var formData = {
                GalleryID : $('#GalleryID').val(),
                GroupID : $('#GroupID').val(),
                x : $('#x').val(),
                y : $('#y').val(),
                w : $('#w').val(),
                h : $('#h').val(),
                FileName : $('#file_name').val(),
                RawName : $('#raw_name').val(),
                Title : $('#title').val(),
                Active : $('#active').val()
            };
            GalleryManager.FinalizeThumb(formData);
        });

        $('body').on('click', '.make_cover_photo', function () {               
            var PictureID = $(this).data('pictureid');
            GalleryManager.MakeCoverPhoto(PictureID);
        });

        $('body').on('click', '.delete_photo', function () {
            if (confirm("Are you sure you want to delete this photo?")) {
                var PhotoID = $(this).data("pictureid");
                GalleryManager.DeletePhoto(PhotoID);
            }
        });

        $('body').on('click', '#btnUploadPicture', function () {
            GalleryManager.ClearUpload();
            var GroupID = $('#GroupID').val();                
            if (GroupID == '') {
                alert("Your must select a gallery group to upload pictures to.\n If there isn't a gallery group, try creating one.");
                return;
            }
            $.fancybox.open({
                'href': '#UploadPictures'
            });
        });

        $('body').on('change', '#select_gallery', function () {
            var select_id = $(this).val();
            $('#GalleryID').val(select_id);

            GalleryManager.LoadGallery(select_id);
        });

    })( jQuery );
