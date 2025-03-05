var forum_tinymce_toolbar = $('#forum_tinymce_toolbar').val();
var forum_tinymce_plugins = $('#forum_tinymce_plugins').val();

// Initiate the tinymce editor on any textarea with a class of richText
tinymce.init({
	selector:'textarea.richText',
	skin: 'forum',
	plugins: forum_tinymce_plugins,
	toolbar: forum_tinymce_toolbar,
	menubar: false,
	statusbar: false,
	height : '220',
	content_css : '/vendor/innoboxrr/forum/assets/css/forum.css',
	template_popup_height: 380,
	setup: function (editor) {
        editor.on('init', function(args) {
        	// The tinymce editor is ready
            document.getElementById('new_discussion_loader').style.display = "none";
            if(!editor.getContent()){
                document.getElementById('tinymce_placeholder').style.display = "block";
            }
			document.getElementById('forum_form_editor').style.display = "block";

            // check if user is in discussion view
            if ($('#new_discussion_loader_in_discussion_view').length > 0) {
                document.getElementById('new_discussion_loader_in_discussion_view').style.display = "none";
                document.getElementById('forum_form_editor_in_discussion_view').style.display = "block";
            }
        });
        editor.on('keyup', function(e) {
        	content = editor.getContent();
        	if(content){
        		//$('#tinymce_placeholder').fadeOut();
        		document.getElementById('tinymce_placeholder').style.display = "none";
        	} else {
        		//$('#tinymce_placeholder').fadeIn();
        		document.getElementById('tinymce_placeholder').style.display = "block";
        	}
        });
    }
});

function initializeNewTinyMCE(id){
    tinymce.init({
        selector:'#'+id,
        skin: 'forum',
        plugins: forum_tinymce_plugins,
        toolbar: forum_tinymce_toolbar,
        menubar: false,
        statusbar: false,
        height : '300',
        content_css : '/vendor/innoboxrr/forum/assets/css/forum.css',
        template_popup_height: 380
    });
}
