<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;

// Let's find a user, say User ID 20
$user = User::find(20);
Auth::login($user);

// Let's check student 260001's current user ID
$student = Student::where('student_number', '260001')->first();
$originalUserId = $student->user_id;
echo "Original Student User ID: " . ($originalUserId ?? 'NULL') . PHP_EOL;

$controller = app(PaymentController::class);

// Create request to link
$request = Request::create('/payment/link-student', 'POST', [
    'student_number' => '260001',
    'date_of_birth' => '2011-05-20'
]);

$response = $controller->linkStudent($request);
echo "=== TESTING PAYMENT LINK ENDPOINT ===" . PHP_EOL;
echo "Status Code: " . $response->getStatusCode() . PHP_EOL;
echo "Content: " . $response->getContent() . PHP_EOL;

// Check student 260001's new user ID
$studentFresh = Student::where('student_number', '260001')->first();
echo "New Student User ID: " . ($studentFresh->user_id ?? 'NULL') . PHP_EOL;

// Restore original user ID so we don't pollute the test environment
$studentFresh->user_id = $originalUserId;
$studentFresh->save();
$applicant = $studentFresh->applicant;
if ($applicant) {
    $applicant->user_id = $originalUserId;
    $applicant->save();
}
echo "Restored original Student User ID: " . ($studentFresh->fresh()->user_id ?? 'NULL') . PHP_EOL;
