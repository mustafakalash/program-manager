<?php
  global $wpdb;
  $program_table_name = $wpdb->prefix.'pm_programs';
  $application_table_name = $wpdb->prefix.'pm_applications';

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
              <a href='?page=pm-program-manager&amp;del_program=$token&amp;confirm_del=1'>Confirm deletion.</a>
            </p>
          </div>
        ";
      }
    }
  }

  $sql = [];
  $sql[] = "
    SELECT * FROM $program_table_name
  ";
  $where = [];
  if(isset($_POST['s'])) {
    $query = $wpdb->_real_escape($_POST['s']);
    $where[] = "
      `name` LIKE '%$query%'
    ";
  }
  if(isset($_POST['hidefull'])) {
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
  $programs = $wpdb->get_results(implode(" ", $sql).';');
?>

<h1>
  Programs
  <a class="page-title-action" href="?page=pm-program-manager&amp;pm_page=add-program">Add New</a>
</h1>
<form method="post">
  <p class="search-box">
    <input name="s" type="search" />
    <input class="button" value="Search Programs" type="submit" />
  </p>
  <div class="tablenav top">
    <label>
      <input name="hidefull" type="checkbox" <? if(isset($_POST['hidefull'])) { echo "checked"; } ?> />
      Hide full programs
    </label>
    <input class="button action" value="Apply" type="submit" />
  </div>
  <table class="striped widefat fixed">
    <thead>
      <tr>
        <th class="sortable <? if($orderby === 'name') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?page=pm-program-manager&amp;orderby=name&amp;orderdir=<? echo $neworderdir ?>">
            <span>Program</span>
            <span class="sorting-indicator"></span>
          </a>
        </th>
        <th class="sortable <? if($orderby === 'slots') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?page=pm-program-manager&amp;orderby=slots&amp;orderdir=<? echo $neworderdir ?>">
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
                      <a class='submitdelete' href='?page=pm-program-manager&amp;del_program=$program->token'>Delete</a>
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
