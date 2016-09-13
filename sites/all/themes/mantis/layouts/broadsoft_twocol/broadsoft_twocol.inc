<?php

// Plugin definition
$plugin = array(
  'title' => t('Broadsoft Two Column'),
  'category' => t('Broadsoft'),
  'icon' => 'broadsoft.png',
  'theme' => 'broadsoft_twocol',
  'css' => 'broadsoft_twocol.css',
  'regions' => array(
    'top' => t('Top'),
    'left' => t('Left side'),
    'right' => t('Right side'),
  ),
);
