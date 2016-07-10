<?php
  switch($_GET['pm_page']) {
    case 'add-program':
      $page = 'add-program';
      break;
    case 'edit-program':
      $page = 'edit-program';
      break;
    case 'view-applications':
      $page = 'view-applications';
      break;
    default:
      $page = 'view-programs';
      break;
  }
?>

<div class="wrap">
    <?
      ob_start();
      require($page.'.php');
      ob_end_flush();
    ?>
</div>
