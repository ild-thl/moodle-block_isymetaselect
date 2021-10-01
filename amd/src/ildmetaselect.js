// Standard license block omitted.
/*
 * @package    ildmetaselect
 * @author     Markus Strehling <markus.strehling@oncampus.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module block_ildmetaselect/ildmetaselect
  */

 define(['jquery', 'core/ajax']
    , function($, ajax) {

    var run = false;

    function set_filter(response){
        var university_list = JSON.parse(response.university);
        var subjectarea_list = JSON.parse(response.subjectarea);
        var courselanguage_list = JSON.parse(response.courselanguage);
        var processingtime_list = JSON.parse(response.processingtime);
        var starttime_list = JSON.parse(response.starttime);

        var subjectarea = $('select[name=subjectarea]');
        var university = $('select[name=university]');
        var courselanguage = $('select[name=courselanguage]');
        var processingtime = $('select[name=processingtime]');
        var starttime = $('select[name=starttime]');

        //console.log(response.university);

        recreate_select(university , university_list, university.find('option:selected').text());
        recreate_select(subjectarea , subjectarea_list, subjectarea.find('option:selected').text());
        recreate_select(processingtime , processingtime_list, processingtime.find('option:selected').text());
        recreate_select(starttime , starttime_list, starttime.find('option:selected').text());
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
        var subjectarea = $('select[name=subjectarea]').val();
        var university = $('select[name=university]').val();
        var courselanguage = $('select[name=courselanguage]').val();
        var processingtime = $('select[name=processingtime]').val();
        var starttime = $('select[name=starttime]').val();

        if(subjectarea === null){
            subjectarea = 0;
        }
        if(university === null){
            university = 0;
        }
        if(courselanguage === null){
            courselanguage = 0;
        }
        if(processingtime === null){
            processingtime = "-";
        }
        if(starttime === null){
            starttime = "-";
        }

        var promises = ajax.call([
            { methodname: 'blocks_ildmetaselect_getfilter',
            args: {
                subjectarea: subjectarea,
                university: university,
                courselanguage: courselanguage,
                processingtime: processingtime,
                starttime: starttime
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

            $('select[name=subjectarea]').change(function(){
                call_get_filter();
            });
            $('select[name=university]').change(function(){
                call_get_filter();
            });
            $('select[name=courselanguage]').change(function(){
                call_get_filter();
            });
            $('select[name=processingtime]').change(function(){
                call_get_filter();
            });
            $('select[name=starttime]').change(function(){
                call_get_filter();
            });
        }
    };
});