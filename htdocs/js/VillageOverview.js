<?php namespace Twlan; ?>
/* 23706 trunk*/
VillageOverview = {
    urls:
    {},
    init: function()
    {
        if (mobile) return;
        $('#overviewtable').sortable(
        {
            placeholder: "vis placeholder",
            cursor: 'move',
            items: "div.moveable",
            handle: 'h4',
            opacity: 0.6,
            start: function()
            {
                $('.hidden_widget').fadeTo(0, 0.5)
            },
            stop: function()
            {
                $('.hidden_widget').hide()
            },
            update: function()
            {
                var columns = {
                        leftcolumn: [],
                        rightcolumn: []
                    },
                    widgets = $(this).sortable("toArray");
                for (var i in widgets)
                    if (widgets.hasOwnProperty(i))
                    {
                        var parent = document.getElementById(widgets[i]).parentNode.id;
                        columns[parent].push(widgets[i])
                    };
                $.post(VillageOverview.urls.reorder, columns)
            }
        });
        UI.ToolTip($('.effect_tooltip'))
    },
    toggleWidget: function(widget, icon)
    {
        var element = $('#' + widget + ' > div');
        element.toggle();
        icon.src = element.is(':hidden') ? 'graphic/plus.png' : 'graphic/minus.png';
        $.post(VillageOverview.urls.toggle,
        {
            widget: widget,
            hide: Number(element.is(':hidden'))
        });
        return false
    },
    change_order: function(link, feature_id, cost)
    {
        var confirmChangeOrderCallback = function()
        {
            document.location.replace(link)
        };
        Premium.check(feature_id, cost, confirmChangeOrderCallback);
        return false
    },
    refreshAMSettingsWidget: function()
    {
        console.log('AM widget refreshing...');
        if ($('#show_am_settings').length) TribalWars.get('overview',
        {
            ajax: 'am_settings'
        }, function(response)
        {
            $('#show_am_settings > .widget_content').html(response);
            UI.ToolTip($('.tooltip'))
        })
    }
}