// ====================================================
// ================================== FB Login Script
// ====================================================
// https://developers.facebook.com/docs/javascript/reference/FB.api
// https://developers.facebook.com/docs/graph-api/reference/user

  // function statusChangeCallback(response) {  // Called with the results from FB.getLoginStatus().
  //   console.log('FB statusChangeCallback');
  //   console.log(response);                   // The current login status of the person.
  //   if (response.status === 'connected') {   // Logged into your webpage and Facebook.
  //     // testAPI();  
  //     FB.api('/me', {fields: 'last_name,email,picture'},function(meres) {
  //         console.log(meres);  
  //      console.log('Good to see you, ' + meres.name + '.');
  //      console.log('Your id is ',meres.id);
  //      FB.api( `/${meres.id}/`,
  //           function (pres) {
  //               if (pres && !pres.error) {
  //               console.log('profile api ',pres);
  //             }
  //           }
  //       );
  //    });
  //   if (response.status === 'connected') {   // Logged into your webpage and Facebook.
  //     // testAPI();  
  //     document.getElementById('status').innerHTML = 'logged in code pending';

  //   } else {                                 // Not logged into your webpage or we are unable to tell.
  //     document.getElementById('status').innerHTML = 'Please log ' +
  //       'into this webpage.';
  //   }
  // }

  // window.fbAsyncInit = function() {
  //   FB.init({
  //     appId      : FB_APP_ID,
  //     xfbml      : true,
  //     version    : 'v18.0'
  //   });
  //   FB.AppEvents.logPageView();
  //   FB.getLoginStatus(function(response) {   // Called after the JS SDK has been initialized.
  //     statusChangeCallback(response);        // Returns the login status.
  //   });
  // };

  // (function(d, s, id){
  //    var js, fjs = d.getElementsByTagName(s)[0];
  //    if (d.getElementById(id)) {return;}
  //    js = d.createElement(s); js.id = id;
  //    js.src = "https://connect.facebook.net/en_US/sdk.js";
  //    fjs.parentNode.insertBefore(js, fjs);
  //  }(document, 'script', 'facebook-jssdk'));

  //  document.getElementById('fb_login').addEventListener("click", function() {
  //   // document.getElementById("demo").innerHTML = "Hello World";
  //   FB.login(function(response) {
  //     // handle the response
  //     console.log('fb login response ', response);
  //     if (response.status === 'connected') {
  //       // Logged into your webpage and Facebook.
  //     } else {
  //       // The person is not logged into your webpage or we are unable to tell. 
  //     }
  //     }, {scope: 'public_profile,email'});
  // });


// ====================================================
// ================================== Validation Script
// ====================================================
(function () {
  "use strict";

  var FormValidation = function(obj) {
      var inputField = function (data) {
          this.obj = data;
          this.validations = typeof data.dataset.inputValidation !== "undefined" ? getValidations(data.dataset.inputValidation) : [];
          this.isValid = true;
          this.errorText = [];
      };
      var validation = function (data) {
          this.message = data.message;
          this.regexp = data.regexp;
      };

      var submitBtnCssClass = "submit-btn",
          formValidCssClass = "form-valid",
          errorCssClass = "field-wrapper-has-error",
          validTagNames = ["input", "textarea", "select"],
          inputFields = getValidTagNames(),
          submitBtn = obj.getElementsByClassName(submitBtnCssClass)[0],
          requiredFieldText = obj.dataset.requiredFieldText,
          isValid = false,
          lookup = {};

      function getValidations(inputValidation) {
          var data = JSON.parse(inputValidation);
          var items = [];
          for (var i = 0; i < data.length; i++) {
              items.push(new validation(data[i]));
          }

          return items;
      };

      function validateForm(e) {
          var isValidForm = true;
          if (e.target.type !== "button") {
              var input = lookup[e.target.id];

              // Validate input
              validateInputFieldWithMarkup(input);
          } else {
              // Todo: validate on button click
              getValidTagNames().forEach(function(field) {
                  validateInputFieldWithMarkup(field);
              });
          }

          // Validate form and activate button if form is valid
          getValidTagNames().forEach(function (field) {
              if (!validateInputField(field)) {
                  isValidForm = false;
                  return;
              }
          });

          isValid = isValidForm;

          if (isValid) {
              submitBtn.disabled = false;
              submitBtn.classList.remove('disabled');

              if (!obj.classList.contains(formValidCssClass)) {
                  obj.classList.add(formValidCssClass);
              }
          } else {
              submitBtn.disabled = true;
              submitBtn.classList.add('disabled');
              obj.classList.remove(formValidCssClass);
          }
      };

      function validateInputField(field) {
          field.isValid = true;

          if (field.obj.required && hasEmptyValue(field.obj.value)) {
              field.isValid = false;
          }

          if (!isEmpty(field.validations)) {
              for (var i = 0; i < field.validations.length; i++) {
                  if (!isValidRegexp(field.validations[i].regexp, field.obj.value) && field.validations[i].regexp !== null) {
                      field.isValid = false;
                  }
              }
          }

          return field.isValid;
      };

      function validateInputFieldWithMarkup(input) {
          var validations = input.validations;
          var errorMsg = [], error = false;

          if (validations.length > 0) {
              for (var i = 0; i < validations.length; i++) {
                  if (!isValidRegexp(validations[i].regexp, input.obj.value) && validations[i].regexp !== null) {
                      errorMsg.push(validations[i].message);
                      error = true;
                  }
              }

              if (hasEmptyValue(input.obj.value) && input.obj.required) {
                  errorMsg.push(requiredFieldText);
                  error = true;
              }

              if (error) {
                  setInvalidMarkup(input, errorMsg);
              } else {
                  setValidMarkup(input);
                  input.isValid = true;
              }
          } else if (input.obj.required) {
              if (hasEmptyValue(input.obj.value)) {
                  setInvalidMarkup(input, [requiredFieldText]);
              } else {
                  setValidMarkup(input);
              }
          }
      };

      function getValidTagNames() {
          var tagNames = [], tagName;

          for (var i = 0; i < validTagNames.length; i++) {
              tagName = obj.getElementsByTagName(validTagNames[i]);
              if (typeof tagName !== "undefined") {
                  for (var j = 0; j < tagName.length; j++) {
                      tagNames.push(new inputField(tagName[j]));
                  }
              }
          }

          return tagNames;
      };

      // Input Field Required Markup
      function setRequiredMarkup(input) {
          // input.previousSibling.previousSibling.outerHTML += " *";
          input.parentNode.parentNode.querySelector('label').innerHTML += '*';
      };

      //Input Field Valid Markup
      function setValidMarkup(input) {
          input.obj.parentElement.classList.remove(errorCssClass);

          input.isValid = true;
      };

      //Input Field Invalid Markup
      function setInvalidMarkup(input, errorMsg) {
          var errorElement = input.obj.nextSibling.nextSibling;

          if (typeof errorElement !== "undefined" && errorElement) {
              errorElement.textContent = errorMsg.join(", ");
              input.isValid = false;
          }

          var pNodes = input.obj.parentNode.classList;

          for (var i = 0; i < pNodes.length; i++) {
              if (pNodes[i] === errorCssClass) {
                  return;
              }
          }

          input.obj.parentElement.className += " " + errorCssClass;
      };

      function isEmpty(_obj) {
          for (var prop in _obj) {
              if (_obj.hasOwnProperty(prop))
                  return false;
          }

          return true && JSON.stringify(_obj) === JSON.stringify({});
      };

      function isValidRegexp(regexp, value) {
        // console.log('the vlaue ',regexp, value);
          if (regexp == "max30") { // max 30 chars
              regexp = /^[a-zA-ZåäöÅÄÖ\s]{0,5}$/;
          } else if (regexp == "numbers") { // numbers only
              regexp = /^[0-9]*$/;
          } else if (regexp == 'email') { // email
              regexp = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          } else if (regexp == 'phone_if_filled' && value!='') { // validate 10 digit phone if filled
            regexp = /^\d{10}$/;
          } else if (regexp == 'full_name') { // full_name
            regexp = /^[a-zA-ZåäöÅÄÖ\s]{0,50}$/;
          } else if (regexp == 'password') { // password
            regexp = /^(?=.*[^a-zA-Z0-9]).{8,}$/;
          } else if (regexp == 'phone_if_filled' && value =='') { // do not validate if phone field empty
            return true;
          } else if (regexp == 'min8') {
            regexp = /.{8,}/;
          } else if (regexp == 'atleast_one_special') {
            regexp = /[!#$%&()*+,_.:@<>?[\]{}^|]/; // /[^a-zA-Z0-9\s]/;
          } else if (regexp == 'atleast_one_digit') {
            regexp = /\d/;
          }
          return new RegExp(regexp).test(value);
      };

      function hasEmptyValue(val) {
          return val == null || val === "";
      };

      // Shorcut function for debugging
      function debug(text, _obj) {
          if (typeof _obj === "undefined") { _obj = ""; }
          console.log(text, _obj);
      };

      function init() {
          if (inputFields.length === 0) return;

          // Create a lookup array to easily find inputs by id
          for (var i = 0, len = inputFields.length; i < len; i++) {
              lookup[inputFields[i].obj.id] = inputFields[i];
          };

          obj.addEventListener("blur", validateForm, true);

          if (!isValid) {
              submitBtn.disabled = true;
          }

          // Add * for required fields
          inputFields.forEach(function (field) {
              if (field.obj.required) {
                  setRequiredMarkup(field.obj);
              }
          });
      };

      init();
  };

var form = document.getElementsByClassName("js-form")[0];
form.classList.remove("form-has-loaded");

FormValidation(form);

})();