/* miniEditor */
tinymce.init({
    selector: ".miniEditor",
    height: 50,
    menubar: false,
    //language : 'vi_VN',
    entity_encoding: "raw",
    allow_script_urls: true,
    plugins: [
        "advlist link image lists charmap preview hr anchor pagebreak table contextmenu paste",
        "searchreplace wordcount visualblocks code fullscreen insertdatetime media textcolor lineheight"
    ],
    relative_urls: false,
    convert_urls: true,
    fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 28px 32px 36px',
    lineheight_formats: '8px 10px 12px 14px 16px 18px 24px 28px 32px 36px',

    toolbar1: "alignleft aligncenter alignright alignjustify  | bold italic underline link | fontsizeselect forecolor backcolor | code removeformat",
});

/* mceEditor */
tinymce.init({
    selector: ".mceEditor",
    setup: function (editor) {
        editor.on('change', function () {
            tinymce.triggerSave();
        });
    },
    height: 300,
    menubar: false,
    //language : 'vi_VN',
    entity_encoding: "raw",
    allow_script_urls: true,
    plugins: [
        "advlist link image lists charmap preview hr anchor pagebreak table contextmenu paste",
        "searchreplace wordcount visualblocks code fullscreen insertdatetime media textcolor lineheight"
    ],
    relative_urls: false,
    convert_urls: true,
    fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 28px 32px 36px',
    lineheight_formats: '8px 10px 12px 14px 16px 18px 24px 28px 32px 36px',

    toolbar1: "undo redo | alignleft aligncenter alignright alignjustify outdent indent  | link image hr pagebreak visualblocks | table | code fullscreen removeformat",
    toolbar2: "formatselect | fontsizeselect | lineheightselect | bold italic underline strikethrough subscript superscript | bullist numlist | forecolor backcolor",

    file_browser_callback: function (field, url, type, win) {
        tinyMCE.activeEditor.windowManager.open({
            file: '/kcfinder/browse.php?opener=tinymce4&field=' + field + '&type=' + type,
            title: 'KCFinder',
            width: 700,
            height: 500,
            inline: true,
            close_previous: false
        }, {
                window: win,
                input: field
            });
        return false;
    }
});