<!DOCTYPE html>
<html lang="en-US">

<head>
   <!-- css -->
   <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/bookflip/demo/assets/css/style.css') ?>"/>
   <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/bookflip/min_version/ipages.min.css') ?>"/>


   <!-- /end css -->
   <title><?= $brochure->title ?> </title>
</head>

<body>

   <!-- flipbook markup -->
   <div id="flipbook" style="height:100vh;"></div>
   <!-- /end flipbook markup -->

   <!-- scripts-section -->
   <script type="text/javascript" src="<?= site_url('assets/plugins/bookflip/demo/assets/js/jquery.min.js') ?>"></script>
   <script type="text/javascript" src="<?= site_url('assets/plugins/bookflip/min_version/pdf.min.js') ?>"></script>
   <script type="text/javascript" src="<?= site_url('assets/plugins/bookflip/min_version/jquery.ipages.min.js') ?>"></script>
   <script>
      $(document).ready(function() {
         var options = {
            responsive: true,
            autoFit: true,
            autoHeight: false,
            pdfUrl: "<?= $pdf_url ?>",
            pdfAutoCreatePages: true,
            pdfBookSizeFromDocument: true,
            zoom: 1,
            toolbarControls: [{
                  type: 'share',
                  active: false
               },
               {
                  type: 'sound',
                  active: true,
                  optional: false
               },
               {
                  type: 'outline',
                  active: true
               },
               {
                  type: 'thumbnails',
                  active: false
               },
               {
                  type: 'gotofirst',
                  active: true
               },
               {
                  type: 'prev',
                  active: true
               },
               {
                  type: 'pagenumber',
                  active: true
               },
               {
                  type: 'next',
                  active: true
               },
               {
                  type: 'gotolast',
                  active: true
               },
               {
                  type: 'zoom-in',
                  active: true
               },
               {
                  type: 'zoom-out',
                  active: true
               },
               {
                  type: 'zoom-default',
                  active: true
               },
               {
                  type: 'optional',
                  active: false
               },
               {
                  type: 'download',
                  active: false,
                  optional: false
               },
               {
                  type: 'fullscreen',
                  active: true,
                  optional: false
               },
            ],

            bookmarks: [],
         };

         $('#flipbook').ipages(options);

         // Events
         $('#flipbook').on('ipages:ready', function(e, plugin) {
            console.log('event:ready');
         });

         $('#flipbook').on('ipages:showpage', function(e, plugin, page) {
            console.log('event:showpage [' + page + ']');
         });

         $('#flipbook').on('ipages:hidepage', function(e, plugin, page) {
            console.log('event:hidepage [' + page + ']');
         });
      });
   </script>
   <!-- /end scripts-section -->
</body>

</html>