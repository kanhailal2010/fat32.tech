<?php
$subscription = getFromCache('subscription');
if(empty($subscription)) {
  $res = getUserSubscriptionDetails($_SESSION['user_email']);
  $subscription = isset($res[1]) ? $res[1] : $subscription;
}
// debug($subscription);

$planDetails = getPlans($subscription['sub_plan_code']);
// debug($planDetails);
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Your Profile</h1>
    </div>

    <div class="row">
      <div class="col-4">
        <div class="list-group" id="list-tab" role="tablist">
          <a class="list-group-item list-group-item-action active" id="list-profile-list" data-toggle="list" href="#list-profile" role="tab" aria-controls="profile">Profile</a>
          <a class="list-group-item list-group-item-action" id="list-plan-list" data-toggle="list" href="#list-plan" role="tab" aria-controls="plans">Active Plans</a>
          <!-- <a class="list-group-item list-group-item-action" id="list-messages-list" data-toggle="list" href="#list-messages" role="tab" aria-controls="messages">Messages</a>
          <a class="list-group-item list-group-item-action" id="list-settings-list" data-toggle="list" href="#list-settings" role="tab" aria-controls="settings">Settings</a> -->
        </div>
      </div>
      <div class="col-8">
        <div class="tab-content" id="nav-tabContent">
          <div class="tab-pane fade show active" id="list-profile" role="tabpanel" aria-labelledby="list-profile-list">
            <dl class="row">
              <dt class="col-sm-3">Name</dt>
              <dd class="col-sm-9"><?php echo $_SESSION['user_name']; ?></dd>
              
              <dt class="col-sm-3">Email</dt>
              <dd class="col-sm-9"><?php echo $_SESSION['user_email']; ?></dd>
            </dl>
          </div>

          <div class="tab-pane fade" id="list-plan" role="tabpanel" aria-labelledby="list-plan-list">
            <dl class="row">
              <dt class="col-sm-3">Plan</dt>
              <dd class="col-sm-9"><?php echo $planDetails['name']; ?></dd>
              <dt class="col-sm-3">Plan Code</dt>
              <dd class="col-sm-9"><?php echo $subscription['sub_plan_code']; ?></dd>
              
              <dt class="col-sm-3">Expiry</dt>
              <dd class="col-sm-9"><?php echo Date('Y-m-d',strtotime($subscription['sub_end_date'])); ?></dd>
            </dl>
          </div>
        </div>
      </div>
    </div> <!-- row -->
    
</div>
<!-- /.container-fluid -->