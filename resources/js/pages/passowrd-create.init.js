/*
Template Name: Steex - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Password addon Js File
*/

// password addon
Array.from(document.querySelectorAll("form .auth-pass-inputgroup")).forEach(function (item) {
    Array.from(item.querySelectorAll(".password-addon")).forEach(function (subitem) {
        subitem.addEventListener("click", function (event) {
            var passwordInput = item.querySelector(".password-input");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        });
    });
});

var passInput = document.getElementById("password-input");
var confirmInput = document.getElementById("confirm-password-input")
var letter = document.getElementById("pass-lower");
var capital = document.getElementById("pass-upper");
var number = document.getElementById("pass-number");
var length = document.getElementById("pass-length");
var confirmation = document.getElementById("pass-confirm");

// When the user clicks on the password field, show the message box
passInput.onfocus = function () {
    document.getElementById("password-contain").style.display = "block";
};

// When the user starts to type something inside the password field
passInput.onkeyup = function () {
    // Validate lowercase letters
    var lowerCaseLetters = /[a-z]/g;
    if (passInput.value.match(lowerCaseLetters)) {
        letter.classList.remove("invalid");
        letter.classList.add("valid");
    } else {
        letter.classList.remove("valid");
        letter.classList.add("invalid");
    }

    // Validate capital letters
    var upperCaseLetters = /[A-Z]/g;
    if (passInput.value.match(upperCaseLetters)) {
        capital.classList.remove("invalid");
        capital.classList.add("valid");
    } else {
        capital.classList.remove("valid");
        capital.classList.add("invalid");
    }

    // Validate numbers
    var numbers = /[0-9]/g;
    if (passInput.value.match(numbers)) {
        number.classList.remove("invalid");
        number.classList.add("valid");
    } else {
        number.classList.remove("valid");
        number.classList.add("invalid");
    }

    // Validate length
    if (passInput.value.length >= 8) {
        length.classList.remove("invalid");
        length.classList.add("valid");
    } else {
        length.classList.remove("valid");
        length.classList.add("invalid");
    }
};

confirmInput.onkeyup = function() {
    if (confirmInput.value == passInput.value) {
        confirmation.classList.remove("invalid");
        confirmation.classList.add("valid");
    } else {
        confirmation.classList.remove("valid");
        confirmation.classList.add("invalid");
    }

}
