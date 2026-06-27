<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\GoogleVisionService;

class PaymentController extends Controller
{
    /**
     * Show the parent's payment dashboard listing all enrolled children with balances.
     */
    public function showDashboard(Request $request)
    {
        $user = Auth::user();

        // ── AUTO-LINK STUDENTS BY PARENT EMAIL ──────────────────────────────
        if ($user && $user->email) {
            try {
                $userEmail = trim(strtolower($user->email));
                $matchingApplicants = \App\Models\EnrollmentApplicant::whereRaw('LOWER(TRIM(parent_email)) = ?', [$userEmail])->get();
                foreach ($matchingApplicants as $applicant) {
                    $student = \App\Models\Student::where('enrollment_applicant_id', $applicant->id)->first();
                    if ($student) {
                        $updated = false;
                        if ($student->user_id !== $user->id) {
                            $student->user_id = $user->id;
                            $student->save();
                            $updated = true;
                        }
                        if ($applicant->user_id !== $user->id) {
                            $applicant->user_id = $user->id;
                            $applicant->save();
                            $updated = true;
                        }
                        if ($updated) {
                            \Illuminate\Support\Facades\Log::info("Auto-linked student {$student->student_number} to parent user ID {$user->id} via email matching.");
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Auto-linking student error: ' . $e->getMessage());
            }
        }
        // ────────────────────────────────────────────────────────────────────

        // Fetch all students associated with the parent user
        $students = $user->students()
            ->with(['applicant', 'account'])
            ->get();

        return view('payment.dashboard', compact('user', 'students'));
    }

    /**
     * Link an existing student to the parent's user account.
     */
    public function linkStudent(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        $studentNumber = trim($validated['student_number']);
        $dob = $validated['date_of_birth'];

        // Find student by student number (handling optionally formatted numbers)
        $student = \App\Models\Student::where('student_number', $studentNumber)
            ->orWhere('student_number', str_replace('-', '', $studentNumber))
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'No student record found with the given student number.',
            ], 404);
        }

        $applicant = $student->applicant;
        if (!$applicant) {
            return response()->json([
                'success' => false,
                'message' => 'No application details found for this student.',
            ], 404);
        }

        // Verify Date of Birth
        $formattedDob = $applicant->date_of_birth ? $applicant->date_of_birth->format('Y-m-d') : null;
        if ($formattedDob !== $dob) {
            return response()->json([
                'success' => false,
                'message' => 'The provided date of birth does not match our records.',
            ], 422);
        }

        $user = Auth::user();

        // If already linked to this parent
        if ($student->user_id === $user->id) {
            return response()->json([
                'success' => true,
                'message' => 'This child is already linked to your account.',
            ]);
        }

        // Update student and applicant's user_id
        $student->user_id = $user->id;
        $student->save();

        if ($applicant->user_id !== $user->id) {
            $applicant->user_id = $user->id;
            $applicant->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Child linked successfully!',
        ]);
    }

    /**
     * Live OCR scan endpoint: scan an uploaded receipt image and return
     * detected reference, amount, and date for client-side auto-fill.
     */
    public function ocrScan(Request $request)
    {
        $request->validate([
            'receipt' => 'required|file|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        try {
            $file = $request->file('receipt');
            $tmpPath = $file->getRealPath();

            $visionService = new GoogleVisionService();
            $ocr = $visionService->scanReceipt($tmpPath);

            $detectedDate = $ocr['detected_datetime'];
            if (!$detectedDate && $ocr['raw_text']) {
                $patterns = [
                    '/\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2},?\s+\d{4}/i',
                    '/\b\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}\b/',
                    '/\b\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}\b/',
                ];
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $ocr['raw_text'], $m)) {
                        $detectedDate = $m[0];
                        break;
                    }
                }
            }

            return response()->json([
                'success'           => $ocr['success'],
                'detected_ref'      => $ocr['detected_ref'],
                'detected_amount'   => $ocr['detected_amount'],
                'detected_date'     => $detectedDate,
                'detected_sender'   => $ocr['detected_sender'] ?? null,
                'detected_receiver' => $ocr['detected_receiver'] ?? null,
                'detected_merchant' => $ocr['detected_merchant'] ?? null,
                'detected_method'   => $ocr['detected_method'] ?? null,
                'detected_account'  => $ocr['detected_account'] ?? null,
                'has_qr'            => $ocr['has_qr'] ?? false,
                'confidence'        => $ocr['confidence'] ?? null,
                'raw_text'          => $ocr['raw_text'],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OCR pre-scan error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'OCR scan failed.'], 500);
        }
    }

    /**
     * Submit proof of payment for tuition or outstanding student account balances.
     */
    public function submitPayment(Request $request)
    {

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'method' => 'required|string|in:gcash,bdo,remittance',
            'reference_no' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:1|max:999999',
            'receipt' => 'required|file|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();

        // Verify the student belongs to this parent
        $student = \App\Models\Student::where('id', $validated['student_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or invalid student record.',
            ], 403);
        }

        // Verify the student applicant status is approved
        $applicant = $student->applicant;
        if ($applicant && $applicant->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Payment cannot be submitted for applications that are pending approval.',
            ], 403);
        }

        $account = $student->account;
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'No student account details found to apply payment.',
            ], 404);
        }

        // Store the receipt image file in public storage disk under receipts/
        $path = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $studentNumberSlug = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower(trim($student->student_number ?? 'student')));
            $timestamp = time();
            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
            $filename = 'payment_receipt_' . $studentNumberSlug . '_' . $timestamp . '.' . $ext;
            
            $path = $file->storeAs('receipts', $filename, 'public');
        }

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload proof of payment receipt.',
            ], 500);
        }

        // ── Google Vision OCR: scan the uploaded receipt ────────────────────
        $ocrStatus        = 'skipped';
        $ocrRawText       = null;
        $ocrScannedRef    = null;
        $ocrScannedAmount = null;

        try {
            $absolutePath = Storage::disk('public')->path($path);
            $visionService = new GoogleVisionService();
            $ocr = $visionService->scanReceipt($absolutePath);

            $ocrStatus        = $ocr['status'];
            $ocrRawText       = $ocr['raw_text'];
            $ocrScannedRef    = $ocr['detected_ref'];
            $ocrScannedAmount = $ocr['detected_amount'];

            // Determine match quality: compare scanned ref & amount vs what parent typed
            if ($ocr['success'] && $ocr['status'] === 'processed') {
                $submittedRef    = strtolower(preg_replace('/\s+/', '', $validated['reference_no'] ?? ''));
                $scannedRef      = strtolower(preg_replace('/\s+/', '', $ocrScannedRef ?? ''));
                $amountMatches   = $ocrScannedAmount !== null && abs($ocrScannedAmount - $validated['amount']) < 1.0;
                $refMatches      = $submittedRef && $scannedRef && str_contains($scannedRef, $submittedRef);

                if ($amountMatches && $refMatches) {
                    $ocrStatus = 'matched';
                } elseif ($amountMatches || $refMatches) {
                    $ocrStatus = 'partial_match';
                } else {
                    $ocrStatus = 'mismatch';
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OCR integration error: ' . $e->getMessage());
            $ocrStatus = 'failed';
        }
        // ─────────────────────────────────────────────────────────────────────

        // Create a StudentAccountPayment record
        $payment = \App\Models\StudentAccountPayment::create([
            'student_account_id'  => $account->id,
            'student_id'          => $student->id,
            'method'              => $validated['method'],
            'reference_no'        => $validated['reference_no'] ?? null,
            'amount'              => $validated['amount'],
            'receipt_url'         => $path,
            'status'              => 'pending',
            'paid_at'             => now(),
            'ocr_status'          => $ocrStatus,
            'ocr_raw_text'        => $ocrRawText,
            'ocr_scanned_ref'     => $ocrScannedRef,
            'ocr_scanned_amount'  => $ocrScannedAmount,
        ]);

        // Build a user-facing message enriched with OCR match feedback
        $ocrNote = match($ocrStatus) {
            'matched'       => ' ✅ Receipt details verified by our system.',
            'partial_match' => ' ⚠️ Some details partially matched. Finance will double-check.',
            'mismatch'      => ' ⚠️ Receipt could not be automatically verified. Please ensure the screenshot is clear.',
            default         => '',
        };

        return response()->json([
            'success' => true,
            'message' => 'Proof of payment submitted successfully! Our Finance Office will verify your transaction shortly.' . $ocrNote,
        ]);
    }
}
