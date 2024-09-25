<!-- ra_bootstrap_subtheme\templates\search-result.tpl.php -->
<?php
global $base_url;
global $user;
if (in_array('editor', $user->roles)) {
    $toclbtn = ' editor';
} else {
    $toclbtn = ' noteditor';
}
$property_image = $result['fields']['ss_my_contenttype_image'];
$city = !empty($result['fields']['sm_locs_city'][0]) ? $result['fields']['sm_locs_city'][0] : $result['fields']['sm_vid_Region'][0];
$bedrooms = $result['fields']['sm_field_bedrooms'][0];
$bathrooms = $result['fields']['sm_field_bathrooms'][0];
$freecancel = $result['fields']['bs_freecancel'];
$cancelpackagename = $result['fields']['ss_freecancel_packagename'];
$content = $result['fields']['content'];
$weight = ' issw' . $result['fields']['is_searchweight'];
$content = strip_tags(html_entity_decode($content, ENT_QUOTES, 'UTF-8'));
$rslt_pricefrom = ceil($result['fields']['ss_setfrom_price']);
$rslt_priceto = ceil($result['fields']['ss_setto_price']);
$query_parameters = drupal_get_query_parameters();
$sspriceminstay = array();
$mstyn = isset($result['fields']['is_minstay']) ? $result['fields']['is_minstay'] : '';
if (isset($query_parameters['dates'])) {
    $dates = explode('TO', $query_parameters['dates']);
    $ci = $dates[0];
    $co = $dates[1];
    $ts_price = json_decode(isset($result['fields']['ts_price']) ? $result['fields']['ts_price']:$result['fields']['ss_price']);
    $prices = array();
    foreach ($ts_price->rates as $key => $value) {
        $start = strtotime($value->start);
        $end = strtotime($value->end);
        if ($start <= $ci && $end >= $ci) {
            $prices[] = $value->pricing->dailyweekday;
            $sspriceminstay[] = $value->pricing->minstay;
        }
        if ($start >= $ci && $end <= $co) {
            $prices[] = $value->pricing->dailyweekday;
            $sspriceminstay[] = $value->pricing->minstay;
        }
        if ($start <= $co && $end >= $co) {
            $prices[] = $value->pricing->dailyweekday;
            $sspriceminstay[] = $value->pricing->minstay;
        }
    }
    if (!empty($prices)) {
        $rslt_pricefrom = min($prices);
        $rslt_priceto = max($prices);
    }
    if (!empty($sspriceminstay)) {
        $mstyn = array_unique($sspriceminstay);
    }
}
$mstyn = (isset($mstyn[0]))?$mstyn[0]:$mstyn;
$rslt_sleeps = $result['fields']['is_field_sleeps_max'];
$loc = explode(',', $result['fields']['locs_field_geolocation']);
$rslt_longitude = isset($loc[0]) ? $loc[0] : '0.0';
$rslt_latitude = isset($loc[1]) ? $loc[1] : '0.0';
$count = count($property_image);
$notitle = 'no image';
$src = $base_url . '/' . path_to_theme() . '/images/no-image.jpg';
$currenturi = parse_url(request_uri());
$currentUri = $currenturi['path'];
$rslt_minstay = !empty($result['fields']['is_minstay']) ? 'minsty' . $result['fields']['is_minstay'] : 'noms';
//fields for new layout
$propertyid = $result['fields']['entity_id'];
$pool = isset($result['fields']['sm_field_pool']) ? $result['fields']['sm_field_pool'][0] : '';
$hotTub = '';
if (!empty($result['fields']['sm_field_amenities'])) {
    if (in_array("Hot Tub", $result['fields']['sm_field_amenities'])) {
        $hotTub = 'Hot Tub';
    }
}
$petsOk = isset($result['fields']['sm_field_pets_ok'][0]) ? $result['fields']['sm_field_pets_ok'][0] : '';
$featured = isset($result['fields']['sm_featured']) ? $result['fields']['sm_featured'] : array();
$rpType = isset($result['fields']['sm_vid_Type'][0]) ? $result['fields']['sm_vid_Type'][0] : '';
$length = 70;
$rslt_price = '';
if (!empty($rslt_pricefrom)) {
    if ($rslt_priceto != $rslt_pricefrom) {
        $rslt_price = "$" . $rslt_pricefrom . " to " . "$" . $rslt_priceto . "/night";
    } else {
        $rslt_price = "~$" . $rslt_pricefrom . "/night";
    }
}

if (preg_match('|search/properties|', $currentUri) || preg_match('|category|', $currentUri)) {
    ?>

    <?php if (!empty($title)) { ?>
        <li class="<?php print $rslt_minstay . $weight; ?>">
            <div class="wrpeachrslt">
                <div class="propimg text-center">
                    <a href="<?php print $url; ?>" title="<?php print $title; ?>">

                        <?php if ($count > 0) { ?>
                            <?php if (!file_exists($property_image)) { ?>
                                <!-- default no-image thumbnail -->
                                <img src="<?php echo $src; ?>" alt="<?php print $notitle; ?>" width="240" />
                            <?php } else { ?>
                                <!-- default image thumbnail -->
                                <img src="<?php print image_style_url('w240hauto', $property_image); ?>" alt="<?php print $title; ?>" />
                            <?php } ?>
                        <?php } ?>

                    </a>
                </div>
                <div class="search-result-info-wrapper">
                    <div class="searchresulttitle">
                        <a title="<?php print $title; ?>" href="<?php print $url; ?>"><?php print strlen($title) > 55 ? substr($title, 0, 56) . '...' : $title; ?></a>
                    </div>
                    <div class="mid">
                        <div class="content">
                            <?php if ($snippet) : ?>
                                <p class="search-snippet">
                                    <?php
                                    if (arg(2) == 'yes') {
                                        print $result_teaser;
                                    } else {
                                        //print title for search result
                                        print '<strong>Sleeps ' . $rslt_sleeps . ', ' . $rslt_price . '</strong>';
                                    }
                                    ?>
                                </p>
                                <?php if(!empty($mstyn) && !empty($dates)){ ?>
                                <div>
                                    <span class="srclabel">Minimum Stay</span>
                                    <span class="srcinfo srccity">: <?php print $mstyn; ?> <?php echo ($mstyn > 1)?"nights":"night" ?></span>
                                </div>
                                <?php } ?>
                                <div>
                                    <span class="srclabel">City</span>
                                    <span class="srcinfo srccity">: <?php print $city; ?></span>
                                </div>
                                <div>
                                    <span class="srclabel"><?php print (int) $bedrooms > 1 ? "Bedrooms" : " Bedroom"; ?></span>
                                    <span class="srcinfo srcbedrooms">: <?php print $bedrooms; ?></span>
                                </div>
                                <div>
                                    <span class="srclabel"><?php print (int) $bathrooms > 1 ? "Bathrooms" : " Bathroom"; ?></span>
                                    <span class="srcinfo srcbathrooms">: <?php print $bathrooms; ?></span>
                                </div>
                                <?php if (!empty($featured)) { ?>
                                    <div class="badges-container">
                                        <?php
                                        foreach ($featured as $key => $value) {
                                            print '<span class="badge badge-info srcameni">' . $value . '</span> ';
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                                <?php if(!empty($freecancel)){ ?>
                                    <div class="freecancel-container">
                                        <span class="badge badge-info srcameni freecancel">FREE CANCELLATION</span>
                                    </div>
                                <?php } ?>
                                <?php $cutContent = preg_replace('/\s+?(\S+)?$/', '', substr($content, 0, $length)); ?>
                                <div class="taxo taxodesc"><p><?php print $cutContent; ?>...</p></div>
                                <div class="taxo taxomore">
                                    <span class="btn taxo taxosom select<?php print $result['fields']['entity_id'] ?><?php print $toclbtn; ?>">
                                        <a class="showonmap" title="View on Map" href="#taxonomymapo">
                                            <span style="display:none;"><img class="imgsom" src="/<?php print path_to_theme(); ?>/images/gmapico.png" alt="view on map"/> </span>View on Map
                                        </a>
                                    </span>
                                    <a title="Check Availability" href="/property-booking/<?php print $propertyid; ?>" class="rpbooknow btn btn-cta pull-right<?php print $toclbtn; ?>" style="display:none;">Check Availability</a>
                                    <a title="<?php print $title; ?> - Property Details" href="<?php print $url; ?>" class="sr-moreinfo btn btn-success pull-right<?php print $toclbtn; ?>">Property Details</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <?php
    }
}
?>
