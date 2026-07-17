<?php
// AJAX endpoint — returns JSON
header('Content-Type: application/json; charset=UTF-8');

$event = get_event_by_slug($_event_slug ?? '');
if (!$event) { echo json_encode(['success'=>false,'message'=>'कार्यक्रम भेटिएन।']); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit;
}

// CSRF
if (!hash_equals(csrf_token(), $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success'=>false,'message'=>'Invalid CSRF token.']); exit;
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$org   = trim($_POST['organization'] ?? '');
$msg   = trim($_POST['message'] ?? '');

if (!$name) {
    echo json_encode(['success'=>false,'message'=>'नाम अनिवार्य छ।']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success'=>false,'message'=>'वैध इमेल ठेगाना दिनुस्।']); exit;
}

// Check reg is still open
$reg_count = db_count("SELECT COUNT(*) FROM event_registrations WHERE event_id=? AND status!='cancelled'", [$event['id']]);
$reg_open  = $event['registration_open'] && !in_array($event['status'],['completed','cancelled']);
if ($event['registration_deadline']) $reg_open = $reg_open && strtotime($event['registration_deadline']) > time();
if ($event['capacity']) $reg_open = $reg_open && $reg_count < $event['capacity'];

if (!$reg_open) {
    echo json_encode(['success'=>false,'message'=>'दर्ता बन्द भइसक्यो।']); exit;
}

// Prevent duplicate
$dup = db_fetch("SELECT id FROM event_registrations WHERE event_id=? AND email=?", [$event['id'], $email]);
if ($dup) {
    echo json_encode(['success'=>false,'message'=>'यो इमेलबाट पहिले नै दर्ता भइसकेको छ।']); exit;
}

$ok = save_event_registration([
    'event_id'     => $event['id'],
    'full_name'    => $name,
    'email'        => $email,
    'phone'        => $phone,
    'organization' => $org,
    'message'      => $msg,
    'status'       => 'pending',
]);

if ($ok) {
    echo json_encode(['success'=>true,'message'=>'दर्ता सफल भयो! धन्यवाद। शीघ्र हामी तपाईंसँग सम्पर्क गर्नेछौं।']);
} else {
    echo json_encode(['success'=>false,'message'=>'दर्ता गर्दा त्रुटि भयो। पुनः प्रयास गर्नुस्।']);
}
