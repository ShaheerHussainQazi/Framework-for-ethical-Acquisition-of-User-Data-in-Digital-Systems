const form = document.querySelector('form');
const username = document.querySelector('input[name="username"]');
const password = document.querySelector('input[name="password"]');

form.addEventListener('submit', (event) => {
	event.preventDefault();

	// validating form
	if (username.value === '' || password.value === '') {
		alert('Please enter both username and password.');
		return;
	}

	// submiting form
	form.submit();
});
