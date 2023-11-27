function onSubmit(token) {
  document.getElementById("captcha-form").submit();
}
// for signup form
grecaptcha.ready(() => {
  grecaptcha.execute(GOOGLE_CAPTCHA_SITE_KEY, { action: 'signup' }).then(token => {
    document.querySelector('#recaptchaResponse').value = token;
  });
});