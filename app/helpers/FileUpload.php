<?php
// FILE: /app/helpers/FileUpload.php

class FileUpload {

    public static function upload($file, $directory = 'general') {
        if (!isset($file['error']) || is_array($file['error'])) {
            return array('success' => false, 'error' => 'Invalid file upload');
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return array('success' => false, 'error' => 'Upload failed with error code: ' . $file['error']);
        }

        // Check file size
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return array('success' => false, 'error' => 'File size exceeds maximum allowed size');
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = explode(',', ALLOWED_EXTENSIONS);

        if (!in_array($extension, $allowedExtensions)) {
            return array('success' => false, 'error' => 'File type not allowed');
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'text/plain'
        );

        if (!in_array($mimeType, $allowedMimes)) {
            return array('success' => false, 'error' => 'Invalid file type');
        }

        // Create directory if it doesn't exist
        $uploadDir = UPLOAD_PATH . '/' . $directory;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return array('success' => false, 'error' => 'Failed to move uploaded file');
        }

        return array(
            'success' => true,
            'filename' => $filename,
            'filepath' => $directory . '/' . $filename,
            'size' => $file['size'],
            'original_name' => $file['name']
        );
    }

    public static function delete($filepath) {
        $fullPath = UPLOAD_PATH . '/' . $filepath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    public static function download($filepath, $filename = null) {
        $fullPath = UPLOAD_PATH . '/' . $filepath;

        if (!file_exists($fullPath)) {
            return false;
        }

        // Prevent directory traversal
        $realPath = realpath($fullPath);
        $uploadRealPath = realpath(UPLOAD_PATH);

        if (strpos($realPath, $uploadRealPath) !== 0) {
            return false;
        }

        if ($filename === null) {
            $filename = basename($filepath);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
