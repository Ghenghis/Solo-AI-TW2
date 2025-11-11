<?php namespace Twlan; ?>
/* 23706 trunk */
/**/
var BuildingMain = {
    BUILD_ERROR_REQ: 1,
    BUILD_ERROR_POP: 2,
    BUILD_ERROR_QUEUE: 3,
    BUILD_ERROR_RES: 4,
    BUILD_ERROR_QUEUE_RES: 5,
    upgrade_building_link: '',
    downgrade_building_link: '',
    link_reduce_buildtime: '',
    link_cancel: '',
    confirm_queue: false,
    mode: 0,
    request_id: 0,
    last_request_id: 0,
    buildings: null,
    order_count: 0,
    is_buildability_initialized: false,
    init: function()
    {
        BuildingMain.init_buildbuttons();
        if (!this.is_buildability_initialized)
        {
            $(window.TribalWars).on('resource_change', function()
            {
                setTimeout(BuildingMain.updateBuildableState, 1)
            });
            this.is_buildability_initialized = true
        };
        OrderProgress.initProgress()
    },
    updateBuildableState: function()
    {
        var order_count = $('#buildqueue_wrap').length == 0 ? 0 : BuildingMain.order_count,
            new_timers = 0;
        BuildingMain.colorAffordability();
        $.each(BuildingMain.buildings, function(building_id, building)
        {
            var $build_options = $('#main_buildrow_' + building_id).find('.build_options'),
                previous_error = parseInt($build_options.data['error']),
                could_afford = $build_options.data('could-afford');
            if (!$build_options) return;
            var error_text, error_code, can_afford = Village.canAfford(building.wood, building.stone, building.iron);
            if (!building.can_build && building.level)
            {
                error_code = BuildingMain.BUILD_ERROR_REQ;
                error_text = building.error
            }
            else if (order_count > 1 && !premium)
            {
                if (can_afford.afford == Village.AFFORD_TYPE_NOW)
                {
                    error_code = BuildingMain.BUILD_ERROR_QUEUE;
                    error_text = 'Queue is currently full'
                }
                else
                {
                    error_code = BuildingMain.BUILD_ERROR_RES;
                    error_text = can_afford.when
                }
            }
            else if (building.pop && building.pop > parseInt(game_data.village.pop_max) - parseInt(game_data.village.pop))
            {
                error_code = BuildingMain.BUILD_ERROR_POP;
                error_text = building.error
            }
            else if (can_afford.afford != Village.AFFORD_TYPE_NOW)
            {
                error_code = BuildingMain.BUILD_ERROR_RES;
                error_text = can_afford.when
            };
            if (error_code == BuildingMain.BUILD_ERROR_RES && building.hasOwnProperty('wood_cheap'))
            {
                var can_afford_cheap = Village.canAfford(building.wood_cheap, building.stone_cheap, building.iron_cheap),
                    $bcr_button = $build_options.find('.btn-bcr');
                if (can_afford_cheap.afford == Village.AFFORD_TYPE_NOW && $bcr_button.hasClass('btn-bcr-disabled'))
                {
                    $bcr_button.removeClass('btn-bcr-disabled')
                }
                else if (can_afford_cheap.afford != Village.AFFORD_TYPE_NOW && !$bcr_button.hasClass('btn-bcr-disabled')) $bcr_button.addClass('btn-bcr-disabled')
            };
            if (typeof error_text == 'undefined' && building.hasOwnProperty('wood_queue_factor'))
            {
                var can_afford_with_queue = Village.canAfford(building.wood_queue_factor, building.stone_queue_factor, building.iron_queue_factor);
                if (can_afford_with_queue.afford != Village.AFFORD_TYPE_NOW)
                {
                    error_code = BuildingMain.BUILD_ERROR_QUEUE_RES;
                    error_text = 'Not enough resources available to add assignment to building queue.'
                }
            };
            var $build_button = $build_options.find('.btn-build'),
                $build_error = $build_options.find('.inactive');
            if (typeof error_text != 'undefined')
            {
                $build_button.hide();
                $build_error.show();
                if ($build_error.html() != error_text) $build_error.html(error_text)
            }
            else
            {
                $build_button.show();
                $build_error.hide()
            }
        })
    },
    init_buildbuttons: function()
    {
        $('#building_wrapper').on('click', '.btn-build', function()
        {
            var $this = $(this);
            BuildingMain.build($this.data('building'));
            return false
        });
        $('#building_wrapper').on('click', '.btn-bcr', function()
        {
            var $this = $(this);
            if ($this.hasClass('btn-bcr-disabled')) return false;
            BuildingMain.build_reduced($this.data('cost'), $this.data('building'));
            return false
        })
    },
    init_buildqueue: function(url)
    {
        $("#buildqueue").sortable(
        {
            axis: 'y',
            handle: '.bqhandle',
            helper: function(e, tr)
            {
                var $originals = tr.children(),
                    $helper = tr.clone();
                $helper.children().each(function(index)
                {
                    $(this).width($originals.eq(index).width())
                });
                return $helper
            },
            stop: function(event, ui)
            {
                var el = ui.item;
                $.ajax(
                {
                    dataType: 'json',
                    type: 'get',
                    url: url,
                    data: $("#buildqueue").sortable('serialize'),
                    success: function (data)
                    {
                        if (data.error)
                        {
                            UI.InfoMessage(data.error, 2e3, true);
                            $("#buildqueue").sortable('cancel');
                            return
                        };
                        BuildingMain.init_buildqueue(url);
                        BuildingMain.update_all(data)
                    }
                })
            }
        });
        $("#buildqueue").sortable('option', 'items', '.sortable_row')
    },
    init_mobilebuildqueue: function (url)
    {
        MDS.orderableQueue.init($('#buildqueue_wrap').find('div').first(), url, function (data)
        {
            BuildingMain.update_all(data)
        })
    },
    build: function (building_id, cheap)
    {
        var updateBuildQueue = function ()
        {
            var current_request_id = ++BuildingMain.request_id,
                data = {
                    id: building_id,
                    force: 1,
                    destroy: BuildingMain.mode,
                    source: game_data.village.id
                };
            if (typeof cheap != "undefined") data.cheap = 1;
            var url = BuildingMain.mode == 0 ? BuildingMain.upgrade_building_link : BuildingMain.downgrade_building_link;
            TribalWars.post(url,
            {}, data, function(data)
            {
                if (current_request_id > BuildingMain.last_request_id)
                {
                    BuildingMain.last_request_id = current_request_id;
                    BuildingMain.update_all(data);
                    UI.SuccessMessage('The building order was successfully queued.')
                }
            })
        };
        if (BuildingMain.confirm_queue && this.mode == 0)
        {
            var msg = '<?php l('buildingMain.queueBuild');?>',
                buttons = [
                {
                    text: "<?php l('game.confirm'); ?>",
                    callback: updateBuildQueue,
                    confirm: true
                }];
            UI.ConfirmationBox(msg, buttons)
        }
        else updateBuildQueue();
        return false
    },
    destroy: function (building_id)
    {
        return BuildingMain.build(building_id)
    },
    build_reduced: function(cost, building)
    {
        Premium.check('BuildCostReduction', cost, function ()
        {
            return BuildingMain.build(building, 1)
        });
        return false
    },
    cancel: function (order_id, spent_pp)
    {
        var msg = '<?php echo l('game.overview.cancel_build');?>'
        var cancelBuildingCallback = function()
            {
                TribalWars.post(BuildingMain.link_cancel, null,
                {
                    id: order_id,
                    destroy: BuildingMain.mode
                }, function(response)
                {
                    BuildingMain.update_all(response)
                })
            },
            buttons = [
            {
                text: '<?php l('game.confirm'); ?>',
                callback: cancelBuildingCallback,
                confirm: true
            }];
        UI.ConfirmationBox(msg, buttons);
        return false
    },
    change_order: function (order_id, feature_id, cost)
    {
        var confirmChangeOrderCallback = function ()
        {
            TribalWars.get(BuildingMain.link_change_order,
            {
                id: order_id,
                destroy: BuildingMain.mode
            }, function(data)
            {
                BuildingMain.update_all(data)
            })
        };
        Premium.check(feature_id, cost, confirmChangeOrderCallback);
        return false
    },
    update_all: function (data)
    {
        if (data.reload)
        {
            document.location.reload();
            return
        };
        var queue_wrapper = $('#buildqueue_wrap');
        if (queue_wrapper.length === 1)
        {
            if (data.building_orders)
            {
                queue_wrapper.replaceWith(data.building_orders)
            }
            else queue_wrapper.remove()
        }
        else $('#building_wrapper').before(data.building_orders);
        if (data.next_buildings)
        {
            $('#building_wrapper').replaceWith(data.next_buildings);
            $('.inactive img').fadeTo(0, 0.5);
            BuildingMain.init_buildbuttons()
        };
        if (typeof data.confirm_queue !== 'undefined') BuildingMain.confirm_queue = data.confirm_queue;
        if (typeof data.population !== 'undefined') $('#pop_current_label').html(data.population);
        startTimer();
        if (typeof QuestArrows !== 'undefined') QuestArrows.init();
        OrderProgress.initProgress();
        UI.ToolTip('.tooltip');
        //Premium.directBuy.init()
    },
    colorAffordability: function()
    {
        ['wood', 'stone', 'iron'].forEach(function(res)
        {
            $('.cost_' + res).each(function()
            {
                $this = $(this);
                if ($this.data('cost') > game_data.village[res])
                {
                    $this.addClass('warn')
                }
                else $this.removeClass('warn')
            })
        })
    }
}