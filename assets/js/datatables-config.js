// Konfigurasi default DataTables
const dataTablesConfig = {
    language: {
        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
    },
    pageLength: 10,
    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
    responsive: true,
    initComplete: function() {
        // Callback setelah tabel selesai diinisialisasi
    }
};

// Fungsi untuk inisialisasi DataTables dengan konfigurasi kustom
function initializeDataTable(tableId, customConfig = {}) {
    const config = { ...dataTablesConfig, ...customConfig };
    return $(`#${tableId}`).DataTable(config);
}

// Fungsi untuk konfirmasi hapus
function confirmDelete(element, message = '') {
    $(element).on('click', function(e) {
        e.preventDefault();
        const username = $(this).data('username');
        const confirmMessage = message || `Yakin ingin menghapus user "${username}"?`;
        
        if(confirm(confirmMessage)) {
            window.location.href = this.href;
        }
    });
} 