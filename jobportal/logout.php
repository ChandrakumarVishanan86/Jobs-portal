<?php
require __DIR__ . '/config/config.php';
Auth::logout();
session_start();
flash('You have been signed out.', 'info');
redirect('');
