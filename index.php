<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <title>Document</title>
</head>
<style>
    select:required:invalid {
        color: gray;
    }
    option[value=""][disabled] {
        display: none;
    }
    option {
        color: black;
    }
</style>

<body>

<div class="container" >

    <h1>Fill Tables with dummy data</h1>

    <div id="message" class="notification"></div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="glyphicon glyphicon-edit"></i>Fill Tables
<!--            <div class="btn-group pull-right">-->
<!--                <a href="http://filldb.info/dummy/step2" class="btn btn-primary active btn-xs">-->
<!--                    <i class="glyphicon glyphicon-list"></i> List-->
<!--                </a>-->
<!--                <a href="http://filldb.info/dummy/grid" class="btn btn-primary btn-xs">-->
<!--                    <i class="glyphicon glyphicon-th-large"></i> Grid-->
<!--                </a>-->
<!--            </div>-->
        </div>

        <form action="handler.php" id="form" method="post" accept-charset="utf-8">

            <div class="panel-body">

                <div class="col-md-3">
                    <label>Current Database</label>
                    <select required name="databases" id="databases" style="width: 200px;">
                        <optgroup label="Select database">
                            <option value="" disabled selected> -- Select Database --</option>
                        </optgroup>
                    </select>
                </div>

                <div class="col-md-3"  style="display: none;">
                    <label>Current Table:</label>
                    <select required name="tables" id="tables"  style="width:200px">
                        <optgroup label="Select tables">
                            <option value="" class="selecting" disabled selected> --Select Table --</option>
                        </optgroup>
                    </select>
                    0 rows
                </div>

                <div class="clearfix"></div>

                <br/>

                <table class="table table-striped table-condensed">

                    <thead>
                        <tr>
                            <th width="20%">
                                Field / Type
                                <small>[Key]</small>
                            </th>
                            <th width="20%">
                                Data Type
                                <div class="d"></div>
                            </th>
                            <th width="15%">
                                Parameters
                                <div class="d"></div>
                            </th>

                            <th width="50%">
                                Example
                                <div class="d"></div>
                            </th>
<!--                            <th>-->
<!--                                <a class="help"-->
<!--                                   title="Force system to return unique values. If checked, please make sure you have more unique values than number of rows to be generated">-->
<!--                                    Unique-->
<!--                                </a>-->
<!--                            </th>-->
<!--                            <th class="d">-->
<!--                                <a class="help" title="If checked the values generated are optional, can be empty">-->
<!--                                    Opt-nal-->
<!--                                </a>-->
<!--                            </th>-->
                        </tr>
                    </thead>

                    <tbody id="tableFields">

                    </tbody>

                </table>

                <div class="row">
                    <div class="col-md-3">
                        <label>Number of rows to be generated</label>
                        <input type="number" name="rows" value="20" id="count" class="form-control"/>
                        <div class="err err_rows"></div>
                    </div>

<!--                    <div class="col-md-6">-->
<!--                        <label>[optional] Generate country specific Names/Addresses/PhoneNumbers<span></label>-->
<!--                        <select name="provider" class="form-control" id="country">-->
<!--                            <option value="" selected="selected"></option>-->
<!--                            <option value="Bulgaria">Bulgaria</option>-->
<!--                            <option value="Bangladesh">Bangladesh</option>-->
<!--                            <option value="Czech Republic">Czech Republic</option>-->
<!--                            <option value="Denmark">Denmark</option>-->
<!--                            <option value="Austria">Austria</option>-->
<!--                            <option value="Germany">Germany</option>-->
<!--                            <option value="Grece">Grece</option>-->
<!--                            <option value="Australia">Australia</option>-->
<!--                            <option value="Canada">Canada</option>-->
<!--                            <option value="United Kingdom">United Kingdom</option>-->
<!--                            <option value="Philippines">Philippines</option>-->
<!--                            <option value="United States">United States</option>-->
<!--                            <option value="South Africa">South Africa</option>-->
<!--                            <option value="Argentina">Argentina</option>-->
<!--                            <option value="Spain">Spain</option>-->
<!--                            <option value="Peru">Peru</option>-->
<!--                            <option value="Finland">Finland</option>-->
<!--                            <option value="Belgium">Belgium</option>-->
<!--                            <option value="France">France</option>-->
<!--                            <option value="Hungary">Hungary</option>-->
<!--                            <option value="Armenia">Armenia</option>-->
<!--                            <option value="Iceland">Iceland</option>-->
<!--                            <option value="Italy">Italy</option>-->
<!--                            <option value="Japan">Japan</option>-->
<!--                            <option value="Latvia">Latvia</option>-->
<!--                            <option value="Montenegro">Montenegro</option>-->
<!--                            <option value="Netherlands">Netherlands</option>-->
<!--                            <option value="Poland">Poland</option>-->
<!--                            <option value="Brazil">Brazil</option>-->
<!--                            <option value="Portugal">Portugal</option>-->
<!--                            <option value="Moldova">Moldova</option>-->
<!--                            <option value="Romania">Romania</option>-->
<!--                            <option value="Russian">Russian</option>-->
<!--                            <option value="Slovakia">Slovakia</option>-->
<!--                            <option value="Serbia">Serbia</option>-->
<!--                            <option value="Turkey">Turkey</option>-->
<!--                            <option value="Ukraine">Ukraine</option>-->
<!--                            <option value="China">China</option>-->
<!--                        </select>-->
<!--                    </div>-->

                </div>

            </div>

            <div class="panel-footer">
                <input type="submit" class="btn btn-primary" id="generate" value="Generate" style="display: none">
            </div>

        </form>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script type="text/javascript" src="main.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</body>
</html>



