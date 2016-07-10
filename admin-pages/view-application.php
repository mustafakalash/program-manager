<h1>View Application</h1>

<?php
  global $wpdb;
  $application_table_name = $wpdb->prefix.'pm_applications';
  $program_table_name = $wpdb->prefix.'pm_programs';
  $token = $wpdb->_real_escape($_GET['app']);
  $existing_app = $wpdb->get_row("
    SELECT * FROM $application_table_name WHERE `token` = '$token';
  ");
  if(!isset($existing_app)) {
    echo "
      <div class='error'>
        <p>
          <strong>ERROR</strong>
          : Application not found.
        </p>
      </div>
    ";
  } else {
    $program = $existing_app->program;
    $program_name = $wpdb->get_row("
      SELECT `name` FROM $program_table_name WHERE `token` = '$program';
    ")->name;
    if(isset($_POST['del_app'])) {
      echo "
        <div class='notice'>
          <p>
            Are you sure that you want to remove the application from <strong>$existing_app->first_name $existing_app->last_name</strong> for the program <strong>$program_name</strong>?
            <br />
            <a href='?page=pm-program-manager&amp;pm_page=view-applications&amp;program=$program&amp;del_app=$token&amp;confirm_del=1'>Confirm deletion.</a>
          </p>
        </div>
      ";
    } elseif(isset($_POST['approve_app'])) {
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
    } elseif(isset($_POST['deny_app'])) {
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

    $application = $wpdb->get_row("
      SELECT * FROM $application_table_name WHERE `token` = '$token';
    ");

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
 ?>

 <p>View, approve, or deny an application.</p>
 <form method="post">
   <table class="form-table">
     <tbody>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="name">
            First Name
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <input value="<? echo $application->first_name; ?>" name="name" type="text" maxlength="255" required disabled />
         </td>
       </tr>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="name">
            Last Name
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <input value="<? echo $application->last_name; ?>" name="name" type="text" maxlength="255" required disabled />
         </td>
       </tr>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="slots">
            Program
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <select name="program" disabled>
             <option value="<? echo $program; ?>"><? echo $program_name; ?></option>
           </select>
         </td>
       </tr>
       <tr class="form-field form-required">
         <th scope="row">
           <label for="slots">
            Status
            <span class="description">(required)</span>
           </label>
         </th>
         <td>
           <select name="approved" disabled>
             <option value="<? echo $application->approved; ?>"><? echo $status; ?></option>
           </select>
         </td>
       </tr>
     </tbody>
   </table>
   <p>Obviously WIP, more info needs to be added.</p>
   <p class="submit">
     <input name="approve_app" class="button button-primary" value="Approve" type="submit" />
     <input name="deny_app" class="button button-primary" value="Deny" type="submit" />
     <input name="del_app" class="button button-primary" value="Delete" type="submit" />
   </p>
 </form>

<? } ?>
