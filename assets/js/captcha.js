// function onSubmit(token) {
//   document.getElementById("captcha-form").submit();
// }
// for signup form
grecaptcha.ready(() => {
  if(captchaInputIds.length > 0) {
    captchaInputIds.forEach(id => {
      // console.log('the id of element is ',id);
      grecaptcha.execute(GOOGLE_CAPTCHA_SITE_KEY, { action: 'signup' }).then(token => {
        document.querySelector(id).value = token;
      });
    });
  }
});