// Format Rupiah
function formatRupiah(angka, prefix = "Rp ") {
  var number_string = angka.replace(/[^,\d]/g, "").toString(),
    split = number_string.split(","),
    sisa = split[0].length % 3,
    rupiah = split[0].substr(0, sisa),
    ribuan = split[0].substr(sisa).match(/\d{3}/gi);

  if (ribuan) {
    separator = sisa ? "." : "";
    rupiah += separator + ribuan.join(".");
  }

  rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
  return prefix + rupiah;
}

// Input Rupiah
document.querySelectorAll(".rupiah-input").forEach(function (input) {
  input.addEventListener("keyup", function (e) {
    this.value = formatRupiah(this.value, "Rp ");
  });
});

// Form Validation
(function () {
  "use strict";

  // Fetch all forms that need validation
  var forms = document.querySelectorAll(".needs-validation");

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add("was-validated");
      },
      false
    );
  });
})();

// Konfirmasi Delete
function confirmDelete(url, name = "") {
  if (confirm("Apakah Anda yakin ingin menghapus " + name + " ini?")) {
    window.location.href = url;
  }
}

// Preview Image
function previewImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function (e) {
      document.querySelector("#preview").src = e.target.result;
    };

    reader.readAsDataURL(input.files[0]);
  }
}

// DataTables Initialization
$(document).ready(function () {
  if ($("#dataTable").length) {
    $("#dataTable").DataTable({
      responsive: true,
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        infoFiltered: "(disaring dari _MAX_ total data)",
        zeroRecords: "Tidak ada data yang cocok",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "Selanjutnya",
          previous: "Sebelumnya",
        },
      },
    });
  }
});

// Alert Timeout
window.setTimeout(function () {
  document.querySelectorAll(".alert").forEach(function (alert) {
    if (!alert.classList.contains("alert-permanent")) {
      var bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }
  });
}, 5000);

// Toggle Password Visibility
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("bi-eye");
    icon.classList.add("bi-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("bi-eye-slash");
    icon.classList.add("bi-eye");
  }
}

// Handle Print
function handlePrint() {
  window.print();
}

// Dynamic Form Fields
function addFormField(containerId, template) {
  const container = document.getElementById(containerId);
  const newField = template.cloneNode(true);
  container.appendChild(newField);
}

function removeFormField(button) {
  button.closest(".form-group").remove();
}

// Search Filter
function searchTable() {
  const input = document.getElementById("searchInput");
  const filter = input.value.toLowerCase();
  const table = document.getElementById("dataTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {
    let show = false;
    const cells = rows[i].getElementsByTagName("td");

    for (let j = 0; j < cells.length; j++) {
      const text = cells[j].textContent || cells[j].innerText;
      if (text.toLowerCase().indexOf(filter) > -1) {
        show = true;
        break;
      }
    }

    rows[i].style.display = show ? "" : "none";
  }
}

// Calculate Total
function calculateTotal() {
  const quantities = document.querySelectorAll(".quantity-input");
  const prices = document.querySelectorAll(".price-input");
  let total = 0;

  for (let i = 0; i < quantities.length; i++) {
    const quantity = parseFloat(quantities[i].value) || 0;
    const price = parseFloat(prices[i].value.replace(/[^0-9.-]+/g, "")) || 0;
    total += quantity * price;
  }

  document.getElementById("totalAmount").textContent = formatRupiah(
    total.toString()
  );
}

// Sticky Header
window.onscroll = function () {
  if (document.querySelector(".sticky-header")) {
    var header = document.querySelector(".sticky-header");
    if (window.pageYOffset > header.offsetTop) {
      header.classList.add("sticky");
    } else {
      header.classList.remove("sticky");
    }
  }
};

// Initialize Tooltips
var tooltipTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Initialize Popovers
var popoverTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="popover"]')
);
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
  return new bootstrap.Popover(popoverTriggerEl);
});
