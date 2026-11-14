// script.js - the AJAX part of the project.
// When a student clicks "Register", this sends the request to PHP
// in the background, then updates the page WITHOUT reloading it.

function registerEvent(eventId, button) {
    // 1. Show the user something is happening
    button.disabled = true;
    button.innerText = "Registering...";

    // 2. Package the data to send (like a hidden form)
    let formData = new FormData();
    formData.append("event_id", eventId);

    // 3. Send it to PHP in the background (this is the AJAX call)
    fetch("register_event.php", {
        method: "POST",
        body: formData
    })
    .then(function (response) {
        return response.json();          // 4. Read PHP's JSON reply
    })
    .then(function (data) {
        if (data.success) {
            // 5a. Success: change the button and the seat count
            button.innerText = "Registered ✓";
            button.classList.add("registered");

            let seatsText = document.getElementById("seats-" + eventId);
            if (data.seats_left > 0) {
                seatsText.innerHTML = "🎟️ " + data.seats_left + " of " + data.max_seats + " seats left";
            } else {
                seatsText.innerHTML = "<span class='full'>❌ Event Full</span>";
            }

            showToast("✅ " + data.message);
        } else {
            // 5b. Failure: show why and let them try again
            showToast("⚠️ " + data.message);
            button.disabled = false;
            button.innerText = "Register";
        }
    })
    .catch(function () {
        showToast("⚠️ Something went wrong. Please try again.");
        button.disabled = false;
        button.innerText = "Register";
    });
}

// A small popup message at the bottom of the screen that disappears by itself
function showToast(message) {
    let toast = document.getElementById("toast");
    toast.innerText = message;
    toast.classList.add("show");

    setTimeout(function () {
        toast.classList.remove("show");
    }, 3000);   // hide after 3 seconds
}
