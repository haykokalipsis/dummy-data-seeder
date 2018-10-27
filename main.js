$(document).ready(function () {

    const DATABASES = $('#databases');
    const TABLES = $('#tables');
    const TABLE_FIELDS = $('#tableFields');
    const FORM = $('#form');

    var options;

    getJsonData();
    getDatabases();

    // console.log(options);

    DATABASES.on('change', function () {
        getDatabasesTables(this.value);
    });

    TABLES.on('change', function () {
        getTablesFields(this.value);
    });

    FORM.submit(function (event) {
        insert(event);
    });

    $(document).on('change', '.select-faker-field', function () {
        console.log(this);
        selectFakerField($(this), options);
    });

    $(document).on('keydown', '.params-input', function (event) {
        tabOrSmth($(this), event);
    });

    $(document).on('input', '.params-input', function () {
        getResultForChangedParameter($(this));
    });



    function getJsonData() {
        $.getJSON('Faker fields with comments test.JSON', function (json) {
            options = json;
        });
    }

    function getDatabases() {
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {action : 'get_databases'},
            dataType: 'json',
            success: function (data) {
                // $('#tables>optgroup').empty();
                TABLES.find('> optgroup').empty();
                TABLES.find('> optgroup').append('<option value="" disabled selected> --Select Table --</option>');

                for (i = 0; i < data.length; i++) {
                    let option = $("<option value='"+data[i]+"'>"+data[i]+"</option>");
                    DATABASES.find('> optgroup').append(option);
                }
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode + ', ' +str);
            }
        });
    }

    function getDatabasesTables(database) {
        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {action : 'get_databases_tables', database : database},
            dataType: 'json',
            success: function (data) {
                // $('#tables>optgroup').empty();
                TABLES.find('> optgroup').empty();
                TABLE_FIELDS.empty();
                TABLES.find('> optgroup').append('<option value="" disabled selected> --Select Table --</option>');

                for (i = 0; i < data.length; i++) {
                    let option = $("<option value='"+data[i]+"'>"+data[i]+"</option>");
                    TABLES.find('> optgroup').append(option);
                }

                TABLES.parent().show(200);
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode);
            }
        });

    }

    function getTablesFields(tableName) {
        let database = DATABASES.val().trim();

        $.ajax({
            type: 'POST',
            url: 'handler.php',
            data: {action : 'get_tables_fields', table: tableName, database : database},
            dataType: 'json',
            success: function (data) {
                TABLE_FIELDS.empty();
                draw_form(data, options);
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode);
            }
        });
    }

    function draw_form(data, options) {

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

            button = $('<button type="button" class="btn btn-link btn-sm">');
            button.append($("<i class='glyphicon glyphicon-search'>"));
            select = $('<select class="select-faker-field">').attr('field-name', data[i]['name']);

            $.each(options, function (provider, fields) {
                optgroup = $('<optgroup label=' + provider + '>');
                select.append(optgroup);
                $.each(fields, function (key, value) {
                    optgroup.append($('<option>').attr({'provider' : provider,'value' : key }).text(key));
                });
            });

            row.append($('<td>').append(select).append(button));

            row.append($('<td>').append($('<div class="params">')));
            row.append($('<td>').append($('<div class="example">')));
            row.append($('<td>').append($('<input type="checkbox" name="unique[]">')));
            row.append($('<td>').append($('<input type="checkbox" name="optional[]">')));

            TABLE_FIELDS.append(row);
        }

    }

    function selectFakerField(field, options) {
        let paramsDiv = field.closest('tr').find('.params');
        let exampleDiv = field.closest('tr').find('.example');
        let provider = field.find('option:selected').attr('provider').trim();
        let fakerName = field.val().trim();
        let parameters = options[provider][fakerName]["params"];
        let example = options[provider][fakerName]["example"];

        paramsDiv.empty();

        $.each(parameters, function (key, value) {
            paramsDiv.append($('<input class="params-input" disabled="disabled" style="height: 25px; width:200px" type="text"  placeholder="'+value+'">'));
        });

        paramsDiv.find('.params-input').first().attr('disabled', false);
        exampleDiv.empty().append($('<small>').text(example));
    }


    function getResultForChangedParameter(paramInput) {

        let database = DATABASES.val().trim();
        let exampleDiv = paramInput.closest('tr').find('.example');
        let paramInputs = paramInput.parent().find('input');
        let parameters = Array();
        let faker_name = paramInput.closest('tr').find('.select-faker-field').val().trim();

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

    function tabOrSmth(paramInput, event) {

        if (paramInput.val() && event.keyCode == 9) {
            paramInput.next('.params-input').attr('disabled', false);
        }

    }

    function insert(event) {

        event.preventDefault();

        let count = $('#count').val();
        let fields = [];
        let fakerFields = $('.select-faker-field');
        let table = TABLES.val().trim();

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
        let database = DATABASES.val().trim();

        $.ajax({
            type : 'POST',
            url : 'handler.php',
            data : {action : 'insert', fields : jsonedFields, count : count, database : database, table : table},
            dataType: 'html',

            success: function(data) {
                console.log(data);
            },
            error: function (xhr, str) {
                alert('Возникла ошибка: ' + xhr.responseCode + ', ' +str);
            }
        });
        return false; // отменяем отправку формы, т.е. перезагрузку страницы
    }

});