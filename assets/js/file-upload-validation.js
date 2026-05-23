$(document).ready(function () {
    $(document).on("change", "input[type='file']", function () {
        var acceptAttr = $(this).attr("accept");
        var allowedExtensions;
        if (acceptAttr) {
            allowedExtensions = acceptAttr.split(",").map(ext => ext.trim().replace(".", "").toLowerCase());
        } else {
            allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
        }
        var files = this.files;
        var validFiles = [];
        for (var i = 0; i < files.length; i++) {
            var fileName = files[i].name;
            var fileExtension = fileName.split('.').pop().toLowerCase();
            if (allowedExtensions.includes(fileExtension)) {
                validFiles.push(files[i]);
            } else {
                alert_float('danger', "Invalid file..\nOnly " + allowedExtensions.join(", ").toUpperCase() + " files are allowed.");
            }
        }
        if (validFiles.length !== files.length) {
            $(this).val("");
        }
    });
});
