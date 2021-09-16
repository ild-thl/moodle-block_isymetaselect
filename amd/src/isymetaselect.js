// Standard license block omitted.
/*
 * @package    isymetaselect
 * @author     Markus Strehling <markus.strehling@oncampus.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module block_isymetaselect/isymetaselect
  */

 define(['jquery', 'core/ajax']
    , function($, ajax) {

    var run = false;

    function set_filter(response){
        var meta2_list = JSON.parse(response.meta2);
        var meta6_list = JSON.parse(response.meta6);
        var courselanguage_list = JSON.parse(response.courselanguage);
        var meta4_list = JSON.parse(response.meta4);
        var meta5_list = JSON.parse(response.meta5);

        var meta6 = $('select[name=meta6]');
        var meta2 = $('select[name=meta2]');
        var courselanguage = $('select[name=courselanguage]');
        var meta4 = $('select[name=meta4]');
        var meta5 = $('select[name=meta5]');

        //console.log(response.meta2);

        recreate_select(meta2 , meta2_list, meta2.find('option:selected').text());
        recreate_select(meta6 , meta6_list, meta6.find('option:selected').text());
        recreate_select(meta4 , meta4_list, meta4.find('option:selected').text());
        recreate_select(meta5 , meta5_list, meta5.find('option:selected').text());
        recreate_select(courselanguage , courselanguage_list, courselanguage.find('option:selected').text());

        return;
    }

    function recreate_select(select, newOptions, val){
        var sel;

        select.empty(); // remove old options

        $.each(newOptions, function(key,value) {
            var split = value.split("=>");
            var option = $("<option></option>").attr("value", split[0]);
            if(val == split[1]){
                sel = split[0];
            }
            option.text(split[1]);
            if(split[0] == '-' || split[0] == 0){
                option.attr('disabled', 'disabled');
            }
            select.append(option);
        });
        select.val(sel);
    }

    function call_get_filter(){
        var meta6 = $('select[name=meta6]').val();
        var meta2 = $('select[name=meta2]').val();
        var courselanguage = $('select[name=courselanguage]').val();
        var meta4 = $('select[name=meta4]').val();
        var meta5 = $('select[name=meta5]').val();

        if(meta6 === null){
            meta6 = 0;
        }
        if(meta2 === null){
            meta2 = 0;
        }
        if(courselanguage === null){
            courselanguage = 0;
        }
        if(meta4 === null){
            meta4 = "-";
        }
        if(meta5 === null){
            meta5 = "-";
        }

        var promises = ajax.call([
            { methodname: 'blocks_isymetaselect_getfilter',
            args: {
                meta6: meta6,
                meta2: meta2,
                courselanguage: courselanguage,
                meta4: meta4,
                meta5: meta5
                }
            }
        ]);

        promises[0].done(function(response) {
            set_filter(response);
        });
    }

    return {
        init: function() {
            if(run){
                return;
            }
            run = true;

            $('select[name=meta6]').change(function(){
                call_get_filter();
            });
            $('select[name=meta2]').change(function(){
                call_get_filter();
            });
            $('select[name=courselanguage]').change(function(){
                call_get_filter();
            });
            $('select[name=meta4]').change(function(){
                call_get_filter();
            });
            $('select[name=meta5]').change(function(){
                call_get_filter();
            });
        }
    };
});