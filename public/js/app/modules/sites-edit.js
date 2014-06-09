/**
 * Created by penyaev on 09.06.14.
 */

Pixelf.Modules.SitesEdit = (function () {
    var vk_lead_template = _.template($('#lead-template').html());
    var leads_to_remove = 0;

    var add_lead = function (model, animate) {
        var $newLead = $(vk_lead_template(_.extend({}, {
            group_id: $('.vk-lead').length,
            caption: '',
            vk_lead_id: null,
            secret: null
        }, model))).hide();

        $('.leads').append($newLead);

        if (animate)
            $newLead.slideDown().find('input').first().focus();
        else
            $newLead.show();
    };

    var update_save_button = function () {
        if (leads_to_remove) {
            $('#js-save-button').removeClass('btn-success').addClass('btn-danger').find('#js-save-button-text').text('Сохранить и удалить ' + leads_to_remove + ' ' + Pixelf.Tools.Pluralize(leads_to_remove, ['оффер', 'оффера', 'офферов']));
        } else {
            $('#js-save-button').addClass('btn-success').removeClass('btn-danger').find('#js-save-button-text').text('Сохранить');
        }
    };

    var check_duplicate_ids = function () {
        var ids = {};
        var duplicate_ids = [];
        var value;
        $('.vk-lead-id-input').each(function () {
            value = $(this).val();
            if (ids[value]) {
                ids[value]++;
                duplicate_ids.push(value);
            } else
                ids[value] = 1;
        });
        duplicate_ids = _.uniq(duplicate_ids);
        if (duplicate_ids.length) {
            $('.js-alert').text('Вы указали одинаковы'+((duplicate_ids.length > 1) ? 'е' : 'й')+' vk_lead_id ' + duplicate_ids.join(', ') + ' для нескольких разных офферов').slideDown();
        } else {
            $('.js-alert').slideUp();
        }
    };

    var init = function (leads) {
        $('.js-add-lead').click(function () {
            add_lead({}, true);
            return false;
        });

        $('.leads').on('click', '.js-remove-lead', function () {
            $(this).parents('.vk-lead').slideUp(function () {
                $(this).remove();
                leads_to_remove++;
                update_save_button();
            });
            return false;
        }).on('click keyup', '.vk-lead-id-input', function () {
            check_duplicate_ids();
        });

        _.each(leads, function (lead) {
            add_lead(lead, false);
        });
    };

    return {
        init: init
    };
})();
