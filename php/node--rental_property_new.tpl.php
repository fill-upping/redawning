<!-- ra_bootstrap_subtheme\templates\node-rental_property.tpl.php -->
<?php
$path = drupal_get_path('theme', variable_get('theme_default', NULL));
$calendar_path = drupal_get_path('module', 'calendar');
drupal_add_css($path . '/css/rental-property.css');
drupal_add_css($calendar_path . '/eternicode/datepicker.css');
drupal_add_css(RAProperty::getFontAwesome());
drupal_add_js($calendar_path . '/eternicode/bootstrap-datepicker.js');
drupal_add_js($path . '/js/theia-sticky-sidebar.js');
drupal_add_js($path . '/js/jquery.sticky.js');
drupal_add_js($calendar_path . '/eternicode/layout_prop_detail_calendar.js', array('scope' => 'footer', 'weight' => 2));
drupal_add_js($path . '/js/availability_calendar.js');
drupal_add_js($path . '/js/sticky-top-navbar.js');

// owl carousel
drupal_add_css($path . '/plugin/owl-carousel/owl.carousel.css');
drupal_add_css($path . '/plugin/owl-carousel/owl.theme.css');
drupal_add_css($path . '/plugin/owl-carousel/owl.transition.css');
drupal_add_js($path . '/plugin/owl-carousel/owl.carousel.min.js', array('scope' => 'footer'));

// photoswipe
drupal_add_css($path . '/plugin/photoswipe/dist/photoswipe.css');
drupal_add_css($path . '/plugin/photoswipe/dist/default-skin/default-skin.css');
drupal_add_js($path . '/plugin/photoswipe/dist/photoswipe.min.js', array('scope' => 'footer'));
drupal_add_js($path . '/plugin/photoswipe/dist/photoswipe-ui-default.min.js', array('scope' => 'footer'));
drupal_add_js($path . '/js/photoswipe_rental_property_page.js', array('scope' => 'footer'));

drupal_add_js($path . '/js/rental-prop.js', array('scope' => 'footer'));


    $cleaningfeepopupText = theme_get_setting('cleaningfee_pptxt');
    $travelInsurancepopupText = theme_get_setting('ti_pptxt');
    $tiwidthPopupText = theme_get_setting('tiWidth_pptxt');
    $arrivalfeetext = theme_get_setting('arrivalfee_pptxt');
    $calendarpageinfo_top = theme_get_setting('topinfo_calp');
    $calendarpageinfo_btm = theme_get_setting('btminfo_calp');

    $actTheme = variable_get('theme_default');
    if ($actTheme != "ra_bootstrap_subtheme") {
        $cftxt = !empty($cleaningfeepopupText) ? '<div id="clnfee" title="Cleaning Fees"><p>' . $cleaningfeepopupText . '</p><p style="text-align:center; margin-top:10px;"><button class="rpbooknow closepopup" type="button">Close</button></p></div>' : '';
        $titxt = !empty($travelInsurancepopupText['value']) ? '<div id="tipop" title="Travel Insurance">' . $travelInsurancepopupText['value'] . '</div>' : '';
        $aftxt = !empty($arrivalfeetext['value']) ? '<div id="afee" title="At Arrival Fees"><p>' . $arrivalfeetext['value'] . '</p><p style="text-align:center; margin-top:10px;"><button class="rpbooknow closepopup" type="button">Close</button></p></div>' : '';
    }

    drupal_add_js(array('tiwidthPopupText' => $tiwidthPopupText), 'setting');
    $rs = db_query("SELECT deposit_percentage,arrivalfees,currency FROM {price} WHERE nid = " . $nid)->fetch();

    $deposit = (!empty($rs) && $rs->deposit_percentage != 0) ? $rs->deposit_percentage : 50;

    $qminstay = db_query('select p.nid, least(peak_min_stay, off_min_stay, IFNULL((select min(special_minstay)
        from price_dates s where s.nid = p.nid),least(peak_min_stay, off_min_stay))) as minim, peak_min_stay,
        off_min_stay, (select min(special_minstay) from price_dates s where s.nid = p.nid) as min_special
        from price p where nid = :nid', array(':nid' => $nid))->fetch();

    $qminstaymin = (!empty($qminstay)) ? $qminstay->minim : 2;
    $lowest_minstay = '+' . $qminstaymin . ' day';

    $current = date('m/d/Y');
    $startselected = $endselected = null;

    $date = new \DateTime();
    $local_losangeles = $date->format('m/d/Y');
    $date->add(new \DateInterval('P3Y'));
    $dateEnd = $date->format('m/d/Y');

    $cico_time = new redawning\property\CicoTime($nid);
    $cicotime = $cico_time->getFormatedCicoTimeInfo();

    // get free cancel from backoffice
    $freeCancel = new redawning\backoffice\BackofficeFreeCancel($nid);
    
    global $base_url;
    $base_url_modified = str_replace("http://", "https://", $base_url);
    drupal_add_js(array('sitedate' => $local_losangeles, 'enddate' => $dateEnd, 'startselect' => $startselected,
                        'endselect' => $endselected, 'blockeddates' => getBlockedDates($nid), 'baseurl' => $base_url_modified,
                        'non_usd_check' => $non_usd_check), 'setting');

$node->locations = isset($node->field_geolocation[$node->language][0])?$node->field_geolocation[$node->language][0]:NULL;
$fddrap1 = '';
$LatLng = '';
$LngLat = '';
if(!empty($node->locations)) {
    $stap = isset ($node->locations['street']) ? trim($node->locations['street']).' ' : '';
    $ctap = isset ($node->locations['city']) ? trim($node->locations['city']).', ' : '';
    $pvap = isset ($node->locations['province']) ? trim($node->locations['province']).' ' : '';
    $pcap = isset ($node->locations['postal_code']) ? trim($node->locations['postal_code']).', ' : '';
    $pnap = isset ($node->locations['province_name']) ? trim($node->locations['province_name']).', ' : '';
    $cnap = isset ($node->locations['country_name']) ? trim($node->locations['country_name']) : '';
    $fddrap1 = $stap.$ctap.$pvap.$pcap.$pnap.$cnap;
    $fddrap2 = str_replace(' ', '+', $fddrap1);
    try{
        if(!isset($node->in_preview) && !empty($node->nid)){
            if($node->locations['latitude'] == '0.000000' && $node->locations['longitude'] == '0.000000'){
                $Address = urlencode($fddrap2);
                $request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=".$Address;
                $xml = simplexml_load_file($request_url) or die("url not loading");
                $status = $xml->status;
                if ($status=="OK") {
                    $Lat = $xml->result->geometry->location->lat;
                    $Lon = $xml->result->geometry->location->lng;
                    $LatLng = "$Lat,$Lon";
                    $LngLat = "$Lon,$Lat";
                }
            }else{
                $LatLng = $node->locations['latitude'].','.$node->locations['longitude'] ;
                $LngLat = $node->locations['longitude'].','.$node->locations['latitude']; 
            }
        }else{
            $Address = urlencode($fddrap2);
            $request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=".$Address;
            $xml = simplexml_load_file($request_url) or die("url not loading");
            $status = $xml->status;
            if ($status=="OK") {
                $Lat = $xml->result->geometry->location->lat;
                $Lon = $xml->result->geometry->location->lng;
                $LatLng = "$Lat,$Lon";
                $LngLat = "$Lon,$Lat";
            }
        }
    } catch (Exception $e){
        watchdog('LatLng', $e->getMessage());
    }
} else {
    //set default location
    $LatLng = "30.243207, -170.859375";
}
drupal_add_js('jQuery(document).ready(function () {
    var limit = 10-1;
    jQuery(".property_detail ul.nav-list").find("li:gt(" + limit + ")").hide();
    jQuery(".property_detail ul.nav-list").filter(function() {
        return jQuery(this).find("li").length > 10;
    }).each(function() {
        jQuery(\'<a href="#" class="nrpmf"></a>\').text("+ Show more").click(function() {
            if (jQuery(this).prev().find("li:hidden").length > 0) {
                jQuery(this).prev().find("li:gt(" + limit + ")").slideDown();
                jQuery(this).addClass("open").text("- Show fewer");
            }else {
                jQuery(this).prev().find("li:gt(" + limit + ")").slideUp();
                jQuery(this).removeClass("open").text("+ Show more");
            }
            return false;
        }).insertAfter(jQuery(this));
    });

    //ra1858gfo
    jQuery("#myCarousel").css({"text-align":"center", "border-bottom":"2px solid #DDD", "padding-bottom":"5px", "margin-bottom":"10px"});
    var spanlength = jQuery(".carousel-indicators").find("span").length;
    jQuery("#lesmorethm").hide();
    jQuery(".carousel-indicators")
    .find("span")
    .each(function(index){
        var dataslideto = jQuery(this).attr("data-owl-slide-to");
        if(dataslideto > 11){
            jQuery(this).hide();
            jQuery(this).addClass("morethanlimitdefaultshow");
        }
    });
    if(spanlength > 12){
        jQuery(".showmore-nav#morelesthm").show();
        jQuery(".showmore-nav#morelesthm").click(function(){
            jQuery(".carousel-indicators span.morethanlimitdefaultshow").fadeIn("fast");
            jQuery(".carousel-indicators span.morethanlimitdefaultshow").addClass("myhideagain");
            jQuery(".carousel-indicators span.morethanlimitdefaultshow").removeClass("morethanlimitdefaultshow");
            jQuery(this).hide();
            jQuery(".showmore-nav#lesmorethm").show();
            jQuery(".showmore-nav#lesmorethm").css({"display":"inline-block"});
        });
        jQuery(".showmore-nav#lesmorethm").click(function(){
            jQuery(".carousel-indicators span.myhideagain").fadeOut("fast");
            jQuery(".carousel-indicators span.myhideagain").addClass("morethanlimitdefaultshow");
            jQuery(".carousel-indicators span.myhideagain").removeClass("myhideagain");
            jQuery(this).hide();
            jQuery(".showmore-nav#morelesthm").show();
        });
    }else{
        jQuery("#morelesthm").hide();
    }
    //end ra1858
});

(function ($){
//ra1858
    var lastScroll = $(window).scrollTop();
    $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        var divTop = $("#myCarousel").offset().top;
        if ( scrollTop > lastScroll ) {
            $("#carthm span").click(function(){
                var aTag = $(\'a[id="main-content"]\');
                $("body").stop().animate({scrollTop: aTag.offset().top},"fast");
            });
        }
        lastScroll = scrollTop;
    });
//end ra1858
})(jQuery);
', array('type' => 'inline', 'scope' => 'header')
);

$accomodation = array();
$accomodation_field = $group['accommodation_title'];
if(isset($accomodation_field)){
    foreach($accomodation_field as $acctittle=>$acc){
         $data = $node->$acctittle;
        if(!empty($data)){
             isset($data[$node->language][0]['value']) ? $value = $data[$node->language][0]['value'] : $value = '';
            $accomodation[] = $acc." : ".$value;
         }
     }
 }

function facilitiesList_HTML($count, $split_percolumn, $total_item, $array_content, $value) {
    $v['show_all'] = $v['show_some'] = '';
    $start_column_section = '<div class="span4">';
    $end_column_section = '</div>';
    $some_total = 6;
    $some_percolumn = 2;

    if($count == 1) {
        $v['show_all'] .= $start_column_section . '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>';
    } else {
        if($count == count($array_content)) {
            $v['show_all'] .= '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>' . $end_column_section;
        } else {
            if($count % $split_percolumn == 0) {
                $v['show_all'] .= '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>'. $end_column_section . $start_column_section;
            } else {
                $v['show_all'] .= '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>';
            }
        }
    }

    if($count <= $some_total) {
        if($count == 1) {
            $v['show_some'] .= $start_column_section . '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>';
        } else {
            if($count == $some_total) {
                $v['show_some'] .= '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>' . $end_column_section;
            } else {
                if($count % $some_percolumn == 0) {
                    $v['show_some'] .= '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>'. $end_column_section . $start_column_section;
                } else {
                    $v['show_some'] .= '<li><i class="fa fa-check-circle" aria-hidden="true"></i>' . $value . '</li>';
                }
            }
        }
    }
    return $v;
}

$sleepmax = isset($node->field_sleeps_max[$node->language][0]['value'])?$node->field_sleeps_max[$node->language][0]['value']:0;
$count_guests = $sleepmax;
drupal_add_js(array('sleepmax' => $sleepmax), 'setting');
$childrenAllowed = isset($node->field_children_ok[$node->language][0]['value'])?$node->field_children_ok[$node->language][0]['value'] : TRUE;
if ($childrenAllowed === 'no'){
    $childrenAllowed = FALSE;
}
drupal_add_js(array('children_allowed' => $childrenAllowed), 'setting');

?>


<?php
    $renagreementnid = theme_get_setting('renagreementnid');
    $nodeterms = node_load($renagreementnid);
    $tmlang = isset($nodeterms->language) ? $nodeterms->language : 'und';
    $tmttl = !empty($nodeterms->title) ? $nodeterms->title : 'Rental Agreement';
    $tmbody = !empty($nodeterms->body[$tmlang][0]['value']) ? $nodeterms->body[$tmlang][0]['value'] : 'www.redawning.com/full-terms';
    $tmsummary = !empty($nodeterms->body[$tmlang][0]['summary']) ? $nodeterms->body[$tmlang][0]['summary'] : '';
?>
<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>

    <?php if ($unpublished): ?>
    <div class="alert alert-error"><h1><?php print t('Unpublished'); ?></h1></div>
    <?php endif; ?>
    <div class="content"<?php print $content_attributes; ?>>
        <?php
          // We hide the comments and links now so that we can render them later.
          hide($content['comments']);
          hide($content['links']);
        ?>

        <div class="property_detail" itemscope itemtype="http://schema.org/Product">
            <input type="hidden" value="<?php print $title?>" itemprop="name" />
            <?php 
                echo "<input type='hidden' value='" . substr( $freeCancel->getFreeCancelPackageName(), 4 ) . "' id='cancel-policy' />";
                echo '<input type="hidden" value="' . theme_get_setting("balancedue") . '" id="payment-due" />';
            ?>
            <div class="row-fluid">
            <!-- top -->

                <div class="span8 prop-content">
                    <!-- START - sticky anchor navigation -->
                    <div class="sticky-rentalprop-topmenu">
                        <div class="container">
                            <div class="row-fluid">
                                <div class="span12 text-center">
                                    <ul class="top-prop-nav" id="top-prop-nav-id">
                                        <li class="indicator">
                                            <span id="menu-indicator-mobile"></span>
                                        </li>
                                        <a href="javascript:void(0);" style="font-size:15px;" onclick="toggleNavMenu();">
                                            <li class="indicator icon">
                                                <i class="fa fa-bars" aria-hidden="true"></i>
                                            </li>
                                        </a>
                                        <li><a href="#nav-prop-overview"><i class="fa fa-home" aria-hidden="true"></i> Overview</a></li>
                                        <li><a href="#nav-prop-location"><i class="fa fa-map-marker" aria-hidden="true"></i> Location</a></li>
                                        <li><a href="#nav-prop-facilities"><i class="fa fa-list-ul" aria-hidden="true"></i> Facilities</a></li>
                                        <li><a href="#nav-prop-availability"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></i> Availability</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="rentalprop-topmenu" class="theiaStickySidebar">
                        <div>
                            <div class="row-fluid">
                                <div class="span12">
                                    <ul>
                                        <a href="#nav-prop-overview"><li id="btn-nav-prop-overview"><i class="fa fa-home" aria-hidden="true"></i> Overview</li></a>
                                        <a href="#nav-prop-location"><li id="btn-nav-prop-location"><i class="fa fa-map-marker" aria-hidden="true"></i> Location</li></a>
                                        <a href="#nav-prop-facilities"><li id="btn-nav-prop-facilities"><i class="fa fa-list-ul" aria-hidden="true"></i> Facilities</li></a>
                                        <a href="#nav-prop-availability"><li id="btn-nav-prop-availability"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Availability</li></a>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END - sticky anchor navigation -->

                    <div id="nav-prop-overview" class="row-fluid">
                      <!-- START - property images -->
                        <div id="e11296left" class="property_thumb">
                            <div id="myCarousel" class="carousel slide">
                                <a name="see480320"></a>
                                    <?php
                                    $imgdata = isset($node->field_property_images[$node->language]) ? $node->field_property_images[$node->language] : array();
                                        print '<div id="owl-property-image" class="owl-carousel" itemscope itemtype="http://schema.org/ImageGallery">';
                                        foreach ($imgdata as $key => $img) {
                                            $photoswipe_tag_suffix = sprintf('<a href="%s" itemprop="contentUrl" data-size="%sx%s">', file_create_url($img['uri']), $img['width'], $img['height']);
                                            $photoswipe_tag_prefix = '</a>';
                                            $style = "1024x768";
                                            $fileview = image_style_url($style, $img['uri']);
                                            $fileurl = file_create_url($img['uri']);
                                            if (!file_exists($img['uri'])){
                                                $fileview = file_create_url(drupal_get_path('theme', 'ra_bootstrap_subtheme') . '/images/no-image.jpg');
                                                $photoswipe_tag_suffix = sprintf('<a href="%s" itemprop="contentUrl" data-size="%sx%s">', $fileview, 90, 80);
                                            }
                                            $clse = ($key == 0) ? 'item active' : 'item' ;
                                            $propregion = isset($node->taxonomy_vocabulary_1[$node->language][0]['taxonomy_term']->name) ? 'in ' . $node->taxonomy_vocabulary_1[$node->language][0]['taxonomy_term']->name : '';
                                            $imgalt = $node->title.' Vacation Rental '.$propregion.' - RedAwning';
                                            print '<figure class="owl-peritem-slide '.$clse.'" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">' . $photoswipe_tag_suffix . '<img class="lazyOwl" alt="' . $imgalt . '" data-src="' . $fileview . '" width="1024" height="768" />' . $photoswipe_tag_prefix . '</figure>';

                                        }
                                        print '</div>';
                                        print '<a class="carousel-control left" data-slide="prev"></a><a class="carousel-control right" data-slide="next"></a>';

                                        print '<div class="owl-controls clickable"><div class="owl-pagination">';
                                        print '<div id="carthm" class="carousel-indicators brounded">';
                                        $allImages = '';
                                        $count = 1;
                                        foreach ($imgdata as $key => $bul) {
                                            $stylethm = "thumbnail";
                                            $thmview = image_style_url($stylethm, $bul['uri']);
                                            if (!file_exists($bul['uri'])){
                                                $thmview = file_create_url(drupal_get_path('theme', 'ra_bootstrap_subtheme') . '/images/no-image.jpg');
                                            }
                                            $allImages .= '"' . $thmview . '"';
                                            if ( $count != count($imgdata) ) {
                                                $allImages .= ',';
                                            }
                                            $clsebul = ($key == 0) ? ' active' : '' ;
                                            print '<span data-owl-slide-to="'. $key .'" class="thumb-owl-nav'.$clsebul.'">'.'<img itemprop="image" class="img-transparent" alt="img' . $key . '" src="' . $thmview . '" /></span>';
                                            $count++;
                                        }
                                        print '</div><span class="showmore-nav" id="morelesthm" title="Show more"></span><span class="showmore-nav" id="lesmorethm" title="Show fewer"></span>';
                                        print '</div></div>';
                                    ?>
                            </div>
                        </div>
                        <!-- END - property images -->

                        <div id="calendar-mobile">
                            <div class="row-fluid">
                                <div class="span12">
                            <div class="calendar-rightsidebar">
                            <!-- price -->
                            <?php
                                $valcurrency = variable_get('currencyvalue',array());
                                $arrivalfees = variable_get('arrivalfees',array());
                                $pricefrom = $pricerange->pricefrom;
                                $setprice='';
                                if (!empty($pricerange)) {
                                    if ($pricerange->priceto != $pricerange->pricefrom) {
                                        $setprice = "$" . ceil($pricerange->pricefrom) . "-$" . ceil($pricerange->priceto);
                                    } else {
                                        $setprice = "$" . ceil($pricerange->pricefrom);
                                    }
                                }

                                if (isset($accomodation)) {
                                  $mtne = array();
                                  foreach ($accomodation as $acckeys => $gr2) {
                                      if (preg_match('/(Bathrooms)|(Bedrooms)|(Pets Allowed)|(Sleep)/', $gr2)) {
                                          $item = explode(' : ', $gr2);
                                          $mtne[trim($item[0])] = $item[1];
                                      }
                                  }
                               }

                               $featured_var = json_decode(variable_get('featured_filter'), true);
                               $amenities_field = $node->field_amenities;
                               isset($amenities_field[$node->language]) ? $amenities = $amenities_field[$node->language] : $amenities = null;
                               if (isset($amenities)) {
                                       $ameno = array();
                                       foreach ($amenities as $amkeys => $am) {
                                            if (in_array($am['value'], $featured_var['Hot Tub'])) {
                                                $ameno['Hot Tub'][] = $am['value'];
                                            } elseif(in_array($am['value'], $featured_var['Pool'])) {
                                                $ameno['Pool'][] = $am['value'];
                                            }
                                       }
                               }
                            ?>
                            <div class="row-fluid">
                                <div class="span12">
                                <div id="e11296right">
                                        <div class="property-detail">
                                                <h1 class="pricepernight ext-center nomargin"><span class="price"><?php print $setprice; ?></span> <span>/night</span></h1>
                                                <div class="minimal-stay">
                                                    <span> Minimum Nights Stay : <span class="edit-req-minstay"> - </span> </span>
                                                </div>
                                                <div class="row-fluid">
                                                    <div class="span12">
                                                        <div class="property-details-container">
                                                            <ul class="nav nav-list">
                                                                <?php if (isset($mtne['Bedrooms'])) { ?>
                                                                <li class="liste">
                                                                    <span class="srclabel bgcolon"><?php print ($mtne['Bedrooms'] > 1) ? 'Bedrooms' : 'Bedroom'; ?></span>
                                                                    <span class="badge"><?php print $mtne['Bedrooms']; ?></span>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if (isset($mtne['Bathrooms'])) { ?>
                                                                    <li class="liste">
                                                                        <span class="srclabel bgcolon"><?php print ($mtne['Bathrooms'] > 1) ? 'Bathrooms' : 'Bathroom'; ?></span>
                                                                        <span class="badge"><?php print $mtne['Bathrooms']; ?></span>
                                                                    </li>
                                                                <?php } ?>
                                                                <?php if (isset($mtne['Sleeps'])) { ?>
                                                                    <li class="liste">
                                                                        <span class="srclabel bgcolon"><?php print ($mtne['Sleeps'] > 1) ? 'Sleeps' : 'Sleep'; ?></span>
                                                                        <span class="badge"><?php print $mtne['Sleeps']; ?></span>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                        <div class="property-details-container">
                                                            <ul class="nav nav-list">
                                                                <?php if(isset($node->taxonomy_vocabulary_3['und'][0]['taxonomy_term']->name)) { ?>
                                                                    <li class="liste"><span class="srclabel bgcolon">Type</span>&nbsp;<span class="label label-inverse fcap"><?php print $node->taxonomy_vocabulary_3['und'][0]['taxonomy_term']->name; ?></span>
                                                                    </li>
                                                                <?php } ?>
                                                                <?php if( (isset($node->taxonomy_vocabulary_4['und'][0]['taxonomy_term']->name)) && $node->taxonomy_vocabulary_4['und'][0]['taxonomy_term']->name != "other view") { ?>
                                                                    <li class="liste"><span class="srclabel bgcolon">View</span>&nbsp;<span class="label label-inverse fcap"><?php print $node->taxonomy_vocabulary_4['und'][0]['taxonomy_term']->name; ?></span>
                                                                    </li>
                                                                <?php } ?>
                                                                <?php
                                                                    if(( !empty($mtne['Pets Allowed']) && strtolower($mtne['Pets Allowed']) == 'yes') || !empty($ameno['Pool']) || !empty($ameno['Hot Tub'])) {
                                                                        print '<li class="liste">';
                                                                            if(!empty($ameno['Pool'])) { print '<span class="label label-info">Pool</span>'; }
                                                                            if(!empty($ameno['Hot Tub'])) { print '<span class="label label-info">Hot Tub</span>'; }
                                                                            if(!empty(isset($mtne['Pets Allowed']) && strtolower($mtne['Pets Allowed']) == 'yes')) { print '<span class="label label-info">Pets OK</span>'; }
                                                                        print '</li>';
                                                                    }
                                                                ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                        </div>
                                </div>
                            </div>


                            <?php if($non_usd_check): ?>
                                <div class="row-fluid">
                                    <div class="span12">
                                        <div style="margin-top:3px;margin-bottom:7px;" class="alert alert-info">
                                            <div style="text-align:center;" class="err-msg-cal">
                                                <p style="text-align:center;">
                                                    <?php print $non_usd_check; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             <?php endif; ?>

                            <div class="row-fluid">
                                <div class="span12">
                                    <div class="date-picker-containter">

                                       <input id="edit-inputdate1" type="hidden" name="inputdate1" value="" />
                                       <input id="edit-inputdate2" type="hidden" name="inputdate2" value="" />
                                       <input id="edit-departuredate" type="hidden" name="departuredate" value="" />
                                       <input id="edit-insurance" type="hidden" name="insurance" value="" />
                                       <input id="edit-default-clean-fee" type="hidden" name="default_clean_fee" value="" />
                                       <input id="edit-peak-count" type="hidden" name="peak_count" value="" />
                                       <input id="edit-off-count" type="hidden" name="off_count" value="" />
                                       <input id="edit-spl-count" type="hidden" name="spl_count" value="" />
                                       <input id="edit-sourcepage" type="hidden" name="sourcepage" value="" />
                                       <input id="edit-hidden-uid" type="hidden" name="hidden_uid" value="" />
                                       <input type="hidden" id="edit-hidden-id" name="hidden_id" value="<?php print $nid; ?>">

                                        <h3>Choose Your Dates</h3>
                                        <div class="row-fluid">
                                         <div class="span12 datepicker-wrapper">
                                           <div class="input-daterange rentalpage" id="datepicker-mobile">
                                              <input type="text" class="input-medium chcknc" name="start" id="chckn" placeholder="Check-in" autocomplete="off" readonly="readonly" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>
                                              <input type="text" class="input-medium chcktc" name="end" id="chckt" placeholder="Check-out" autocomplete="off" readonly="readonly" <?php print ($non_usd_check ? 'disabled="disabled"' : '') ?> style="background: rgb(238, 238, 238);">
                                               <select id="edit-input-guests-mobile" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>
                                                   <?php if(!empty($sleepmax)) { ?>
                                                     <?php for($i=1;$i <= $sleepmax;$i++){ ?>
                                                       <option value="<?php print $i; ?>" <?php print ($i == $count_guests ? ' selected ' : '') ?>><?php print $i . ($i > 1 ? ' Adults' : ' Adult'); ?> </option>
                                                     <?php } ?>
                                                   <?php } ?>
                                               </select>
                                              <select id="edit-input-child-guests-mobile" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>
                                                  <?php if (!empty($sleepmax)) { ?>
                                                      <option value="0" selected> 0 Children</option>
                                                  <?php } ?>
                                              </select>
                                           </div>
                                         </div>
                                         <div class="row-fluid">
                                           <div class="checkbox-date-book">
                                               <label style="display:inline-block;" class="option checkbox control-label" for="edit-agreetravelins-mobile">
                                                   <input type="checkbox" id="edit-agreetravelins-mobile" name="agreetravelins" value="1" class="form-checkbox" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>Protect your stay with Travel Insurance
                                                   <span data-target="#pyswti" role="button" class="badge badge-info tiqst rp-tiqst" data-toggle="modal">?</span>
                                               </label>
                                           </div>
                                        </div>
                                       </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row-fluid loading-wrapper">
                                <div class="col-lg-12">
                                    <div class="loading">
                                        <img src="/<?php print $calendar_path; ?>/eternicode/loader.gif"/> <span>Calculating...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="span12">
                                <div class="notif-error-msg-cal">
                                </div>
                            </div>
                            <div id="calendar-result-mobile" class="row-fluid price-calculation-result">
                                <div class="row-fluid">
                                    <div class="span12">
                                        <div class="upper-charges-detail-wrapper">
                                            <table class="upper-charges-detail">
                                                <tbody>
                                                    <tr>
                                                        <td><i style="margin-right:1px;" class="fa fa-moon-o" aria-hidden="true"></i> Total Nights</td>
                                                        <td><span class="total-nights"></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><i style="margin-right:4px;" class="fa fa-dollar" aria-hidden="true"></i> Daily Rate Charges</td>
                                                        <td><span class="dailyRatesChrg"></span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <table class="middle-charges-detail">
                                            <tbody>
                                                <tr class="item-calculation-result">
                                                    <td>Rent ($)</td>
                                                    <td><input readonly="readonly" type="text" class="edit-rental-charges" name="edit-rental-charges"/></td>
                                                </tr>
                                                <tr class="item-calculation-result">
                                                    <td>Cleaning Fee ($) <span data-target="#clnf" role="button" class="badge badge-info" data-toggle="modal">?</span></td>
                                                    <td><input readonly="readonly" type="text" class="edit-clean-fee" name="edit-clean-fee"/></td>
                                                </tr>
                                                <tr class="item-calculation-result">
                                                    <td>Taxes ($)</td>
                                                    <td><input readonly="readonly" type="text" class="edit-rental-tax" name="edit-rental-tax"/></td>
                                                </tr>
                                                <tr class="item-calculation-result travel-insurance-line">
                                                    <td>Travel Insurance ($)</td>
                                                    <td><input readonly="readonly" type="text" class="edit-travelinsurance" name="edit-travelinsurance"/></td>
                                                </tr>
                                                <tr class="total-calculation-result">
                                                    <td>Total ($)</td>
                                                    <td><input readonly="readonly" type="text" class="edit-booking-charges" name="edit-booking-charges"/></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="row-fluid">
                                            <div class="well agreement-wrapper">
                                                <div class="agreement-content">
                                                    <?php print $tmsummary; ?>
                                                    <label class="option checkbox control-label" for="edit-agree"> <input type="checkbox" id="edit-agree-mobile" name="agree" value="1" class="form-checkbox"> I Agree to the <span data-target="#rntlagree" role="button" class="rental-agreement-modal" data-toggle="modal">Rental Agreement terms</span> </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row-fluid">
                                <div class="span12">
                                    <div id="tomove">
                                        <!-- book now button -->
                                        <div class="nrp-btntop text-center">
                                        <!-- <p><a title="Get Price & Book Now!" class="booking-button rpbooknow btn btn-large btn-cstblock" href="<?php print url('property-booking/' . $node->nid); ?>">Book Now</a></p>-->
                                            <button class="buuking booking-button rpbooknow btn btn-large btn-cstblock fullw btn form-submit" id="edit-submit" name="op" value="Book Now" type="submit" disabled="disabled" itemprop="availability" href="http://schema.org/InStock">Book Now</button>
                                            <p class="aside">
                                            <small>Properties are going fast!</small><br/>
                                            <?php if(module_exists('sugarcrm') && function_exists('sugarcrm_render_modal')) { ?>
                                            <a title="Inquire About This Property" class="askquest btn btn-small" href="#" data-toggle="modal" data-target="#leadsModal">Inquire About This Property</a>
                                            <?php } else { ?>
                                            <a title="Inquire About This Property" class="askquest btn btn-small" href="#" onclick="return SnapEngage.startLink();">Inquire About This Property</a>
                                            <?php }?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                </div>
                            </div>
                        <hr>
                        </div>

                        <!-- START - Property Body Content -->
                        <div class="row-fluid">
                            <div style="display:none;">
                                <div class="forthumb">
                                    <div class="property_thumb"></div>
                                </div>
                                <div class="forinfo">
                                    <div class="well light">
                                        <div id="e1info"></div>
                                        <div class="text-center">
                                            <p><small>Properties are going fast!</small></p>
                                            <p>
                                            <?php if(module_exists('sugarcrm') && function_exists('sugarcrm_render_modal')) { ?>
                                            <a title="Inquire About This Property" class="askquest btn btn-small" href="#" data-toggle="modal" data-target="#leadsModal">Inquire About This Property</a>
                                            <?php } else { ?>
                                            <a title="Inquire About This Property" class="askquest btn btn-small" href="#" onclick="return SnapEngage.startLink();">Inquire About This Property</a>
                                            <?php }?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <!-- book now button -->
                                <div class="nrp-btntop text-center">
                                    <p><a title="Get Price & Book Now!" class="rpbooknow btn btn-cta btn-large btn-cstblock" href="<?php print url('property-booking/' . $node->nid); ?>">Get Price & Book Now!</a></p>

                                </div>
                                <?php if(!empty($node->locations['city'])){?>
                                <div class="rpsmap text-center">
                                    <p><a class="btn btn-cstblock" title="<?php print $node->locations['city'] ?>" href="#" data-toggle="modal" data-target="#siModal"><img class="rpsmapimg hidden" alt="<?php print $node->locations['city'] ?>" src="/<?php print $path; ?>/images/ikongmap.png"/><span class="rpsmaptxt"><span class="txtvom">View on Map</span>&nbsp;&nbsp;-&nbsp;&nbsp;<?php print $node->locations['city'] ?></span></a></p>
                                </div>
                                <?php }?>
                            </div>
                            <div class="rpbodywrp" itemprop="description">
                                <!-- special terms Header-->
                                <div class="clearer"></div>
                                <?php if(isset($node->field_special_terms_top[$node->language][0]['value'])){ ?>
                                    <div class="notes-special-terms">
                                        <?php print $node->field_special_terms_top[$node->language][0]['value'];?>
                                    </div>
                                <?php } ?>
                                <!-- content -->

                                <?php $descr = isset($node->body[$node->language][0]['value']) ? urlencode(strip_tags($node->body[$node->language][0]['value'])) : "" ?>
 

                                <?php print isset($node->body[$node->language][0]['value'])?$node->body[$node->language][0]['value']:""; ?>

                                <?php if($additional_cost){ ?>
                                    <div class="clearer"></div>
                                    <div style="display:block;margin-top:20px;padding-top:10px;">
                                        <span style="font-weight:bold;"><?php print $additional_cost; ?></span>
                                    </div>
                                <?php } ?>

                                <!-- arrival fees -->
                                <?php if(!empty($pricerange)){
                                if($pricerange->arrivalfees > 0){?>
                                <div class="clearer"></div>
                                <div class="notes-arrivalfees">
                                    <p>There is an At Arrival Fee of <strong><?php print $pricerange->currency." ".$pricerange->arrivalfees?></strong> for this property.</p>
                                </div>
                                <?php }}?>

								<!-- special terms -->
                                <div class="clearer"></div>
                                <?php if(isset($node->field_special_terms[$node->language][0]['value'])){ ?>
                                    <div class="notes-special-terms">
                                        <?php print $node->field_special_terms[$node->language][0]['value'];?>
                                    </div>
                                <?php } ?>

                                <!-- TAX-ID -->
                                <?php if(!empty($node->field_tax_id[$node->language][0]['value'])){ ?>
                                    <p style="font-weight:bold;margin-top:20px;">
                                        <?php print tax_id_class()->getTaxIDWithPrefix($node->field_tax_id[$node->language][0]['value']); ?>
                                    </p>
                                <?php } ?>
                                <!-- TAX-ID -->

                                <!-- checkin checkout time information -->
                                <?php echo $cicotime ?>

                                <!-- check-in check-out day -->
                                <?php if (isset($cico)) { ?>
                                    <div class="clearer"></div>
                                    <div class="notes-special-terms">
                                        <p><b>Please note these Check-in Day Requirements for this property: </b></p>
                                        <ul>
                                            <?php foreach ($cico as $cico_info):?>
                                            <li><?php print $cico_info;?></li>
                                            <?php endforeach;?>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <!-- END - Property Body Content -->

                    </div>

                    <!-- START - Property Maps -->
                    <div id="nav-prop-location" class="row-fluid section-nav">
                        <div class="span12">
                            <h4 class="title-section-prop-detail"> <i class="fa fa-map-marker" aria-hidden="true"></i> Location</h4>
                            <div id="map"></div>
                        </div>
                    </div>
                    <!-- END - Property Maps -->

                    <!-- START - Facilities section -->
                    <div id="nav-prop-facilities" class="row-fluid section-nav">
                        <div class="span12">
                            <h4 class="title-section-prop-detail"><i class="fa fa-list-ul" aria-hidden="true"></i> Facilities</h4>
                            <div class="facilities-list-content">
                                <div class="row-fluid">
                                    <div class="facilities-title">Accommodations</div>
                                    <div class="span12">
                                        <ul>
                                        <?php
                                            $show_all = $show_some = '';
                                            $start_column_section = '<div class="span4">';
                                            $end_column_section = '</div>';
                                            $some_total = 6;
                                            $some_percolumn = 2;
                                            if (isset($accomodation)) {
                                                $accomodation_filter = array_filter($accomodation, function($var){
                                                    return !preg_match('/(Bathrooms)|(Bedrooms)|(Pool)|(Pets Allowed)|(Sleep)/', $var);
                                                });
                                                asort($accomodation_filter);
                                                $accomodation_filter = array_values($accomodation_filter);
                                                $total_item = count($accomodation_filter);
                                                $split_percolumn = ceil($total_item/3);
                                                $count = 0;
                                                foreach ($accomodation_filter as $acckeys => $gr) {
                                                    $exp = explode(':', $gr);
                                                    if(count($exp) == 2) $gr = $exp[0] . '<span class="other_value">' . $exp[1] . '</span>';
                                                    $count++;
                                                    $facility_list = facilitiesList_HTML($count, $split_percolumn, $total_item, $accomodation_filter, $gr);
                                                    $show_all .= $facility_list['show_all'];
                                                    $show_some .= $facility_list['show_some'];

                                                }
                                            }
                                        ?>

                                        <div id="facility-accomodations">
                                            <?php
                                                if($count > $some_total) {
                                                    print $show_some;
                                                    drupal_add_js(array(
                                                        'show_all_accomodations' => $show_all,
                                                        'show_some_accomodations' => $show_some),
                                                    'setting');
                                                } else {
                                                    print $show_all;
                                                }
                                             ?>
                                        </div>

                                        </ul>
                                    </div>
                                    <?php
                                        if($count > $some_total)  {
                                            print '<div class="span12 text-center show-more">
                                                        <span id="show-more-btn-accomodations">+show more</span>
                                                    </div>';
                                        }
                                    ?>
                                </div>
                                <div class="row-fluid">
                                    <div class="facilities-title margin-top-10">Amenities</div>
                                    <div class="span12">
                                        <ul>
                                        <?php
                                            $show_all = $show_some = '';
                                            $amenities_field = $group['okamenities'];
                                            if (isset($amenities_field[$node->language])) {
                                                $amenities = $amenities_field[$node->language];
                                                asort($amenities);
                                                $total_item = count($amenities);
                                                $split_percolumn = ceil($total_item/3);
                                                $count = 0;
                                                foreach ($amenities as $amkeys => $am) {
                                                    $exp = explode(' -- ', $am['value']);
                                                    if(count($exp) == 2) $am['value'] = $exp[0] . ' <span class="other_value">' . $exp[1] . '</span>';
                                                    $count++;
                                                    $facility_list = facilitiesList_HTML($count, $split_percolumn, $total_item, $amenities, $am['value']);
                                                    $show_all .= $facility_list['show_all'];
                                                    $show_some .= $facility_list['show_some'];
                                                }
                                            } else {
                                                $amenities = $amenities_field;
                                                asort($amenities);
                                                $total_item = count($amenities);
                                                $split_percolumn = ceil($total_item/3);
                                                $count = 0;
                                                if(is_array($amenities) && !empty($amenities)){
                                                    foreach ($amenities as $amkeys => $am) {
                                                        $exp = explode(' -- ', $am);
                                                        if(count($exp) == 2) $am = $exp[0] . ' <span class="other_value">' . $exp[1] . '</span>';
                                                        $count++;
                                                        $facility_list = facilitiesList_HTML($count, $split_percolumn, $total_item, $amenities, $am);
                                                        $show_all .= $facility_list['show_all'];
                                                        $show_some .= $facility_list['show_some'];
                                                    }
                                                }
                                            }
                                        ?>

                                        <div id="facility-amenities">
                                            <?php
                                                if($count > $some_total) {
                                                    print $show_some;
                                                    drupal_add_js(array(
                                                        'show_all_amenities' => $show_all,
                                                        'show_some_amenities' => $show_some),
                                                    'setting');
                                                } else {
                                                    print $show_all;
                                                }
                                             ?>
                                        </div>
                                    </ul>
                                    </div>
                                    <?php
                                        if($count > $some_total)  {
                                            print '<div class="span12 text-center show-more">
                                                        <span id="show-more-btn-amenities">+show more</span>
                                                    </div>';
                                        }
                                    ?>
                                </div>
                                <div class="row-fluid">
                                    <div class="facilities-title margin-top-10">Entertainment</div>
                                    <div class="span12">
                                        <ul>
                                        <?php
                                            $show_all = $show_some = '';
                                            $entertainment_field = $group['okentertainments'];
                                            if (isset($entertainment_field[$node->language])) {
                                                $entertainment = $entertainment_field[$node->language];
                                                asort($entertainment);
                                                $total_item = count($entertainment);
                                                $split_percolumn = ceil($total_item/3);
                                                $count = 0;
                                                foreach ($entertainment as $entkeys => $en) {
                                                    $exp = explode(' -- ', $en['value']);
                                                    if(count($exp) == 2) $en['value'] = $exp[0] . ' <span class="other_value">' . $exp[1] . '</span>';
                                                    $count++;
                                                    $facility_list = facilitiesList_HTML($count, $split_percolumn, $total_item, $entertainment, $en['value']);
                                                    $show_all .= $facility_list['show_all'];
                                                    $show_some .= $facility_list['show_some'];

                                                }
                                            } else {
                                                $entertainment = $entertainment_field;
                                                asort($entertainment);
                                                $total_item = count($entertainment);
                                                $split_percolumn = ceil($total_item/3);
                                                $count = 0;
                                                if(is_array($entertainment) && !empty($entertainment)){
                                                    foreach ($entertainment as $entkeys => $en) {
                                                        $exp = explode(' -- ', $en);
                                                        if(count($exp) == 2) $en = $exp[0] . ' <span class="other_value">' . $exp[1] . '</span>';
                                                        $count++;
                                                        $facility_list = facilitiesList_HTML($count, $split_percolumn, $total_item, $entertainment, $en);
                                                        $show_all .= $facility_list['show_all'];
                                                        $show_some .= $facility_list['show_some'];
                                                    }
                                                }
                                            }
                                        ?>

                                        <div id="facility-entertainment">
                                            <?php
                                                if($count > $some_total) {
                                                    print $show_some;
                                                    drupal_add_js(array(
                                                        'show_all_entertainment' => $show_all,
                                                        'show_some_entertainment' => $show_some),
                                                    'setting');
                                                } else {
                                                    print $show_all;
                                                }
                                             ?>
                                        </div>

                                        </ul>
                                    </div>
                                    <?php
                                        if($count > $some_total)  {
                                            print '<div class="span12 text-center show-more">
                                                        <span id="show-more-btn-entertainment">+show more</span>
                                                    </div>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- START - availibility section -->
                     <div id="nav-prop-availability" class="row-fluid section-nav">
                        <div class="span12">
                            <h4 class="title-section-prop-detail"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Availability</h4>
                            <div class="row-fluid">
                                <div class="span12">
                                    <div class="calendar-availability-wrapper"></div>
                                    <div id="loading-cal-avail-wrapper" class="loading-style-v1">
                                        <div class="loading-content">
                                            <img src="/<?php print $calendar_path; ?>/eternicode/loader.gif"/> <span>Loading ...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row-fluid">
                                <div class="span12">
                                    <div class="nav-legend-cal-wrapper bottom-cal-avail-wrapper">
                                        <div class="calendar-avail-nav-wrapper center">
                                            <span id="prev-btn-cal-avail-bottom" class="cal-avail-nav-trigger calendar-avail-nav-disable"><i class="fa fa-chevron-left" aria-hidden="true"></i>prev</span>
                                            <span id="next-btn-cal-avail-bottom" class="cal-avail-nav-trigger calendar-avail-nav-disable">next<i class="fa fa-chevron-right" aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                    <!-- END - availibility section -->
                </div>


                <!-- price and top book now button -->
                <div class="span4 prop-right-sidebar">
                    <div class="calendar-rightsidebar">
                        <!-- price -->
                        <?php
                            $valcurrency = variable_get('currencyvalue',array());
                            $arrivalfees = variable_get('arrivalfees',array());
                            $setprice='';
                            if (!empty($pricerange)) {
                                if ($pricerange->priceto != $pricerange->pricefrom) {
                                    $setprice = "$" . ceil($pricerange->pricefrom) . "-$" . ceil($pricerange->priceto);
                                } else {
                                    $setprice = "$" . ceil($pricerange->pricefrom);
                                }
                            }

                            if (isset($accomodation)) {
                              $mtne = array();
                              foreach ($accomodation as $acckeys => $gr2) {
                                  if (preg_match('/(Bathrooms)|(Bedrooms)|(Pets Allowed)|(Sleep)/', $gr2)) {
                                      $item = explode(' : ', $gr2);
                                      $mtne[trim($item[0])] = $item[1];
                                  }
                              }
                           }

                           $featured_var = json_decode(variable_get('featured_filter'), true);
                           $amenities_field = $node->field_amenities;
                           isset($amenities_field[$node->language]) ? $amenities = $amenities_field[$node->language] : $amenities = null;
                           if (isset($amenities)) {
                                   $ameno = array();
                                   foreach ($amenities as $amkeys => $am) {
                                        if (in_array($am['value'], $featured_var['Hot Tub'])) {
                                            $ameno['Hot Tub'][] = $am['value'];
                                        } elseif(in_array($am['value'], $featured_var['Pool'])) {
                                            $ameno['Pool'][] = $am['value'];
                                        }
                                   }
                           }
                        ?>
                        <div class="row-fluid">
                            <div class="span12">
                            <div id="e11296right">
                            <div class="property-detail" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                        <meta itemprop="priceCurrency" content="USD" />
                                            <h1 class="pricepernight ext-center nomargin"><span class="price"><meta itemprop="price" content="<?php print $pricefrom?>" /><?php print $setprice; ?></span> <span>/night</span></h1>
                                            <div class="minimal-stay">
                                                <span> Minimum Nights Stay : <span class="edit-req-minstay"> - </span> </span>
                                            </div>
                                            <div class="row-fluid">
                                                <div class="span12">
                                                    <div class="property-details-container">
                                                        <ul class="nav nav-list">
                                                            <?php if (isset($mtne['Bedrooms'])) { ?>
                                                            <li class="liste">
                                                                <span class="srclabel bgcolon"><?php print ($mtne['Bedrooms'] > 1) ? 'Bedrooms' : 'Bedroom'; ?></span>
                                                                <span class="badge"><?php print $mtne['Bedrooms']; ?></span>
                                                            </li>
                                                            <?php } ?>
                                                            <?php if (isset($mtne['Bathrooms'])) { ?>
                                                                <li class="liste">
                                                                    <span class="srclabel bgcolon"><?php print ($mtne['Bathrooms'] > 1) ? 'Bathrooms' : 'Bathroom'; ?></span>
                                                                    <span class="badge"><?php print $mtne['Bathrooms']; ?></span>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if (isset($mtne['Sleeps'])) { ?>
                                                                <li class="liste">
                                                                    <span class="srclabel bgcolon"><?php print ($mtne['Sleeps'] > 1) ? 'Sleeps' : 'Sleep'; ?></span>
                                                                    <span class="badge"><?php print $mtne['Sleeps']; ?></span>
                                                                </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                    <div class="property-details-container">
                                                        <ul class="nav nav-list">
                                                            <?php if(isset($node->taxonomy_vocabulary_3['und'][0]['taxonomy_term']->name)) { ?>
                                                                <li class="liste"><span class="srclabel bgcolon">Type</span>&nbsp;<span class="label label-inverse fcap"><?php print $node->taxonomy_vocabulary_3['und'][0]['taxonomy_term']->name; ?></span>
                                                                </li>
                                                            <?php } ?>
                                                            <?php if( (isset($node->taxonomy_vocabulary_4['und'][0]['taxonomy_term']->name)) && $node->taxonomy_vocabulary_4['und'][0]['taxonomy_term']->name != "other view") { ?>
                                                                <li class="liste"><span class="srclabel bgcolon">View</span>&nbsp;<span class="label label-inverse fcap"><?php print $node->taxonomy_vocabulary_4['und'][0]['taxonomy_term']->name; ?></span>
                                                                </li>
                                                            <?php } ?>
                                                            <?php
                                                                if(( !empty($mtne['Pets Allowed']) && strtolower($mtne['Pets Allowed']) == 'yes') || !empty($ameno['Pool']) || !empty($ameno['Hot Tub'])) {
                                                                    print '<li class="liste">';
                                                                        if(!empty($ameno['Pool'])) { print '<span class="label label-info">Pool</span>'; }
                                                                        if(!empty($ameno['Hot Tub'])) { print '<span class="label label-info">Hot Tub</span>'; }
                                                                        if(!empty(isset($mtne['Pets Allowed']) && strtolower($mtne['Pets Allowed']) == 'yes')) { print '<span class="label label-info">Pets OK</span>'; }
                                                                    print '</li>';
                                                                }
                                                            ?>
                                                            <?php if( strpos( $freeCancel->getFreeCancelPackageName(), "yes" ) !== false ){ ?>
                                                                <li class="liste"><span class="label label-info freecancel">FREE CANCELLATION</span></li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                    </div>
                            </div>
                        </div>

                        <div class="row-fluid">
                            <div class="span12">
                                <div class="date-picker-containter">

                                   <input id="edit-inputdate1" type="hidden" name="inputdate1" value="" />
                                   <input id="edit-inputdate2" type="hidden" name="inputdate2" value="" />
                                   <input id="edit-departuredate" type="hidden" name="departuredate" value="" />
                                   <input id="edit-insurance" type="hidden" name="insurance" value="" />
                                   <input id="edit-default-clean-fee" type="hidden" name="default_clean_fee" value="" />
                                   <input id="edit-peak-count" type="hidden" name="peak_count" value="" />
                                   <input id="edit-off-count" type="hidden" name="off_count" value="" />
                                   <input id="edit-spl-count" type="hidden" name="spl_count" value="" />
                                   <input id="edit-sourcepage" type="hidden" name="sourcepage" value="" />
                                   <input id="edit-hidden-uid" type="hidden" name="hidden_uid" value="" />
                                   <input type="hidden" id="edit-hidden-id" name="hidden_id" value="<?php print $nid; ?>">

                                    <h3>Choose Your Dates</h3>
                                    <div class="row-fluid">
                                     <div class="span12 datepicker-wrapper">
                                       <div class="input-daterange rentalpage" id="datepicker">
                                          <input type="text" class="input-medium chcknc" name="start" id="chckn" placeholder="Check-in" autocomplete="off" readonly="readonly" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>
                                          <input type="text" class="input-medium chcktc" name="end" id="chckt" placeholder="Check-out" autocomplete="off" readonly="readonly" <?php print ($non_usd_check ? 'disabled="disabled"' : "") ?> style="background: rgb(238, 238, 238);">
                                       </div>
                                     </div>
                                    <div class="row-fluid">
                                       <div class="span12 datepicker-wrapper">
                                       <div class="input-daterange rentalpage" id="datepicker2">
                                           <select id="edit-input-guests" class="guest" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>
                                               <?php if(!empty($sleepmax)) { ?>
                                                 <?php for($i=1;$i <= $sleepmax;$i++){ ?>
                                                   <option value="<?php print $i; ?>" <?php print ($i == $count_guests ? ' selected ' : '') ?>><?php print $i . ($i > 1 ? ' Adults' : ' Adult'); ?> </option>
                                                 <?php } ?>
                                               <?php } ?>
                                           </select>
                                          <select id="edit-input-child-guests" class="child" <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?>>
                                               <?php if(!empty($sleepmax)) { ?>
                                                   <option value="0" selected> 0 Children</option>
                                               <?php } ?>
                                           </select>
                                       </div>
                                    </div>
                                     <div class="row-fluid">
                                       <div class="checkbox-date-book">
                                           <label style="display:inline-block;" class="option checkbox control-label" for="edit-agreetravelins">
                                               <input <?php print ($non_usd_check ? 'disabled="disabled" style="background-color:rgb(238, 238, 238);"' : ''); ?> type="checkbox" id="edit-agreetravelins" name="agreetravelins" value="1" class="form-checkbox">Protect your stay with Travel Insurance
                                               <span data-target="#pyswti" role="button" class="badge badge-info tiqst rp-tiqst" data-toggle="modal">?</span>
                                           </label>
                                       </div>
                                    </div>
                                   </div>
                                  </div>
                                </div>
                            </div>
                        </div>
                        <div class="row-fluid loading-wrapper">
                            <div class="col-lg-12">
                                <div class="loading">
                                    <img src="/<?php print $calendar_path; ?>/eternicode/loader.gif"/> <span>Calculating...</span>
                                </div>
                            </div>
                        </div>
                        <div class="notif-error-msg-cal">
                        </div>
                        <div id="calendar-result" class="row-fluid price-calculation-result">
                         <div class="row-fluid">
                            <div class="span12">
                                <div class="upper-charges-detail-wrapper">
                                    <table class="upper-charges-detail">
                                        <tbody>
                                            <tr>
                                                <td><i style="margin-right:1px;" class="fa fa-moon-o" aria-hidden="true"></i> Total Nights</td>
                                                <td><span class="total-nights"></span></td>
                                            </tr>
                                            <tr>
                                                <td><i style="margin-right:4px;" class="fa fa-dollar" aria-hidden="true"></i> Daily Rate Charges</td>
                                                <td><span class="dailyRatesChrg"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <table class="middle-charges-detail">
                                    <tbody>
                                        <tr class="item-calculation-result">
                                            <td>Rent ($)</td>
                                            <td><input readonly="readonly" type="text" class="edit-rental-charges" name="edit-rental-charges"/></td>
                                        </tr>
                                        <tr class="item-calculation-result">
                                            <td>Cleaning Fee ($) <span data-target="#clnf" role="button" class="badge badge-info" data-toggle="modal">?</span></td>
                                            <td><input readonly="readonly" type="text" class="edit-clean-fee" name="edit-clean-fee"/></td>
                                        </tr>
                                        <tr class="item-calculation-result">
                                            <td>Taxes ($)</td>
                                            <td><input readonly="readonly" type="text" class="edit-rental-tax" name="edit-rental-tax"/></td>
                                        </tr>
                                        <tr class="item-calculation-result travel-insurance-line">
                                            <td>Travel Insurance ($)</td>
                                            <td><input readonly="readonly" type="text" class="edit-travelinsurance" name="edit-travelinsurance"/></td>
                                        </tr>
                                        <tr class="total-calculation-result">
                                            <td>Total ($)</td>
                                            <td><input readonly="readonly" type="text" class="edit-booking-charges" name="edit-booking-charges"/></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="row-fluid">
                                    <div class="well agreement-wrapper">
                                        <div class="agreement-content">
                                            <?php print $tmsummary ?>
                                            <label class="option checkbox control-label" for="edit-agree"> <input type="checkbox" id="edit-agree" name="agree" value="1" class="form-checkbox"> I Agree to the <span data-target="#rntlagree" role="button" class="rental-agreement-modal" data-toggle="modal">Rental Agreement terms</span> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($non_usd_check): ?>
                        <div class="row-fluid">
                            <div class="span12">
                                <div style="margin-top:3px;margin-bottom:7px;" class="alert alert-info">
                                    <div style="text-align:center;" class="err-msg-cal">
                                        <p style="text-align:center;">
                                            <?php print $non_usd_check; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                     <?php endif; ?>

                    <div class="row-fluid">
                        <div class="span12">
                            <div id="tomove">
                                <!-- book now button -->
                                <div class="nrp-btntop text-center">
                                <!-- <p><a title="Get Price & Book Now!" class="booking-button rpbooknow btn btn-large btn-cstblock" href="<?php print url('property-booking/' . $node->nid); ?>">Book Now</a></p>-->
                                    <button class="buuking booking-button rpbooknow btn btn-large btn-cstblock fullw btn form-submit" id="edit-submit" name="op" value="Book Now" type="submit" disabled="disabled" itemprop="availability" href="http://schema.org/InStock">Book Now</button>
                                    <p class="aside">
                                    <small>Properties are going fast!</small><br/>
                                    <?php if(module_exists('sugarcrm') && function_exists('sugarcrm_render_modal')) { ?>
                                    <a title="Inquire About This Property" class="askquest btn btn-small" href="#" data-toggle="modal" data-target="#leadsModal">Inquire About This Property</a>
                                    <?php } else { ?>
                                    <a title="Inquire About This Property" class="askquest btn btn-small" href="#" onclick="return SnapEngage.startLink();">Inquire About This Property</a>
                                    <?php }?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

  </div>

    <!-- comments/reviews -->
    <?php
        $comments = render($content['comments']);
        if(!empty($comments)){
    ?>
        <hr>
        <div class="row-fluid">
            <div class="span12">
                <?php print $comments; ?>
            </div>
        </div>
    <?php } ?>

</div><!-- /.node -->



<!-- Root element of PhotoSwipe. Must have class pswp. -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

    <!-- Background of PhotoSwipe.
         It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>

    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">

        <!-- Container that holds slides.
            PhotoSwipe keeps only 3 of them in the DOM to save memory.
            Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                <!--  Controls are self-explanatory. Order can be changed. -->

                <div class="pswp__counter"></div>

                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                <button class="pswp__button pswp__button--share" title="Share"></button>

                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
                <!-- element will get class pswp__preloader--active when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>

            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>

            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>

        </div>

    </div>

</div>


<div class="modal hide fade" id="siModal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3><?php print $node->title; ?></h3>
    </div>
    <div class="modal-body">

    </div>
    <?php print $mapmsg; ?>
</div>

    <div id="pyswti" class="cstmodal-large modal hide fade" tabindex="-1" role="dialog" aria-labelledby="pyswtiLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="pyswtiLabel">Travel Insurance</h3>
        </div>
        <div class="modal-body"> <?php print $travelInsurancepopupText['value']; ?> </div>
        <div class="modal-footer">
            <div class="text-center">
                <h3>From the Moment You Book To the Moment You Return Home Safely . . .<br/>We&rsquo;ve Got You Covered</h3>
                <p><a class="btn btn-info" href="/travel-insurance" style="color:#fff; outline:none;" target="_blank" title="Learn More - Travel Insurance">Learn More</a></p>
            </div>
        </div>
    </div>

    <div id="clnf" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="clnfLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="pyswtiLabel">Cleaning Fees</h3>
        </div>
        <div class="modal-body"> <?php print $cleaningfeepopupText; ?></div>
    </div>

    <div id="aaf" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="aafLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="pyswtiLabel">At Arrival Fees</h3>
        </div>
        <div class="modal-body"> <?php print $arrivalfeetext['value']; ?></div>
    </div>

    <div id="rgr" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="rgrLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-body">
            <div class="text-center">
                <a title="RedAwning Rental Agreement" target="_blank" href="/terms">RedAwning Rental Agreement</a>
            </div>
        </div>
        <div class="modal-footer">
            <div class="text-center">
                <button id="btn-continue-book" class="btn btn-success">Agree and Continue</button>
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>

    <div id="rntlagree" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="clnfLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="pyswtiLabel"> <?php print $tmttl;?> </h3>
        </div>
        <div class="modal-body"> <?php print $tmbody;?> </div>
    </div>

    <div id="resultmsg" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="clnfLabel" aria-hidden="true">
        <div class="modal-body" style="background-color:#f2dede;color:#b94a48;">

        </div>
        <div class="modal-footer">
            <div class="text-center">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>

    <!-- Mapbox --> 
    <script> 
        mapboxgl.accessToken = '<?php echo theme_get_setting("mapboxapi") ?>'; 
        var map = new mapboxgl.Map({ 
            container: 'map', 
            style: 'mapbox://styles/mapbox/streets-v10', 
            zoom: 13, 
            center: [<?php echo $LngLat;?>] 
        }); 
        var geojson = { 
            type: 'FeatureCollection', 
            features: [{ 
                type: 'Feature', 
                geometry: { 
                    type: 'Point', 
                    coordinates: [<?php echo $LngLat;?>] 
                }, 
                properties: { 
                    description: '<?php echo $fddrap1;?>' 
                } 
            }], 
        } 
        geojson.features.forEach(function(marker) { 
 
            // create a HTML element for each feature 
            var el = document.createElement('div'); 
            el.className = 'marker'; 
            <?php if ( user_is_logged_in() ) {
                echo "el.className = 'admin-marker'; ";
            } ?>
    
            // make a marker for each feature and add to the map 
            new mapboxgl.Marker(el) 
            .setLngLat(marker.geometry.coordinates) 
            <?php if ( user_is_logged_in() ) {
                echo " .setPopup(new mapboxgl.Popup({ offset: 25 }) // add popups 
                .setHTML('$fddrap1')) ";
            }?>
            .addTo(map); 
        }); 
    </script> 
<?php if(module_exists('sugarcrm') && function_exists('sugarcrm_render_modal')) sugarcrm_render_modal();?>

<!-- <script type="application/ld+json">
{
        "@context": "http://schema.org/",
        "@type": "Product",
        "name": "<?php print $title?>",
        "image": [
            <?php print $allImages ?>
        ],
        "description": "<?php print urldecode(str_replace(["%0D","%0A"], " ",  $descr)) ?>",
        "aggregateRating": {

        },
        "offers": {

        }
}
</script>