const body = document.querySelector("body");
const sidebar = body.querySelector(".sidebar");
const toggle = body.querySelector(".toggle");
const modeSwitch = body.querySelector(".toggle-switch");
const modeText = body.querySelector(".mode-text");


toggle.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});

modeSwitch.addEventListener("click", () => {
  body.classList.toggle("dark");
  modeText.innerText = body.classList.contains("dark") ? "Light Mode" : "Dark Mode";

  if(body.classList.contains("dark")){
    modeText.innerText = "Light Mode"
  }else{
    modeText.innerText = "Dark Mode"
  }
});

