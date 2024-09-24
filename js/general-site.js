(function ($) {

    var typing = {
        fakeSearchTermLength: 0,
        fakeSearchTerm: '',
        fakeSearches: [ 'Kauai', 'Lake Tahoe', 'Italy', 'Vail, Colorado', 'Seattle', 'Florida Keys', 'Oregon Coast', 'San Francisco', 'Maui', 'New York City', 'Orlando', 'Portland', 'Anna Maria Island', 'Ocean City, MD', 'Smoky Mountains', 'Fernandina Beach', 'Keystone, CO', 'Mammoth' ],

        combineTypingEffects: function () {
            typing.doFakeTyping();
            setTimeout(typing.doFakeErasing, 1500);
        },

        doFakeTyping: function () {
            typing.fakeSearchTerm = typing.fakeSearches[Math.floor(Math.random() * typing.fakeSearches.length)];
            typing.type();
        },

        type: function () {
            typing.fakeSearchElement.html(typing.fakeSearchTerm.substr(0, typing.fakeSearchTermLength++));
            if(typing.fakeSearchTermLength < typing.fakeSearchTerm.length+1) {
                $('.advanced_search_custom_longhomepage_block_form_class .fake-label').addClass( 'hide' );
                $('#cursor').removeClass( 'hide' );
                setTimeout(typing.type, 50);
            } else {
                typing.fakeSearchTermLength = 0;
                typing.fakeSearchTerm = '';
            }
        },

        doFakeErasing: function () {
            typing.fakeSearchTerm = typing.fakeSearchElement.html();
            typing.fakeSearchTermLength = typing.fakeSearchTerm.length;
            if (typing.fakeSearchTermLength>0) {
                typing.erase();
            }
        },

        erase: function () {
            typing.fakeSearchElement.html(typing.fakeSearchTerm.substr(0, typing.fakeSearchTermLength--));
            if(typing.fakeSearchTermLength >= 0) {
                setTimeout(typing.erase, 50);
            } else {
                typing.fakeSearchTermLength = 0;
                typing.fakeSearchTerm = '';
                $('.advanced_search_custom_longhomepage_block_form_class .fake-label').removeClass( 'hide' );
                $('#cursor').addClass( 'hide' );
            }
        },

        cursorAnimation: function () {
            $('#cursor').animate({
                opacity: 0
            }, 'fast', 'swing').animate({
                opacity: 1
            }, 'fast', 'swing');
        }
    };

    var stepper = {
        stepperNumber: '',
        minusButton: '',

        // check to see if the input is at '0'...
        checkStepperNumber: function ( thisStepper ) {
            stepperInput = $( thisStepper ).find( 'input' );
            stepperNumber = Number( stepperInput.val() );
            decrementButton = $( thisStepper ).find( 'button.minus' );

            if ( stepperNumber === '0' || stepperNumber <= 0 ) {
                // if so, disable the minus button.
                decrementButton.prop( 'disabled', true );
                stepperInput.val( 0 );
            } else {
                // if number is positive, enable the minus button
                decrementButton.prop( 'disabled', false );
            }

        },

        init: function () {

            var allSteppers = $( '.input-stepper' );

            allSteppers.each( function ( index, element ) {
                var thisStepperInput = $( element ).find( 'input' );
                var thisMinusButton = $( element ).find( 'button.minus' );

                // handles the case where the bedrooms input should say 'Studio' instead of 0
                if ( $( element ).hasClass( 'bedrooms' ) && ( thisStepperInput.val() === '0' || thisStepperInput.val() <= 0 || thisStepperInput.val() === 'Studio' ) ) {
                    thisMinusButton.prop( 'disabled', true );
                    thisStepperInput.val( 'Studio' );
                } else if ( !thisStepperInput.hasClass( 'bedrooms' ) && ( thisStepperInput.val() === '0' || thisStepperInput.val() <= 0 ) ) {
                    thisMinusButton.prop( 'disabled', true );
                    thisStepperInput.val( 0 );
                } else {
                    // if number is positive, enable the minus button
                    thisMinusButton.prop( 'disabled', false );
                }
            });
        }
    };

    function getMobileOperatingSystem() { 
        var userAgent = navigator.userAgent || navigator.vendor || window.opera; 
       
            // Windows Phone must come first because its UA also contains "Android" 
          if (/windows phone/i.test(userAgent)) { 
              return "Windows Phone"; 
          } 
       
          if (/android/i.test(userAgent)) { 
              return "Android"; 
          } 
       
          // iOS detection from: http://stackoverflow.com/a/9039885/177710 
          if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) { 
              return "iOS"; 
          } 
       
          return "unknown"; 
    } 

    function testDate(date, paymentdue , policy) {
        var cancelText;
        var policyText;
        var cancelPolicyCheck;
        var sixtyDayCheck;
        var checkinDate = date;
        var dateFrom = moment().format("MM/DD/YYYY");
        var dateTo = moment(dateFrom).add(policy,"days").format('MM/DD/YYYY');
        var cancelUntil = moment(checkinDate).subtract(policy,"days").format('MM/DD/YYYY');
        var balanceDue = moment(checkinDate).subtract(paymentdue, "days").format('MM/DD/YYYY');
        if ( moment(checkinDate).isAfter(dateTo) ) {
            cancelPolicyCheck = true;
        }
        else {
            cancelPolicyCheck = false;
        }
        if ( moment(balanceDue).isAfter(dateFrom) ) {
            sixtyDayCheck = true;
        }
        else {
            sixtyDayCheck =false;
        }
        if ( $(".date-cancel-time").length <= 0 ) {
            var cancelReplacement = "\\[date-cancel-until\\]";
            var paymentReplacement = "\\[payment-due\\]";
            var beginBold = "\\[begin-bold\\]";
            var endBold = "\\[end-bold\\]";
            if ( $(".cancellation").length > 0 && $(".policy").length > 0 ) {
                cancelText = $(".cancellation").html().replace("[date-cancel-time]","<span class='date-cancel-time'>" + dateTo + "</span>")
                    .replace(new RegExp(cancelReplacement, 'g'),"<span class='date-cancel-until'>" + cancelUntil + "</span>")
                    .replace(new RegExp(beginBold, 'g'),"<b>")
                    .replace(new RegExp(endBold, 'g'),"</b>")
                    .replace("[policy]","<span class='policy-days'>" + policy + "</span>");
                policyText = $(".policy").html()
                    .replace("[date-balance-due]","<span class='date-balance-due'>" + balanceDue + "</span>")
                    .replace(new RegExp(beginBold, 'g'),"<b>")
                    .replace(new RegExp(endBold, 'g'),"</b>")
                    .replace(new RegExp(paymentReplacement, 'g'), "<span class='payment-due'>" + paymentdue + "</span>");
                $(".cancellation").html(cancelText);
                $(".policy").html(policyText);
            }
        }
        else {
            $(".date-cancel-time").html(dateTo);
            $(".date-cancel-until").html(cancelUntil);
            $(".policy-days").html(policy);
            $(".date-balance-due").html(balanceDue);
            $(".payment-due").html(paymentdue)
        }
        if ( cancelPolicyCheck == false ) {
            $("div.cancellation-not-qualify").show();
            $("div.cancellation-qualify").hide();
            $("div.cancellation-strict").hide();
        }
        else {
            $("div.cancellation-not-qualify").hide();
            $("div.cancellation-qualify").show();
            $("div.cancellation-strict").hide();
        }
        if ( sixtyDayCheck == false ) {
            $("div.policy-within60").show();
            $("div.policy-outside60").hide();
        }
        else {
            $("div.policy-within60").hide();
            $("div.policy-outside60").show();
        }

        if ( checkinDate == "" && paymentdue == "" && policy == "") {
            $("div.cancellation-qualify").hide();
            $("div.cancellation-not-qualify").hide();
            $("div.cancellation-strict").show();
            $("div.policy").hide();
        }
    }

    $( document ).on( 'ready', function () {
          // initialize input mask inputs
          $( '[data-inputmask]' ).inputmask();
          /* REDAWNING-3338: Luxury property filter */
          var pricemin = parseInt( getUrlQueryParameter("pricemin") );
          if ( pricemin >= 500  ) {
            $("input.luxury").prop("checked",true);
            $(".page-header").append(" - Luxury properties");
          }
          else {
            $("input.luxury").prop("checked", false)
          }
          
            var priceminVal = getUrlQueryParameter("pricemin");
            var priceminStr = "pricemin=" + priceminVal;
            var luxuryQueryString = window.location.search.replace(priceminStr, "pricemin=500");
            var nonluxuryQueryString = window.location.search.replace(priceminStr, "pricemin=20");

            $("input.luxury").click(function() {
                if ( priceminVal != null ) {
                    if ( $(this).hasClass("luxury") && priceminVal != 500 ) {
                        window.location.search = luxuryQueryString;
                    }
                    else if ( $(this).hasClass("luxury") && priceminVal == 500 ) {
                        window.location.search = nonluxuryQueryString;
                    }
                }
                else {
                    if ( $(this).hasClass("luxury") && priceminVal != 500 ) {
                        window.location.search += "&pricemin=500";
                    }
                    else if ( $(this).hasClass("luxury") && priceminVal == 500 ) {
                        window.location.search += "&pricemin=20";
                    } 
                }
            });
            /* End REDAWNING-3338 */

          /* REDAWNING-3329: pet-friendly filter */
          var petFriendly = getUrlQueryParameter("pets_ok");

          if ( getUrlQueryParameter("pets_ok") == "yes" ) {
            $(".pet-friendly").prop("checked", true)
          }
          else {
            $(".pet-friendly").prop("checked", false)
          }

          $(".pet-friendly").click(function() {
            if ( petFriendly != null ) {
                window.location.search = window.location.search.replace("&pets_ok=yes", "").replace("?pets_ok=yes&", "?");
            }
            else {
                window.location.search += "&pets_ok=yes";
            }
                
          })
          /* end REDAWNING-3329 */

          /* REDAWNING-4373: prepopulating guess and dates */
          var guest = getUrlQueryParameter("guests");
          var date = getUrlQueryParameter("dates");
          var sleep = getUrlQueryParameter("sleepsmax");
          var expired = new Date(jQuery.now() + 2592000000);
          var datejson = {};
          var dates, start, end, sleeps, sleepmin;
          var t = new Date().getTime();
          //test for query parameters in url
          if ( location.search != "" ) {
              //if there are guests, populate guest dropdown for property pages. does not take in to account children
            if ( guest ) {
                $("#edit-input-guests option").each(function() {
                    if ( $(this).attr("value") == guest ) {
                        $(this).prop("selected", "true");
                    }
                })
                $.cookie("guests", guest, {expires : expired, path: "/"});
            }
            //if there is sleepsmax for the category/region page, populate that
            if ( sleep ) {
                sleeps = sleep.split("TO");
                sleepmin = sleeps[0];
                $("#edit-slpmxmin").attr("value", sleepmin)

            }
            //populate dates
            ///if rental property page and it has a dates parameter, store them in cookie
            if ( location.pathname.indexOf("rental-property") > -1 && date ) {
                date = decodeURIComponent(date);
                dates = date.split("TO");
                if ( dates[0].indexOf("/") < 0 ) {
                    dates[0] = moment.unix(dates[0]).utc().format("MM/DD/YYYY");
                    dates[1] = moment.unix(dates[1]).utc().format("MM/DD/YYYY");
                }
                datejson["ssdate"] = dates[0];
                datejson["esdate"] = dates[1];
                $.cookie("searchdates", JSON.stringify(datejson), {expires : expired, path: "/"});
            }
            ///if category page
            else if ( location.pathname.indexOf("category") > -1 ) {
                ////test if there is a cookie already set or a date parameter and read those
                if ( !$.cookie("searchdates") && date ) {
                    date = decodeURIComponent(date);
                    dates = date.split("TO");
                    if ( dates[0].indexOf("/") < 0 ) {
                        dates[0] = moment.unix(dates[0]).utc().format("MM/DD/YYYY");
                        dates[1] = moment.unix(dates[1]).utc().format("MM/DD/YYYY");
                    }
                    datejson["ssdate"] = dates[0];
                    datejson["esdate"] = dates[1];
                    $.cookie("searchdates", JSON.stringify(datejson), {expires : expired, path: "/"});
                }
                ////if not, write to the cookie the datepicker values
                else if ( $("#dpci2").attr("value") != "" ) {
                    datejson["ssdate"] = $("#dpci2").attr("value");
                    datejson["esdate"] = $("#dpco2").attr("value");
                    $.cookie("searchdates", JSON.stringify(datejson), {expires : expired, path: "/"});
                }
            }
            ///if search page and is redirected from city/state in parameters
            else if( location.pathname.indexOf("search/properties") > -1 ) {
                if ( !$.cookie("searchdates") ) {
                    date = decodeURIComponent(date);
                    dates = date.split("TO");
                    if ( dates[0].indexOf("/") < 0 ) {
                        dates[0] = moment.unix(dates[0]).utc().format("MM/DD/YYYY");
                        dates[1] = moment.unix(dates[1]).utc().format("MM/DD/YYYY");
                    }
                    datejson["ssdate"] = dates[0];
                    datejson["esdate"] = dates[1];
                    $.cookie("searchdates", JSON.stringify(datejson), {expires : expired, path: "/"});
                }
            }
            if ( $.cookie("searchdates") ) {
                var d = JSON.parse( $.cookie("searchdates") );
                start = d["ssdate"];
                end = d["esdate"];
            }
            ///populate datepicker on category page from cookie
            if ( $("#ra-region-search #dpci2").length > 0 && start != null) {
                $("#ra-region-search #dpci2").datepicker("setDate", start);
                $("#ra-region-search #dpco2").datepicker("setDate", end);
            }
            ///populate datepicker on property page from cookie
            if ( $(".prop-right-sidebar .date-picker-containter #chckn").length > 0 && start != null ) {
                $(".prop-right-sidebar .date-picker-containter #chckn").datepicker("setDate", start);
                $(".prop-right-sidebar .date-picker-containter #chckt").datepicker("setDate", end);
                $(".prop-right-sidebar .date-picker-containter #chckt").datepicker("hide");
            }
            ///populating dates in search bar
            if ( $("#dpci").length > 0 && start != null ) {
                $("#dpci").val(start); 
                $("#dpco").val(end); 
                $("#dpci_mobile").val(start); 
                $("#dpco_mobile").val(end); 
            } 
          }
          /* end REDAWNING-4373: prepopulating guess and dates */

          // long homepage script
          $('.long-homepage-wrapper .footer-column header > h2 > a').text('Sign Up for our club newsletter');
          $('.long-homepage-wrapper .footer-column input#edit-submitted-email').attr('placeholder', 'Email Address');
          $('.long-homepage-wrapper .footer-column label[for="edit-submitted-email"]').addClass( 'visually-hidden' );

        // fake search typing animation
        if ( $('#fakeSearchTerm').length ) {
            // clear last search if not the search results page
            if ( !$( 'body' ).hasClass( 'page-search' ) ) {
              $( '#headerSearchInput' ).val( '' );
            }
            // run the fake typing script
            setInterval(typing.cursorAnimation, 750);
            typing.fakeSearchElement = $('#fakeSearchTerm');
            setInterval(typing.combineTypingEffects, 5000);
        }

        $( '.typing-effect, .fake-label, .search-bar' ).on( 'click', function () {
            $( this ).closest( '.typing-effect' ).addClass( 'hide' );
            $( '#headerSearchInput' ).val( '' ).focus();
        });

        $( '#headerSearchInput' ).on( 'keypress', function () {
            $( '.typing-effect' ).addClass( 'hide' );
        });

        // input steppers
        // on button.plus click ...
        $( '.input-stepper button.plus' ).on( 'click', function ( e ) {
            thisStepper = $( e.target ).closest( '.input-stepper' );
            stepperInput = thisStepper.find( 'input' );

            // check the input value
            if ( isNaN( stepperInput.val() ) ) {
              stepperNumber = 0;
            } else {
              stepperNumber = Number( stepperInput.val() );
            }

            // increment the input value
            stepperNumber++;
            stepperInput.val( stepperNumber ).change();

            // then check the stepper number
            stepper.checkStepperNumber( thisStepper );
        });

        // on button.minus click ...
        $( '.input-stepper button.minus' ).on( 'click', function ( e ) {
            thisStepper = $( e.target ).closest( '.input-stepper' );
            stepperInput = thisStepper.find( 'input' );

            // check the input value
            if ( isNaN( stepperInput.val() ) ) {
              stepperNumber = 0;
            } else {
              stepperNumber = Number( stepperInput.val() );
            }

            // decrement the input value
            stepperNumber--;
            stepperInput.val( stepperNumber ).change();

            // then check the stepper number
            stepper.checkStepperNumber( thisStepper );
        });

        // on input field blur ...
        $( '.input-stepper input' ).on( 'blur', function ( e ) {
            thisStepper = $( e.target ).closest( '.input-stepper' );
            // check the stepper number
            stepper.checkStepperNumber( thisStepper );
        });

        // checkbox styles
        $( 'input[type="checkbox"]:checked' ).addClass( 'fa fa-check' );
        $( 'input[type="checkbox"]:not(:checked)' ).removeClass( 'fa fa-check' );

        $( 'input[type="checkbox"]' ).on( 'change', function () {
          $( this ).toggleClass( 'fa fa-check' );
        });

        // check the stepper number on load
        if ( document.getElementsByClassName( 'input-stepper' ).length ) {
          stepper.init();
        }

        $(".not_found a.back").on("click", function() {
            event.preventDefault();
            window.history.back();
        });

        $("#dropdownMenu2").click(function() {
            if ( $(".adv-srch-datepicker:visible").length > 0 ) {
                $(".adv-srch-datepicker").hide()
            }
        })
        if ( $.cookie("mobile_app_alert_dismissal") == undefined ) {
            $.cookie("mobile_app_alert_dismissal", "false", {expires : expired, path : "/", domain: location.href})
        }
        $(".alert.alert-dismissible .close").on("click", function() {
            $.cookie("mobile_app_alert_dismissal", "true")
        })

        if ( getMobileOperatingSystem() == 'iOS' || getMobileOperatingSystem() == "Android" ) {
            if ( $.cookie("mobile_app_alert_dismissal") != "true" ) { 
                $(".alert.alert-dismissible").addClass("show");
            }
            else {
                $(".alert.alert-dismissible").removeClass("show");
            }

            if ( getMobileOperatingSystem() == 'iOS' ) {
                $(".app-link").attr("href", "https://itunes.apple.com/us/developer/redawning/id1271483955?mt=8")
            }
            else if ( getMobileOperatingSystem() == "Android" ) {
                $(".app-link").attr("href", "https://play.google.com/store/apps/details?id=com.redawning.raapp&hl=en")
            }
        }
        $.ajax({
            url: "https://api.ipify.org/?format=json",
            cache: false,
            dataType: "json",
            type: "GET",
            success: function(result, success) {
                $.cookie("user_ip", result.ip), {expires : expired, path : "/"};
        
                $.getJSON('https://ipinfo.io/' + $.cookie("user_ip") + "?token=dce465519b1d80", function(data){
                    $.cookie("user_loc", data.loc, {expires : expired, path : "/"} );
                
                    var loc = $.cookie("user_loc").split(",");
                    var lat = loc[0];
                    var long = loc[1];
                    var attr = $(".explore-nearby").attr("href");
                    $(".explore-nearby").attr("href",attr+"?ptype=locality&platitude="+lat+"&plongitude="+long);
        
                })
            }
        });   
        
        /* REDAWNING-4372: cancellation policy */
        if ( $("#cancel-policy").attr("value") != "" && isNaN(parseInt($("#cancel-policy").attr("value"))) == false ) {
            var today = moment().format("MM/DD/YYYY");
            var cancelPolicy = $("#cancel-policy").attr("value");
            var balancedue = $("#payment-due").attr("value");
            var checkinDate = $("#chckn").attr("value");
            var cancelPolicyCheck;

            if ( location.pathname.indexOf("rental-property") > 0 ) { 
                if ( checkinDate != ""  ) {
                    testDate(checkinDate, balancedue , cancelPolicy );
                };
                $("body").on("change", "#chckn", function() {
                    testDate($(this).attr("value"), balancedue , cancelPolicy);
                });
            }
            else if ( location.pathname.indexOf("property-reservation") > 0 ) {
                testDate($("#edit-arrival").attr("value"), balancedue , cancelPolicy);
            }
        }
        else {
            testDate( "", "", "" );
        }
    });
})(jQuery);
