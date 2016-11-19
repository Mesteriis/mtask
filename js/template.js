'use strict';

var mtask = new function(){
    this.init = function()
    {
        mtask.widgets.init();
    }
}

mtask.widgets = new function()
{
    this.items = {};

    /*
    * Виджет сортировки таблиц
    * */
    this.items['datatable'] = function(selector)
    {
        if (!$.fn.DataTable) {
            return;
        }

        var defaults = mtask.widgets.items.datatable.defaults;

        $(selector).each(function() {
            var element = $(this);

            var arConfig = $.extend(
                {},
                defaults,
                element.data('config') || {}
            )

            element.DataTable(arConfig);
        });
    };

    this.items['datatable'].defaults = {
        "language": {
            "lengthMenu": "_MENU_ Записей на странице",
            "zeroRecords": "По вашему запросу записи не найдены.",
            "info": "Страница _PAGE_ из _PAGES_",
            "infoEmpty": "По вашему запросу записи не найдены.",
            "infoFiltered": "(Отфильтровано из _MAX_ записей.)"
        },
        "pagingType": "numbers"
    };


    /**
     * Инициализация виджетов
     */
    this.init = function($selector)
    {
        if( $selector == undefined || !$selector.length ){
            $selector = $('body');
        }

        $.each(this.items, function(widgetName){
            this.call(this, $selector.find('.widget.' + widgetName));
        });
    }
}

$(document).ready(function() {
    mtask.init();
});