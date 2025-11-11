<?php namespace Twlan; ?>
/* 23706 trunk*/
/*bb62d89590e79d613a36466115662620*/
function init_techqueue(techqueue_id, url)
{
    if (techqueue_id.lastIndexOf("#", 0) !== 0) techqueue_id = "#" + techqueue_id;
    var techqueue = $(techqueue_id);
    if (techqueue.find('.sortable_row').length > 1)
    {
        techqueue.sortable(
        {
            axis: 'y',
            handle: '.bqhandle',
            stop: function(event, ui)
            {
                var el = ui.item;
                $.ajax(
                {
                    dataType: 'json',
                    type: 'get',
                    url: url,
                    data: techqueue.sortable('serialize'),
                    success: function(data)
                    {
                        if (data.code == false)
                        {
                            techqueue.sortable('cancel');
                            return
                        };
                        $("#current_research").replaceWith(data.table);
                        init_techqueue(techqueue_id, url);
                        startTimer()
                    }
                })
            }
        });
        techqueue.sortable('option', 'items', '.sortable_row')
    }
}