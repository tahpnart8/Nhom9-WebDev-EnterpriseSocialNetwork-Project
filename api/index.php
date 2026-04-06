<?php
// Vercel Serverless Function Entry Point
// Chuyển working directory về thư mục gốc của project
chdir(__DIR__ . '/..');

// Include file router chính
require_once __DIR__ . '/../index.php';
