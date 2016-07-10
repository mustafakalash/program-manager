<h1>Edit Program</h1>

<?php
  global $wpdb;
  $program_table_name = $wpdb->prefix.'pm_programs';
  $token = $wpdb->_real_escape($_GET['program']);
  $existing_program = $wpdb->get_row("
    SELECT * FROM $program_table_name WHERE `token` = '$token';
  ");
  if(!isset($existing_program)) {
    echo "
      <div class='error'>
        <p>
          <strong>ERROR</strong>
          : Program not found.
        </p>
      </div>
    ";
  } else {
    $name = $existing_program->name;
    $slots = $existing_program->slots;
    if(isset($_POST['editprogram']))  {
      $name = $wpdb->_real_escape($_POST['name']);
      $slots = $wpdb->_real_escape($_POST['slots']);
      $wpdb->update(
        $program_table_name,
        array(
          'name' => $name,
          'slots' => $slots,
        ),
        array(
          'token' => $token
        )
      );
      echo "
        <div class='updated'>
          <p>The program <strong>$name</strong> has been edited.</p>
        </div>
      ";
    }
 ?>

<p>Edit a program.</p>
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
          <input value="<? echo $name; ?>" name="name" type="text" maxlength="255" required />
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
          <input value="<? echo $slots; ?>" name="slots" type="number" min="0" max="32767" required />
        </td>
      </tr>
    </tbody>
  </table>
  <p class="submit">
    <input name="editprogram" class="button button-primary" value="Edit Program" type="submit" />
  </p>
</form>

<? } ?>
