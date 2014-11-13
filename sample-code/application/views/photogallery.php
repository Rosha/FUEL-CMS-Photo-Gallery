		<div class="topbar">
			<span id="close" class="back">&larr;</span>
			<h3 id="name"></h3>
		</div>
        <div class="clear"></div>
        <div>
		    <ul id="tp-grid" class="tp-grid">
                <?php 
                foreach($Gallery as $Picture) { 
                    if ($Picture->Folder != "" && $Picture->PictureSRC != "") {
                        if (file_exists ('./assets/gallerymanager/'.$Picture->GalleryFolder.'/'.$Picture->Folder.'/'.$Picture->PictureThumb) ) {
                        $picture_title = $Picture->PictureTitle != "" ? $Picture->PictureTitle : $Picture->GroupTitle;
                ?>           
			    <li data-pile="<?php echo $Picture->GroupTitle; ?>">
				    <a href="/assets/gallerymanager/<?=$Picture->GalleryFolder?>/<?=$Picture->Folder."/".$Picture->PictureSRC; ?>" class="fancybox" rel="<?=$Picture->GroupTitle; ?>" >
					    <span class="tp-info"><span><?=$picture_title; ?></span></span>
					    <img src="/assets/gallerymanager/<?=$Picture->GalleryFolder; ?>/<?=$Picture->Folder."/".$Picture->PictureThumb; ?>" alt="<?=$picture_title; ?>" />
				    </a>
			    </li>
                <?php 
                        }
                    } 
                } ?>
		    </ul>
        </div>

        <div class="clear"></div>

   	<script type="text/javascript" src="/assets/js/jquery.stapel.js"></script>
	<script type="text/javascript">
        
         $(function () {

             $(".fancybox").fancybox({
                 'transitionIn': 'elastic',
                 'transitionOut': 'elastic',
                 'speedIn': 600,
                 'speedOut': 200,
                 'overlayShow': false
             });

             var $grid = $('#tp-grid'),
                 $name = $('#name'),
                 $close = $('#close'),
                 $loader = $('<div class="loader"><i></i><i></i><i></i><i></i><i></i><i></i><span>Loading...</span></div>').insertBefore($grid),
                 stapel = $grid.stapel({
                     delay: 50,
                     onLoad: function () {
                         $loader.remove();
                     },
                     onBeforeOpen: function (pileName) {
                         $name.html(pileName);
                     },
                     onAfterOpen: function (pileName) {
                         $close.show();
                     }
             });

             $close.on('click', function () {
                 $close.hide();
                 $name.empty();
                 stapel.closePile();
             });

         });

	</script>