document.addEventListener("DOMContentLoaded", function () {
  let age = 0;

  const nowDate = new Date().getTime();
  const dateOfBirthField = document.getElementsByName(
    "account_date_of_birth"
    )[0];
  const registerBtn = document.querySelector('.register button[type="submit"]');

  function setAge(value) {
    const selectedDate = new Date(value).getTime();
    const ageInMiliseconds = nowDate - selectedDate;
    age = parseInt(ageInMiliseconds / (1000 * 60 * 60 * 24 * 365.25));
  }

  if (dateOfBirthField) {
    dateOfBirthField.addEventListener("keyup", ({ target }) => setAge(target.value));
    dateOfBirthField.addEventListener('input', ({ target }) => setAge(target.value))
  }

  if (registerBtn) {
    registerBtn.addEventListener("click", function (e) {
      const underage = document.querySelector(".register .underage");
      const nameField = document.querySelector(
        '.register input[name="account_first_name"]'
      );
      const lastNameField = document.querySelector(
        '.register input[name="account_last_name"]'
      );
      const dateOfBirthField = document.querySelector(
        '.register input[name="account_date_of_birth"]'
      );

      setAge(dateOfBirthField.value)

      console.log(age);
  
      if (
        nameField.value !== "" &&
        lastNameField.value !== "" &&
        dateOfBirthField.value !== "" &&
        age < 18
      ) {
        e.preventDefault();
        underage.classList.toggle("hide");
      }
    });
  }
});
