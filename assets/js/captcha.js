function onSubmit(token) {
  document.getElementById("captcha-form").submit();
}
// for signup form
grecaptcha.ready(() => {
  grecaptcha.execute(google_captcha_site_key, { action: 'signup' }).then(token => {
    document.querySelector('#recaptchaResponse').value = token;
  });
});