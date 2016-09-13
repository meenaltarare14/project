<?php
/**
 * @file
 * Displays a clean–markup-style panel pane.
 *
 * Available variables:
 * - $pane->type: the content type inside this pane
 * - $pane->subtype: The subtype, if applicable. If a view it will be the
 *   view name; if a node it will be the nid, etc.
 * - $title: The title of the content
 * - $content: The actual content
 * - $links: Any links associated with the content
 * - $more: An optional 'more' link (destination only)
 * - $admin_links: Administrative links associated with the content
 * - $feeds: Any feed icons or associated with the content
 * - $display: The complete panels display object containing all kinds of
 *   data including the contexts and all of the other panes being displayed.
 *
 * @see template_preprocess_clean_markup_panels_clean_element()
 * @see template_preprocess_panels_pane()
 * @see panels-pane.tpl.php
 *
 * @ingroup themeable
 */
?>
<?php if ($pane_prefix): ?>
  <?php print $pane_prefix; ?>
<?php endif; ?>
<?php if ($pane_wrapper): ?><<?php print $pane_wrapper_tag; ?><?php print $additional_attributes; ?> class="<?php print $classes . $clean_markup_classes; ?>" <?php print $id; ?>><?php endif; ?>
<?php if ($inner_wrapper): ?><div class="inner pane-inner"><?php endif; ?>
  <?php if ($admin_links): ?>
    <?php print $admin_links; ?>
  <?php endif; ?>

  <?php print render($title_prefix); ?>

  <?php if ($title): ?>

    <?php if($title_wrapper != '' && isset($title_wrapper)){ ?>
      <<?php print $title_wrapper; ?><?php print ' class="title sidebar-part__title"'; ?>>
    <?php } else { ?>
      <h2 class="title sidebar-part__title">
    <?php } ?>


    <span class="sidebar-part__title-inline"><?php print $title; ?></span>
    <?php if ($more): ?>
    <?php endif; ?>


    <?php if($title_wrapper != '' && isset($title_wrapper)){ ?>
      </<?php print $title_wrapper; ?>>
    <?php } else { ?>
      </h2>
    <?php } ?> 
    
  <?php endif; ?>

<?php print render($title_suffix); ?>

<?php if ($feeds): ?>
  <div class="feed">
    <?php print $feeds; ?>
  </div>
<?php endif; ?>

<?php if ($content_wrapper): ?><<?php print $content_wrapper_tag; ?> <?php print $content_attributes; ?>><?php endif; ?>
<?php print render($content); ?>
<?php if ($content_wrapper): ?></<?php print $content_wrapper_tag; ?>><?php endif; ?>

<?php if ($links): ?>
  <div class="links">
    <?php print $links; ?>
  </div>
<?php endif; ?>

<?php if ($inner_wrapper): ?></div><?php endif; ?>
<?php if ($pane_wrapper): ?></<?php print $pane_wrapper_tag; ?>><?php endif; ?>
<?php if ($pane_suffix): ?>
  <?php print $pane_suffix; ?>
<?php endif; ?>
