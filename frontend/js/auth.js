document.addEventListener("DOMContentLoaded", function () {
  // Example: Alert on submit
  document.querySelectorAll(".auth-form").forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      alert("Authentication logic goes here.");
      this.reset();
    });
  });
});
