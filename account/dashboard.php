<?php 
$email = getSessionValue('user_email', null);
$user = getUserByEmail($email);
$accountStatusClass = $user['active'] ? 'success' : 'danger';
$accountStatus      = $user['active'] ? 'ACTIVE' : 'INACTIVE';
saveToCache('account_status', $accountStatus);

$subscription = getUserSubscriptionDetails($email);
saveToCache('subscription',$subscription[1]);

// debug($user);
// debug($subscription);
$subClass       = isset($subscription[1]['subscription_status']) && $subscription[1]['subscription_status'] == 'active' ? 'success' : 'danger';
$subTxt         = isset($subscription[1]['subscription_status']) ? $subscription[1]['subscription_status'] : 'INACTIVE';

$endDate        = isset($subscription[1]['sub_end_date']) ? $subscription[1]['sub_end_date'] : Date('Y-m-d 00:00:00');
$subEndingIn    = daysRemaining($endDate);
$endDateTimeStamp = strtotime($endDate);
$todayTimeStamp   = strtotime(Date('Y-m-d 00:00:00'));

$endDateDisp    = Date('Y-m-d', $endDateTimeStamp);
$endDateDisp    = ($todayTimeStamp > $endDateTimeStamp) ? "Ended:$endDateDisp" : "Ends:$endDateDisp";

$queuedDuration = getQueuedSubscriptionDays($user['id']);
$totalDuration  = $subEndingIn + $queuedDuration;
$subEndingIn    = $queuedDuration > 0 ? "$subEndingIn + ($queuedDuration Days queued)": $subEndingIn;
?>
<!-- Begin Page Content -->
<div class="container-fluid dashboard">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-<?=$accountStatusClass?> text-white shadow">
                <div class="card-body">
                    Account Status
                    <div class="text-white-50 small text-uppercase"><?=$accountStatus?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-<?=$subClass?> text-white shadow">
                <div class="card-body">
                    Subscription Status
                    <div class="text-white-50 small text-uppercase">
                      <?=$subTxt?>
                      <span class="float-right"><?=$endDateDisp?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Subscription Ending In (Days)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?=$subEndingIn?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Receipts (Annual)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">&#8377;100/-</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div> -->

    </div> <!-- row -->
    
    <div class="row">
      <div class="col-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pending Actions</h6>
            </div>
            <div class="card-body">
              <p>Your subsciption to Extension is ending in <?=$totalDuration?> Days!!. Renew it soon. </p>
              <a href="<?php echo siteUrl('subscription');?>" class="btn btn-light btn-icon-split btn-lg">
                  <span class="icon text-gray-600">
                      <i class="fas fa-shopping-cart"></i>
                  </span>
                  <span class="text">Renew Now!</span>
              </a>
            </div>
        </div>
      </div>
    </div> <!-- row -->

    <!-- Content Row -->
    <div class="row">
        <!-- <div class="col-md-6">
            <h4 class="small font-weight-bold">Subscription Remaining <span class="float-right">80%</span></h4>
            <div class="progress mb-4">
                <div class="progress-bar bg-info" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
         </div> -->
    </div> <!-- row -->

    <!-- Content Row -->
    <div class="row">
        <!-- <div class="col-xl-12 col-md-12 mb-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trial Period</h6>
                </div>
                <div class="card-body">
                    <p>You are currently serving the trial period.</p>
                    <p class="mb-0">Your Trial period will end in 14Days.</p>
                </div>
            </div>
        </div> -->
    </div> <!-- row -->

    <div class="row">
        <!-- Earnings (Monthly) Card Example -->
        <!-- <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Earnings (Monthly)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$40,000</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Earnings (Monthly) Card Example -->
        <!-- <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Earnings (Annual)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$215,000</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Earnings (Monthly) Card Example -->
        <!-- <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasks
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">50%</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: 50%" aria-valuenow="50" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Pending Requests Card Example -->
        <!-- <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>

</div>
<!-- /.container-fluid -->