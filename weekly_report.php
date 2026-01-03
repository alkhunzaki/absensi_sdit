<?php
// weekly_report.php
include 'config.php';
check_login();

$pesan = '';
$status_pesan = '';
// Default to the current week
$week_filter = isset($_GET['week']) ? $_GET['week'] : date('Y-\WW');

// Logic to save report data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reports'])) {
    $report_data = $_POST['reports'];
    $year_week = $_POST['year_week'];
    $berhasil_disimpan = 0;

    foreach ($report_data as $id_siswa => $data) {
        $subject = mysqli_real_escape_string($koneksi, $data['subject']);
        $score = mysqli_real_escape_string($koneksi, $data['score']);
        $notes = mysqli_real_escape_string($koneksi, $data['notes']);
        $id_siswa_safe = (int)$id_siswa;

        // Only save if the subject is filled
        if (!empty($subject)) {
            // Check if a report for this student, week, and subject already exists
            $check_query = "SELECT id_report FROM weekly_reports WHERE id_siswa = $id_siswa_safe AND year_week = '$year_week' AND subject = '$subject'";
            $result_check = mysqli_query($koneksi, $check_query);

            if (mysqli_num_rows($result_check) > 0) {
                // Update existing record
                $id_report = mysqli_fetch_assoc($result_check)['id_report'];
                $query = "UPDATE weekly_reports SET score = '$score', notes = '$notes' WHERE id_report = $id_report";
            } else {
                // Insert new record
                $query = "INSERT INTO weekly_reports (id_siswa, year_week, subject, score, notes) VALUES ($id_siswa_safe, '$year_week', '$subject', '$score', '$notes')";
            }
            
            if(mysqli_query($koneksi, $query)) {
                $berhasil_disimpan++;
            }
        }
    }
    
    if ($berhasil_disimpan > 0) {
        $pesan = sprintf($lang['weekly_report_save_success'], $berhasil_disimpan);
        $status_pesan = "sukses";
    } else {
        $pesan = $lang['no_data_to_save'];
        $status_pesan = "gagal";
    }
}

// Fetch student data
$result_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama_lengkap ASC");

template_header($lang['menu_weekly_report']);
?>

<h1 class="text-3xl font-bold text-gray-800 mb-6"><?= $lang['weekly_report_title'] ?></h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <form action="weekly_report.php" method="get" class="flex flex-wrap items-end gap-4 mb-4">
        <div>
            <label for="week" class="block text-sm font-medium text-gray-700"><?= $lang['select_week'] ?></label>
            <input type="week" name="week" id="week" value="<?= htmlspecialchars($week_filter) ?>" class="mt-1 px-3 py-2 border border-gray-300 rounded-md">
        </div>
        <div>
            <button type="submit" class="py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i><?= $lang['show'] ?>
            </button>
        </div>
    </form>
    
    <hr class="my-4">

    <form action="weekly_report.php" method="post">
        <input type="hidden" name="year_week" value="<?= htmlspecialchars($week_filter) ?>">
        <h2 class="text-xl font-semibold mb-4"><?= sprintf($lang['report_form_for'], substr($week_filter, 6, 2), substr($week_filter, 0, 4)) ?></h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= $lang['subject'] ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= $lang['score'] ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= $lang['notes'] ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($siswa = mysqli_fetch_assoc($result_siswa)): $id_siswa = $siswa['id_siswa']; ?>
                    <tr>
                        <td class="px-4 py-4 text-sm font-medium text-gray-900 align-top"><?= htmlspecialchars($siswa['nama_lengkap']) ?></td>
                        <td class="px-4 py-4 align-top">
                            <input type="text" name="reports[<?= $id_siswa ?>][subject]" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="e.g., Mathematics">
                        </td>
                        <td class="px-4 py-4 align-top">
                            <input type="text" name="reports[<?= $id_siswa ?>][score]" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="e.g., 95 or A+">
                        </td>
                        <td class="px-4 py-4 align-top">
                            <input type="text" name="reports[<?= $id_siswa ?>][notes]" class="w-full px-2 py-1 border border-gray-300 rounded-md text-sm" placeholder="Specific notes...">
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            <button type="submit" class="py-2 px-4 border shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i><?= $lang['save_reports'] ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($pesan) && !empty($status_pesan)): ?>
    Swal.fire({
        title: '<?= ($status_pesan == 'sukses') ? $lang['success_title'] : $lang['info_title']; ?>',
        text: '<?= addslashes(htmlspecialchars_decode($pesan)); ?>',
        icon: '<?= ($status_pesan == 'sukses') ? 'success' : 'info'; ?>',
        confirmButtonText: '<?= $lang['ok_button'] ?>'
    });
    <?php endif; ?>
});
</script>

<?php
template_footer();
?>
