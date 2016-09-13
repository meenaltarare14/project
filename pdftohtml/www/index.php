<?php
    error_reporting(0);
    session_start();
	
    $files =  array_diff(scandir("../ReleaseDocs_R21"), array('..', '.'));
	
        if(array_key_exists("file", $_GET)){
            $file = $_GET["file"];
        } else {
            $file = array_shift ($files);
        }

        if(!isset($_SESSION["Temporary_extraction"][$file])) {
            $_SESSION["temp_filename"] = $file;
            $cmd = "java -jar texthunter.jar -solr no ../ReleaseDocs_R21/$file";
			$_SESSION["Temporary_extraction"][$file] = utf8_encode(shell_exec($cmd));	
        }
		

        $cleanedPages = "";
        $pages_sidebar = "";
		$title = "";
	$sidebar = [];
		
        if(isset($_SESSION["Temporary_extraction"][$file])) {
			
            $response = json_decode(utf8_encode($_SESSION["Temporary_extraction"][$file]));
			
            $title = $response->metadata->title;
	    

            foreach($response->pages as $page) {
		
               // $pages_sidebar .= '<li><a href="#page_'.$page->page.'" class="cc-active">Page '.$page->page.'</a></li>';
                $cleanedPages  .= "<hr id='page_$page->page'/>   <div class='pado-section-enter'>";
		$cleanedPages  .= '<article class="document type-pressapps_document status-publish clearfix">';
                foreach ($page->content as $tag) {
			$class ="";
			if(isset($tag->class)){
				$class = $tag->class;			
			}
			
			switch($tag->tag) {
				case 'ul':
					$cleanedPages .= "<ul>";
						foreach ($tag->value as $li) {
							$cleanedPages .= "<li>".processTag($li->value)."</li>";
						}
						$cleanedPages .= "</ul>";

				break;
				case "toc":
				case "tof" :
					if(sizeof($tag->value) > 0) {
							$cleanedPages .= '<table class="toc">';
							foreach($tag->value as $tr) {
								$cleanedPages .= '<tr><td class="toc-title">'.$tr->value.'</td>
								<td class="toc-page"><a href="#page_'.$tr->page.'">'.$tr->page.'</a></td></tr>';
							}
							$cleanedPages .= '</table>';
						}
				break;
				case "table":
					$cleanedPages .= '<table>';
				foreach($tag->value as $row) {
					$cleanedPages .= '<tr>';
					foreach($row as $td) {
						$cleanedPages .= "<{$td->tag}>".processTag($td->value)."</{$td->tag}>";
					}
					$cleanedPages .= '</tr>';
				}
				$cleanedPages .= '</table>';
				break;
				case "note":
					$cleanedPages .= '<div class="note">';
					$cleanedPages .= processTag($tag->value);
					$cleanedPages .= "</div>";
				break;
				case "img" :
					$cleanedPages .= '<img src="images/'.$tag->value.'" />';
				break;
				case "H1" :
					$cleanedPages .= "<{$tag->tag} class='pado-section-heading'>".processTag($tag->value)."</{$tag->tag}>";
					
					$sidebar[] = ['page'=>$page->page, 'value' =>processTag($tag->value)];
					break;
				default:
					$cleanedPages .= "<{$tag->tag} class='{$class}'>".processTag($tag->value)."</{$tag->tag}>";
					break;
			}			
        }
		$cleanedPages .='<div class="pado-votes"><p data-original-title="57 people found this helpful" data-toggle="tooltip" title="" class="pado-likes"><i class="si-checkmark3"></i> <span class="count">57</span></p> <p data-original-title="17 people did not find this helpful" data-toggle="tooltip" title="" class="pado-dislikes"><i class="si-cross2"></i> <span class="count">17</span></p> </div>                                <p class="pado-back-top"><a href="#top">Back To Top</a></p>
                            ';
		$cleanedPages  .= "</article></div>";
            }
	    
        }
	
function processTag($tag) {
	$result = $tag;
	preg_match_all('#\|sSs\|(.*?)\|eEe\|#s', $tag, $matches);
	if(sizeof($matches) > 0) {    
		for ($i = 0; $i < count($matches[1]); $i++) {
			$ext_tag = $matches[1][$i];
			$curr_tag = $matches[0][$i];
			$position = strpos($ext_tag, '|mMm|');
			$json = json_decode(substr($ext_tag, 0, $position));
			switch($json->tag) {
			case 'a':
				$linkText = substr($ext_tag, $position + 5);
				$external = '';
				if($json->class == 'external') {
					 $external  = 'target="_blank"';
				}
				$replacement = '<a  href="'.$json->link.'" '.$external.'>'.$linkText.'</a>';   
			}       
		$result = preg_replace('/'.preg_quote($curr_tag , '/').'/', $replacement, $result);
		}    
	}
	return $result;   
}

?>

<!DOCTYPE html>
<html class=" wf-opensans-n4-active wf-active js flexbox flexboxlegacy canvas canvastext webgl no-touch geolocation postmessage no-websqldatabase indexeddb hashchange history draganddrop websockets rgba hsla multiplebgs backgroundsize borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients no-cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio localstorage sessionstorage webworkers applicationcache svg inlinesvg smil svgclippaths" lang="en-US"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Broadsoft</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

                          <script async="" type="text/javascript" src="js-r/webfont.js"></script><script>
                            /* You can add more configuration options to webfontloader by previously defining the WebFontConfig with your options */
                            if ( typeof WebFontConfig === "undefined" ) {
                                WebFontConfig = new Object();
                            }
                            WebFontConfig['google'] = {families: ['Open+Sans:400']};

                            (function() {
                                var wf = document.createElement( 'script' );
                                wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1.5.3/webfont.js';
                                wf.type = 'text/javascript';
                                wf.async = 'true';
                                var s = document.getElementsByTagName( 'script' )[0];
                                s.parentNode.insertBefore( wf, s );
                            })();
                        </script>

		<link href="css/css.css" rel="stylesheet">
		<style type="text/css">
img.wp-smiley,
img.emoji {
	display: inline !important;
	border: none !important;
	box-shadow: none !important;
	height: 1em !important;
	width: 1em !important;
	margin: 0 .07em !important;
	vertical-align: -0.1em !important;
	background: none !important;
	padding: 0 !important;
}
</style>
<link rel="stylesheet" id="roots_css-css" href="css-r/main.css" type="text/css" media="all">
<script type="text/javascript" src="js-r/jquery.js"></script>
<script type="text/javascript" src="js-r/jquery-migrate.js"></script>

  <style type="text/css" id="helpdesk-css">.navbar-default .navbar-nav > li > a { color: #444444; }.navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus, .navbar-default .navbar-nav > li > a:hover { color: #00aff0; }.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus, .dropdown-menu > li > a:hover { background-color: #00aff0; }section .box i, section .box h3, .sidebar h3 { color: #00A4E0; }.btn-primary { background-color: #00A4E0; border-color: #00A4E0}.btn-primary:hover { background-color: #3d3d3d; border-color: #3d3d3d}.pagination > .active > a, .pagination > .active > span, .pagination > .active > a:hover, .pagination > .active > span:hover, .pagination > .active > a:focus, .pagination > .active > span:focus { background-color: #00A4E0; border-color: #00A4E0}.pagination > li > a, .pagination > li > a:hover { color: #00A4E0;}</style>		<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
		<style type="text/css" title="dynamic-css" class="options-output">.banner{background:#f7f7f7;}.banner{padding-top:15px;padding-bottom:15px;}.sidebar-footer{background:#ffffff;}.content-info, .content-info a, .content-info a:hover, .content-info h3, .content-info a .icon-wrap{color:#dbdbdb;}.footer-bottom{border-top:1px solid #e2e2e2;}.footer-bottom{background:#ffffff;}.footer-bottom, .footer-bottom a, .footer-bottom i{color:#c9c9c9;}h1, h2, h3, h4, h5, h6{font-family:"Open Sans";font-weight:400;font-style:normal;color:#000000;opacity: 1;visibility: visible;-webkit-transition: opacity 0.24s ease-in-out;-moz-transition: opacity 0.24s ease-in-out;transition: opacity 0.24s ease-in-out;}.wf-loading h1, h2, h3, h4, h5, h6,{opacity: 0;}.ie.wf-loading h1, h2, h3, h4, h5, h6,{visibility: hidden;}body{font-family:"Open Sans";font-weight:400;font-style:normal;color:#6d767e;font-size:15px;opacity: 1;visibility: visible;-webkit-transition: opacity 0.24s ease-in-out;-moz-transition: opacity 0.24s ease-in-out;transition: opacity 0.24s ease-in-out;}.wf-loading body,{opacity: 0;}.ie.wf-loading body,{visibility: hidden;}a{color:#00A4E0;}a:hover{color:#3d3d3d;}.comments, #respond, .sidebar{color:#84949f;}</style>
<style id="fit-vids-style">.fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}</style><style class="firebugResetStyles" type="text/css" charset="utf-8">/* See license.txt for terms of usage */
/** reset styling **/
.firebugResetStyles {
    z-index: 2147483646 !important;    top: 0 !important;
    left: 0 !important;
    display: block !important;
    border: 0 none !important;
    margin: 0 !important;
    padding: 0 !important;
    outline: 0 !important;
    min-width: 0 !important;
    max-width: none !important;
    min-height: 0 !important;
    max-height: none !important;
    position: fixed !important;
    transform: rotate(0deg) !important;
    transform-origin: 50% 50% !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    background: transparent none !important;    pointer-events: none !important;
    white-space: normal !important;
}
style.firebugResetStyles {
    display: none !important;
}

.firebugBlockBackgroundColor {
    background-color: transparent !important;
}
.firebugResetStyles:before, .firebugResetStyles:after {
    content: "" !important;
}
/**actual styling to be modified by firebug theme**/
.firebugCanvas {
    display: none !important;
}
.firebugLayoutBox {
    width: auto !important;
    position: static !important;
}
.firebugLayoutBoxOffset {
    opacity: 0.8 !important;
    position: fixed !important;
}
.firebugLayoutLine {
    opacity: 0.4 !important;
    background-color: #000000 !important;
}
.firebugLayoutLineLeft, .firebugLayoutLineRight {
    width: 1px !important;
    height: 100% !important;
}
.firebugLayoutLineTop, .firebugLayoutLineBottom {
    width: 100% !important;
    height: 1px !important;
}
.firebugLayoutLineTop {
    margin-top: -1px !important;
    border-top: 1px solid #999999 !important;
}
.firebugLayoutLineRight {
    border-right: 1px solid #999999 !important;
}
.firebugLayoutLineBottom {
    border-bottom: 1px solid #999999 !important;
}
.firebugLayoutLineLeft {
    margin-left: -1px !important;
    border-left: 1px solid #999999 !important;
}

.firebugLayoutBoxParent {
    border-top: 0 none !important;
    border-right: 1px dashed #E00 !important;
    border-bottom: 1px dashed #E00 !important;
    border-left: 0 none !important;
    position: fixed !important;
    width: auto !important;
}
.firebugRuler{
    position: absolute !important;
}
.firebugRulerH {
    top: -15px !important;
    left: 0 !important;    width: 100% !important;
    height: 14px !important;
    background: url("data:image/png,%89PNG%0D%0A%1A%0A%00%00%00%0DIHDR%00%00%13%88%00%00%00%0E%08%02%00%00%00L%25a%0A%00%00%00%04gAMA%00%00%D6%D8%D4OX2%00%00%00%19tEXtSoftware%00Adobe%20ImageReadyq%C9e%3C%00%00%04%F8IDATx%DA%EC%DD%D1n%E2%3A%00E%D1%80%F8%FF%EF%E2%AF2%95%D0D4%0E%C1%14%B0%8Fa-%E9%3E%CC%9C%87n%B9%81%A6W0%1C%A6i%9A%E7y%0As8%1CT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AATE9%FE%FCw%3E%9F%AF%2B%2F%BA%97%FDT%1D~K(%5C%9D%D5%EA%1B%5C%86%B5%A9%BDU%B5y%80%ED%AB*%03%FAV9%AB%E1%CEj%E7%82%EF%FB%18%BC%AEJ8%AB%FA'%D2%BEU9%D7U%ECc0%E1%A2r%5DynwVi%CFW%7F%BB%17%7Dy%EACU%CD%0E%F0%FA%3BX%FEbV%FEM%9B%2B%AD%BE%AA%E5%95v%AB%AA%E3E5%DCu%15rV9%07%B5%7F%B5w%FCm%BA%BE%AA%FBY%3D%14%F0%EE%C7%60%0EU%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5JU%88%D3%F5%1F%AE%DF%3B%1B%F2%3E%DAUCNa%F92%D02%AC%7Dm%F9%3A%D4%F2%8B6%AE*%BF%5C%C2Ym~9g5%D0Y%95%17%7C%C8c%B0%7C%18%26%9CU%CD%13i%F7%AA%90%B3Z%7D%95%B4%C7%60%E6E%B5%BC%05%B4%FBY%95U%9E%DB%FD%1C%FC%E0%9F%83%7F%BE%17%7DkjMU%E3%03%AC%7CWj%DF%83%9An%BCG%AE%F1%95%96yQ%0Dq%5Dy%00%3Et%B5'%FC6%5DS%95pV%95%01%81%FF'%07%00%00%00%00%00%00%00%00%00%F8x%C7%F0%BE%9COp%5D%C9%7C%AD%E7%E6%EBV%FB%1E%E0(%07%E5%AC%C6%3A%ABi%9C%8F%C6%0E9%AB%C0'%D2%8E%9F%F99%D0E%B5%99%14%F5%0D%CD%7F%24%C6%DEH%B8%E9rV%DFs%DB%D0%F7%00k%FE%1D%84%84%83J%B8%E3%BA%FB%EF%20%84%1C%D7%AD%B0%8E%D7U%C8Y%05%1E%D4t%EF%AD%95Q%BF8w%BF%E9%0A%BF%EB%03%00%00%00%00%00%00%00%00%00%B8vJ%8E%BB%F5%B1u%8Cx%80%E1o%5E%CA9%AB%CB%CB%8E%03%DF%1D%B7T%25%9C%D5(%EFJM8%AB%CC'%D2%B2*%A4s%E7c6%FB%3E%FA%A2%1E%80~%0E%3E%DA%10x%5D%95Uig%15u%15%ED%7C%14%B6%87%A1%3B%FCo8%A8%D8o%D3%ADO%01%EDx%83%1A~%1B%9FpP%A3%DC%C6'%9C%95gK%00%00%00%00%00%00%00%00%00%20%D9%C9%11%D0%C0%40%AF%3F%EE%EE%92%94%D6%16X%B5%BCMH%15%2F%BF%D4%A7%C87%F1%8E%F2%81%AE%AAvzr%DA2%ABV%17%7C%E63%83%E7I%DC%C6%0Bs%1B%EF6%1E%00%00%00%00%00%00%00%00%00%80cr%9CW%FF%7F%C6%01%0E%F1%CE%A5%84%B3%CA%BC%E0%CB%AA%84%CE%F9%BF)%EC%13%08WU%AE%AB%B1%AE%2BO%EC%8E%CBYe%FE%8CN%ABr%5Dy%60~%CFA%0D%F4%AE%D4%BE%C75%CA%EDVB%EA(%B7%F1%09g%E5%D9%12%00%00%00%00%00%00%00%00%00H%F6%EB%13S%E7y%5E%5E%FB%98%F0%22%D1%B2'%A7%F0%92%B1%BC%24z3%AC%7Dm%60%D5%92%B4%7CEUO%5E%F0%AA*%3BU%B9%AE%3E%A0j%94%07%A0%C7%A0%AB%FD%B5%3F%A0%F7%03T%3Dy%D7%F7%D6%D4%C0%AAU%D2%E6%DFt%3F%A8%CC%AA%F2%86%B9%D7%F5%1F%18%E6%01%F8%CC%D5%9E%F0%F3z%88%AA%90%EF%20%00%00%00%00%00%00%00%00%00%C0%A6%D3%EA%CFi%AFb%2C%7BB%0A%2B%C3%1A%D7%06V%D5%07%A8r%5D%3D%D9%A6%CAu%F5%25%CF%A2%99%97zNX%60%95%AB%5DUZ%D5%FBR%03%AB%1C%D4k%9F%3F%BB%5C%FF%81a%AE%AB'%7F%F3%EA%FE%F3z%94%AA%D8%DF%5B%01%00%00%00%00%00%00%00%00%00%8E%FB%F3%F2%B1%1B%8DWU%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*UiU%C7%BBe%E7%F3%B9%CB%AAJ%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5J%95*U%AAT%A9R%A5*%AAj%FD%C6%D4%5Eo%90%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5%86%AF%1B%9F%98%DA%EBm%BBV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%ADV%AB%D5j%B5Z%AD%D6%E4%F58%01%00%00%00%00%00%00%00%00%00%00%00%00%00%40%85%7F%02%0C%008%C2%D0H%16j%8FX%00%00%00%00IEND%AEB%60%82") repeat-x !important;
    border-top: 1px solid #BBBBBB !important;
    border-right: 1px dashed #BBBBBB !important;
    border-bottom: 1px solid #000000 !important;
}
.firebugRulerV {
    top: 0 !important;
    left: -15px !important;
    width: 14px !important;
    height: 100% !important;
    border-left: 1px solid #BBBBBB !important;
    border-right: 1px solid #000000 !important;
    border-bottom: 1px dashed #BBBBBB !important;
}
.overflowRulerX > .firebugRulerV {
    left: 0 !important;
}
.overflowRulerY > .firebugRulerH {
    top: 0 !important;
}
.fbProxyElement {
    position: fixed !important;
    pointer-events: auto !important;
}
</style></head><body class="page page-id-308 page-template-default document-2">

  <!--[if lt IE 8]>
    <div class="alert alert-warning">
      You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.    </div>
  <![endif]-->

  <header class="banner navbar navbar-default navbar-static-top" role="banner">
  <div class="container">

    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
              <a class="navbar-brand-img" title="Document" href="#"><img src="img/logo.png" alt="Document"></a>
      
    </div>


    <nav class="collapse navbar-collapse navbar-left" role="navigation">
      <ul id="menu-main" class="nav navbar-nav"><li class="menu-default-template">
	 <select id="file-select" name="file">
        <?php foreach ($files as $f) : ?>
        <option value="<?php print $f;?>" <?php if($f == $file) : ?>selected<?php endif; ?>><?php print $f;?></option>
        <?php endforeach ?>
	</select>
      </li>
<li class=" menu-light-template">
    	<a href="download.php?file=<?php print $file; ?>" target="_blank" class="btn btn-download"><img src="img/download.png" width="25" alt="Download <?php print $file; ?>" /> Download Now</a>
    </li>
</ul>    </nav>

    <nav class="collapse navbar-collapse navbar-right" role="navigation">
          </nav>

  </div>
</header>

  
  <div class="wrap container" role="document">
    <div class="content row">
      <div class="col-sm-12 sub">
            </div>
      <main class="main" role="main">
          <div class="page-header">
  <h1><?php print $title; ?></h1>
</div>
  <script type="text/javascript">
    var pado_top_offset     = 30;
    var pado_offset = 30;
    var pado_sidebar_width = 30;
</script>
<div id="pado-main" class="pado-light pado-counter">
    <div class="ps-container" style="position: relative; top: 30px; width: 342px; height: 690px;" id="pado-sidebar">

		<?php if(!empty($sidebar)) :
		$i = 1;
		foreach ($sidebar as $item) : ?>
			<ul class="<?php if($i == 1) : ?>open_arrow<?php endif; ?>">
               <li class="sidebar_cat_title"><a class="pado_sidebar_cat_title" href="#page_<?php print $item['page']; ?>"><?php print $item['value']; ?></a></li>

                   <!--
                    <li style="display: list-item;" class="sidebar_doc_title pado-document-1"><a href="#document-1"><i class="si-image2"></i> Style options tab</a></li>


                
                    <li style="display: list-item;" class="sidebar_doc_title pado-document-2"><a href="#document-2"><i class="si-file-text2"></i> Live document search</a></li>


                
                    <li style="display: list-item;" class="sidebar_doc_title pado-document-3"><a href="#document-3"><i class="si-image2"></i> General tab</a></li>-->


                </ul>
		<?php $i++; endforeach;
			endif; ?>
	    
		
                
    <div style="width: 342px; display: none; left: 0px; bottom: 3px;" class="ps-scrollbar-x-rail"><div style="left: 0px; width: 0px;" class="ps-scrollbar-x"></div></div><div style="top: 0px; height: 690px; display: none; right: 3px;" class="ps-scrollbar-y-rail"><div style="top: 0px; height: 0px;" class="ps-scrollbar-y"></div></div></div>

    <div style="width: 775.2px;" id="pado-content">
        <?php
		print $cleanedPages;
	?>                                                                                        
    </div> 
</div>

      </main><!-- /.main -->
          </div><!-- /.content -->
  </div><!-- /.wrap -->


<link rel="stylesheet" id="pressapps-document-css" href="css-r/pressapps-document-public.css" type="text/css" media="all">
<style id="pressapps-document-inline-css" type="text/css">
.pado-default .sidebar_doc_title a, .pado-default .sidebar_doc_title a:visited, .pado-section-heading, .pado-back-top a:hover, .pado-sharing-link:hover i { color: #03A9F4}
.pado-default .sidebar_doc_title:hover, .pado-default .pado-section-heading:before, .pado-default .sidebar_doc_active { background-color: #03A9F4}
.pado-light #pado-sidebar .sidebar_cat_title:hover a, .pado-light #pado-sidebar .open_arrow .pado_sidebar_cat_title, .pado-light .pado-section-heading a:hover { color: #03A9F4}
.pado-light .sidebar_doc_title:hover, .pado-light .sidebar_doc_active { border-color: #03A9F4}
.pado-light .pado-section-heading { border-bottom: solid 1px #03A9F4 }
</style>
<script type="text/javascript" src="js-r/modernizr.js"></script>
<script type="text/javascript" src="js-r/scripts.js"></script>
<script type="text/javascript" src="js-r/jquery_002.js"></script>
<script type="text/javascript" src="js-r/wp-embed.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
//var PADO = {"base_url":"http:\/\/plugins.pressapps.io\/document"};
/* ]]> */
</script>
<script type="text/javascript" src="js-r/pressapps-document-public.js"></script>
</body></html>