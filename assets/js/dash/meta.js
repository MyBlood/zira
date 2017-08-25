var dash_meta_load = function() {
    desk_window_form_init(this);
    $(this.content).find('input.logo_option').parent().append('<span class="glyphicon glyphicon-folder-open" style="position:absolute;right:30px;top:10px;cursor:pointer"></span>');
    $(this.content).find('input.logo_option').parent().children('.glyphicon').click(this.bind(this, function(){
        desk_file_selector(function(selected){
            if (selected && selected.length>0 && typeof(selected[0].type)!="undefined" && selected[0].type=='image') {
                var src = selected[0].data;
                var regexp = new RegExp('\\'+desk_ds, 'g');
                $(this.content).find('input.logo_option').val(src.replace(regexp,'/'));
            }
        }, this);
    }));
    $(this.element).find('#dashmetaform_access_label').click(zira_bind(this, function(){
        var button = $(this.element).find('#dashmetaform_access_button');
        var container = $(this.element).find('#dashmetaform_access_container');
        if ($(container).css('display')=='none') {
            $(container).slideDown();
            $(button).find('.glyphicon').removeClass('glyphicon-menu-right').addClass('glyphicon glyphicon-menu-down');
        } else {
            $(container).slideUp();
            $(button).find('.glyphicon').removeClass('glyphicon-menu-down').addClass('glyphicon glyphicon-menu-right');
        }
    }));
};