<?php
  global $wpdb;
  $application_table_name = $wpdb->prefix.'pm_applications';
  $program_table_name = $wpdb->prefix.'pm_programs';

  $program = $wpdb->_real_escape($_GET['program']);
  $program_name = $wpdb->get_row("SELECT `name` FROM $program_table_name WHERE `token` = '$program';")->name;
  if(!isset($program_name)) {
    echo "
      <div class='error'>
        <p>
          <strong>ERROR</strong>
          : Program not found.
        </p>
      </div>
    ";
  } else {
    $sql = [];
    $sql[] = "
      SELECT * FROM $application_table_name
    ";
    $where = [];
    $where[] = "
      `program` = '$program'
    ";
    if(isset($_GET['s'])) {
      $query = $wpdb->_real_escape($_GET['s']);
      $where[] = "
        (`first_name` LIKE '%$query%' OR `last_name` LIKE '%$query%')
      ";
    }
    if(isset($_GET['hideseen'])) {
      $where[] = "
        `approved` = 0
      ";
    }
    if(count($where)) {
      $sql[] = "
        WHERE ". implode(' AND ', $where) ."
      ";
    }
    $orderby = $_GET['orderby'];
    if(isset($orderby) && ($orderby === 'first_name' || $orderby === 'last_name' || $orderby === 'approved')) {
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
      'pm_page' => 'view-applications',
      'program' => $program,
      'orderby' => $orderby,
      'orderdir' => $orderdir,
      's' => $_GET['s'],
      'hideseen' => $_GET['hideseen']
    );

    if(isset($_GET['del_app'])) {
      $token = $wpdb->_real_escape($_GET['del_app']);
      $existing_app = $wpdb->get_row("
        SELECT * FROM $application_table_name WHERE `token` = '$token';
      ");
      if(isset($existing_app)) {
        if(isset($_GET['confirm_del'])) {
          $wpdb->delete($application_table_name, array(
            'token' => $token
          ));
          echo "
            <div class='updated'>
              <p>The application from <strong>$existing_app->first_name $existing_app->last_name</strong> for the program <strong>$program_name</strong> has been removed.</p>
            </div>
          ";
        } else {
          echo "
            <div class='notice'>
              <p>
                Are you sure that you want to remove the application from <strong>$existing_app->first_name $existing_app->last_name</strong> for the program <strong>$program_name</strong>?
                <br />
                <a href='?"; $newproperties = $properties; $newproperties['del_app'] = $token; $newproperties['confirm_del'] = 1; echo http_build_query($newproperties, '', '&amp;'); echo "'>Confirm deletion.</a>
              </p>
            </div>
          ";
        }
      }
    } elseif(isset($_GET['approve_app'])) {
      $token = $wpdb->_real_escape($_GET['approve_app']);
      $existing_app = $wpdb->get_row("
        SELECT * FROM $application_table_name WHERE `token` = '$token';
      ");
      if(isset($existing_app)) {
        $wpdb->update(
          $application_table_name,
          array(
            'approved' => 1
          ),
          array(
            'token' => $token
          )
        );
        echo "
          <div class='updated'>
            <p>The application from <strong>$existing_app->first_name $existing_app->last_name</strong> for the program <strong>$program_name</strong> has been approved.</p>
          </div>
        ";
      }
    } elseif(isset($_GET['deny_app'])) {
      $token = $wpdb->_real_escape($_GET['deny_app']);
      $existing_app = $wpdb->get_row("
        SELECT * FROM $application_table_name WHERE `token` = '$token';
      ");
      if(isset($existing_app)) {
        $wpdb->update(
          $application_table_name,
          array(
            'approved' => -1
          ),
          array(
            'token' => $token
          )
        );
        echo "
          <div class='updated'>
            <p>The application from <strong>$existing_app->first_name $existing_app->last_name</strong> for the program <strong>$program_name</strong> has been denied.</p>
          </div>
        ";
      }
    }

    $applications = $wpdb->get_results(implode(" ", $sql).';');
?>

<h1>
  '<? echo $program_name; ?>' Applications
</h1>
<form method="get">
  <?
    while($property = current($properties)) {
      $key = key($properties);
      next($properties);
      if($key === 'hideseen') {
        continue;
      }
      echo "
        <input type='hidden' name='$key' value='$property' />
      ";
    }
  ?>
  <p class="search-box">
    <input name="s" type="search" value="<? echo $_GET['s']; ?>" />
    <input class="button" value="Search Applications" type="submit" />
  </p>
  <div class="tablenav top">
    <label>
      <input name="hideseen" type="checkbox" <? if(isset($_GET['hideseen'])) { echo "checked"; } ?> />
      Show only waiting applications
    </label>
    <input class="button action" value="Apply" type="submit" />
  </div>
  <table class="striped widefat fixed">
    <thead>
      <tr>
        <th class="sortable <? if($orderby === 'first_name') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?<? $newproperties = $properties; $newproperties['orderby'] = 'first_name'; $newproperties['orderdir'] = $neworderdir; echo http_build_query($newproperties, '', '&amp;'); ?>">
            <span>First Name</span>
            <span class="sorting-indicator"></span>
          </a>
        </th>
        <th class="sortable <? if($orderby === 'last_name') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?<? $newproperties = $properties; $newproperties['orderby'] = 'last_name'; $newproperties['orderdir'] = $neworderdir; echo http_build_query($newproperties, '', '&amp;'); ?>">
            <span>Last Name</span>
            <span class="sorting-indicator"></span>
          </a>
        </th>
        <th class="sortable <? if($orderby === 'approved') { echo 'sorted '; } echo strtolower($orderdir); ?>">
          <a href="?<? $newproperties = $properties; $newproperties['orderby'] = 'approved'; $newproperties['orderdir'] = $neworderdir; echo http_build_query($newproperties, '', '&amp;'); ?>">
            <span>Application Status</span>
            <span class="sorting-indicator"></span>
          </a>
        </th>
      </tr>
    </thead>
    <tbody>
      <?
        if(count($applications)) {
          foreach($applications as $application) {
            switch($application->approved) {
              case -1:
                $status = 'Denied';
                break;
              case 0:
                $status = 'Waiting';
                break;
              case 1:
                $status = 'Approved';
                break;
            }
            echo "
              <tr>
                <td>
                  <strong>
                    $application->first_name
                  </strong>
                  <div class='row-actions'>
                    <span class='edit'>
                      <a href='?"; $newproperties = $properties; $newproperties['approve_app'] = $application->token; echo http_build_query($newproperties, '', '&amp;'); echo "'>Approve</a>
                      |
                    </span>
                    <span class='edit'>
                      <a href='?"; $newproperties = $properties; $newproperties['deny_app'] = $application->token; echo http_build_query($newproperties, '', '&amp;'); echo "'>Deny</a>
                      |
                    </span>
                    <span class='edit'>
                      <a href='?page=pm-program-manager&amp;pm_page=view-application&amp;app=$application->token'>View</a>
                      |
                    </span>
                    <span class='delete'>
                      <a class='submitdelete' href='?"; $newproperties = $properties; $newproperties['del_app'] = $application->token; echo http_build_query($newproperties, '', '&amp;'); echo "'>Delete</a>
                    </span>
                  </div>
                </td>
                <td>
                  $application->last_name
                </td>
                <td>
                  $status
                </td>
              </tr>
            ";
          }
        } else {
          echo "
            <tr>
              <td>
                No applications found.
              </td>
              <td></td>
              <td></td>
            </tr>
          ";
        }
      ?>
    </tbody>
  </table>
</form>

<? } ?>
