// Konfirmasi hapus dengan SweetAlert2
function confirmDelete(url, nama) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: `Data "${nama}" akan dihapus secara permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f5576c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    } else {
        if (confirm(`Yakin ingin menghapus data "${nama}"?`)) {
            window.location.href = url;
        }
    }
}

function confirmDeleteHistory(url, nama, jenis) {
    var pesan = jenis == 'masuk' 
        ? 'Stok barang akan otomatis BERKURANG.' 
        : 'Stok barang akan otomatis DITAMBAHKAN kembali.';
    
    if (confirm('Yakin ingin menghapus riwayat "' + nama + '"? ' + pesan)) {
        window.location.href = url;
    }
}

// Toast notification
function showToast(message, type = 'success') {
    const toastHTML = `
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.querySelector('.toast');
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    setTimeout(() => {
        toastElement.parentElement.remove();
    }, 4500);
}

// Auto dismiss alert
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.classList.contains('alert-dismissible')) {
                var closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }
        }, 5000);
    });
    
    console.log('Script.js loaded successfully!');
});

// Filter tabel
function filterTable(inputId, tableId) {
    var input = document.getElementById(inputId);
    if (!input) {
        console.error('Input dengan ID "' + inputId + '" tidak ditemukan!');
        return;
    }
    
    var filter = input.value.toLowerCase();
    var table = document.getElementById(tableId);
    if (!table) {
        console.error('Tabel dengan ID "' + tableId + '" tidak ditemukan!');
        return;
    }
    
    var tbody = table.getElementsByTagName('tbody')[0];
    if (!tbody) {
        console.error('Tabel tidak memiliki tbody!');
        return;
    }
    
    var rows = tbody.getElementsByTagName('tr');
    var found = false;
    
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var text = row.textContent.toLowerCase();
        if (text.indexOf(filter) > -1) {
            row.style.display = '';
            found = true;
        } else {
            row.style.display = 'none';
        }
    }
    
    var noResult = document.getElementById('noResult');
    if (!found && rows.length > 0) {
        if (!noResult) {
            noResult = document.createElement('tr');
            noResult.id = 'noResult';
            var td = document.createElement('td');
            td.colSpan = table.rows[0].cells.length;
            td.className = 'text-center text-muted py-4';
            td.innerHTML = '<i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada data yang ditemukan';
            noResult.appendChild(td);
            tbody.appendChild(noResult);
        }
        noResult.style.display = '';
    } else if (noResult) {
        noResult.style.display = 'none';
    }
}


// Auto dismiss alert setelah 5 detik
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('alert-dismissible')) {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }
        }, 5000);
    });
    console.log('Script.js loaded successfully!');
    console.log('filterTable function exists:', typeof filterTable !== 'undefined');
});

// Format currency
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

// Filter by Kategori
function filterByKategori() {
    var filter = document.getElementById('filterKategori').value;
    var url = new URL(window.location.href);
    if (filter) {
        url.searchParams.set('kategori', filter);
    } else {
        url.searchParams.delete('kategori');
    }
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Filter by Stok
function filterByStok() {
    var filter = document.getElementById('filterStok').value;
    var url = new URL(window.location.href);
    if (filter) {
        url.searchParams.set('stok', filter);
    } else {
        url.searchParams.delete('stok');
    }
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Dark Mode Toggle
function toggleDarkMode() {
    console.log('Dark mode toggled'); // Untuk debugging
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    
    var icon = document.getElementById('darkModeIcon');
    if (icon) {
        if (isDark) {
            icon.className = 'bi bi-sun-fill';
            icon.style.color = '#ffd700';
        } else {
            icon.className = 'bi bi-moon-fill';
            icon.style.color = '';
        }
    }
}

// Cek preferensi dari localStorage saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Dark Mode
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        var icon = document.getElementById('darkModeIcon');
        if (icon) {
            icon.className = 'bi bi-sun-fill';
            icon.style.color = '#ffd700';
        }
    }
    
    console.log('Script.js loaded successfully!');
    
    // Auto dismiss alerts
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.classList.contains('alert-dismissible')) {
                var closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) closeBtn.click();
            }
        }, 5000);
    });
});