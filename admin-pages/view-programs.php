<?php
  global $wpdb;
  $program_table_name = $wpdb->prefix.'pm_programs';
  $application_table_name = $wpdb->prefix.'pm_applications';

  $sql = [];
  $sql[] = "
    SELECT * FROM $program_table_name
  ";
  $where = [];
  if(isset($_GET['s'])) {
    $query = $wpdb->_real_escape($_GET['s']);
    $where[] = "
      `name` LIKE '%$query%'
    ";
  }
  if(isset($_GET['hidefull'])) {
    $where[] = "
      `slots` > `filled_slots`
    ";
  }
  if(count($where)) {
    $sql[] = "
      WHERE ". implode(' AND ', $where) ."
    ";
  }
  $orderby = $_GET['orderby'];
  if(isset($orderby) && ($orderby === 'name' || $orderby === 'slots')) {
    $orderdir = $_GET['orderdir'];
    if(!($orderdir === 'DESC')) {
      $orderdir = 'ASC';
    }
    if($orderdir === 'ASC') {
      $neworderdir = 'DESC';
    } else {
      $neworderdir = 'ASC';
    }
    $sql[] = "
      ORDER BY $orderby $orderdir
    ";
  } else {
    $orderdir = 'DESC';
    $neworderdir = 'ASC';
  }

  $properties = array(
    'page' =>'pm-program-manager',
    'pm_page' => 'view-programs',
    'orderby' => $orderby,
    'orderdir' => $orderdir,
    's' => $_GET['s'],
    'hidefull' => $_GET['hidefull']
  );

  if(isset($_GET['del_program'])) {
    $token = $wpdb->_real_escape($_GET['del_program']);
    $existing_program = $wpdb->get_row("
      SELECT * FROM $program_table_name WHERE `token` = '$token';
    ");
    if(isset($existing_program)) {
      if(isset($_GET['confirm_del'])) {
        $wpdb->delete($program_table_name, array(
          'token' => $token
        ));
        $wpdb->delete($application_table_name, array(
          'program' => $token
        ));
        echo "
          <div class='updated'>
            <p>The program <strong>$existing_program->name</strong> and all of its applications have been removed.</p>
          </div>
        ";
      } else {
        echo "
          <div class='notice'>
            <p>
              Are you sure that you want to remove <strong>$existing_program->name</strong> and all of its applications?
              <br />
              <a href='?"; $newproperties = $properties; $newproperties['del_program'] = $token; $newproperties['confirm_del'] = 1; echo http_build_query($newproperties, '', '&amp;'); echo "'>Confirm deletion.</a>
            </p>
          </div>
        ";
      }
    }
  }

  $programs = $wpdb->get_results(implode(" ", $sql).';');
?>

<h1>
  Programs
  <a class="page-title-action" href="?page=pm-program-manager&amp;pm_page=add-program">Add New</a>
</h1>
<form method="get">
  <?
    while($property = current($properties)) {
      $key = key($properties);
      echo "
        <input type='hidden' name='$key' value='$property' />
      ";
      next($properties);
    }
  ?>
  <p class="search-box">
    <input name="s" type="search" value="<? echo $_GET['s']; ?>" />
    <input class="button" value="Search Programs" type="submit" />
  </p>
  <div class="tablenav top">
    <label>
      <input name="hidefull" type="checkbox" <? if(isset($_GET['hidefull'])) { echo "checked"; } ?> />
      Hide full programs
    </label>
    <input class="button action" value="Apply" type="submit" />
  </div>
  <table class="striped widefat fixed">
    <thead>
      <tr>
        <th class="sortable <? if($orderby === 'name') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?<? $newproperties = $properties; $newproperties['orderby'] = 'name'; $newproperties['orderdir'] = $neworderdir; echo http_build_query($newproperties, '', '&amp;'); ?>">
            <span>Program</span>
            <span class="sorting-indicator"></span>
          </a>
        </th>
        <th class="sortable <? if($orderby === 'slots') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?<? $newproperties = $properties; $newproperties['orderby'] = 'slots'; $newproperties['orderdir'] = $neworderdir; echo http_build_query($newproperties, '', '&amp;'); ?>">
            <span>Remaining Slots</span>
            <span class="sorting-indicator"></span>
          </a>
        </th>
      </tr>
    </thead>
    <tbody>
      <?
        if(count($programs)) {
          foreach($programs as $program) {
            $slots = $program->slots - $program->filled_slots;
            if($slots <= 0) {
              $slots = 'FULL';
            }
            echo "
              <tr>
                <td>
                  <strong>
                    <a href='?page=pm-program-manager&amp;pm_page=view-applications&amp;program=$program->token'>$program->name</a>
                  </strong>
                  <div class='row-actions'>
                    <span class='edit'>
                      <a href='?page=pm-program-manager&amp;pm_page=edit-program&amp;program=$program->token'>Edit</a>
                      |
                    </span>
                    <span class='delete'>
                      <a class='submitdelete' href='?"; $newproperties = $properties; $newproperties['del_program'] = $program->token; echo http_build_query($newproperties, '', '&amp;'); echo "'>Delete</a>
                    </span>
                  </div>
                </td>
                <td>
                  $slots
                </td>
              </tr>
            ";
          }
        } else {
          echo "
            <tr>
              <td>
                No programs found.
              </td>
              <td></td>
            </tr>
          ";
        }
      ?>
    </tbody>
  </table>
</form>
