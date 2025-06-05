function togglePasswordVisibility(inputId, icon) {
  const input = document.getElementById(inputId);
  const isHidden = input.type === "password";

  input.type = isHidden ? "text" : "password";

  // Ganti ikon tergantung kondisi
  icon.src = isHidden
    ? "https://i.imgur.com/03EOylj.png" // eye-slash
    : "https://cdn-icons-png.flaticon.com/512/709/709612.png"; // eye
}
