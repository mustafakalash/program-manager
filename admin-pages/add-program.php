<h1>Add New Program</h1>

<?php
  global $wpdb;
  $program_table_name = $wpdb->prefix.'pm_programs';
  if(isset($_POST['createprogram']))  {
    $name = $wpdb->_real_escape($_POST['name']);
    $token = md5(uniqid($name, true));
    $slots = $wpdb->_real_escape($_POST['slots']);
    $wpdb->insert(
      $program_table_name,
      array(
        'token' => $token,
        'name' => $name,
        'slots' => $slots,
        'filled_slots' => 0
      )
    );
    echo "
      <div class='updated'>
        <p>The program <strong>$name</strong> has been added.</p>
      </div>
    ";
  }
 ?>

<p>Create a new program.</p>
<form method="post">
  <table class="form-table">
    <tbody>
      <tr class="form-field form-required">
        <th scope="row">
          <label for="name">
           Name
           <span class="description">(required)</span>
          </label>
        </th>
        <td>
          <input name="name" type="text" maxlength="255" required />
        </td>
      </tr>
      <tr class="form-field form-required">
        <th scope="row">
          <label for="slots">
           Slots
           <span class="description">(required)</span>
          </label>
        </th>
        <td>
          <input name="slots" type="number" min="0" max="32767" required />
        </td>
      </tr>
    </tbody>
  </table>
  <p class="submit">
    <input name="createprogram" class="button button-primary" value="Add New Program" type="submit" />
  </p>
</form>
