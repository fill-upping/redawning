jQuery('document').ready(function(){
    var page_position = 1;
    var monthperPage = 9;
    var loading_content = jQuery('#loading-cal-avail-wrapper');
    var calendarwrapper = jQuery('.calendar-availability-wrapper');
    
    // regenerate calendar
    calendarwrapper.html('');
    setLoading();
    generateCalendarAvailability();
    finishLoading();
    clickableDates();
    
    function generateCalendarAvailability() {
        var currentYearMonth = new Date();
        var currentTimestamp = new Date(currentYearMonth.getFullYear(), currentYearMonth.getMonth(), 1);
        var blockeddates = Drupal.settings.blockeddates;
        var strbd = '';
        var strbdci = '';
        var strbdco = '';
        jQuery.each(blockeddates, function (key, val) {
            if (key != 0) {
                strbd += ' || ';
                strbdci += ' || ';
                strbdco += ' || ';
            }
            
            var ci = convtotime(val.sdate);
            var co = convtotime(val.edate);
            strbdci += 'date.valueOf() == ' + ci.valueOf();
            ci.setDate(ci.getDate() + 1);
            strbd += '(date.valueOf() >= ' + ci.valueOf() + ' && date.valueOf() <= ' + co.valueOf() + ')';
            co.setDate(co.getDate() + 1);
            strbdco += 'date.valueOf() == ' + co.valueOf();
        });
        
        var first_month = count_first_month = page_position <= 1 ? page_position : (page_position * monthperPage) - (monthperPage -1);
        var limit_month = page_position * monthperPage;
        
        for(i = first_month ; i <= limit_month; i++) {
            calendarwrapper.append('<div class="avail-cal-month" id="avail-cal-' + i + '"></div>');
            
            if(i <= monthperPage) {
                if(i <= 1) {
                    currentTimestamp.setMonth(currentTimestamp.getMonth());
                } else {
                    currentTimestamp.setMonth(currentTimestamp.getMonth() + 1);
                }
                
            }else if(i > monthperPage) {
                if((i%9) == 1) {
                    currentTimestamp.setMonth(currentTimestamp.getMonth() + (i-1));
                } else {
                    currentTimestamp.setMonth(currentTimestamp.getMonth() + 1);
                }
                
            }
            
            jQuery('#avail-cal-' + i).datepicker({
                beforeShowDay: function(date){
                        return {classes: eval(strbd) ? 'red disabled' : false + eval(strbdci) ? 'startblock' : false + eval(strbdco) ? 'endblock' : false}
                },
                todayHighlight: true,
            });
            jQuery('#avail-cal-' + i).datepicker('update', currentTimestamp);            
        }
        styleCalendar();
        
        // prevent month list default action on eternicode bootstrap datepicker
        jQuery('.avail-cal-month table thead tr, .avail-cal-month table tbody tr td').on('click', function(e){
            e.preventDefault();
            return false;
        });
    }
    
    function convtotime(date) {
        var odate = date.split('-');
        var newdate = new Date(odate[1] + '/' + odate[2] + '/' + odate[0]);
        return newdate;
    }
    
    function styleCalendar() {
        jQuery('.calendar-availability-wrapper table.table-condensed thead th.prev, .calendar-availability-wrapper table.table-condensed thead th.next').remove();
        jQuery('th.datepicker-switch').attr({'colspan':'7'});
        jQuery('.calendar-availability-wrapper table.table-condensed tbody tr td').removeClass('active');
    }
    
    jQuery('.cal-avail-nav-trigger').on('click', function(e){
        var btn = jQuery(this).attr('id');
        if(btn.indexOf('next') >= 0) {
            page_position = page_position + 1;
        } else if(btn.indexOf('prev') >= 0) {
            if(page_position > 1) {
                page_position = page_position - 1;
            }
        }
        
        // regenerate calendar
        calendarwrapper.html('');
        setLoading();
        generateCalendarAvailability();
        finishLoading();
        clickableDates();
        
        if(page_position > 1) {
            if(!jQuery('span[id*="prev-btn-cal-avail"]').hasClass('calendar-avail-nav')) {
                jQuery('span[id*="prev-btn-cal-avail"]').removeClass('calendar-avail-nav-disable').addClass('calendar-avail-nav');
            }
        } else {
            if(!jQuery('span[id*="prev-btn-cal-avail"]').hasClass('calendar-avail-nav-disable')) {
                jQuery('span[id*="prev-btn-cal-avail"]').removeClass('calendar-avail-nav').addClass('calendar-avail-nav-disable');
            }
        }
    });
    
    function setLoading() {
        calendarwrapper.hide();
        loading_content.show();        
        if(!jQuery('span[id*="prev-btn-cal-avail"]').hasClass('calendar-avail-nav-disable')) {
            jQuery('span[id*="prev-btn-cal-avail"]').removeClass('calendar-avail-nav').addClass('calendar-avail-nav-disable');
        }
        if(!jQuery('span[id*="next-btn-cal-avail"]').hasClass('calendar-avail-nav-disable')) {
            jQuery('span[id*="next-btn-cal-avail"]').removeClass('calendar-avail-nav').addClass('calendar-avail-nav-disable');
        }
    }
    
    function finishLoading() {        
        loading_content.hide();
        calendarwrapper.show();
        if(jQuery('span[id*="next-btn-cal-avail"]').hasClass('calendar-avail-nav-disable')) {
            jQuery('span[id*="next-btn-cal-avail"]').removeClass('calendar-avail-nav-disable').addClass('calendar-avail-nav');
        }
    }
    /* Start REDAWNING-3330: clickable dates on availability section of property page */
    var counter = 0;
    var start = "";
    var end = "";
    var week = "";
    var startMonth = "";
    var endMonth = "";
    var today = new Date();
    var todaySimpl = ( today.getMonth() + 1 ) + '/' + today.getDate() + "/" + today.getFullYear();
    var date_diff_indays = function(date1, date2) {
        dt1 = new Date(date1);
        dt2 = new Date(date2);
        return Math.floor((Date.UTC(dt2.getFullYear(), dt2.getMonth(), dt2.getDate()) - Date.UTC(dt1.getFullYear(), dt1.getMonth(), dt1.getDate()) ) /(1000 * 60 * 60 * 24));
    }
    function parseDate(date) {
        var selectedDate = date;
        var month = selectedDate.split("/")[0];
        var day = selectedDate.split("/")[1];
        var year = selectedDate.split("/")[2];
        var monthName = "";
        switch ( month ) {
            case "01":
            monthName = "January"
            break;
            case "02":
            monthName = "February"
            break;
            case "03":
            monthName = "March"
            break;
            case "04":
            monthName = "April"
            break;
            case "05":
            monthName = "May"
            break;
            case "06":
            monthName = "June"
            break;
            case "07":
            monthName = "July"
            break;
            case "08":
            monthName = "August"
            break;
            case "09":
            monthName = "September"
            break;
            case "10":
            monthName = "October"
            break;
            case "11":
            monthName = "November"
            break;
            case "12":
            monthName = "December"
            break;
        }
        var dateHeader = monthName + " " + year;
        var parseDay = parseInt(day,10);
        return [dateHeader, parseDay];
    }
    //disable dates prior to today's date
    jQuery(".calendar-availability-wrapper td.today").prevAll().addClass("disabled past").parent().prevAll().find("td.day").addClass("disabled past");
    function clickableDates() {
        jQuery(".calendar-availability-wrapper td.day").on("click", function() {
            if ( !jQuery(this).hasClass("new") && !jQuery(this).hasClass("old") && !jQuery(this).hasClass("disabled") ) {
                var monthYear = jQuery(this).closest(".avail-cal-month").find("thead th.datepicker-switch").html();
                var year = monthYear.substr(monthYear.lastIndexOf(" ") + 1);
                var monthName = monthYear.substr(0, monthYear.indexOf(" "));
                var day = jQuery(this).html();
                var monthNumeric = "";
                switch ( monthName ) {
                    case "January":
                        monthNumeric = "01"
                        break;
                    case "February":
                        monthNumeric = "02"
                        break;
                    case "March":
                        monthNumeric = "03"
                        break;
                    case "April":
                        monthNumeric = "04"
                        break;
                    case "May":
                        monthNumeric = "05"
                        break;
                    case "June":
                        monthNumeric = "06"
                        break;
                    case "July":
                        monthNumeric = "07"
                        break;
                    case "August":
                        monthNumeric = "08"
                        break;
                    case "September":
                        monthNumeric = "09"
                        break;
                    case "October":
                        monthNumeric = "10"
                        break;
                    case "November":
                        monthNumeric = "11"
                        break;
                    case "December":
                        monthNumeric = "12"
                        break;
                }
                var fullDate = monthNumeric + "/" + day + "/" + year;
                //reset calendars if two dates have already been selected and a third date is clicked
                if ( counter == 2 ) {
                    counter = 0;
                    jQuery(".prop-right-sidebar .date-picker-containter #chckn").datepicker("setDate", "");
                    jQuery(".prop-right-sidebar .date-picker-containter #chckt").datepicker("setDate", "");
                    jQuery("td.day.selected").removeClass("disabled");
                    jQuery("td.day").removeClass("range selected active startDate endDate");
                    jQuery(".calendar-availability-wrapper tr").removeClass("startWeek endWeek");
                    jQuery(".avail-cal-month").removeClass("startMonth endMonth");
                }

                //first click on calendar selects day as start and add to right-hand date picker
                if ( counter == 0 ) {
                    //check if date is prior to today's date 
                    start = fullDate;
                    startMonth = monthNumeric;
                    if ( date_diff_indays(todaySimpl, start) > 0 ) {
                        jQuery(this).addClass("selected disabled startDate").parent().addClass("startWeek").closest(".avail-cal-month").addClass("startMonth");
                        jQuery(".prop-right-sidebar #chckn").datepicker("setDate", fullDate);
                    }
                }
                //detect second click on day
                else if ( counter == 1 ) {
                    end = fullDate;
                    endMonth = monthNumeric;
                    var totalDays = date_diff_indays(start, end);
                    
                    //check if end date is after start. if so, add classes to elements and add to datepicker
                    if ( totalDays > 0 ) {
                        //if end date is in a different week than start date
                        if ( !jQuery(this).parent().hasClass("startWeek") ) {
                            jQuery(this).addClass("selected disabled active endDate").parent().addClass("endWeek").closest(".avail-cal-month").addClass("endMonth");
                            jQuery("td.selected.disabled:not(.active)").nextUntil('td.day.active').addClass("range");
                            jQuery('td.selected.disabled.active').prevUntil('td.selected.disabled:not(.active)').addClass("range");
                            jQuery(".prop-right-sidebar #chckt").datepicker("setDate", end);
                            //fill in weeks between start and end date if spans more than two weeks
                            jQuery("td.selected.disabled:not(.active)").parent().nextUntil("tr.endWeek").find("td").addClass("range");
                            jQuery("td.selected.disabled.active").parent().prevUntil("tr.startWeek").find("td").addClass("range");
                            if ( !jQuery(".startMonth").hasClass("endMonth") ) {
                                jQuery(".startMonth").nextUntil(".endMonth").find("td.day").addClass("range");
                            }
                        }
                        //if end date is in the same week as the start date
                        else {
                            jQuery(this).addClass("selected disabled active").parent().addClass("endWeek").closest(".avail-cal-month").addClass("endMonth");
                            jQuery("td.selected.disabled:not(.active)").nextUntil('td.day.active').addClass("range");
                            jQuery(".prop-right-sidebar #chckt").datepicker("setDate", end);
                        }
                    }
                    //if end date is before start, alert user and reset calendar datepicker
                    else {
                        start = end;
                        jQuery("tr.startWeek").removeClass("startWeek");
                        jQuery("td.startDate").removeClass("startDate");
                        jQuery("td.day.selected.disabled").removeClass("selected disabled");
                        jQuery(this).addClass("startDate selected disabled").parent().addClass("startWeek");
                        jQuery(".prop-right-sidebar #chckn").datepicker("setDate", start);
                        jQuery(".prop-right-sidebar #chckt").datepicker("setDate", start);
                        if ( !jQuery(this).closest(".avail-cal-month").hasClass("startMonth") ) {
                            jQuery(".avail-cal-month").removeClass("startMonth");
                            jQuery(this).closest(".avail-cal-month").addClass("startMonth");
                        }
                        startMonth = monthNumeric;
                        counter = 0;
                    }
                }
                //clear class status from grayed out dates from other months of current month
                jQuery("td.day.new.range").removeClass("range");
                jQuery('td.day.old.range').removeClass("range");
                counter++;
            }
        })
    } 
    /* Adding code to update main calendars from sidebar datepicker */
    //clear classes when check-in input field is clicked on
    jQuery("body").on("click", "#chckn", function() {
        jQuery(".calendar-availability-wrapper").find(".avail-cal-month").removeClass("startMonth endMonth").find("tr").removeClass("startWeek endWeek").find("td.day:not(.old, .past, .new, .red)").removeClass("selected disabled startDate range active endDate");
        //reset counter
        counter = 0;
    });
    //if check-out date input is clicked, assume user is trying to change end date
    jQuery("body").on("click", "#chckt", function() {
        jQuery(".calendar-availability-wrapper").find(".avail-cal-month").removeClass("endMonth").find("tr").removeClass("endWeek").find("td.day:not(.old, .past, .new, .red)").removeClass("endDate selected active disabled range");
        counter = 1;
    });
    //if user clicks on datepicker in sidebar, change availability calendar
    jQuery("body").on("click", ".day", function() {
        if ( !jQuery(this).hasClass("old new past") ) {
            var selectedDate = "";
            //reset counter if check-in/out have been selected already
            if ( counter == 2 || jQuery(this).hasClass("old") || jQuery(this).hasClass("past") || jQuery(this).hasClass("new") ) {
                counter = 0;
            }
            //add start-date to main calendar
            if ( counter == 0 ) {
                selectedDate = jQuery("#chckn").val();
                start = selectedDate;
                jQuery(".calendar-availability-wrapper div.datepicker-days table th.datepicker-switch").each(function() {
                    if ( jQuery(this).html() == parseDate(selectedDate)[0] ) {
                        jQuery(this).closest(".avail-cal-month").addClass("startMonth");
                    }
        
                    jQuery(".startMonth td.day").each(function() {
                        if ( jQuery(this).html() == parseDate(selectedDate)[1] ) {
                            jQuery(this).not(".old, .new, .past").addClass("startDate selected disabled").parent().addClass("startWeek");
                        }
                    })
                })
                if ( jQuery("td.endDate").length > 0 ) {
                    jQuery("td.endDate").addClass("selected disabled")
                    if ( jQuery(".startWeek").hasClass("endWeek") ) {
                        jQuery(".startDate").nextUntil(".endDate").addClass("range");
                    }
                    else if ( !jQuery("tr.startWeek").hasClass("endWeek") ) {
                        jQuery("td.startDate").nextUntil("td.endDate").addClass("range");
                        jQuery("td.endDate").prevUntil("td.startDate").addClass("range");
                        jQuery("td.endDate").parent().prevUntil("tr.startWeek").find("td:not(.old, .past, .new)").addClass("range");
                        if ( !jQuery(".startMonth").hasClass("endMonth") ) {
                            jQuery(".startWeek").nextUntil(".endMonth").find("td.day:not(.new)").addClass("range");
                            jQuery(".endMonth").prevUntil(".startMonth").find("td.day:not(.old, .past, .new)").addClass("range");
                        }
                    }
                } 
            }
            //add end date to main calendar
            else if ( counter == 1 ) {
                selectedDate = jQuery("#chckt").val();
                jQuery(".calendar-availability-wrapper div.datepicker-days table th.datepicker-switch").each(function() {
                    if ( jQuery(this).html() == parseDate(selectedDate)[0] ) {
                        jQuery(this).closest(".avail-cal-month").addClass("endMonth");
                    }
                    jQuery(".endMonth td.day").each(function() {
                        if ( jQuery(this).html() == parseDate(selectedDate)[1] ) {
                            jQuery(this).not(".old, .new, .past").addClass("endDate selected active disabled").parent().addClass("endWeek");
                        }
                    })
                })
                //test if start and end are in the same week
                if ( jQuery("tr.startWeek").hasClass("endWeek") ) {
                    jQuery("td.endDate").prevUntil("td.startDate").addClass("range");
                }
                //if start and end are in different weeks, fill in dates in between
                else if ( !jQuery(".startWeek").hasClass("endWeek") ) {
                    jQuery("td.startDate").prevUntil("td.past").addClass("blah");
                    jQuery("td.startDate").nextUntil("td.endDate").addClass("range");
                    jQuery("td.endDate").prevUntil("td.startDate").addClass("range");
                    jQuery("td.endDate").parent().prevUntil("tr.startWeek").find("td:not(.old, .past, .new)").addClass("range");
                    if ( !jQuery(".startMonth").hasClass("endMonth") ) {
                        jQuery(".startWeek").nextUntil(".endWeek").find("td.day:not(.old, .past, .new)").addClass("range").nextUntil(".endMonth").find("td.day:not(.old, .past, .new)").addClass("range");
                        jQuery(".endMonth").prevUntil(".startMonth").find("td.day:not(.old, .past, .new)").addClass("range");
                    }
                }
            }
            counter++;
        }
    })
})