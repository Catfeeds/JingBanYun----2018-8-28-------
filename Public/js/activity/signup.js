        //bindQueryDistrictEvent("/Home/Teach", 'MyProvince', 'MyCity', 'MyDistrict', '');
        //bindQueryDistrictEvent("/Home/Teach", 'SchProvince', 'SchCity', 'SchDistrict', 'school');
        var lock = 0;

        var activity_id = "132";
        var global_error = 0;
        var global_last_error = 0;
       /* if($('#code').length >0) {*/
            function regcode() {
                var regCode = /(?!^\d+$)(?!^[a-zA-Z]+$)[0-9a-zA-Z]{5,7}/;
                if ((typeof $('#code').val() == 'undefined') || $('#code').attr('disabled') == 'disabled')
                    return true;
                var code = $.trim($('#code').val());
                if (regCode.test(code) == true) {
                    if (code.length < 6) {
                        $('#codeError').show();
                        return false;
                    } else {
                        $('#codeError').hide();
                        checkCode();
                        return true;
                    }
                } else if (code == '') {
                    $('#codeError').hide();
                    return false;
                } else {
                    $('#codeError').show();
                    return false;
                }
                ;
            }
       /* }*/
	   if($('#code').length >0){
           $('#code').bind('input change', function(){
               regcode();
           })
	   }


        //教师姓名
        //姓名 只能输入汉字或者字母
		/*if($('#tname').length > 0){*/
            function regtname() {
                var regName = /^(([\u4E00-\u9FA5]{2,7})|([a-zA-Z]{3,10}))$/;
                var tname = $.trim($('#tname').val());
                if (regName.test(tname) == true) {
                    $('#tnameError').hide();
                    return true;
                } else if (tname == '') {
                    $('#tnameError').hide();
                    return false;
                } else {
                    $('#tnameError').show();
                    return false;
                }
            }
		/*}*/


        //学科
		/*if($('#course_id').length > 0){*/
            function regCourse() {
                var course_id = $.trim($('#course_id').val());
                if (course_id == '') {
                    $('#courseError').hide();
                    return false;
                } else {
                    $('#courseError').hide();
                    return true;
                }
            }
	/*	}*/


        //参评课题
		/*if($("#lesson").length > 0){*/
            $("#lesson").blur(function () {
                var lesson = $.trim($("#lesson").val());
                if (!lesson) {
                    global_error = 1;
                    $('#lesson').siblings('.right').css('visibility', 'inherit');
                } else {
                    $('#lesson').siblings('.right').css('visibility', 'hidden');
                }
            })
	/*	}*/


		/*if($('#issue').length > 0){*/
            function regLesson() {
                var issue = $.trim($('#issue').val());
                if (issue == '') {
                    $('#issueError').hide();
                    return false;
                } else {
                    $('#issueError').hide();
                    return true;
                }
            }
	/*	}*/


        //所属区县
		/*if($('#SchProvince').length >0 && $('#SchCity').length > 0 && $('#SchDistrict').length >0){*/
            function regCounty() {
                var SchProvince = $.trim($('#SchProvince').val());
                var SchCity = $.trim($('#SchCity').val());
                var SchDistrict = $.trim($('#SchDistrict').val());
                if (SchProvince == '' || SchCity == '' || SchDistrict == '') {
                    $('#countyError').hide();
                    return false;
                } else {
                    $('#countyError').hide();
                    return true;
                }
            }
	/*	}*/


        //学校
		/*if($('#school').length > 0){*/
            function regSchool() {
                var school = $.trim($('#school').val());
                if (school == '' || school == null) {
                    $('#schoolError').hide();
                    return false;
                } else {
                    $('#schoolError').hide();
                    return true;
                }
            }
		/*}*/


        //详细地址
		/*if($('#SchAddress').length > 0){*/
            function regAddressDetail() {
                var address = $.trim($('#SchAddress').val());
                if (address == '') {
                    $('#SchCountyError').hide();
                    return false;
                } else {
                    $('#SchCountyError').hide();
                    return true;
                }
            }
	/*	}*/


        //教师年龄
		/*if($('#age').length > 0){*/
            function regage() {
                var regAge = /^\+?[1-9][0-9]*$/;
                var age = $.trim($('#age').val());
                if (regAge.test(age) == true) {
                    if (age > 100) {
                        $('#ageError').show();
                        return false;
                    } else {
                        $('#ageError').hide();
                        return true;
                    }
                } else if (age == '') {
                    $('#ageError').hide();
                    return false;
                } else {
                    $('#ageError').show();
                    return false;
                }
            }
		/*}*/


        //学历
		/*if($('#education').length > 0){*/
            function regEducation() {
                var education = $.trim($('#education').val());
                if (education == '') {
                    $('#educationError').hide();
                    return false;
                } else {
                    $('#educationError').hide();
                    return true;
                }
            }
	/*	}*/


        //职称
		/*if($('#job').length > 0){*/
            function regPosition() {
                var position = $.trim($('#job').val());
                if (position == '') {
                    $('#jobError').hide();
                    return false;
                } else {
                    $('#jobError').hide();
                    return true;
                }
            }
		/*}*/


        //教师邮箱
		/*if($('#email').length > 0){*/
            function regemail() {
                var regEmail = /^(\w)+(\.\w+)*@(\w)+((\.\w{2,3}){1,3})$/;
                var email = $.trim($('#email').val());
                if (regEmail.test(email) == true) {
                    $('#emailError').hide();
                    return true
                } else if (email == '') {
                    $('#emailError').hide();
                    return false;
                } else {
                    $('#emailError').show();
                    return false;
                }
            }

      /*  }*/

        //学校邮编
		/*if($('#postcode').length > 0){*/
            function regpostcode() {
                var regPostcode = /^[1-9]\d{5}$/;
                var postcode = $.trim($('#postcode').val());
                if (regPostcode.test(postcode) == true) {
                    $('#postcodeError').hide();
                    return true
                } else if (postcode == '') {
                    $('#postcodeError').hide();
                    return false;
                } else {
                    $('#postcodeError').show();
                    return false;
                }
            }
		/*}*/


        //办公电话
		/*if($('#officePhone').length > 0){*/
            function regofficePhone() {
                var regOfficePhone = /^(\(\d{3,4}\)|\d{3,4}-)?\d{7,8}$/;
                var officePhone = $.trim($('#officePhone').val());
                if (regOfficePhone.test(officePhone) == true) {
                    $('#officePhoneError').hide();
                    return true
                } else if (officePhone == '') {
                    $('#officePhoneError').hide();
                    return true;
                } else {
                    $('#officePhoneError').show();
                    return false;
                }
            }
	/*	}*/


        //移动电话
		/*if($('#mobilePhone').length > 0){*/
            function regmobilePhone() {
                var regMobilePhone = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
                var mobilePhone = $.trim($('#mobilePhone').val());
                if (regMobilePhone.test(mobilePhone) == true) {
                    $('#mobilePhoneError').hide();
                    return true
                } else if (mobilePhone == '') {
                    $('#mobilePhoneError').hide();
                    return false;
                } else {
                    $('#mobilePhoneError').show();
                    return false;
                }
            }
		/*}*/


        //地方课程
		/*if($('#localCourse').length > 0){*/
            function regLocalCourse() {
                var local_course = $.trim($('#localCourse').val());
                if (local_course == '') {
                    $('#localCourseError').hide();
                    return false;
                } else {
                    $('#localCourseError').hide();
                    return true;
                }
            }
		/*}*/


        //校本课程
		/*if( $('#schoolCourse').length > 0){*/
            function regSchoolCourse() {
                var school_course = $.trim($('#schoolCourse').val());
                if (school_course == '') {
                    $('#schoolCourseError').hide();
                    return false;
                } else {
                    $('#schoolCourseError').hide();
                    return true;
                }
            }
		/*}*/

        /*
        *验证自定义属性
        */
        function user() {
            var tip = true;
            var new_arr = new Array();
            for(i=0;i<$('.user-defined').length;i++) {
                new_arr.push($('.user-defined').eq(i).val());
            }
            //console.log($(new_arr[0]).val());return false;
            for(r=0;r<new_arr.length;r++){
                if(new_arr[r] == ''){
                    $('.usererror').eq(r).show();
                    //$('#userError').hide();
                    tip = false;
                }
            }
            if(tip == false){
                return false;
            }else{
                return true;
            }

        }

        function check() {
        	if (regcode() == false && $('#code').length >0) {
        		$('#codeError').show();
        		return false
        	} else if (regtname() == false && $('#tname').length > 0) {
        		$('#tnameError').show();
        		return false
        	} else if (regCourse() == false && $('#course_id').length > 0) {
        		$('#courseError').show();
        		return false
        	} else if (regLesson() == false && $("#lesson").length > 0) {
        		$('#issueError').show();
        		return false
        	} else if ($.trim($('#issue').val()) == '' && $("#issue").length > 0) {
        		$('#issueError').show();
        		return false
        	} else if (regCounty() == false && $('#SchProvince').length >0 && $('#SchCity').length > 0 && $('#SchDistrict').length >0) {
        		$('#countyError').show();
        		return false
        	} else if (regSchool() == false && $('#school').length > 0) {
        		$('#schoolError').show();
        		return false
        	} else if (regAddressDetail() == false && $('#SchAddress').length > 0) {
        		$('#SchCountyError').show();
        		return false
        	} else if (regage() == false && $('#age').length > 0) {
        		$('#ageError').show();
        		return false
        	} else if (regEducation() == false && $('#education').length > 0) {
        		$('#educationError').show();
        		return false
        	} else if (regPosition() == false && $('#job').length > 0) {
        		$('#jobError').show();
        		return false
        	} else if (regemail() == false && $('#email').length > 0) {
        		$('#emailError').show();
        		return false
        	} else if (regpostcode() == false && $('#postcode').length > 0) {
        		$('#postcodeError').show();
        		return false
        	} else if (regofficePhone() == false && $('#officePhone').length > 0) {
        		$('#officePhoneError').show();
        		return false
        	} else if (regmobilePhone() == false && $('#mobilePhone').length > 0) {
        		$('#mobilePhoneError').show();
        		return false
        	} else if (regLocalCourse() == false && $('#localCourse').length > 0) {
        		$('#localCourseError').show();
        		return false
        	} else if (regSchoolCourse() == false && $('#schoolCourse').length > 0) {
                $('#schoolCourseError').show();
                return false
            }else if(user() == false){
                //$('#userError').show();
                return false
        	} else {
        		return true
        	}
        }
