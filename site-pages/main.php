<?php
  switch($_GET['pm_page']) {
    case 'add-application':
      $page = 'add-application';
      break;
    default:
      $page = 'view-programs';
      break;
  }

  ob_start();
  require($page.'.php');
  ob_end_flush();
?>
