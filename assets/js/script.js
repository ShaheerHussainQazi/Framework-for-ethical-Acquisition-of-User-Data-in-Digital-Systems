function logout() {
	// Sending a request to logout.php
	fetch("/logout/logout.php").then(() => {
	  // Redirecting the user back to the login page
	  window.location.href = "/index.html";
	});
}

function goback() {
    window.history.back();
}