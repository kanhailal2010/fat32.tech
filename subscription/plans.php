<?php 
$paidPlans = getPaidPlans();
// debug($paidPlans);
?>
<div class="background">
<section id="plans" class="features-area">
<div class="row">
  <div class="col"></div>
  <div class="col-10 col-sm-10 text-center">
  
    <div class="panel pricing-table flex">
      <?php $i = 0; ?>
      <?php foreach($paidPlans as $code => $plan ): $i++;?>
      <div class="pricing-plan">
        <img src="<?=siteUrl('assets/images/'.$plan['image']);?>" alt="" class="pricing-img">
        <h2 class="pricing-header"><?=$plan['title']?></h2>
        <ul class="pricing-features">
          <li class="pricing-features-item"><?=$plan['name']?></li>
          <li class="pricing-features-item">Full access to features for <?=$plan['duration']?> Days.</li>
        </ul>
        <span class="pricing-price">&#8377;<?=$plan['price']?>/-</span>
        <?php if(!isset($_SESSION['logged_in'])): ?>
        <a href="/login?redirect_to=<?php echo urlencode(siteUrl('subscription/plans'));?>" class="button-30">Sign up</a>
        <?php else: ?>
        <form action="/subscription/checkout" method="post">
          <input type="hidden" value="<?=$code?>" id="subscription_plan_code" name="subscription_plan_code"/>
          <input type="hidden" value="<?=$plan['name']?>" id="subscription_plan_name" name="subscription_plan_name"/>
          <input type="hidden" value="2" id="subscription_plan_id" name="subscription_plan_id"/>
          <input type="hidden" value="" id="plan<?=$i?>" name="recaptcha_response" />
        <button type="submit" name="selected_plan" value="monthly" class="button-30">Buy <?=$plan['title']?></button>
        </form>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <!-- <div class="pricing-plan">
        <img src="https://s28.postimg.cc/ju5bnc3x9/plane.png" alt="" class="pricing-img">
        <h2 class="pricing-header">Small team</h2>
        <ul class="pricing-features">
          <li class="pricing-features-item">Never sleeps</li>
          <li class="pricing-features-item">Multiple workers for more powerful apps</li>
        </ul>
        <span class="pricing-price">$150</span>
        <a href="#/" class="button-30 is-featured">Free trial</a>
      </div>
      
      <div class="pricing-plan">
        <img src="https://s21.postimg.cc/tpm0cge4n/space-ship.png" alt="" class="pricing-img">
        <h2 class="pricing-header">Enterprise</h2>
        <ul class="pricing-features">
          <li class="pricing-features-item">Dedicated</li>
          <li class="pricing-features-item">Simple horizontal scalability</li>
        </ul>
        <span class="pricing-price">$400</span>
        <a href="#/" class="button-30">Free trial</a>
      </div> -->

    </div> <!-- panel -->

    
      </div> <!-- col-10 -->

      <div class="col"></div>

    </div><!-- row -->
  </section>
</div><!-- background -->

<script>
  let GOOGLE_CAPTCHA_SITE_KEY = '<?php echo $_ENV['GOOGLE_CAPTCHA_SITE_KEY']; ?>';
  let captchaInputIds = ['#plan1','#plan2','#plan3'];
</script>
<?php 
  $globalJs = [
    'Google Captcha JS' => 'https://www.google.com/recaptcha/api.js?render='.$_ENV['GOOGLE_CAPTCHA_SITE_KEY'],
    'Capcha Code'       => siteUrl('/assets/js/captcha.js'),
  ];
?>