<?php
include 'config.php';
check_login();

template_header('Dashboard');
?>
<h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Kehadiran</h1>

<!-- Grid Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
        <i class="fas fa-users text-4xl text-blue-500"></i>
        <div class="ml-4">
            <h2 class="text-gray-600 text-lg">Jumlah Siswa</h2>
            <p id="stat-total-siswa" class="text-2xl font-bold animate-pulse text-gray-300">...</p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
        <i class="fas fa-user-check text-4xl text-green-500"></i>
        <div class="ml-4">
            <h2 class="text-gray-600 text-lg">Hadir Hari Ini</h2>
            <p id="stat-hadir-ini" class="text-2xl font-bold animate-pulse text-gray-300">...</p>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
        <i class="fas fa-user-times text-4xl text-red-500"></i>
        <div class="ml-4">
            <h2 class="text-gray-600 text-lg">Tidak Hadir Hari Ini</h2>
            <p id="stat-tidak-hadir-ini" class="text-2xl font-bold animate-pulse text-gray-300">...</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md relative min-h-[300px]">
        <h3 class="font-bold text-gray-700 mb-4">Rekap Presensi Hari Ini</h3>
        <div id="loader-pie" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-70 z-10"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i></div>
        <canvas id="pieChart"></canvas>
    </div>
    <div class="lg:col-span-3 bg-white p-6 rounded-lg shadow-md relative min-h-[300px]">
        <h3 class="font-bold text-gray-700 mb-4">Grafik Kehadiran per Bulan</h3>
        <div id="loader-bar" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-70 z-10"><i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i></div>
        <canvas id="barChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('api_dashboard.php');
        if (!response.ok) throw new Error('Gagal mengambil data');
        const data = await response.json();

        // Update Statistik Text
        document.getElementById('stat-total-siswa').innerText = data.jumlah_siswa + ' Siswa';
        document.getElementById('stat-total-siswa').classList.remove('animate-pulse', 'text-gray-300');
        
        document.getElementById('stat-hadir-ini').innerText = data.hadir_hari_ini + ' Siswa';
        document.getElementById('stat-hadir-ini').classList.remove('animate-pulse', 'text-gray-300');
        
        document.getElementById('stat-tidak-hadir-ini').innerText = data.tidak_hadir_hari_ini + ' Siswa';
        document.getElementById('stat-tidak-hadir-ini').classList.remove('animate-pulse', 'text-gray-300');

        // Render Pie Chart
        new Chart(document.getElementById('pieChart').getContext('2d'), {
            type: 'pie', data: { labels: data.pie.labels, datasets: [{ label: 'Jumlah Siswa', data: data.pie.values,
            backgroundColor: ['rgba(75, 192, 192, 0.7)','rgba(255, 206, 86, 0.7)','rgba(54, 162, 235, 0.7)','rgba(255, 99, 132, 0.7)'],
            borderColor: ['rgba(75, 192, 192, 1)','rgba(255, 206, 86, 1)','rgba(54, 162, 235, 1)','rgba(255, 99, 132, 1)'], borderWidth: 1 }] },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });
        document.getElementById('loader-pie').remove();

        // Render Bar Chart
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar', data: { labels: data.bar.labels, datasets: [
            { label: 'Hadir', data: data.bar.hadir, backgroundColor: 'rgba(75, 192, 192, 0.7)' },
            { label: 'Izin', data: data.bar.izin, backgroundColor: 'rgba(255, 206, 86, 0.7)' },
            { label: 'Sakit', data: data.bar.sakit, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
            { label: 'Alfa', data: data.bar.alfa, backgroundColor: 'rgba(255, 99, 132, 0.7)' } ] },
            options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }, plugins: { legend: { position: 'top' } } }
        });
        document.getElementById('loader-bar').remove();

    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'Gagal memuat data dashboard. Silakan refresh halaman.', 'error');
    }
});
</script>
<?php
template_footer();
?>
