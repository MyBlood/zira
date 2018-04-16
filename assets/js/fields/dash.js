var dash_fields_group_load = function() {
    for (var i=0; i<this.options.bodyItems.length; i++) {
        if (typeof(this.options.bodyItems[i].activated)!="undefined" &&
            this.options.bodyItems[i].activated == 0
        ) {
            $(this.options.bodyItems[i].element).addClass('inactive');
        }
    }
};

var dash_fields_group_language = function(element) {
    var language = this.options.data.language;
    var id = $(element).attr('id');
    var item = this.findMenuItemByProperty('id',id);
    if (item && typeof(item.language)!="undefined") {
        if (item.language!=language) {
            this.options.data.language=item.language;
            desk_window_reload(this);
            $(element).parents('ul').find('.glyphicon-ok').removeClass('glyphicon-ok').addClass('glyphicon-filter');
            $(element).find('.glyphicon').removeClass('glyphicon-filter').addClass('glyphicon-ok');
        } else {
            this.options.data.language='';
            desk_window_reload(this);
            $(element).parents('ul').find('.glyphicon-ok').removeClass('glyphicon-ok').addClass('glyphicon-filter');
        }
    }
};

var dash_fields_group_drag = function() {
    this.isContentDragging = false;
    this.dragStartY = null;
    this.dragStartItem = null;
    this.dragOverItem = null;
    this.dragReplaced = false;
    this.dragImage = new Image(); this.dragImage.src=dash_fields_blank_src;
    $(this.content).bind('dragstart',this.bind(this,function(e){
        if (this.isDisabled()) return;
        if (typeof(e.originalEvent.target)=="undefined") return;
        //if ($(e.originalEvent.target).parents('li').children('a').hasClass('inactive')) return;
        this.isContentDragging = true;
        this.dragStartY = e.originalEvent.pageY;
        this.dragStartItem = $(e.originalEvent.target).parents('li').children('a').attr('id');
        e.originalEvent.dataTransfer.setDragImage(this.dragImage,-10,0);
        $(this.content).find('#'+this.dragStartItem).parent('li').css('opacity',.5);
        for (var i=0; i<this.options.bodyItems.length; i++) {
            this.options.bodyItems[i].is_dragged = false;
        }
    }));
    $(this.content).bind('dragover',this.bind(this,function(e){
        if (this.isDisabled()) return;
        if (typeof(e.originalEvent.target)=="undefined" || !this.isContentDragging) return;
        var item = $(e.originalEvent.target).parents('li').children('a');
        if ($(item).length==0 || $(item).parents('#'+this.getId()).length==0) return;
        //if ($(item).hasClass('inactive')) return;
        if ($(item).parent('li').hasClass('tmp-drag-fields-item')) return;
        if (this.dragReplaced && $(item).attr('id') == this.dragStartItem) {
            var startItem = this.findBodyItemByProperty('id',this.dragStartItem);
            var endItem = this.findBodyItemByProperty('id',this.dragOverItem);
            if (startItem && endItem && typeof(startItem.sort_order)!="undefined" && typeof(endItem.sort_order)!="undefined") {
                var start_order = startItem.sort_order;
                var end_order = endItem.sort_order;
                startItem.sort_order = end_order;
                endItem.sort_order = start_order;
                startItem.is_dragged = true;
                endItem.is_dragged = true;
            }
            this.dragOverItem = null;
            this.dragStartY = e.originalEvent.pageY;
            this.dragReplaced = false;
        }
        if (this.dragStartItem!=$(item).attr('id') && this.dragOverItem!=$(item).attr('id')) {
            this.dragOverItem=$(item).attr('id');
            var tmp = '<li class="tmp-drag-fields-item"></li>';
            if (e.originalEvent.pageY > this.dragStartY) {
                $(this.content).find('#'+this.dragOverItem).parent('li').after(tmp);
            } else {
                $(this.content).find('#'+this.dragOverItem).parent('li').before(tmp);
            }
            $(this.content).find('li.tmp-drag-fields-item').replaceWith($(this.content).find('#'+this.dragStartItem).parent('li'));
            this.dragReplaced = true;
        }
    }));
    $(this.element).bind('drop',this.bind(this,function(e){
        if (this.isDisabled()) return;
        var dragged = [];
        var orders = [];
        for (var i=0; i<this.options.bodyItems.length; i++) {
            if (typeof(this.options.bodyItems[i].sort_order)!="undefined" && typeof(this.options.bodyItems[i].is_dragged)!="undefined" && this.options.bodyItems[i].is_dragged) {
                dragged.push(this.options.bodyItems[i].data);
                orders.push(this.options.bodyItems[i].sort_order);
            }
        }
        if (dragged.length>1 && orders.length>1) {
            desk_window_request(this, url('fields/dash/groupdrag'),{'items':dragged,'orders':orders});
        }
    }));
    $(this.content).bind('dragend',this.bind(this,function(e){
        $(this.content).find('#'+this.dragStartItem).parent('li').css('opacity',1);
        this.isContentDragging = false;
        this.dragStartY = null;
        this.dragStartItem = null;
        this.dragOverItem = null;
        this.dragReplaced = false;
        $(this.content).find('li.tmp-drag-fields-item').remove();
    }));
};

var dash_fields_group_fields = function() {
    var selected = this.getSelectedContentItems();
    if (selected && selected.length==1) {
        var data = {'items':[selected[0].data]};
        desk_call(dash_fields_fields_wnd, null, {
            'data':data
        });
    }
};

var dash_fields_item_create = function() {
    var data = {
        'data': {
            'items': [],
            'group_id': this.options.data.items[0]
        },
        'reload': this.className,
        'onClose':function(){
            desk_window_reload_all(this.options.reload);
        }
    };
    desk_call(dash_fields_field_wnd, null, data);
};

var dash_fields_item_edit = function() {
    var selected = this.getSelectedContentItems();
    if (selected && selected.length==1) {
        var data = {
            'data':{
                'items': [selected[0].data],
                'group_id': this.options.data.items[0]
            },
            'reload': this.className,
            'onClose':function(){
                desk_window_reload_all(this.options.reload);
            }
        };
        desk_call(dash_fields_field_wnd, null, data);
    }
};

var dash_fields_field_form_init = function() {
    $(this.content).find('select.field-types-select').eq(0).change(this.bind(this, function(){
        var val = $(this.content).find('select.field-types-select').eq(0).val();
        if (val == 'radio' || val == 'select') {
            $(this.content).find('.form_field_values_wrapper').show();
            desk_call(dash_fields_field_values_init, this);
        } else {
            $(this.content).find('.form_field_values_wrapper').hide();
        }
    }));
    var values = $(this.content).find('input.field-values-hidden').eq(0).val();
    if (values.length>0) {
        $(this.content).find('.form_field_values_wrapper').show();
        desk_call(dash_fields_field_values_init, this);
        var vals = values.split(',');
        for (var i=0; i<vals.length; i++) {
            var w = $(this.content).find('.field-values-input-wrapper').last();
            $(w).parent().append('<div class="field-values-input-wrapper" style="position:relative;margin-top:10px">'+w.html()+'</div>');
            $(this.content).find('input.field-values-input').last().val(vals[i]);
        }
        $(this.content).find('.field-values-input-wrapper').eq(0).remove();
        desk_call(dash_fields_field_values_init, this);
    }
};

var dash_fields_field_values_init = function() {
    var total = $(this.content).find('input.field-values-input').length;
    $(this.content).find('input.field-values-input').each(function(index){
        if (!$(this).parent().hasClass('field-values-input-wrapper')) {
            $(this).wrap('<div class="field-values-input-wrapper" style="position:relative"></div>');
        }
        $(this).parent('.field-values-input-wrapper').children('.field-values-input-remove').remove();
        $(this).parent('.field-values-input-wrapper').children('.field-values-input-add').remove();
        if (index<total-1) {
            $(this).parent('.field-values-input-wrapper').append('<span class="glyphicon glyphicon-minus-sign field-values-input-remove" style="position:absolute;right:4px;top:10px;cursor:pointer"></span>');
        } else {
            $(this).parent('.field-values-input-wrapper').append('<span class="glyphicon glyphicon-plus-sign field-values-input-add" style="position:absolute;right:4px;top:10px;cursor:pointer"></span>');
        }
    });
    $(this.content).find('.field-values-input-add').unbind('click').click(this.bind(this, function(){
        var w = $(this.content).find('.field-values-input-wrapper').eq(0);
        $(w).parent().append('<div class="field-values-input-wrapper" style="position:relative;margin-top:10px">'+w.html()+'</div>');
        $(this.content).find('input.field-values-input').last().val('');
        desk_call(dash_fields_field_values_init, this);
    }));
    $(this.content).find('.field-values-input-remove').unbind('click').click(function(){
        $(this).parent('.field-values-input-wrapper').remove();
    });
};

var dash_fields_fields_load = function() {
    for (var i=0; i<this.options.bodyItems.length; i++) {
        if (typeof(this.options.bodyItems[i].activated)!="undefined" &&
            this.options.bodyItems[i].activated == 0
        ) {
            $(this.options.bodyItems[i].element).addClass('inactive');
        }
    }
};

var dash_fields_fields_drag = function() {
    this.isContentDragging = false;
    this.dragStartY = null;
    this.dragStartItem = null;
    this.dragOverItem = null;
    this.dragReplaced = false;
    this.dragImage = new Image(); this.dragImage.src=dash_fields_blank_src;
    $(this.content).bind('dragstart',this.bind(this,function(e){
        if (this.isDisabled()) return;
        if (typeof(e.originalEvent.target)=="undefined") return;
        //if ($(e.originalEvent.target).parents('li').children('a').hasClass('inactive')) return;
        this.isContentDragging = true;
        this.dragStartY = e.originalEvent.pageY;
        this.dragStartItem = $(e.originalEvent.target).parents('li').children('a').attr('id');
        e.originalEvent.dataTransfer.setDragImage(this.dragImage,-10,0);
        $(this.content).find('#'+this.dragStartItem).parent('li').css('opacity',.5);
        for (var i=0; i<this.options.bodyItems.length; i++) {
            this.options.bodyItems[i].is_dragged = false;
        }
    }));
    $(this.content).bind('dragover',this.bind(this,function(e){
        if (this.isDisabled()) return;
        if (typeof(e.originalEvent.target)=="undefined" || !this.isContentDragging) return;
        var item = $(e.originalEvent.target).parents('li').children('a');
        if ($(item).length==0 || $(item).parents('#'+this.getId()).length==0) return;
        //if ($(item).hasClass('inactive')) return;
        if ($(item).parent('li').hasClass('tmp-drag-fields-item')) return;
        if (this.dragReplaced && $(item).attr('id') == this.dragStartItem) {
            var startItem = this.findBodyItemByProperty('id',this.dragStartItem);
            var endItem = this.findBodyItemByProperty('id',this.dragOverItem);
            if (startItem && endItem && typeof(startItem.sort_order)!="undefined" && typeof(endItem.sort_order)!="undefined") {
                var start_order = startItem.sort_order;
                var end_order = endItem.sort_order;
                startItem.sort_order = end_order;
                endItem.sort_order = start_order;
                startItem.is_dragged = true;
                endItem.is_dragged = true;
            }
            this.dragOverItem = null;
            this.dragStartY = e.originalEvent.pageY;
            this.dragReplaced = false;
        }
        if (this.dragStartItem!=$(item).attr('id') && this.dragOverItem!=$(item).attr('id')) {
            this.dragOverItem=$(item).attr('id');
            var tmp = '<li class="tmp-drag-fields-item"></li>';
            if (e.originalEvent.pageY > this.dragStartY) {
                $(this.content).find('#'+this.dragOverItem).parent('li').after(tmp);
            } else {
                $(this.content).find('#'+this.dragOverItem).parent('li').before(tmp);
            }
            $(this.content).find('li.tmp-drag-fields-item').replaceWith($(this.content).find('#'+this.dragStartItem).parent('li'));
            this.dragReplaced = true;
        }
    }));
    $(this.element).bind('drop',this.bind(this,function(e){
        if (this.isDisabled()) return;
        var dragged = [];
        var orders = [];
        for (var i=0; i<this.options.bodyItems.length; i++) {
            if (typeof(this.options.bodyItems[i].sort_order)!="undefined" && typeof(this.options.bodyItems[i].is_dragged)!="undefined" && this.options.bodyItems[i].is_dragged) {
                dragged.push(this.options.bodyItems[i].data);
                orders.push(this.options.bodyItems[i].sort_order);
            }
        }
        if (dragged.length>1 && orders.length>1) {
            desk_window_request(this, url('fields/dash/fielddrag'),{'items':dragged,'orders':orders});
        }
    }));
    $(this.content).bind('dragend',this.bind(this,function(e){
        $(this.content).find('#'+this.dragStartItem).parent('li').css('opacity',1);
        this.isContentDragging = false;
        this.dragStartY = null;
        this.dragStartItem = null;
        this.dragOverItem = null;
        this.dragReplaced = false;
        $(this.content).find('li.tmp-drag-fields-item').remove();
    }));
};