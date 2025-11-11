<?php namespace Twlan; ?>
/* 23706 trunk*/
var TargetField;
(function()
{
    'use strict';
    TargetField = {
        request_id: 0,
        selected_index: null,
        num_villages: 0,
        page_limit: null,
        last_attacked: null,
        autocomplete_visible: false,
        ie_compatibility_mode: true,
        confirmed_village_data: false,
        read_only: false,
        clicked_button: 'attack',
        autocomplete_wrapper: null,
        input_text_field: null,
        script_watcher: null,
        script_old_x: '',
        script_old_y: '',
        init: function()
        {
            this.input_text_field = $('.target-select input[type=text]');
            this.initAutoComplete();
            this.changeSearchType.call($('.target-select input[type=radio]:checked'), false);
            this.page_limit = Math.min(Math.max(Math.round(($(window).height() - this.input_text_field.offset().top) / 50), 5), 10);
            this.setAutoCompleteWrapperPosition();
            this.input_text_field.on('input', function()
            {
                TargetField.ie_compatibility_mode = false;
                TargetField.fetchVillages()
            }).on('keyup', TargetField.textFieldKeyUp).on('keydown', TargetField.textFieldKeyDown).on('remove', TargetField.destroy);
            $('.target-select input[type=radio]').on('change', TargetField.changeSearchType);
            TargetField.input_text_field.parents('form').on('submit', TargetField.beforeSubmit);
            $(window).on('click', TargetField.onWindowClick);
            $('.target-select .btn').on('click', function()
            {
                TargetField.clicked_button = $(this).attr('name')
            });
            this.initScriptCompatibility()
        },
        destroy: function()
        {
            clearInterval(TargetField.script_watcher);
            $(window).off('click', TargetField.onWindowClick);
            this.input_text_field = null
        },
        onWindowClick: function()
        {
            if (TargetField.autocomplete_visible) TargetField.hideAutoCompleteWrapper()
        },
        initAutoComplete: function()
        {
            $('.target-input-autocomplete').autocomplete(
            {
                minLength: 2,
                source: UI.AutoComplete.source
            });
            this.input_text_field.on('autocompleteselect', function(e, ui)
            {
                TargetField.fetchVillages(
                {
                    input: ui.item.value
                })
            })
        },
        initScriptCompatibility: function()
        {
            clearInterval(TargetField.script_watcher);
            TargetField.script_watcher = setInterval(TargetField.checkForScriptChange, 100)
        },
        checkForScriptChange: function()
        {
            var x = $('#inputx').val(),
                y = $('#inputy').val(),
                old_x = TargetField.confirmed_village_data ? TargetField.confirmed_village_data.x : '',
                old_y = TargetField.confirmed_village_data ? TargetField.confirmed_village_data.y : '';
            if (x && y && (x !== old_x || y !== old_y))
            {
                clearInterval(TargetField.script_watcher);
                TargetField.setVillageByCoordinates(x, y, function()
                {
                    $('#inputx').val('');
                    $('#inputy').val('');
                    TargetField.initScriptCompatibility()
                })
            }
        },
        setReadOnly: function()
        {
            this.read_only = true
        },
        setLastAttacked: function(village_data)
        {
            this.last_attacked = village_data;
            $('.target-last-attacked').show().on('click', function(e)
            {
                e.preventDefault();
                TargetField.confirmVillage(TargetField.getVillageDiv(TargetField.last_attacked))
            })
        },
        enableQuickButton: function(type, link)
        {
            $('.target-' + type).show().on('click', function(event)
            {
                TargetField.loadTargetsPopup(event, link)
            })
        },
        loadTargetsPopup: function(event, link)
        {
            UI.AjaxPopup(event, 'village_targets', link, 'Ziu', null,
            {
                reload: true
            }, null, 400)
        },
        setVillageByCoordinates: function(x, y, callback)
        {
            this.fetchVillages(
            {
                type: 'coord',
                input: x + '|' + y
            }, function(data)
            {
                if (data.villages.length) TargetField.confirmVillage(TargetField.getVillageDiv(data.villages[0]));
                if (typeof callback === 'function') callback()
            })
        },
        setVillageByData: function(data)
        {
            TargetField.confirmVillage(TargetField.getVillageDiv(data))
        },
        beforeSubmit: function()
        {
            var $inputx = $('#inputx'),
                $inputy = $('#inputy'),
                $village_div = $('.target-input').find('.village-item');
            if ($village_div.length)
            {
                var data = $village_div.data('village_data');
                $inputx.val(data.x);
                $inputy.val(data.y)
            };
            var input = $('.target-select input[type=text]').val(),
                match;
            if ((match = input.match(/^([0-9]{1,3})\|([0-9]{1,3})$/)))
            {
                $inputx.val(match[1]);
                $inputy.val(match[2])
            };
            return true
        },
        changeSearchType: function(e)
        {
            var $this = $(this),
                $text_input = $this.parents('.target-select').find('input[type=text]'),
                placeholder;
            switch ($this.val())
            {
                case 'coord':
                    placeholder = '123|456';
                    break;
                case 'village_name':
                    placeholder = 'Dorfname igäh';
                    break;
                case 'player_name':
                    placeholder = 'Spielername igäh';
                    break
            };
            $text_input.attr('placeholder', placeholder).data('search-type', $this.val());
            TargetField.clearVillages();
            if ($this.val() === 'player_name')
            {
                $text_input.autocomplete('enable')
            }
            else $text_input.autocomplete('disable');
            if (e !== false) $text_input.focus()
        },
        clearVillages: function()
        {
            this.hideAutoCompleteWrapper();
            this.removeConfirmedVillage()
        },
        fetchVillages: function(payload_override, callback)
        {
            var payload = {
                ajax: 'target_selection',
                input: $('.target-select input[type=text]').val(),
                type: $('.target-select input[type=radio]:checked').val(),
                request_id: ++TargetField.request_id,
                limit: TargetField.page_limit,
                offset: 0
            };
            payload = $.extend(payload, payload_override);
            if (payload.input.length === 0) return;
            if (typeof callback === 'undefined') callback = function(data)
            {
                TargetField.handleVillagesData(data)
            };
            TribalWars.get('api', payload, callback)
        },
        handleVillagesData: function(data)
        {
            if (data.request_id !== this.request_id) return;
            var $wrapper = TargetField.getAutoCompleteWrapper();
            this.hideAutoCompleteWrapper();
            this.num_villages = data.villages.length + data.offset;
            if (data.offset === 0)
            {
                this.selected_index = null;
                $wrapper.empty()
            }
            else $wrapper.find('.village-more').remove();
            if (data.villages.length === 0) return;
            this.showAutoCompleteWrapper();
            $.each(data.villages, function(i, village_data)
            {
                $wrapper.append(TargetField.getVillageDiv(village_data))
            });
            if (data.more)
            {
                var $more = $('<div class="village-item village-more">Meh azeige</div>').on('click', function(e)
                {
                    e.stopPropagation();
                    TargetField.loadMoreVillages()
                });
                $wrapper.append($more)
            };
            this.setAutoCompleteWrapperPosition()
        },
        showAutoCompleteWrapper: function()
        {
            if ($('.target-select input[type=radio]:checked').val() === 'player_name') this.input_text_field.autocomplete('disable');
            this.getAutoCompleteWrapper().show();
            this.autocomplete_visible = true
        },
        hideAutoCompleteWrapper: function()
        {
            if ($('.target-select input[type=radio]:checked').val() === 'player_name') this.input_text_field.autocomplete('enable');
            this.getAutoCompleteWrapper().hide();
            this.autocomplete_visible = false
        },
        getAutoCompleteWrapper: function()
        {
            if (!this.autocomplete_wrapper) this.autocomplete_wrapper = $('<div class="target-select-autocomplete"></div>').appendTo('body');
            return this.autocomplete_wrapper
        },
        setAutoCompleteWrapperPosition: function()
        {
            var $wrapper = this.getAutoCompleteWrapper(),
                $input_container = $('.target-input'),
                input_pos = $input_container.offset(),
                input_height = $input_container.height();
            $wrapper.css('top', (input_pos.top + input_height + 2) + 'px');
            $wrapper.css('left', input_pos.left);
            $wrapper.css('max-height', (TargetField.page_limit * 50) + 'px')
        },
        confirmVillage: function($village_div)
        {
            TargetField.removeConfirmedVillage();
            TargetField.getAutoCompleteWrapper().hide();
            var $container = $('.target-select .target-input');
            $container.find('input').hide();
            $container.append($village_div);
            $village_div.removeClass('village-selected');
            this.confirmed_village_data = $village_div.data('village_data');
            this.updateURLForConfirmedVillage();
            $('.target-select .btn').eq(0).focus();
            if (this.confirmed_village_data.hasOwnProperty('disallow_support'))
            {
                $('#target_support').attr('disabled', 'disabled')
            }
            else $('#target_support').removeAttr('disabled');
            $('.unitsInput').removeAttr('disabled');
            if (this.confirmed_village_data.hasOwnProperty('disallow_units')) $.each(this.confirmed_village_data.disallow_units, function(k, unit_id)
            {
                $('#unit_input_' + unit_id).attr('disabled', 'disabled')
            });
            if (this.confirmed_village_data.hasOwnProperty('warning'))
            {
                $('#command-form-warning').html('<p>' + this.confirmed_village_data.warning + '</p>')
            }
            else $('#command-form-warning').text('')
        },
        removeConfirmedVillage: function()
        {
            var $container = $('.target-select .target-input');
            if ($container.find('.village-item').length)
            {
                $container.find('input').show().val('').focus();
                $container.find('.village-item').remove();
                this.confirmed_village_data = false;
                $('input[name=x], input[name=y]').val('');
                this.updateURLForConfirmedVillage()
            }
        },
        getVillageDiv: function(village_data)
        {
            var $village_div = $('<div class="village-item"><img class="village-delete" alt="" /><img class="village-picture" alt="" /><span class="village-name"></span><span class="village-info"></span><span class="village-distance"></span></div>').data('village_data', village_data);
            if (!this.read_only) $village_div.on('click', function(e)
            {
                e.stopPropagation();
                if ($(this).parent().hasClass('target-select-autocomplete'))
                {
                    TargetField.confirmVillage($village_div)
                }
                else TargetField.removeConfirmedVillage()
            });
            var village_name_snipped = village_data.name;
            if (village_name_snipped.length > 18) village_name_snipped = village_name_snipped.substr(0, 18) + '&hellip;';
            var village_name = s('%1 (%2|%3)', village_name_snipped, village_data.x, village_data.y),
                village_owner = village_data.player_name ? village_data.player_name : 'Barbare',
                village_info = '<strong>Bsitzer:</strong> ' + village_owner + ' <strong>Pünkt:</strong> ' + village_data.points,
                distance = Math.round(Math.sqrt(village_data.distance)),
                distance_units = distance === 1 ? s('%1 Fäld', distance) : s('%1 Fälder', distance),
                village_distance = '<strong>Entfernig:</strong> ' + distance_units;
            $village_div.find('.village-picture').attr('src', village_data.image);
            $village_div.find('.village-delete').attr('src', image_base + '/delete.png');
            $village_div.find('.village-name').html(village_name);
            $village_div.find('.village-info').html(village_info);
            $village_div.find('.village-distance').html(village_distance);
            if (this.read_only) $village_div.addClass('read-only');
            return $village_div
        },
        textFieldKeyUp: function(e)
        {
            if (TargetField.ie_compatibility_mode && e.keyCode !== 38 && e.keyCode !== 40) TargetField.fetchVillages();
            var $this = $(this),
                val = $this.val();
            if ($this.data('search-type') === 'coord')
            {
                val = val.replace(/[,\.]/, '|');
                val = val.replace(/[^[0-9\|]+/, '');
                if (val.length === 3 && e.keyCode !== 8 && e.keyCode !== 46) val = val + '|';
                if (val.indexOf('||') !== -1) val = val.replace(/(\|{2,})/, '|');
                if (val.length > 7) val = val.substr(0, 7);
                $this.val(val)
            };
            return true
        },
        textFieldKeyDown: function(e)
        {
            if (e.keyCode === 38)
            {
                TargetField.selectPrevVillage();
                return false
            }
            else if (e.keyCode === 40)
            {
                TargetField.selectNextVillage();
                return false
            }
            else if (e.keyCode === 13)
            {
                TargetField.confirmVillageAtIndex(TargetField.selected_index);
                return false
            };
            return true
        },
        selectNextVillage: function()
        {
            if (this.selected_index === null)
            {
                this.selectVillageAtIndex(0)
            }
            else if (this.selected_index + 1 <= this.num_villages) this.selectVillageAtIndex(this.selected_index + 1)
        },
        selectPrevVillage: function()
        {
            if (this.selected_index !== null && this.selected_index > 0) this.selectVillageAtIndex(this.selected_index - 1)
        },
        selectVillageAtIndex: function(index)
        {
            this.unselectSelectedVillage();
            var $el = this.getAutoCompleteWrapper().find('div').eq(index);
            $el.addClass('village-selected');
            this.selected_index = index;
            var offset_in_container = index * 41,
                selected_offset = $el.position().top,
                viewport_height = parseInt($el.parent().css('max-height'));
            if (selected_offset < 10)
            {
                $el.parent().scrollTop(offset_in_container)
            }
            else if (selected_offset > viewport_height - 40) $el.parent().scrollTop(offset_in_container)
        },
        unselectSelectedVillage: function()
        {
            this.getAutoCompleteWrapper().find('div.village-selected').removeClass('village-selected')
        },
        confirmVillageAtIndex: function(index)
        {
            var $el = this.getAutoCompleteWrapper().find('div').eq(index);
            if ($el.length)
                if ($el.hasClass('village-more'))
                {
                    this.loadMoreVillages();
                    this.selectVillageAtIndex(index - 1)
                }
                else this.confirmVillage($el)
        },
        updateURLForConfirmedVillage: function()
        {
            var village_data = this.confirmed_village_data ? this.confirmed_village_data :
                {
                    id: 0
                },
                url = document.location.href;
            if (url.substr(-1) === '#') url = url.substr(0, url.length - 1);
            var target_regex = /target=([0-9]+)/;
            if (url.match(target_regex))
            {
                url = url.replace(target_regex, 'target=' + village_data.id)
            }
            else url += '&target=' + village_data.id;
            if (Modernizr.history) history.replaceState(
            {}, '', url)
        },
        loadMoreVillages: function()
        {
            this.fetchVillages(
            {
                offset: this.num_villages
            })
        }
    }
}())