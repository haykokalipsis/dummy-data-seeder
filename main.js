$(document).ready( function () {

class App
{
    constructor()
    {
        // App.DATABASES = $('#databases');
        // App.TABLES = $('#tables');
        // App.FIELDS = $('#tableFields');
        // App.FORM = $('#form');
        // App.BUTTON = $('#generate');
        //
        // App.DATABASES.change( (e) => App.getDatabasesTables(e.target.value) );
        // App.TABLES.change( (e) => App.getTablesFields(e.target.value) );
        // App.FORM.submit( (e) => App.insert(e) );
        //
        // $(document).on('change', '.select-faker-field', (event) => {
        //     App.selectFakerField(event.currentTarget, App.OPTIONS);
        // });
        //
        // $(document).on('keydown', '.params-input', (event) => {
        //     App.tabOrSmth(event.currentTarget, event);
        // });
        //
        // $(document).on('input', '.params-input', (e) => {
        //     App.getResultForChangedParameter(e.target);
        // });
        //
        // App.init();
    }

    static init()
    {
        App.setElements();
        App.setEvents();
        App.getOptions();
        App.getDatabases();
    }

    static setElements()
    {
        App.DATABASES = $('#databases');
        App.TABLES = $('#tables');
        App.FIELDS = $('#tableFields');
        App.FORM = $('#form');
        App.BUTTON = $('#generate');
    }

    static setEvents()
    {
        App.DATABASES.change( (e) => App.getDatabasesTables(e.target.value) );
        App.TABLES.change( (e) => App.getTablesFields(e.target.value) );
        App.FORM.submit( (e) => App.insert(e) );

        $(document).on('change', '.select-faker-field', (event) => {
            App.selectFakerField(event.currentTarget, App.OPTIONS);
        });

        $(document).on('keydown', '.params-input', (event) => {
            App.tabOrSmth(event.currentTarget, event);
        });

        $(document).on('input', '.params-input', (e) => {
            App.getResultForChangedParameter(e.target);
        });
    }

    static getOptions()
    {
        $.getJSON('Faker fields with comments test.JSON', function (json) {
            App.OPTIONS = json;
        });
    }

    static getDatabases()
    {
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {action : 'get_databases'},
            dataType: 'json',
            success: function (data) {
                // $('#tables>optgroup').empty();
                App.TABLES.find('> optgroup').empty();
                App.TABLES.find('> optgroup').append('<option value="" disabled selected> --Select Table --</option>');

                for (let i = 0; i < data.length; i++) {
                    let option = $("<option value='"+data[i]+"'>"+data[i]+"</option>");
                    App.DATABASES.find('> optgroup').append(option);
                }
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode + ', ' +str);
            }
        });
    }

    static getDatabasesTables(database)
    {
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {action : 'get_databases_tables', database : database},
            dataType: 'json',
            success: function (data) {
                // $('#tables>optgroup').empty();
                App.TABLES.find('> optgroup').empty();
                App.FIELDS.empty();
                App.TABLES.find('> optgroup').append('<option value="" disabled selected> --Select Table --</option>');

                for (let i = 0; i < data.length; i++) {
                    let option = $("<option value='"+data[i]+"'>"+data[i]+"</option>");
                    App.TABLES.find('> optgroup').append(option);
                }

                App.TABLES.parent().show(200);
                App.BUTTON.hide(200);
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode);
            }
        });
    }

    static getTablesFields(tableName)
    {
        let database = App.DATABASES.val().trim();

        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {action : 'get_tables_fields', table: tableName, database : database},
            dataType: 'json',
            success: (data) => {
                App.FIELDS.empty();
                App.drawForm(data, App.OPTIONS);
                App.BUTTON.show(200);
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode);
            }
        });
    }

    static insert(event) {

        event.preventDefault();
        App.BUTTON.attr('disabled', true);

        let count = $('#count').val();
        let fields = [];
        let fakerFields = $('.select-faker-field');
        let table = App.TABLES.val().trim();

        $.each(fakerFields, function () {
            let fieldParamsValues = [];
            let fieldParams = $(this).closest('tr').find('.params-input');
            let columnName = $(this).closest('tr').find('.column_name').text().trim();

            $.each(fieldParams, function() {
                if($(this).val().trim() !== '') {
                    fieldParamsValues.push($(this).val().trim());
                }
            });

            if($(this).val().trim() !== 'Null') {
                let field = {
                    column_name : columnName,
                    column_value : {
                        field_name : $(this).val().trim(),
                        params : fieldParamsValues
                    }
                };

                fields.push(field);
            }

        });

        // console.log(fields);
        let jsonedFields = JSON.stringify(fields);
        let database = App.DATABASES.val().trim();

        $.ajax({
            type : 'POST',
            url : 'handler.php',
            // contentType: "application/json; charset=utf-8",
            data : {action : 'insert', fields : jsonedFields, count : count, database : database, table : table},
            dataType: 'JSON',

            success: function(data) {
                console.info(data);
                alert('Done, ' + data["rows_affected"] + ' rows added.');
                App.BUTTON.attr('disabled', false);
            },
            error: function(request, status, error) {
                App.BUTTON.attr('disabled', false);
                alert('Возникла ошибка: ' + status + ', ' + error);
                console.log(request);
            }
        });
        return false; // отменяем отправку формы, т.е. перезагрузку страницы
    }

    static tabOrSmth(paramInput, event)
    {

        if ($(paramInput).val() && event.keyCode == 9) {
            $(paramInput).next('.params-input').attr('disabled', false);
        }

    }

    static selectFakerField(field, options)
    {
        let paramsDiv = $(field).closest('tr').find('.params');
        let exampleDiv = $(field).closest('tr').find('.example');
        let provider = $(field).find('option:selected').attr('provider').trim();
        let fakerName = $(field).find('option:selected').text().trim();
        let parameters = options[provider][fakerName]["params"];
        let example = options[provider][fakerName]["example"];

        paramsDiv.empty();

        $.each(parameters, function (key, value) {
            paramsDiv.append($('<input class="params-input" disabled="disabled" style="height: 25px; width:200px" type="text"  placeholder="'+value+'">'));
        });

        paramsDiv.find('.params-input').first().attr('disabled', false);
        exampleDiv.empty().append($('<small>').text(example));
    }

    static getResultForChangedParameter(paramInput)
    {

        let database = App.DATABASES.val().trim();
        let exampleDiv = $(paramInput).closest('tr').find('.example');
        let paramInputs = $(paramInput).parent().find('input');
        let parameters = Array();
        let faker_name = $(paramInput).closest('tr').find('.select-faker-field').val().trim();

        $.each(paramInputs, function () {
            if($(this).val().trim().length === 0) {
                $(this).nextAll().attr('disabled', true);
                $(this).nextAll().val('');
            }
            else {
                $(this).next().attr('disabled', false);
            }
        });

        $.each(paramInputs, function () {
            if($(this).val() !== '' )
                parameters.push($(this).val().trim());
        });

        let jsonedParameters = JSON.stringify(parameters);
        // alert(jsonString);

        $.ajax({
            type : 'POST',
            url : 'handler.php',
            data : {action : 'generate_example', faker_name : faker_name, parameters : jsonedParameters, database: database},
            dataType: 'html',

            success: function(data) {
                exampleDiv.empty().append($('<small>').text(data));
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode + ', ' +str);
            }
        });
    }

    static drawForm(data, options) {

        let fieldName, fieldType, fieldNull, fieldKey, fieldExtra, fieldDefault, fieldStatus, fieldForeignKey, select, optgroup, button;

        for (let i = 0; i < data.length; i++) {

            let row = $('<tr>');
            let td = $("<td>");

            fieldNull = (data[i]['null'] === 'NO') ? 'Not Null' : 'Nullable';
            fieldExtra = (data[i]['extra'] === '') ? '' : data[i]['extra'];
            fieldDefault = (data[i]['default'] === '') ? '' : data[i]['default'];
            fieldForeignKey = (data[i]['foreign_key'] === '') ? '' : data[i]['foreign_key'];

            switch (data[i]['key']) {
                case 'PRI' : fieldKey = 'Primary Key'; break;
                case 'UNI' : fieldKey = 'Unique'; break;
                case 'MUL' : fieldKey = 'Multiple'; break;
                default : fieldKey = '';
            }

            fieldName = $('<b class="column_name">').text(data[i]['name']);
            fieldType = $('<span style="color: cornflowerblue; ">').text(data[i]['field_type']);
            fieldNull = $('<span style="color: red; font-weight: bold;">').text(fieldNull);

            td.append(fieldName, ' | ', fieldType, $('</br>'), fieldNull);

            if(fieldKey !== '')
                td.append(' | ', $('<span style="color: blueviolet; font-size: small;">').text(fieldKey));

            if(fieldExtra !== '')
                td.append($('<br>'), $('<span style="color: green; font-style: oblique; font-size: small">').text(fieldExtra));

            if(fieldDefault !== '')
                td.append($('<br>'), $('<span style="color: orange; font-style: italic; font-size: small">').text(fieldDefault));

            if(fieldForeignKey === true)
                td.append($('<span style="color: green; font-style: italic; font-size: small">').text('references ' + data[i]['referenced-column-name'] + ' on ' + data[i]['referenced-table-name']));

            row.append(td);

            // button = $('<button type="button" class="btn btn-link btn-sm">');
            // button.append($("<i class='glyphicon glyphicon-search'>"));
            select = $('<select class="select-faker-field">').attr('field-name', data[i]['name']);

            $.each(options, function (provider, fields) {
                optgroup = $('<optgroup label=' + provider + '>');
                select.append(optgroup);
                $.each(fields, function (key, value) {
                    let fakername = value.hasOwnProperty('fakername') ? value['fakername'] : key;
                    optgroup.append($('<option>').attr({'provider' : provider,'value' : fakername }).text(key));
                });
            });

            // row.append($('<td>').append(select).append(button));
            row.append($('<td>').append(select));

            row.append($('<td>').append($('<div class="params">')));
            row.append($('<td>').append($('<div class="example">')));
            // row.append($('<td>').append($('<input type="checkbox" name="unique[]">')));
            // row.append($('<td>').append($('<input type="checkbox" name="optional[]">')));

            App.FIELDS.append(row);
        }

    }
}
// let app = new App();
App.init();

});