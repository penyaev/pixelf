/**
 * Created by penyaev on 27.05.14.
 */

var query;
var filtered_sites;

var list_item_template = _.template($('#list-item-template').html());
var go_multi_template = _.template($('#go-multi-template').html());

var $site_search = $('#site-search');
var $list = $('#sites-list');
var $go_multi_block = $('#js-go-multi');
var $go_multi_hint = $('#js-multi-hint');

var sites_for_multi = [];


function render() {
    $list.html('');
    query = $site_search.val();
    if (query.length < 2)
        return;
    filtered_sites = _.filter(sites, function (site) {
        return (site['domain'].indexOf(query) >= 0) || (site['site_uid'].indexOf(query) >= 0);
    });
    _.each(filtered_sites, function (site) {
        site.added_for_multi = _.contains(sites_for_multi, site['site_id'].toString());
        $list.append(list_item_template(site));
    })
}
render();

$site_search.keyup(function () {
    render();
}).focus();

$list.on('click', '.js-add-to-multi', function () {
    var site_id = $(this).attr('data-site-id');
    if (_.contains(sites_for_multi, site_id)) {
        sites_for_multi = _.without(sites_for_multi, site_id);
    } else {
        sites_for_multi = _.union(sites_for_multi, [site_id]);
    }
    if (sites_for_multi.length > 1) {
        $go_multi_hint.hide();
        $go_multi_block
            .html('')
            .append(go_multi_template({
                sites_for_multi: sites_for_multi
            }))
            .show();
    } else {
        $go_multi_hint.show();
        $go_multi_block.hide();
    }
    render();

    return false;
});