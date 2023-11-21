<?php 
require_once(__DIR__.'/header.php'); 
require_once(__DIR__.'/db.php'); 
?>
<?php // change passowrd logic here 
$alertDisplay = 'none';
$alertMsg     = '';
$alertType    = '';

if(isset($_POST['change_password'])) {
  $password   = $_POST['password'];
  $password2  = $_POST['password2'];
  
  $alertDisplay = 'block';
  $alertMsg     = 'Password change failed. Enter Same password on both fields and try again';
  $alertType    = 'danger';
  if($password==$password2 && isset($_SESSION['user_email']) && changePassword($_SESSION['user_email'],$password)) {
    $alertDisplay = 'block';
    $alertMsg     = 'Password changed successfully';
    $alertType    = 'success';
  }

}
?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php include_once('topbar_menu.php');?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Change Password</h1>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <div class="alert alert-<?php echo $alertType;?>" role="alert" style="display:<?php echo $alertDisplay;?>">
                          <?php echo $alertMsg; ?>
                        </div>

                      <form method="post">
                        <div class="form-group">
                          <label for="pass1">Password</label>
                          <input type="text" required="true" name="password" class="form-control" id="pass1" placeholder="Password">
                        </div>
                        <div class="form-group">
                          <label for="pass2">Confirm Password</label>
                          <input type="password" required="true" name="password2" class="form-control" id="pass2" placeholder="Confirm Password">
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Submit</button>
                      </form>

                      </div>
                    </div> <!-- row -->
                    
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
<?php include_once('footer.php');?>