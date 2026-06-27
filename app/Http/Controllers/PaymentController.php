<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Show the parent's payment dashboard listing all enrolled children with balances.
     */
    public function showDashboard(Request $request)
    {
        $user = Auth::user();

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
     * Submit proof of payment for tuition or outstanding student account balances.
     */
    public function submitPayment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'method' => 'required|string|in:gcash,maya,bdo',
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

        // Create a StudentAccountPayment record
        $payment = \App\Models\StudentAccountPayment::create([
            'student_account_id' => $account->id,
            'student_id' => $student->id,
            'method' => $validated['method'],
            'reference_no' => $validated['reference_no'] ?? null,
            'amount' => $validated['amount'],
            'receipt_url' => $path,
            'status' => 'pending',
            'paid_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proof of payment submitted successfully! Our Finance Office will verify your transaction shortly.',
        ]);
    }
}
