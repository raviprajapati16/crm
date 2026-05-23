<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $brochure->title ?> </title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>

    <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/3d-flipbook/css/flipbook.style.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/3d-flipbook/css/font-awesome.css') ?>">

    <script src=" <?= site_url('assets/plugins/3d-flipbook/js/flipbook.min.js?v=' . time()) ?>"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $(document).on("contextmenu", function(e) {
                e.preventDefault();
            });

            var options = {
                pdfUrl: "<?= $pdf_url ?>",
                rangeChunkSize : 5000,
                assets: {
                    flipMp3: "<?= site_url('assets/plugins/3d-flipbook/mp3/turnPage.mp3') ?>",
                },
                btnBookmark: {
                    enabled: false
                },
                btnShare: {
                    enabled: false
                },
                btnPrint: {
                    enabled: false
                },
                btnDownloadPdf: {
                    enabled: false
                },
                btnDownloadPages: {
                    enabled: false
                },
                btnToc: {
                    enabled: false
                },
                btnSelect: {
                    enabled: false
                }
            };
            $("#container").flipBook(options);

        })
    </script>

</head>

<body>
    <div id="container"></div>
</body>

</html>