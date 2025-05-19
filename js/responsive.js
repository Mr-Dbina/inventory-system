const inputs = document.querySelectorAll(".input");

function addcl() {
  let parent = this.parentNode.parentNode;
  parent.classList.add("focus");
}

function remcl() {
  let parent = this.parentNode.parentNode;
  if (this.value == "") {
    parent.classList.remove("focus");
  }
}

// Apply focus/blur events to all inputs
inputs.forEach(input => {
  input.addEventListener("focus", addcl);
  input.addEventListener("blur", remcl);
});

// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
  const passwordToggle = document.getElementById('password-toggle');
  const passwordInput = document.getElementById('password');
  
  if (passwordToggle && passwordInput) {
    passwordToggle.addEventListener('click', () => {
      // Toggle between password and text type
      const isVisible = passwordInput.type === 'text';
      passwordInput.type = isVisible ? 'password' : 'text';
      
      // Toggle icon classes - use the correct Boxicons classes
      passwordToggle.classList.remove('bxs-lock');
      passwordToggle.classList.remove('bxs-lock-open');
      
      // Add the appropriate icon
      if (isVisible) {
        passwordToggle.classList.add('bxs-lock');
      } else {
        passwordToggle.classList.add('bxs-lock-open');
      }
    });
  }
});