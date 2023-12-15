jQuery( document ).ready(function($) {
    jQuery('#banner_start_hours_field').datetimepicker({
        datepicker: false,
        format: 'H:i',
        step: 15,
        inline: true
    });
    jQuery('#banner_end_hours_field').datetimepicker({
        datepicker: false,
        format: 'H:i',
        step: 15,
        inline: true
    });
    jQuery('#banner_date_field').datetimepicker({
        timepicker:false,
        format:'d.m.Y',
        inline: true
    });
});
