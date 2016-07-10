<h2>Application</h2>

<?php
  global $wpdb;
  $application_table_name = $wpdb->prefix.'pm_applications';
  $program_table_name = $wpdb->prefix.'pm_programs';
  $program = $wpdb->_real_escape($_GET['program']);
  $existing_program = $wpdb->get_row("
    SELECT * FROM $program_table_name WHERE `token` = '$program';
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
  } elseif($existing_program->slots <= $existing_program->filled_slots) {
    echo "
      <div class='error'>
        <p>
          <strong>ERROR</strong>
          : Program is full.
        </p>
      </div>
    ";
  } else {
    if(isset($_POST['add_app'])) {
      $first_name = $wpdb->_real_escape($_POST['first_name']);
      $last_name = $wpdb->_real_escape($_POST['last_name']);
      $token = md5(uniqid($first_name.$last_name, true));
      $wpdb->insert(
        $application_table_name,
        array(
          'token' => $token,
          'first_name' => $first_name,
          'last_name' => $last_name,
          'program' => $existing_program->token,
          'approved' => 0
        )
      );
      echo "
        <div class='update'>
          <p>
            Your application for the program <strong>$program_name</strong> has been added.
          </p>
        </div>
      ";
    }
 ?>

 <p>Apply for the program <strong><? echo $existing_program->name; ?></strong></p>
 <form method="post">
   <table class="form-table">
     <tbody>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="first_name">
            First Name
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <input name="first_name" type="text" maxlength="255" required />
         </td>
       </tr>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="last_name">
            Last Name
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <input name="last_name" type="text" maxlength="255" required />
         </td>
       </tr>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="program">
            Program
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <select name="program" disabled>
             <option value="<? echo $program; ?>"><? echo $existing_program->name; ?></option>
           </select>
         </td>
       </tr>
     </tbody>
   </table>
   <p>Obviously WIP, more info needs to be added.</p>
   <p class="submit">
     <input name="add_app" class="button button-primary" value="Apply" type="submit" />
   </p>
 </form>

<? } ?>
