<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// ---- Email (SMTP) ----
$config['email_smtp_host']   = 'smtp.gmail.com';
$config['email_smtp_port']   = 587;
$config['email_smtp_crypto'] = 'tls';          // port 587 = 'tls', port 465 = 'ssl'
$config['email_smtp_user']   = 'manajemenklien26@gmail.com';             // alamat email pengirim
$config['email_smtp_pass']   = 'yabz fxzn rvbq myng';             // Gmail: App Password 16 karakter
$config['email_from']        = 'manajemenklien26@gmail.com';             // biasanya sama dengan email_smtp_user
$config['email_from_name']   = 'Tridatu Solution';

// ---- WhatsApp (Fonnte) ----
//$config['fonnte_token']      = '';             // token device dari dashboard Fonnte



$config['pengingat_offset'] = [3, 1, 0];   // H-3, H-1, hari H