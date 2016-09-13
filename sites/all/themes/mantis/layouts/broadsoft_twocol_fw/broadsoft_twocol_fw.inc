<?php

// Plugin definition
$plugin = array(
  'title' => t('Broadsoft Two Column (width: 9 - 3 with bottom bar)'),
  'category' => t('Broadsoft'),
  'icon' => 'broadsoft.png',
  'theme' => 'broadsoft_twocol_fw',
  'css' => 'broadsoft_twocol_fw.css',
  'regions' => array(
    'left' => t('Left side'),
    'right' => t('Right side'),
    'bottom' => t('Bottom bar (optional)')
  ),
);
