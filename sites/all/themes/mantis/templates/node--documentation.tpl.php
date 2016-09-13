<?php

		$pages_sidebar = '';
		$cleanedPages = '';
        
		if(isset($node->field_json['und'][0]['value'])) {

            $response = json_decode($node->field_json['und'][0]['value']);

            $title = $response->metadata->title;
			$author = $response->metadata->author;
			$created = $response->metadata->created;
			

            foreach($response->pages as $page) {
				
				$content = '';

				if($page->type == 'toc' || empty($page->content)) {
					continue;
				}

                $content  .= "<hr id='page_$page->page'/>";
                foreach ($page->content as $tag) {
					$tag->tag = strtolower($tag->tag);
			$class ="";
			if(isset($tag->class)){
				$class = $tag->class;
			}

                    if($tag->tag == "ul") {
						$content .= "<ul>";
						foreach ($tag->value as $li) {
							$content .= "<li>".broadsoft_documentation_processTag($li->value)."</li>";
						}
						$content .= "</ul>";
					} else if ($tag->tag == "toc" || $tag->tag == "tof") {

						if(sizeof($tag->value) > 0) {
							$content .= '<div class="table-responsive"><table class="table table-striped no-bold">';
							foreach($tag->value as $tr) {
								//TODO: text formatting  temporary removed from log tag
						$tr->value = preg_replace("#\|bBb\|(.*?)\|\/bBb\|#", "$1", $tr->value);
								$content .= '<tr><td>'.broadsoft_documentation_replace($tr->value).'</td>
								<td><a href="#page_'.$tr->page.'">'.$tr->page.'</a></td></tr>';
							}
							$content .= '</table></div>';
						}


					} else if ($tag->tag == "note") {
					$content .= '<div class="log">';
					foreach($tag->value as $row) {

						$content .= '<p>';
						//TODO: text formatting  temporary removed from log tag
						$row->value = preg_replace("#\|bBb\|(.*?)\|\/bBb\|#", "$1", $row->value);
						$content .= htmlentities($row->value);
						$content .= '</p>';

					}
					$content .= "</div>";
			} else if($tag->tag == "table") {
				$content .= '<div class="table-responsive"><table class="table table-striped table-bordered">';
				foreach($tag->value as $row) {
					$content .= '<tr>';
					foreach($row as $td) {
						$content .= "<{$td->tag}>".broadsoft_documentation_processTag($td->value)."</{$td->tag}>";
					}
					$content .= '</tr>';
				}
				$content .= '</table></div>';
			} else if($tag->tag == "log") {
				$content .= '<div class="log">';
					foreach($tag->value as $row) {
						$content .= '<p>';
						
						//TODO: text formatting  temporary removed from log tag
						$row->value = preg_replace("#\|bBb\|(.*?)\|\/bBb\|#", "$1", $row->value);
						$content .= htmlentities(broadsoft_documentation_replace($row->value));
						$content .= '</p>';
					}
					$content .= "</div>";

			} else if($tag->tag == "img") {
				$content .= '<img src="'. base_path().'sites/all/modules/broadsoft/broadsoft_documentation/images/'.$tag->value.'" />';
			} else {
					if($tag->tag == "h1"){
						if(isset($tag->chapter) && $tag->chapter != '1' && $tag->chapter != '2') {
							$content = str_replace("<hr id='page_{$page->page}'/>", "", $content);
							$content = ' <div class="helpful-div">
      <div class="col-md-10">
          <p>
            Helpful?
          </p>
          <button class="btn helpful">
            <i class="fa fa-thumbs-o-up" aria-hidden="true"></i>
          </button>
          <button class="btn unhelpful">
            <i class="fa fa-thumbs-o-down" aria-hidden="true"></i>
          </button>
        </div>
        <div class="col-md-2 toppage">
          <a href="#"><i class="fa fa-angle-up" aria-hidden="true"></i>
          Back to Top
        </a>
      </div>
    </div>' . $content;
						}
						
						$id='';
						if($tag->chapter) {
							$id = 'id="c_'.$tag->chapter.'"';
						}

						$ch_title = broadsoft_documentation_preprocessTagValue($tag);
						$content .= "<{$tag->tag} class='{$class} chapter' ".$id.">".broadsoft_documentation_processTag($ch_title)."</{$tag->tag}>";
					} else {
						$id='';
						if($tag->chapter) {
							$id = 'id="c_'.$tag->chapter.'"';
						}

						$ch_title = broadsoft_documentation_preprocessTagValue($tag);
						$content .= "<{$tag->tag} class='{$class}' ".$id.">".broadsoft_documentation_processTag($ch_title)."</{$tag->tag}>";
					}
			}
                }
				$cleanedPages .= $content;
            }

			foreach($response->chapters as $chapter) {
				
				$pages_sidebar .= '<li><a href="#c_'.$chapter->tag.'">'.broadsoft_documentation_replace($chapter->title).'</a>';

				if(isset($chapter->children)) {
					$pages_sidebar .= '<ul>';
					foreach($chapter->children as $child) {
						$pages_sidebar .= '<li><a href="#c_'.$child->tag.'">'.broadsoft_documentation_replace($child->title).'</a>';
					}
					$pages_sidebar .= '</ul>';
				}
				$pages_sidebar .= '</li>';
			}
        }

?>
<div class="col-md-3">
    <div class="row fixed-row">

	<nav id="doc-menu">
    <ul class="liststyle-doc nav" >
        <?php print $pages_sidebar; ?>
    </ul>
	</nav>

<div class="share-side">

  <?php if(isset($download_form)): ?>
    <div class="col-md-4">
      <?php print render($download_form); ?>
    </div>
  <?php endif; ?>
  <!--<div class="col-md-4">
    <button type="button" class="btn btn-default orange-brdr"><i class="fa fa-share" aria-hidden="true"></i> Share</button>
  </div>
  <div class="col-md-4">
    <button type="button" class="btn btn-default orange-brdr"><i class="fa fa-heart-o" aria-hidden="true"></i> Favourite</button>
  </div>-->

</div>

  </div>
  </div>

  <div class="col-md-9" id="spyOnThis" data-spy="scroll" data-target="#dateNav">
    <div class="row version-row">
      <div class="col-md-8">

				<div class="description">
          <div class="row">
            <div class="col-md-3 col-sm-3">
                <button>BroadWorks</button>
            </div>
            <div class="col-md-9 col-sm-9">
                <h3><?php print $title; ?></h3>
                <p><?php print $author; ?> / <?php print $created; ?></p>
            </div>
          </div>

        </div>

		</div>

      <div class="col-md-4">
        <div class="version-drop">
          <!--<div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
              Versions 23.34.2
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
              <li><a href="#">Version 1</a></li>
              <li><a href="#">Version 2</a></li>
              <li><a href="#">Version 3</a></li>

            </ul>
          </div>-->
        </div>
      </div>
    </div>
    <div class="row content-row">
      <div class="col-md-12">
        <div class="full-content">
		
         <?php print $cleanedPages; ?>

        </div>
      </div>
    </div>
    <!-- <div class="row private-row" style="display:none;">
      <div class="col-md-10">
        <h3>Private Content</h3>
        <p>
          This content is protected and requires a nondisclosure agreement to view. Simply login with an authorized
account to continue reading.
        </p>
      </div>
      <div class="col-md-2">
        <button class="btn color-green">Login</button>
      </div>
    </div> -->
  </div>






<?php 
		

      	$scrollOutput='';

		$response = json_decode($node->field_json['und'][0]['value']);
		foreach($response->pages as $page) {
			foreach ($page->content as $tag) {
				$tag->tag = strtolower($tag->tag);
				if($tag->chapter) {
					$id = 'id="c_'.$tag->chapter.'"';
				}
				if($tag->tag == "h1"){
					if(isset($tag->chapter)) {
						
						$ch_title = broadsoft_documentation_preprocessTagValue($tag);
						$chapter_title = strip_tags($ch_title);  
						$title_chapter = str_replace($tag->chapter, '', $chapter_title);
						$search_result=showRelatedChapters($title_chapter);
						
					    foreach ($search_result as $key) {
					    	$scrollOutput.="<li class='c_".$tag->chapter."'><a href='".url(drupal_get_path_alias('node/'.$key->entity), array('absolute' => TRUE))."'>".node_load($key->entity)->title."</a></li>";	
	                    }
						
						
		                }}}}
		                ?>
		










<div id="foot-scroll-link">
  <span></span>
</div>
  <section class="foot-scroll">

    <div class="container">
      <div class="jcarousel-wrapper">
    <a href="#" class="jcarousel-control-prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
          
          <div class="jcarousel">
              <ul class="content-scroll">
				<?php print $scrollOutput; ?>
              </ul>
          </div>

          <a href="#" class="jcarousel-control-next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>

      </div>
    </div>
  </section>


