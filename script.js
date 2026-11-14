// Send AJAX request to register the logged-in user for an event
function registerEvent(eventId, btn) {
    // Disable button immediately to prevent double-clicks
    btn.disabled = true;
    btn.innerText = "Wait...";

    // Prepare form data with event ID
    let fd = new FormData();
    fd.append("event_id", eventId);

    // Send POST request to register_event.php
    fetch("register_event.php", { method: "POST", body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update button and remaining seats count on success
                btn.innerText = "Registered";
                btn.classList.add("btn-done");
                let el = document.getElementById("seats-" + eventId);
                el.innerHTML = data.seats_left > 0
                    ? `<b>${data.seats_left}</b> / ${data.max_seats} seats left`
                    : "<span class='err'>Event Full</span>";
                showToast(data.message);
            } else {
                // Re-enable button and show error message
                showToast(data.message);
                btn.disabled = false;
                btn.innerText = "Register";
            }
        })
        .catch(() => {
            // Handle network or server errors
            showToast("Request failed.");
            btn.disabled = false;
            btn.innerText = "Register";
        });
}

// Display a temporary popup notification at the bottom of the screen
function showToast(msg) {
    let t = document.getElementById("toast");
    t.innerText = msg;
    t.className = "show";
    setTimeout(() => t.className = "", 2500);
}
