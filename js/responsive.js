const inputs = document.querySelectorAll(".input");


function addcl(){
	let parent = this.parentNode.parentNode;
	parent.classList.add("focus");
}

function remcl(){
	let parent = this.parentNode.parentNode;
	if(this.value == ""){
		parent.classList.remove("focus");
	}
}


inputs.forEach(input => {
	input.addEventListener("focus", addcl);
	input.addEventListener("blur", remcl);
});
const passwordToggle = document.getElementById('password-toggle');
const passwordInput = document.getElementById('password');

passwordToggle.addEventListener('click', () => {
  const isVisible = passwordInput.type === 'text';
  passwordInput.type = isVisible ? 'password' : 'text';

  passwordToggle.classList.toggle('bxs-lock-open-alt');
  passwordToggle.classList.toggle('bxs-lock-alt');
});
