<?php
// Denvibes contact form → info@denvibes.com (runs on InMotion Hosting's PHP mail).
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo '{"ok":false}'; exit; }

$raw = file_get_contents('php://input');
$d = json_decode($raw, true);
if (!is_array($d)) { $d = $_POST; }

// Honeypot: bots fill hidden fields.
if (!empty($d['website'])) { echo '{"ok":true}'; exit; }

$clean = function ($v, $max = 2000) { return trim(mb_substr(strip_tags((string)$v), 0, $max)); };
$name    = $clean($d['name'] ?? '', 120);
$email   = filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL);
$company = $clean($d['company'] ?? '', 160);
$ints    = $clean($d['interests'] ?? '', 300);
$time    = $clean($d['timeline'] ?? '', 80);
$budget  = $clean($d['budget'] ?? '', 80);
$msg     = $clean($d['message'] ?? '', 5000);

if ($name === '' || !$email) { http_response_code(422); echo '{"ok":false,"error":"Name and a valid email are required."}'; exit; }

$to = 'info@denvibes.com';
$subject = 'New project brief: ' . preg_replace('/[\r\n]+/', ' ', $name) . ($company ? " ($company)" : '');
$body = "Name: $name\nEmail: $email\nCompany: $company\nInterested in: $ints\nTimeline: $time\nBudget: $budget\n\nMessage:\n$msg\n\n— Sent from denvibes.com";
$host = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'denvibes.com');
$headers = "From: Denvibes Website <no-reply@$host>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8\r\n";

$ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers, "-f no-reply@$host");
if (!$ok) { http_response_code(500); echo '{"ok":false}'; exit; }
echo '{"ok":true}';
