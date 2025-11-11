<?php namespace Twlan; ?>
/* 23706 trunk*/
var BuildingSmith = {
    link_research: '',
    link_cancel: '',
    link_remove: '',
    techs: [],
    is_affordability_forecasting_initialized: false,
    init: function()
    {
        this.colorAffordability();
        if (!this.is_affordability_forecasting_initialized)
        {
            $(window.TribalWars).on('resource_change', function()
            {
                setTimeout(function()
                {
                    BuildingSmith.colorAffordability()
                }, 1)
            });
            this.is_affordability_forecasting_initialized = true
        }
    },
    research: function(tech_id)
    {
        TribalWars.post(this.link_research,
        {},
        {
            tech_id: tech_id,
            source: game_data.village.id
        }, function (data)
        {
            if (data.error)
            {
                UI.ErrorMessage(data.error);
                return
            };
            BuildingSmith.update(data)
        }, 'json');
        return false
    },
    cancel: function (order_id)
    {
        TribalWars.post(this.link_cancel,
        {},
        {
            order_id: order_id,
            source: game_data.village.id
        }, function(data)
        {
            if (data.error)
            {
                UI.ErrorMessage(data.error, 2e3);
                return
            };
            BuildingSmith.update(data)
        }, 'json');
        return false
    },
    remove: function(tech_id)
    {
        TribalWars.post(this.link_remove,
        {},
        {
            tech_id: tech_id,
            source: game_data.village.id
        }, function(data)
        {
            if (data.error)
            {
                UI.ErrorMessage(data.error);
                return
            };
            UI.SuccessMessage('The technology is revoked ');
            BuildingSmith.update(data)
        }, 'json');
        return false
    },
    update: function(data)
    {
        if (typeof data.current_research != 'undefined')
            if ($('#current_research').length == 0)
            {
                $('#tech_list').before(data.current_research)
            }
            else if (data.current_research)
        {
            $('#current_research').replaceWith(data.current_research)
        }
        else $('#current_research').remove();
        if (typeof data.tech_list != 'undefined') $('#tech_list').replaceWith(data.tech_list);
        startTimer();
        if (typeof initMobileMove === 'function') initMobileMove()
    },
    colorAffordability: function()
    {
        for (var tech_id in this.techs.available)
        {
            var tech = this.techs.available[tech_id];
            if (!tech.can_research) continue;
            var resources = ['wood', 'stone', 'iron'];
            for (var r = 0; r < resources.length; r++)
            {
                var res = resources[r],
                    $cost = $('#' + tech.id + '_cost_' + res);
                if (tech[res] > game_data.village[res])
                {
                    $cost.addClass('warn')
                }
                else $cost.removeClass('warn')
            }
        }
    }
}