<?php

/*
 * ==========================================================
 * UPLOAD.PHP
 * ==========================================================
 *
 * Manage all uploads of front-end and admin. © 2022-2023 boxcoin.dev. All rights reserved.
 *
 */

require_once('functions.php');
if (BXC_CLOUD) {
    bxc_cloud_load();
}
if (defined('BXC_CROSS_DOMAIN') && BXC_CROSS_DOMAIN) {
    header('Access-Control-Allow-Origin: *');
}
if (isset($_FILES['file'])) {
    if (0 < $_FILES['file']['error']) {
        die(json_encode(['error', 'Boxcoin: Error into upload.php file.']));
    } else {
        $file_name = htmlspecialchars(str_replace(['javascript:', 'onclick=', 'onerror=', '<script', '</script'], '', $_FILES['file']['name']), ENT_NOQUOTES | ENT_SUBSTITUTE, 'utf-8');
        $infos = pathinfo($file_name);
        $path = __DIR__ . '/uploads';
        $url = BXC_URL . 'uploads';
        $directory = bxc_isset($_GET, 'target') == 'checkout-file' ? '/checkout' : false;
        if (sb_is_allowed_extension(bxc_isset($infos, 'extension'))) {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            if ($directory) {
                $path .= $directory;
                $url .= $directory;
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
            }
            if (!file_exists($path . '/index.html')) {
                bxc_file($path . '/index.html', '');
            }
            $file_name = bxc_random_string() . '_' . bxc_string_slug($file_name);
            $path = $path . '/' . $file_name;
            $url = $url . '/' . $file_name;
            move_uploaded_file($_FILES['file']['tmp_name'], $path);
            die(json_encode([true, $url, $file_name]));
        } else {
            die(json_encode([false, 'The file you are trying to upload has an extension that is not allowed.']));
        }
    }
} else {
    die(json_encode([false, 'Boxcoin Error: Key file in $_FILES not found.']));
}

function sb_is_allowed_extension($extension) {
    $extension = strtolower($extension);
    $allowed_extensions = ['oga', 'json', 'psd', 'ai', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'key', 'ppt', 'odt', 'xls', 'xlsx', 'zip', 'rar', 'mp3', 'm4a', 'ogg', 'wav', 'mp4', 'mov', 'wmv', 'avi', 'mpg', 'ogv', '3gp', '3g2', 'mkv', 'txt', 'ico', 'csv', 'ttf', 'font', 'css', 'scss'];
    return in_array($extension, $allowed_extensions) || (defined('BXC_FILE_EXTENSIONS') && in_array($extension, BXC_FILE_EXTENSIONS));
}

?>