<?php namespace Twlan; ?>
/* 23706 trunk*/
/*925342c646e86c73f3456b15feb3841a*/

function init_trainqueue(building_type, url)
{
    var sortable_id = "#trainqueue_" + building_type,
        building_serialized = "building=" + building_type + "&";
    $(sortable_id).sortable(
    {
        axis: 'y',
        handle: '.bqhandle',
        stop: function (event, ui)
        {
            var el = ui.item;
            $.ajax(
            {
                dataType: 'json',
                type: 'get',
                url: url,
                data: building_serialized + $(sortable_id).sortable('serialize'),
                success: function (data)
                {
                    if(data.code == false)
                    {
                        $(sortable_id).sortable('cancel');
                        return
                    };
                    $("#replace_" + building_type).replaceWith(data.table);
                    init_trainqueue(building_type, url);
                    startTimer()
                }
            })
        }
    });
    $(sortable_id).sortable('option', 'items', '.sortable_row')
}

function init_mobiletrainqueue(building_type, url)
{
    MDS.orderableQueue.init($('#replace_' + building_type), url + '&building=' + building_type + '&', function(data)
    {
        $('#replace_' + building_type).replaceWith(data.table);
        init_mobiletrainqueue(building_type, url);
        startTimer()
    })
}