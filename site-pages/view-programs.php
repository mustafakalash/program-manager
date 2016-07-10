<?php
  global $wpdb;
  $program_table_name = $wpdb->prefix.'pm_programs';
  $application_table_name = $wpdb->prefix.'pm_applications';

  $sql = [];
  $sql[] = "
    SELECT * FROM $program_table_name
  ";
  $where = [];
  if(isset($_GET['pm_s'])) {
    $query = $wpdb->_real_escape($_GET['pm_s']);
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
    'pm_page' => 'view-programs',
    'orderby' => $orderby,
    'orderdir' => $orderdir,
    'pm_s' => $_GET['pm_s'],
    'hidefull' => $_GET['hidefull']
  );

  $programs = $wpdb->get_results(implode(" ", $sql).';');
?>

<style>
  table.fixed {
    table-layout: fixed;
  }
  table.widefat {
    border-spacing: 0;
    width: 100%;
    clear: both;
    margin: 0;
  }
  .widefat * {
    word-wrap: break-word;
  }
  .sorting-indicator {
    display: block;
    visibility: hidden;
    width: 10px;
    height: 4px;
    margin-top: 8px;
    margin-left: 7px;
  }
  .sorting-indicator::before {
    font: 400 20px/1 dashicons;
    display: inline-block;
    padding: 0;
    top: -4px;
    left: -8px;
    line-height: 100%;
    position: relative;
    vertical-align: top;
  }
  th.asc .sorting-indicator::before, th.desc:hover .sorting-indicator::before {
    content: "\f142";
  }
  th.desc .sorting-indicator::before, th.asc:hover .sorting-indicator::before {
    content: "\f140";
  }
  th.sorted .sorting-indicator, th.sortable:hover .sorting-indicator {
    visibility: visible;
  }
  th.sortable a span {
    float: left;
  }
  p.search-box {
    float: right;
    margin: 0 0 6px 0;
  }
  div.tablenav {
    clear: both;
    margin: 0 0 4px;
  }
</style>

<h2>Program List</h2>
<form method="get">
  <?
    while($property = current($properties)) {
      $key = key($properties);
      next($properties);
      if($key === 'hidefull') {
        continue;
      }
      echo "
        <input type='hidden' name='$key' value='$property' />
      ";
    }
  ?>
  <p class="search-box">
    <input name="pm_s" type="search" value="<? echo $_GET['pm_s']; ?>" />
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
                  <strong>";
                  if($slots !== 'FULL') {
                    echo "<a href='?pm_page=add-application&amp;program=$program->token'>$program->name</a>";
                  } else {
                    echo $program->name;
                  }
                  echo "
                  </strong>
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
