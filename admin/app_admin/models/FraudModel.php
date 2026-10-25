<?php defined('BASEPATH') or exit('No direct script access allowed');

// Fraud kuralları tek kaynaktan (ana uygulama) yönetilir; admin ayrı kopya tutmaz.
require_once dirname(APPPATH) . '/../app/models/FraudModel.php';
