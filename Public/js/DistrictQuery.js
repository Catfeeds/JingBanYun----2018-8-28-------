String.prototype.format = function (args) {
    var result = this;
    if (arguments.length > 0) {
        if (arguments.length == 1 && typeof (args) == "object") {
            for (var key in args) {
                if (args[key] != undefined) {
                    var reg = new RegExp("({" + key + "})", "g");
                    result = result.replace(reg, args[key]);
                }
            }
        } else {
            for (var i = 0; i < arguments.length; i++) {
                if (arguments[i] != undefined) {
                    var reg = new RegExp("({)" + i + "(})", "g");
                    result = result.replace(reg, arguments[i]);
                }
            }
        }
    }
    return result;
}

function bindQueryDistrictEvent(url, provinceId, cityId, districtId, SchoolId) {
    var option_select = '<option value="" >-请选择-</option>';  
    if (undefined != provinceId) {      
        $.get(  '/index.php?m=Home&c=Teach&a=getProvince', {}, function (res) {
            var provinces = res;
            var html = [];
            var tpl = '<option value="{1}" data-id="{1}" data-name="{0}">{0}</option>';
            html.push(option_select);

            var shengfen = $('#'+provinceId).val();
            if(shengfen == '' || shengfen == null) {
                shengfen = $('#'+provinceId).attr('default_id');
            }
            var tplone = '<option value="{1}" data-id="{1}" data-name="{0}" selected >{0}</option>';

 
            $.each(provinces, function (i, n) {
                if (n.id == shengfen) {

                    html.push(tplone.format(n.name, n.id));
                } else {
                    html.push(tpl.format(n.name, n.id));
                }

            });
            $('#' + provinceId).html(html.join(''));        
            $('#' + provinceId).change();
            
        });         
        $('#' + provinceId).unbind().change(function(){
            $('#'+cityId).find('option:not(:eq(0))').remove();
            $('#'+districtId).find('option:not(:eq(0))').remove();
            $('#'+SchoolId).find('option:not(:eq(0))').remove();
            
            if($("#" + provinceId + "  option:selected").attr('data-id')!=undefined){
                $.get('/index.php?m=Home&c=Teach&a=getCityByProvince', {id: $("#" + provinceId + "  option:selected").attr('data-id')}, function (res) {
                    var citys = res;
                    var html = [];
                    var tpl = '<option value="{1}" data-id="{1}" data-name="{0}">{0}</option>';
                    var isLoadDefaultId = false;
                    html.push(option_select);

                    var shiqu = $('#'+cityId).val();
                    if(shiqu == '' || shiqu == null) {
                        shiqu = $('#'+cityId).attr('default_id');
                        isLoadDefaultId = true;
                    }
                    var tplone = '<option value="{1}" data-id="{1}" data-name="{0}" selected >{0}</option>';


                    $.each(citys, function (i, n) {
                        if (n.id == shiqu) {

                            html.push(tplone.format(n.name, n.id));
                        } else {
                            html.push(tpl.format(n.name, n.id));
                        }

                    });

                    if (undefined != cityId) {
                        $('#' + cityId).html(html.join(''));
                        $('#' + cityId).change();
                    }
                    if(isLoadDefaultId)
                        $('#' + cityId).change();
                });
                $('#' + districtId).html(option_select);
                $('#' + SchoolId).html(option_select);
            }
        }); 
    }
    if (undefined != cityId) {
        $('#' + cityId).unbind().change(function () {
            $('#'+districtId).find('option:not(:eq(0))').remove();
            $('#'+SchoolId).find('option:not(:eq(0))').remove();
            if($("#" + cityId + "  option:selected").attr('data-id')!=undefined){
                $.get( '/index.php?m=Home&c=Teach&a=getDistrictByCity', {id: $("#" + cityId + "  option:selected").attr('data-id')}, function (res) {
                    var districts = res;
                    var html = [];

                    var tpl = '<option value="{1}" data-id="{1}" data-name="{0}">{0}</option>';
                    var isLoadDefaultId = false;
                    html.push(option_select);

                    var quxian = $('#'+districtId).val();
                    if(quxian == '' || quxian == null) {
                        quxian = $('#'+districtId).attr('default_id');
                        isLoadDefaultId = true;
                    }
                    var tplone = '<option value="{1}" data-id="{1}" data-name="{0}" selected >{0}</option>';

                    $.each(districts, function (i, n) {


                        if (n.id == quxian) {

                            html.push(tplone.format(n.name, n.id));
                        } else {
                            html.push(tpl.format(n.name, n.id));
                        }
                    });
                    if (undefined != districtId) {
                        $('#' + districtId).html(html.join(''));
                        $('#' + districtId).change();
                    }
                    if(isLoadDefaultId)
                        $('#' + districtId).change();
                });
            }
            $('#' + SchoolId).html(option_select);
        });
    }

    if (undefined != districtId) {
        $('#' + districtId).unbind().change(function () {
            $('#'+SchoolId).find('option:not(:eq(0))').remove();
            if($("#" + districtId + "  option:selected").attr('data-id')!=undefined){
                $.get('/index.php?m=Home&c=Teach&a=getSchoolByDistrict', {id: $("#" + districtId + "  option:selected").attr('data-id')}, function (res) {
                    var schools = res;
                    var html = [];
                    var option_select = '<option value="" >-请选择学校-</option>';
                    var tpl = '<option value="{1}" data-id="{1}" data-name="{0}" hasAdmin="{2}">{0}</option>';

                    var xuexiao = $('#'+SchoolId).val();
                    if(xuexiao == '')
                        xuexiao = $('#'+SchoolId).attr('default_id');

                    var tplone = '<option value="{1}" data-id="{1}" data-name="{0}" hasAdmin="{2}" selected >{0}</option>';

                    html.push(option_select);
                    $.each(schools, function (i, n) {


                        if (n.id == xuexiao) {

                            html.push(tplone.format(n.name, n.id,n.hasadmin));
                        } else {
                            html.push(tpl.format(n.name, n.id,n.hasadmin));
                        }
                    });
                    if (undefined != SchoolId)
                        $('#' + SchoolId).html(html.join(''));
                });
            }
        });
    }

}

