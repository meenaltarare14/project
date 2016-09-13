<?php
if($_REQUEST['tm']){

	$display_id = 'panel_pane_1';
	$view_name = 'broadsoft_content_search';
	$view = views_get_view($view_name);
	$view->set_display($display_id);
	$filters = $view->display_handler->get_option('filters');
	$filters['search_api_views_fulltext']['value'] = $_REQUEST['tm'];
	$view->display_handler->override_option('filters', $filters);
	$view->pre_execute();
	$view->execute();
	$output=$view->result;
	echo "<pre>";
	print_r($output);
	exit;


	$solr = apachesolr_get_solr();
	$query = apachesolr_drupal_query("apachesolr");
	$query->addParam('q', $_REQUEST['tm']); // keyword to be searched
	$query->addParam('hl', true); // enable highlighting
	$query->addParam('rows', '10');
	$response = $query->search();
	$raw_results = apachesolr_search_process_response($response, $query);
	echo "<pre>";
	print_r($raw_results);

	echo "=============Different Result================";

	$keys = $_REQUEST['tm'];
	$params['q'] = $keys;
	$context['page_id'] = $search_page['page_id'];
	$context['search_type'] = 'apachesolr_search_results';
	$results = apachesolr_search_run('apachesolr', $params, "", "search/site");
	//$search_page = apachesolr_search_page_load('core_search');
	//$results = apachesolr_search_search_results($keys, NULL, $search_page);



	print_r($results);
	exit;

}
?>
<script src="<?php print base_path().path_to_theme(); ?>/js/jquery.scrollNav.min.js"></script>
 <script type="text/javascript" src="<?php print base_path().path_to_theme(); ?>/js/jquery.jcarousel.min.js"></script>
 <script>
	(function ($) {
  Drupal.behaviors.yourBehaviorName = {
    attach: function (context, settings) {

    //jQuery('.liststyle-doc').scrollNav();
	//jQuery('#dateNav').scrollspy({ target: '#spyOnThis' })

	var header = jQuery(".fixed-row");
	jQuery(window).scroll(function() {
        var scroll = jQuery(window).scrollTop();

        if (scroll >= 150) {
            jQuery(".fixed-row").addClass("pullheader");
        } else {
            jQuery(".fixed-row").removeClass("pullheader");
        }
		setTimeout(showRelatedContent,1200);
    });

	jQuery('a[href*="#"]:not([href="#"])').click(function() {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {


      var target = jQuery(this.hash);
      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
      if (target.length) {
        jQuery('html, body').animate({
          scrollTop: target.offset().top
        }, 1000, "linear");
        return false;
      }
		setTimeout(showRelatedContent,1200);
    }
  });

  jQuery(window).on("scroll", function() {
  	var scrollHeight = jQuery(document).height();
  	var scrollPosition = jQuery(window).height() + jQuery(window).scrollTop();
  	if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
  	    jQuery(".fixed-row").addClass("pullbottom");
        jQuery(".foot-scroll").addClass("pushup");
        jQuery("#foot-scroll-link").addClass("notfixedtofoot");

  	} else {
      jQuery(".fixed-row").removeClass("pullbottom");
      jQuery(".foot-scroll").removeClass("pushup");
      jQuery("#foot-scroll-link").removeClass("notfixedtofoot");
    }
  });

  jQuery('#foot-scroll-link').click(function() {
        jQuery('.foot-scroll').slideToggle("fast");
        jQuery('#foot-scroll-link').toggleClass("fixedtofoot");
        e.stopPropagation();
});



  jQuery('#download-pdf').click(function(e) {

  });

  jQuery('ul.liststyle-doc a').click(function(e) {

    e.stopPropagation();
  });

    }
  };
})(jQuery);
</script>



<script >
function showRelatedContent(){
   jQuery('.jcarousel li').hide();
    //var dms=jQuery('ul.liststyle-doc > li .active:eq(0)').html();
  var dms=jQuery('.liststyle-doc .active:eq(0)').find('a').attr('href');
  if(typeof dms!='undefined' && dms!=''){
      dms=dms.replace("#","");
      jQuery('.'+dms).show();
  }

}

(function($) {
    jQuery(function() {
        /*
        Carousel initialization
        */
        jQuery('.jcarousel')
            .jcarousel({
                // Options go here
            });

        /*
         Prev control initialization
         */
        jQuery('.jcarousel-control-prev')
            .on('jcarouselcontrol:active', function() {
                jQuery(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                jQuery(this).addClass('inactive');
            })
            .jcarouselControl({
                // Options go here
                target: '-=1'
            });

        /*
         Next control initialization
         */
        jQuery('.jcarousel-control-next')
            .on('jcarouselcontrol:active', function() {
                jQuery(this).removeClass('inactive');
            })
            .on('jcarouselcontrol:inactive', function() {
                jQuery(this).addClass('inactive');
            })
            .jcarouselControl({
                // Options go here
                target: '+=1'
            });

        /*
         Pagination initialization
         */
        jQuery('.jcarousel-pagination')
            .on('jcarouselpagination:active', 'a', function() {
                jQuery(this).addClass('active');
            })
            .on('jcarouselpagination:inactive', 'a', function() {
                jQuery(this).removeClass('active');
            })
            .jcarouselPagination({
                // Options go here
            });
    });
})(jQuery);

</script>


 <style>

img {
max-width:100%;
}

body.layout-2 .navbar-inverse{
  float: left;
width: 100%;
border-radius: 0px;
margin-bottom: 0px;
}

.dashboard-breadcrumbs.xchange{
    padding: 2em 2em 2em !important;
}

  .support-sol{
    width: 26%;
    text-align: left;
  }

  .support-sol .caret{
    float: right;
    margin-top: 6px;
  }

  .support-sol button{
    border: 1px solid #ddd;
    color: #3e3e3e;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
  }


.single-docu{
  position: relative;
  width: 100%;
  height: auto;
  padding-bottom: 5px;
  overflow: hidden;
  float: left;
}


.single-docu .col-md-9{
  height: auto;
  float: left;
  position: relative;
}


  ul.liststyle-doc{
    margin: 0px;
    padding: 0px;
        list-style: none;
  }

  ul.liststyle-doc li{
    width: 100%;
    float: left;
  }

  ul.liststyle-doc li.active{

  }

ul.liststyle-doc li.active ul{
  display:block !important;
}

  ul.liststyle-doc li a{
    padding: 18px;
    width: 100%;
    float: left;
    padding-left: 4em;
    position:relative;
    color:#282f39;
  }

  ul.liststyle-doc li a:hover{
    text-decoration: none;
  }

  ul.liststyle-doc li a:before{
    position:absolute;
    content: "\f054";
    font: normal normal normal 14px/1 FontAwesome;
    left: 2em;
    top: 20px;
  }


ul.liststyle-doc li ul li a:before{
  display: none;
}
  ul.liststyle-doc li.active a{
    border-right:4px solid #76c430;
    color: #76c430;
    font-weight: 700;
  }

  ul.liststyle-doc li.active a:before{
    content: "\f078";
  }

ul.liststyle-doc li.active a:before i{
  font-weight: bold;
}

ul.liststyle-doc li.active a li a{

}

ul.liststyle-doc li ul { display: none;
  list-style:none;
  transition: 0.2s 1s;
  margin-top: -5px;
 }

ul.liststyle-doc li > ul {
  position: relative;
  float: left;
  width: 100%;
  transition-delay: 0s;
}

ul.liststyle-doc li li a:hover { background: #ddd; }

ul.liststyle-doc li ul li { border-top: 0; }

ul.liststyle-doc li ul li.active a{
  font-weight: bold !important;
}



ul.liststyle-doc li ul li a{
  font-weight: 300 !important;
  border-right: 4px solid transparent !important;
  padding-top: 8px;
  padding-bottom: 8px;
  font-size: 12px;
  color: #333 !important;
}

ul.liststyle-doc li ul li.active a{
  border-right: 4px solid #76c430 !important;
  color: #76c430 !important;
}

ul.liststyle-doc:before, ul.liststyle-doc:after {
  content: " "; /* 1 */
  display: table; /* 2 */
}

ul.liststyle-doc:after { clear: both; }

.dashboard-breadcrumbs{
  float: left;
  background: #fff;
position: relative;
z-index: 999;
padding-top: 18px;
}

nav .navbar{
margin-bottom: 0px;
}

.current-sub-nav{
          background: #fff;
          float: left;
position: relative;
top:0px;
}
.single-docu:before{
  content: "";
  background: #f3f3f3;
  width: 25%;
  left:0px;
  position:absolute;
  height:100%;
}
.version-row{
  padding: 3em;
border-bottom: 1px solid #ddd;
float: left;
width: 103%;
}
.platfom-sol{
  border: 1px solid #ddd;
padding: 10px 2em;
border-radius: 3px;
width: auto;
float: left;
}

.platfom-sol i{
  padding: 0px 12px;
COLOR: #7fcc50;
font-size: 11px;
}

.version-drop button{
  border: 1px solid #ddd;
padding: 10px 2em;
color: #333;
float: right;
width: auto;
}

.version-drop .dropdown{
  position: relative;
  float: right;
}

.content-row .full-content{
    padding: 3em;
}

.content-row .full-content h1.doc-title{
  font-size: 22px;
    font-weight: 700;
    color: #7fcc50;

}

.full-content p,.full-content h1, .full-content h2, .full-content hr, .full-content ul{
  float:left;
  width: 100%;
}

.full-content .table-responsive{
  float: left;
  width: 100%;
}

.full-content img{
  float: left;
  display: block;
  clear:both;
}

.content-row .full-content h1.chapter{
  font-size: 21px;
    text-transform: uppercase;
    color: #444;
    font-weight: 800;
    position: relative;
    padding-bottom: 15px;
    width: 100%;
}

.content-row .full-content h1.chapter:before{


}

.content-row{
  padding-bottom: 9em;
}

h1 .chapter-number{
  background: #7fcc50;
  color: #fff;
  padding: 5px 9px;
  font-size: 18px;
  margin-right: 19px;
  margin-top: -3px;
  float: left;
  border-radius: 2px;
  text-align: center;
}

.content-row .full-content h2{
  font-size: 20px;
      text-transform: uppercase;
      color: #444;
      font-weight: 800;
      position: relative;
      padding-bottom: 15px;
      float: left;
      width: 100%;
}

.content-row .full-content h3{
  font-size: 19px;
    text-transform: uppercase;
    color: #333;
    font-weight: 800;
    position: relative;
    padding-bottom: 15px;
    float: left;
    width: 100%;
}

.content-row .full-content h2:before{
  content:"";
  position: absolute;
  border-left: 5px solid #7fcc50;
  left: 0px;
  height: 22px;
  display:none;
}

.content-row .full-content h3{
  font-size: 18px;
    text-transform: uppercase;
    color: #444;
    font-weight: 800;

    position: relative;
	margin-top: 20px;
    padding-bottom: 15px;
    float: left;
    width: 100%;
}

.content-row .full-content h3:before{
  content:"";
  position: absolute;
  border-left: 5px solid #7fcc50;
  left: 0px;
  height: 20px;
  display: none;

}

.content-row .full-content h4{
  font-size: 16px;
    text-transform: uppercase;
    color: #444;
    font-weight: 800;
    position: relative;
    padding-bottom: 15px;
    float: left;
    width: 100%;
}

.content-row .full-content h5{
  font-size: 14px;
    text-transform: uppercase;
    color: #444;
    font-weight: 800;
    position: relative;
    padding-bottom: 15px;
    float: left;
    width: 100%;
}

.content-row .full-content h6{
  font-size: 12px;
    text-transform: uppercase;
    color: #444;
    font-weight: 800;
    position: relative;
    padding-bottom: 15px;
    float: left;
    width: 100%;
}

.content-row{
  border-bottom: 1px solid #ddd;
  float: left;
  width: 100%;
  height: auto;
}

.private-row{
  padding: 9px 3em 17px;
  width: 100%;
  float: left;
}

.private-row h3{
      color: #d04a4a;
      font-size: 18px;
}

.private-row button{
  background: #7fcc50;
    padding: 12px;
    margin-top: 19px;
}

.share-side {
  text-align: center;
  width: 100%;
  float: left;
}

.share-side button{
  background: none;
  color: #f1952f;
  border: 2px solid #f1952f;
  margin: 13px auto;
  font-size: 12px;
  float: none;
  width: 100%;
}

.fixed-row{
  position: fixed;
  width: 25%;
  height: 87%;
  overflow-y: scroll;
  -webkit-transition: height 1s;
  -moz-transition: height 1s;
  -ms-transition: height 1s;
  -o-transition: height 1s;
  transition: height 1s;
}

.pullbottom{
  height: 79vh;
  -webkit-transition: height 1s;
  -moz-transition: height 1s;
  -ms-transition: height 1s;
  -o-transition: height 1s;
  transition: height 1s;
}

.pullheader{
  margin-top: -244px;
  -webkit-transition-property: -webkit-transform, margin-top;
  -webkit-transition-duration: 0.3s;
  -webkit-transition-timing-function: ease-in;
  -webkit-transform: translate(10px 0px);
}

.single-docu .container-fluid{
  float: left;
  width: 100%;
  position: relative;
height: 100%;
padding:0px;
}

.bread-action{
  display:none;
}

.description button{
  background: #139dba;
  color: #fff;
  border: none;
  padding: 12px 20px;
  border-radius: 4px;
  font-size: 13px;
}

.description h3{
  font-size: 16px;
margin-top: 4px;
font-weight: 800;
margin-bottom: 6px;
}

.description p{
  font-size: 12px;
}

.full-content ul li{
	float:left;
	width: 100%;
}

/*
Carousel
*/
.jcarousel {
    position: relative;
    overflow: hidden;
    width: 98%;
    margin: 0px 1%;
}


.jcarousel-wrapper{
  position: relative;
	height: 78px;
}
.jcarousel ul {
    width: 20000em;
    position: relative;


    list-style: none;
    margin: 0;
    padding: 0;
}


.jcarousel li {
  width: 250px;
  float: left;
  margin: 16px;
  padding-left: 44px;
  position: relative;
  font-size: 13px;
}

.jcarousel-control-prev{
  position: absolute;
left: 0px;
top: 24%;
font-size: 28px;
color: #a7a7a7;
}


.jcarousel-control-next{
  position: absolute;
right: 0px;
top: 31%;
font-size: 28px;
color: #a7a7a7;
}

.jcarousel li a{
	padding: 0px 9px 0px 0px;
	float: left;
	width: 100%;
}

.jcarousel li a:hover{
  text-decoration: none;
  cursor: pointer;
}


li.faq:before, li.ticketing:before, li.downloads:before, li.seminar:before{
  background-repeat: no-repeat;
  background-size: contain;
  position: absolute;
  left: 5px;
  top:5px;
  content: "";
  height: 18px;
  width: 18px;
  background-repeat: no-repeat;
}

li.faq:before{
  background-image: url('icon-faq-small.png');
}

li.ticketing:before{
  background-image: url('icon-ticketing-small.png');
}

li.seminar:before{
  background-image: url('icon-training-small.png');
}
li.downloads:before{
  background-image: url('icon-meeting-small.png');
}

.foot-scroll{
  float: left;
  width: 100%;
  position: fixed;
  z-index: 1000;
  box-shadow: 0px 1px 7px #333;
  padding-top: 13px;
  padding-bottom: 14px;
  bottom: 0px;
  background: #fff;
	height: 100px;
}



#foot-scroll-link{
  position: fixed;
    bottom: 95px;
    right: 47.3%;
    height: 23px;
    width: 47px;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
    background: #fff;
    box-shadow: 0px 1px 7px #333;
    z-index:1000;
}

#foot-scroll-link:before{
  content: "\f107";
  font: normal normal normal 14px/1 FontAwesome;
  position: absolute;
  top: 3px;
  left: 19px;
}

#foot-scroll-link.fixedtofoot{
  bottom:0px !important;
  background:#7fcc50;
  color:#fff;
}

#foot-scroll-link.fixedtofoot:before{
  content: "\f106";
}

.notfixedtofoot{
  bottom: 171px !important;
}

#foot-scroll-link.fixedtofoot.notfixedtofoot{
  bottom: 75px !important;
}

.pushup{
    bottom: 75px;
}

.jcarousel li:after{
  content: "";
    right: -16px;
    height: 33px;
    width: 1px;
    top: 0px;
    border-left: 1px solid #d8d8d8;
    position: absolute;
}



.helpful-div{
  float: left;
  width: 100%;
  border-top: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
  padding: 2em 10px 1.7em;
  position: relative;
  margin-bottom: 25px;
  margin-top: 25px;
}


.helpful-div p{
  float: left;
  font-weight: 700;
  margin-right: 53px;
  width: auto;
}

.helpful-div .btn{
  float: left;
  width: 88px;
  margin: 0px 9px;
  background: transparent;
  border: 1px solid #ddd;
  color: #555;
  text-align: center;
  margin-top: -3px;
  font-size: 18px;
}


.helpful-div .helpful.pressed, .helpful-div .helpful:hover{
  background: #11ad11;
color: #fff;
}


.helpful-div .unhelpful.pressed, .helpful-div .unhelpful:hover{
  background: red;
color: #fff;
}

.toppage{
  text-align: center;
  font-size: 12px;
  color: #969494;
}

.toppage i{
    width: 100%;
}

@media only screen and (max-device-height: 920px) {
  .pullbottom{
    height: 75%;
  }
}

@media only screen and (max-device-width: 780px) {

	body.layout-2 .header-icons {
    margin: 18px 11px 10px 0px;
	}

  .current-sub-nav .col-md-6{
      padding: 0px;
  }

  .current-sub-nav .col-md-1{
    float: left;
    width: 16.65%;
  }

  .support-sol{
    width: 100%;
  }
.single-docu:before{
  display:none;
  }

    .single-docu .col-md-3, .single-docu .col-md-9{
      width: 100%;
      float: left;
      padding: 0px;
    }

    .fixed-row {
      position: relative;
      width: 80%;
      height: auto;
      overflow-y: scroll;
      margin: 0px;
      padding:0px;
      float: left;
  }

  .share-side .col-md-4{
    width: 33.3%;
    float: left;
  }

  .version-row {
    display: none;
  }
  .content-row .col-md-12{
    padding: 0px;
  }

  .jcarousel ul{
      width: 100%;
      height: 66px;
      overflow: hidden;
  }
  .jcarousel li {
      width: 100%;
      float: left;
  }
  .content-row .full-content{
      padding: 3em 1.5em;
  }

}

.desc {
	font-style: italic;
}

.log {
	padding: 18px;
	border: 1px solid #ddd;
	background: #f2f2f2;
	margin-bottom: 10px;
	width: 100%;
	float: left;
}

.table-striped > tbody > tr:nth-child(even) {
  background-color: #f0f0f0;
}

.table-striped > tbody > tr:nth-child(odd) {
  background-color: #ccc;
}

.table-striped > tbody > tr:first-child td {
   font-weight: bold;
}

.table-striped.no-bold > tbody > tr:first-child td {
   font-weight: normal;
}
  </style>

<?php
//render page header
print render($page['header']);
?>

  <section class="dashboard-breadcrumbs xchange">
      <div class="container-fluid">
        <div class="col-md-4 bread-links">
          <a href="<?php print $front_page; ?>">MY DASHBOARD</a> / <a href="<?php print $front_page; ?>documentation">DOCUMENTATION</a> / <b>TITLE OF DOC</b></div>
        <div class="col-md-8 bread-action">

          <!--<form action="/ticketing" method="post" id="product-information-form" accept-charset="UTF-8">
            <div>

            </div>
          </form>

          <form action="/ticketing" method="post" id="supported-solutions" accept-charset="UTF-8">
            <div>

                <div class="btn-group pull-right support-sol">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    My Supported Solutions <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    <li><a href="#">I</a></li>
                    <li><a href="#">Am</a></li>
                    <li><a href="#">Not</a></li>
                    <li><a href="#">Sure</a></li>
                    <li><a href="#">What</a></li>
                    <li><a href="#">Goes</a></li>
                    <li><a href="#">Here</a></li>

                  </ul>
                </div>
            </div>
          </form>-->
        </div>
      </div>
  </section>

  <section class="single-docu">
<div class="container-fluid">
  <div class="row">
    <?php
    if(arg(0) == 'documentation'){
      print "<div class='col-md-9'>";
    } else {
      print "<div class='col-md-12'>";
    }
    print render($page['content']);
    print "</div>";
  ?>
  <?php
    if(arg(0) == 'documentation'){
      print '<div class="col-md-3 contents-right" id="sidebar">';
      print render($page['sidebar_first']);
      print '</div>';
    }
  ?>
  </div>
</div>
</section>


<!--<div id="foot-scroll-link">
  <span></span>
</div>
  <section class="foot-scroll">

    <div class="container">
      <div class="jcarousel-wrapper">
    <a href="#" class="jcarousel-control-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>

          <div class="jcarousel">
              <ul class="content-scroll">
                  <li class="meeting"><a href="#"> How many users can be parked simultaneously?</a></li>
                  <li class="faq"><a href="#">  Video coferencing support feature</a></li>
                  <li class="seminar"><a href="#"> Troubleshooting guide: video conferencing for BroadWork...</a></li>
                  <li class="ticketing"><a href="#"> How can I fix the video conferencing issue on my.. </a></li>
                  <li class="meeting"><a href="#">  How many users can be parked simultaneously?</a></li>
                  <li class="faq"> <a href="#"> Video coferencing support feature</a></li>
                  <li class="seminar"><a href="#"> Troubleshooting guide: video conferencing for BroadWork...</a></li>
                  <li class="ticketing"><a href="#"> How can I fix the video conferencing issue on my.. </a></li>
                  <li class="meeting"><a href="#">  How many users can be parked simultaneously?</a></li>
                  <li class="faq"><a href="#">  Video coferencing support feature</a></li>
                  <li class="seminar"><a href="#"> Troubleshooting guide: video conferencing for BroadWork...</a></li>
                  <li class="ticketing"><a href="#"> How can I fix the video conferencing issue on my.. </a></li>
                  <li class="meeting"><a href="#">  How many users can be parked simultaneously?</a></li>
                  <li class="faq"><a href="#">  Video coferencing support feature</a></li>
                  <li class="seminar"><a href="#"> Troubleshooting guide: video conferencing for BroadWork...</a></li>
                  <li class="ticketing"><a href="#"> How can I fix the video conferencing issue on my.. </a></li>
              </ul>
          </div>

          <a href="#" class="jcarousel-control-next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>

      </div>
    </div>
  </section>-->

 <footer>
    <div class="container">
      <div class="inner">
        <span class="wrap-btn">
          <a href="<?php global $base_url; echo $base_url; ?>/modal_forms/nojs/webform/42299" title="Give Feedback" class="btn type-none-background color-green pull-left ctools-use-modal ctools-modal-modal-popup-medium">Give Feedback</a>
        </span>
        <div class="wrap-text">
          <p>© 2016 BroadSoft All Rights Reserved | Build v.1.02</p>
          <p><?php print l('Dev Sitemap','sitemaptmp'); ?></p>
          <a href="#" title="" class="small-logo"></a>
        </div>
      </div>
    </div>
  </footer>


<div id="afPopup"></div>
<?php drupal_add_js(drupal_get_path('module', 'broadsoft_ask_flow').'/broadsoft_ask_flow.js'); ?>
