function logout() {
	// Sending a request to logout.php
	fetch("/logout/logout.php").then(() => {
	  // Redirecting the user back to the login page
	  window.location.href = "/index.html";
	});
}

function openAIAssistant() {
    document.getElementById("aiModal").style.display = "block";
}

function closeAIAssistant() {
    document.getElementById("aiModal").style.display = "none";
}

function generateAIProfile() {
    const text = document.getElementById("userInputText").value;
    const status = document.getElementById("aiStatus");
    status.innerText = "Generating... Please wait.";

    fetch("/ai/generate_profile.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ description: text })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            status.innerText = "Profile created successfully!";
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            status.innerText = "Failed: " + data.message;
        }
    })
    .catch(err => {
        status.innerText = "Error: " + err.message;
    });
}
