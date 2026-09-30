<?php
// Script to download storage/app/public from student.yru.ac.th and upload to site.yru.ac.th and local
$localBase = realpath(__DIR__ . '/../storage/app/public');
echo "Local target base: $localBase\n";

$ftpStudent = ftp_connect('ftp.student.yru.ac.th');
if (!$ftpStudent || !ftp_login($ftpStudent, 'S406665027', '1969900475054')) {
    die("Cannot connect to student.yru.ac.th\n");
}
ftp_pasv($ftpStudent, true);

$ftpSite = ftp_connect('host.site.yru.ac.th');
if (!$ftpSite || !ftp_login($ftpSite, 's406665027', 'Sunlun2548')) {
    die("Cannot connect to site.yru.ac.th\n");
}
ftp_pasv($ftpSite, true);

$remoteStudentBase = '/web/406665027.student.yru.ac.th/private/student-discipline-system/storage/app/public';
$remoteSiteBase = '/private/student-discipline-system/storage/app/public';

function ensureRemoteDir($ftp, $remotePath) {
    $parts = explode('/', trim($remotePath, '/'));
    $cur = '';
    foreach ($parts as $p) {
        $cur .= '/' . $p;
        @ftp_mkdir($ftp, $cur);
    }
}

function syncDir($ftpFrom, $ftpTo, $remoteFromDir, $remoteToDir, $localDir) {
    if (!file_exists($localDir)) {
        mkdir($localDir, 0777, true);
    }
    ensureRemoteDir($ftpTo, $remoteToDir);

    $items = ftp_nlist($ftpFrom, $remoteFromDir);
    if (!$items) return;

    foreach ($items as $item) {
        $baseName = basename($item);
        if ($baseName === '.' || $baseName === '..') continue;

        $fromPath = $remoteFromDir . '/' . $baseName;
        $toPath = $remoteToDir . '/' . $baseName;
        $localFilePath = $localDir . DIRECTORY_SEPARATOR . $baseName;

        // Check if directory
        $size = ftp_size($ftpFrom, $fromPath);
        if ($size == -1) {
            // It's a directory
            echo "Processing DIR: $baseName\n";
            syncDir($ftpFrom, $ftpTo, $fromPath, $toPath, $localFilePath);
        } else {
            // It's a file
            echo "Syncing FILE: $baseName (size: $size bytes)... ";
            // Download to local
            if (ftp_get($ftpFrom, $localFilePath, $fromPath, FTP_BINARY)) {
                // Upload to site.yru.ac.th
                if (ftp_put($ftpTo, $toPath, $localFilePath, FTP_BINARY)) {
                    echo "OK (downloaded & uploaded)\n";
                } else {
                    echo "Upload to site failed!\n";
                }
            } else {
                echo "Download from student failed!\n";
            }
        }
    }
}

echo "=== Starting sync from student.yru.ac.th to local and site.yru.ac.th ===\n";
syncDir($ftpStudent, $ftpSite, $remoteStudentBase, $remoteSiteBase, $localBase);

ftp_close($ftpStudent);
ftp_close($ftpSite);
echo "=== Sync complete! ===\n";
