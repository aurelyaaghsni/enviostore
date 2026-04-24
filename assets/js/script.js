// Animasi sparkle — muncul & hilang bergantian
document.querySelectorAll(".sparkle").forEach((el, i) => {
  const duration = 2.2 + i * 0.4;
  const delay = i * 0.5;
  el.style.animation = `sparkle ${duration}s ease-in-out ${delay}s infinite`;
});

// Animasi partikel naik ke atas
document.querySelectorAll(".particle").forEach((el, i) => {
  const duration = 3 + i * 0.7;
  const delay = i * 0.8;
  el.style.animation = `float-particle ${duration}s ease-out ${delay}s infinite`;
});

// ===== MODAL =====
function bukaModal(id) {
  document.getElementById(id).classList.add("active");
}
function tutupModal(id) {
  document.getElementById(id).classList.remove("active");
}

// Tutup modal kalau klik di luar box
document.addEventListener("click", function (e) {
  if (e.target.classList.contains("modal-overlay")) {
    e.target.classList.remove("active");
  }
});

// ===== GENERATE RFID =====
function generateRFID() {
  // Simulasi ID kartu RFID — format: ENVIO-XXXXXXXX
  const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  let result = "ENVIO-";
  for (let i = 0; i < 8; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  document.getElementById("rfid_input").value = result;
}

// ===== BUKA MODAL EDIT =====
function bukaEdit(id, nama, hp, email) {
  document.getElementById("edit_id").value = id;
  document.getElementById("edit_nama").value = nama;
  document.getElementById("edit_hp").value = hp;
  document.getElementById("edit_email").value = email;
  bukaModal("modalEdit");
}
