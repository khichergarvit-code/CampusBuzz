function registerEvent(eventId, btn) {
    btn.disabled = true;
    btn.innerText = "Wait...";

    let fd = new FormData();
    fd.append("event_id", eventId);

    fetch("register_event.php", { method: "POST", body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.innerText = "Registered";
                btn.classList.add("btn-done");
                let el = document.getElementById("seats-" + eventId);
                el.innerHTML = data.seats_left > 0
                    ? `<b>${data.seats_left}</b> / ${data.max_seats} seats left`
                    : "<span class='err'>Event Full</span>";
                showToast(data.message);
            } else {
                showToast(data.message);
                btn.disabled = false;
                btn.innerText = "Register";
            }
        })
        .catch(() => {
            showToast("Request failed.");
            btn.disabled = false;
            btn.innerText = "Register";
        });
}

function showToast(msg) {
    let t = document.getElementById("toast");
    t.innerText = msg;
    t.className = "show";
    setTimeout(() => t.className = "", 2500);
}
