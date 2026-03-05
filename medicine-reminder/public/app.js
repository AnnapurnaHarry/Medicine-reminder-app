document.addEventListener("DOMContentLoaded", () => {
  document.body.addEventListener("click", async (e) => {
    const btn = e.target.closest("[data-toggle-taken]")
    if (btn) {
      const scheduleId = btn.getAttribute("data-toggle-taken")
      const date = btn.getAttribute("data-date")
      try {
        const res = await fetch("/medicine-reminder/toggle_taken.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ schedule_id: scheduleId, date }),
        })
        const data = await res.json()
        if (data.success) {
          // update UI
          const check = document.querySelector(`#check-${scheduleId}`)
          if (check) check.checked = data.taken === 1
          const prog = document.querySelector("#progress-bar")
          if (prog && data.progressPct != null) {
            prog.style.width = `${data.progressPct}%`
          }
        } else {
          alert(data.message || "Failed to toggle.")
        }
      } catch (err) {
        alert("Network error.")
      }
    }
    // add another time input in add_medicine page
    if (e.target && e.target.matches("[data-add-time]")) {
      e.preventDefault()
      const wrap = document.querySelector("#times-wrapper")
      if (!wrap) return
      const div = document.createElement("div")
      div.className = "inline"
      div.innerHTML =
        '<input type="time" name="times[]" required class="input" style="max-width:200px" /> <button class="btn btn-ghost" data-remove-time>Remove</button>'
      wrap.appendChild(div)
    }
    if (e.target && e.target.matches("[data-remove-time]")) {
      e.preventDefault()
      const parent = e.target.closest(".inline")
      if (parent) parent.remove()
    }
  })
})
