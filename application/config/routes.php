<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['dashboard'] = 'content/dashboard';

$route['klien'] = 'content/klien/index';
$route['klien/tambah'] = 'content/klien/tambah'; // <--- INI YANG KURANG
$route['klien/simpan'] = 'content/klien/simpan'; // Untuk nanti menyimpan data
$route['klien/detail/(:num)'] = 'content/klien/detail/$1';
$route['klien/hapus/(:num)'] = 'content/klien/hapus/$1';
$route['klien/edit/(:num)'] = 'content/klien/edit/$1';
$route['klien/update'] = 'content/klien/update';


$route['project']              = 'content/project';
$route['project/tambah']       = 'content/project/tambah';
$route['project/simpan']       = 'content/project/simpan';
$route['project/detail/(:num)'] = 'content/project/detail/$1';
$route['project/edit/(:num)']   = 'content/project/edit/$1';
$route['project/update/(:num)'] = 'content/project/update/$1';
$route['project/hapus']        = 'content/project/hapus';
$route['project/status/(:num)'] = 'content/project/status/$1';

$route['dokumen'] = 'content/dokumen/index';
$route['dokumen'] = 'content/dokumen/index';
$route['dokumen/tambah'] = 'content/dokumen/tambah';
$route['dokumen/simpan'] = 'content/dokumen/simpan';
$route['dokumen/get_projects'] = 'content/dokumen/get_projects';
$route['dokumen/hapus/(:num)'] = 'content/dokumen/hapus/$1';
$route['dokumen/detail/(:num)'] = 'content/dokumen/detail/$1';
$route['dokumen/sync_to_cloud'] = 'content/dokumen/sync_to_cloud';
$route['dokumen/preview/(:num)'] = 'content/dokumen/preview/$1';
$route['dokumen/unduh/(:num)'] = 'content/dokumen/unduh/$1';
$route['dokumen/edit/(:num)'] = 'content/dokumen/edit/$1';
$route['dokumen/update']      = 'content/dokumen/update';


$route['pembayaran'] = 'content/pembayaran/index';
$route['pembayaran/tambah/(:any)']         = 'content/pembayaran/tambah/$1';
$route['pembayaran/simpan/(:any)']         = 'content/pembayaran/simpan/$1';
$route['pembayaran/detail/(:any)/(:num)']  = 'content/pembayaran/detail/$1/$2';
$route['pembayaran/edit/(:any)/(:num)']    = 'content/pembayaran/edit/$1/$2';
$route['pembayaran/update/(:any)/(:num)']  = 'content/pembayaran/update/$1/$2';
$route['pembayaran/unduh/(:any)/(:num)']   = 'content/pembayaran/unduh/$1/$2';
$route['pembayaran/status/(:any)/(:num)'] = 'content/pembayaran/status/$1/$2';
$route['pembayaran/pratinjau'] = 'content/pembayaran/pratinjau';
$route['pembayaran/berkas/(:num)'] = 'content/pembayaran/berkas/$1';
$route['pembayaran/kirim/(:any)/(:num)'] = 'content/pembayaran/kirim/$1/$2';
$route['pembayaran/hapus'] = 'content/pembayaran/hapus';

$route['pengaturan'] = 'content/pengaturan/index';



$route['pengaturan'] = 'content/pengaturan/index';
$route['pengaturan/update_info'] = 'content/pengaturan/update_info';
$route['pengaturan/update_assets'] = 'content/pengaturan/update_assets';
$route['pengaturan/hapus_asset/(:any)'] = 'content/pengaturan/hapus_asset/$1';
$route['pengaturan/simpan_jenis'] = 'content/pengaturan/simpan_jenis';
$route['pengaturan/hapus_jenis/(:num)'] = 'content/pengaturan/hapus_jenis/$1';



$config['composer_autoload'] = FCPATH . 'vendor/autoload.php';