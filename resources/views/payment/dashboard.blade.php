<x-app-layout>
    <x-slot name="title">Payment Dashboard</x-slot>

    <div class="py-12" x-data="{
        showAddChildModal: false,
        studentNumber: '',
        dob: '',
        loading: false,
        errorMsg: '',
        successMsg: '',
        submitLinkChild() {
            this.loading = true;
            this.errorMsg = '';
            this.successMsg = '';
            fetch('{{ route('payment.link-student') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                },
                body: JSON.stringify({
                    student_number: this.studentNumber,
                    date_of_birth: this.dob
                })
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Failed to link child.');
                return data;
            })
            .then(data => {
                this.successMsg = data.message || 'Child linked successfully!';
                setTimeout(() => { window.location.reload(); }, 1500);
            })
            .catch(error => {
                this.errorMsg = error.message;
                this.loading = false;
            });
        },
        
        // Settle Payment variables and actions
        showSettlePaymentModal: false,
        settleStudentId: null,
        settleStudentName: '',
        settleRemainingBalance: 0,
        settleMethod: 'gcash',
        settleAmount: 0,
        settleReference: '',
        settleReceiptFile: null,
        settleReceiptPreview: null,
        settleLoading: false,
        settleErrorMsg: '',
        settleSuccessMsg: '',
        
        handleSettleFileChange(event) {
            const files = event.target.files;
            if (files.length === 0) return;
            const file = files[0];
            
            if (!file.type.startsWith('image/')) {
                this.settleErrorMsg = 'Only image files (JPG, JPEG, PNG) are supported for proof of payment.';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.settleErrorMsg = 'Receipt image size must not exceed 5MB.';
                return;
            }
            
            this.settleReceiptFile = file;
            this.settleErrorMsg = '';
            
            const reader = new FileReader();
            reader.onload = (e) => {
                this.settleReceiptPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        
        removeSettleFile() {
            this.settleReceiptFile = null;
            this.settleReceiptPreview = null;
            if (this.$refs.settleFileInput) {
                this.$refs.settleFileInput.value = '';
            }
        },
        
        submitSettlePayment() {
            if (!this.settleReceiptFile) {
                this.settleErrorMsg = 'Please upload a proof of payment receipt.';
                return;
            }
            this.settleLoading = true;
            this.settleErrorMsg = '';
            this.settleSuccessMsg = '';
            
            const formData = new FormData();
            formData.append('student_id', this.settleStudentId);
            formData.append('method', this.settleMethod);
            formData.append('reference_no', this.settleReference);
            formData.append('amount', this.settleAmount);
            formData.append('receipt', this.settleReceiptFile);
            
            fetch('{{ route('payment.submit') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Failed to submit payment.');
                return data;
            })
            .then(data => {
                this.settleSuccessMsg = data.message || 'Payment submitted successfully!';
                setTimeout(() => { window.location.reload(); }, 2000);
            })
            .catch(error => {
                this.settleErrorMsg = error.message;
                this.settleLoading = false;
            });
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Payment Dashboard</h1>
                    <p class="mt-2 text-sm text-slate-500">Review outstanding school fees, payments, and balances for your enrolled children.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 self-start">
                    <button @click="showAddChildModal = true" 
                            style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 18px; background-color: #047857; color: #ffffff; border-radius: 16px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; white-space: nowrap; flex-shrink: 0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.15s ease-in-out;"
                            onmouseover="this.style.backgroundColor='#065f46'"
                            onmouseout="this.style.backgroundColor='#047857'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="width: 14px; height: 14px; margin-right: 6px; flex-shrink: 0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Children
                    </button>
                    <div class="inline-flex items-center gap-3 px-4 py-2 bg-emerald-50 text-emerald-800 rounded-2xl border border-emerald-100 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Parent Account Connected
                    </div>
                </div>
            </div>

            <!-- Children / Students Grid -->
            @if($students->isEmpty())
                <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-xs">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-800">No linked children found</h3>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto mb-6">We couldn't find any enrolled students linked to your parent email address. You can link your children directly or coordinate with the admissions office.</p>
                    <button @click="showAddChildModal = true" 
                            style="display: inline-flex; align-items: center; justify-content: center; padding: 10px 24px; background-color: #047857; color: #ffffff; border-radius: 16px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; white-space: nowrap; flex-shrink: 0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.15s ease-in-out;"
                            onmouseover="this.style.backgroundColor='#065f46'"
                            onmouseout="this.style.backgroundColor='#047857'">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="width: 16px; height: 16px; margin-right: 8px; flex-shrink: 0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Link a Child Now
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($students as $student)
                        @php
                            $applicant = $student->applicant;
                            $account = $student->account;
                            
                            $fullName = $applicant 
                                ? strtoupper($applicant->first_name . ' ' . ($applicant->middle_name ? $applicant->middle_name . ' ' : '') . $applicant->last_name) 
                                : strtoupper($student->user->name ?? 'Enrolled Student');
                            
                            $grade = $student->grade_level ?? 'Unassigned';
                            $studentNumber = $student->student_number ?? 'AMIS-XXXX-XXXX';
                            
                            $remainingBalance = $account ? (float) $account->remaining_balance : 0.00;
                            $totalBalance = $account ? (float) $account->total_balance : 0.00;
                            $amountPaid = $account ? (float) $account->amount_paid : 0.00;
                            $status = $account ? $account->status : 'unpaid';
                            
                            // Status Pill Badge configurations
                            $statusClasses = [
                                'paid' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'partial' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'unpaid' => 'bg-rose-50 text-rose-800 border-rose-200',
                            ][$status] ?? 'bg-slate-50 text-slate-800 border-slate-200';
                            
                            $statusLabel = [
                                'paid' => 'Fully Paid',
                                'partial' => 'Partially Paid',
                                'unpaid' => 'Unpaid',
                            ][$status] ?? ucfirst($status);
                        @endphp
                        
                        <!-- Student Payment Card -->
                        <div class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                            <!-- Visual Top Gradient Accent -->
                            <div class="absolute left-0 top-0 h-1.5 w-full bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600"></div>

                            <div>
                                <!-- Card Header: Icon & Status -->
                                <div class="flex items-center justify-between mb-5">
                                    @if($applicant && $applicant->photo_2x2_url)
                                        <div class="h-11 w-11 overflow-hidden rounded-2xl border border-slate-200 shadow-xs bg-slate-50 flex items-center justify-center">
                                            <img src="{{ asset('storage/' . \App\Support\ImageHelper::thumb($applicant->photo_2x2_url, 'medium')) }}" 
                                                 alt="{{ $fullName }}" 
                                                 class="h-full w-full object-cover">
                                        </div>
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50/80 text-emerald-700 border border-emerald-100/50">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <!-- Student Details -->
                                <div class="space-y-1 mb-6">
                                    <h3 class="text-lg font-black text-slate-800 leading-tight uppercase tracking-tight group-hover:text-emerald-800 transition-colors">
                                        {{ $fullName }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                                        <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md">{{ $grade }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span>ID: <strong class="text-slate-700 font-semibold">{{ $studentNumber }}</strong></span>
                                    </div>
                                </div>

                                <!-- Outstanding Balance block -->
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4 mb-6">
                                    <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block">Remaining Balance</span>
                                    <span class="text-2xl font-black text-slate-800 mt-1 block">
                                        ₱{{ number_format($remainingBalance, 2) }}
                                    </span>
                                </div>

                                <!-- Financial breakdown -->
                                <div class="space-y-2 text-xs font-medium text-slate-500 mb-6">
                                    <div class="flex justify-between">
                                        <span>Gross Total Fees</span>
                                        <span class="text-slate-800 font-semibold">₱{{ number_format($totalBalance, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Amount Settle Paid</span>
                                        <span class="text-emerald-700 font-semibold">₱{{ number_format($amountPaid, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action button -->
                            <div class="pt-4 border-t border-slate-100">
                                @if($applicant && $applicant->status !== 'approved')
                                    <div class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold cursor-not-allowed">
                                        <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Pending Approval
                                    </div>
                                @elseif($remainingBalance > 0)
                                    <button @click="showSettlePaymentModal = true; settleStudentId = {{ $student->id }}; settleStudentName = '{{ addslashes($fullName) }}'; settleRemainingBalance = {{ $remainingBalance }}; settleAmount = {{ $remainingBalance }}; settleMethod = 'gcash'; settleReference = ''; settleReceiptFile = null; settleReceiptPreview = null; settleErrorMsg = ''; settleSuccessMsg = '';"
                                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold shadow-xs hover:shadow transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Settle Payment
                                    </button>
                                @else
                                    <div class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-100 text-slate-400 text-sm font-semibold cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Account Settled
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Add Child Modal -->
        <div x-show="showAddChildModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>
            <div class="relative w-full max-w-md overflow-hidden bg-white rounded-3xl border border-slate-200 shadow-xl"
                 @click.away="if (!loading) showAddChildModal = false"
                 x-transition:enter="transition ease-out duration-300 transform scale-95"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform scale-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <!-- Modal Accent Top Line -->
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 to-teal-600"></div>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-slate-950">Add / Link Child</h3>
                        <button @click="showAddChildModal = false" :disabled="loading" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <p class="text-xs text-slate-500 mb-6">Enter your child's Student Number and Date of Birth to link them to your parent portal account.</p>
                    
                    <form @submit.prevent="submitLinkChild()" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Student Number / ID</label>
                            <input type="text" x-model="studentNumber" placeholder="e.g. 260001" 
                                   class="w-full px-4 py-2.5 text-sm uppercase bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:bg-white transition"
                                   :disabled="loading" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Date of Birth</label>
                            <input type="date" x-model="dob" 
                                   class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:bg-white transition"
                                   :disabled="loading" required>
                        </div>
                        
                        <div x-show="errorMsg" x-text="errorMsg" class="p-3.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-100 rounded-xl" x-cloak></div>
                        <div x-show="successMsg" x-text="successMsg" class="p-3.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-100 rounded-xl" x-cloak></div>
                        
                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="showAddChildModal = false" :disabled="loading" 
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading" 
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 disabled:bg-emerald-700/50 rounded-xl shadow-xs transition">
                                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="loading ? 'Linking...' : 'Link Student'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Settle Tuition Payment Modal — wide three-column layout -->
        <div x-show="showSettlePaymentModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>

            <!-- NO @click.away — modal only closes via X button or Cancel -->
            <div class="relative w-full bg-white rounded-3xl shadow-2xl overflow-hidden"
                 style="max-width: 1180px; width: 96%; max-height: 94vh; display: flex; flex-direction: column;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                <!-- Top Gradient Bar -->
                <div style="height: 4px; background: linear-gradient(90deg, #059669, #14b8a6, #0ea5e9); flex-shrink: 0;"></div>

                <!-- Three-column body -->
                <div style="display: flex; flex: 1; overflow: hidden;" class="flex-col lg:flex-row">

                    <!-- ═══ COLUMN 1: FORM ═══ -->
                    <div style="flex: 1.2; overflow-y: auto; padding: 28px 28px 24px; border-right: 1px solid #f1f5f9; display: flex; flex-direction: column;">

                        <!-- Header -->
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px;">
                            <div>
                                <h3 style="font-size: 20px; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">Settle Tuition Payment</h3>
                                <p style="font-size: 11px; color: #94a3b8; margin: 4px 0 0;">Submit your proof of payment for verification.</p>
                            </div>
                            <button @click="if (!settleLoading) showSettlePaymentModal = false"
                                    style="display: flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #64748b; cursor: pointer; flex-shrink: 0; transition: all 0.15s;"
                                    onmouseover="this.style.background='#fee2e2'; this.style.color='#dc2626'; this.style.borderColor='#fca5a5';"
                                    onmouseout="this.style.background='#f8fafc'; this.style.color='#64748b'; this.style.borderColor='#e2e8f0';">
                                <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Full Name + Balance -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px;">
                                <span style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 3px;">Full Name</span>
                                <strong x-text="settleStudentName" style="font-size: 13px; font-weight: 900; color: #0f172a; text-transform: uppercase; display: block; line-height: 1.3;"></strong>
                            </div>
                            <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #6ee7b7; border-radius: 14px; padding: 12px 14px;">
                                <span style="font-size: 9px; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 3px;">Remaining Balance</span>
                                <strong style="font-size: 16px; font-weight: 900; color: #064e3b; display: block;">&#8369;<span x-text="Number(settleRemainingBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></strong>
                            </div>
                        </div>

                        <!-- FORM -->
                        <form @submit.prevent="submitSettlePayment()" style="display: flex; flex-direction: column; gap: 16px; flex: 1;">

                            <!-- Payment Method -->
                            <div>
                                <label style="display: block; font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">Payment Method</label>
                                <select x-model="settleMethod" :disabled="settleLoading" required
                                        style="width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 11px 36px 11px 14px; outline: none; background-color: #f8fafc; font-family: inherit; font-size: 14px; font-weight: 700; color: #0f172a; cursor: pointer; transition: all 0.15s; appearance: none; background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2224%22 height=%2224%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2364748b%22 stroke-width=%222.5%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%226 9 12 15 18 9%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 16px; box-sizing: border-box;"
                                        onfocus="this.style.borderColor='#059669'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(5,150,105,0.12)';"
                                        onblur="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='none';">
                                    <option value="gcash">GCash</option>
                                    <option value="bdo">BDO Bank Transfer</option>
                                    <option value="remittance">Remittance</option>
                                </select>
                            </div>

                            <!-- Amount to Pay -->
                            <div>
                                <label style="display: block; font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">Amount to Pay (&#8369;)</label>
                                <input type="number" x-model.number="settleAmount" :max="settleRemainingBalance" min="1" step="0.01" :disabled="settleLoading" required
                                       style="width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 11px 14px; outline: none; background-color: #f8fafc; font-family: inherit; font-size: 16px; font-weight: 900; color: #064e3b; transition: all 0.15s; box-sizing: border-box;"
                                       onfocus="this.style.borderColor='#059669'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(5,150,105,0.12)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='none';">
                            </div>

                            <!-- Reference / Transaction ID -->
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <label style="font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.08em;">Reference / Transaction ID</label>
                                    <span style="display: inline-flex; align-items: center; gap: 3px; background: linear-gradient(135deg, #4285f4, #34a853); color: white; font-size: 8px; font-weight: 800; padding: 3px 8px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em;">
                                        <svg style="width:9px;height:9px;" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5 3A6.5 6.5 0 0116 9.5c0 1.61-.59 3.09-1.56 4.23l.27.27h.79l5 5-1.5 1.5-5-5v-.79l-.27-.27A6.516 6.516 0 019.5 16 6.5 6.5 0 013 9.5 6.5 6.5 0 019.5 3m0 2C7 5 5 7 5 9.5S7 14 9.5 14 14 12 14 9.5 12 5 9.5 5z"/></svg>
                                        Google AI Vision
                                    </span>
                                </div>
                                <input type="text" x-model="settleReference" placeholder="e.g. GCash Ref # / BDO Trace Code" :disabled="settleLoading"
                                       style="width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 11px 14px; outline: none; background-color: #f8fafc; font-family: inherit; font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; transition: all 0.15s; box-sizing: border-box;"
                                       onfocus="this.style.borderColor='#059669'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(5,150,105,0.12)';"
                                       onblur="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='none';">
                                <p style="font-size: 10px; color: #94a3b8; margin: 5px 0 0; font-style: italic;">Our AI will scan your receipt and verify this reference number automatically.</p>
                            </div>

                            <!-- Proof of Payment -->
                            <div>
                                <label style="display: block; font-size: 9px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px;">Proof of Payment Receipt</label>
                                <div x-show="!settleReceiptPreview" x-cloak>
                                    <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 22px 16px; background: #f8fafc; cursor: pointer; transition: all 0.2s;"
                                           onmouseover="this.style.borderColor='#059669'; this.style.background='#f0fdf4';"
                                           onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                                        <svg style="width:28px;height:28px;color:#94a3b8;margin-bottom:8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span style="font-size: 13px; font-weight: 700; color: #334155;">Upload Receipt Screenshot</span>
                                        <span style="font-size: 11px; color: #94a3b8; margin-top: 3px;">JPG, JPEG, or PNG — up to 5MB</span>
                                        <input type="file" x-ref="settleFileInput" @change="handleSettleFileChange($event)" accept="image/*" class="hidden" :required="!settleReceiptFile">
                                    </label>
                                </div>
                                <div x-show="settleReceiptPreview" style="display: flex; flex-direction: column; align-items: center;" x-cloak>
                                    <div style="position: relative; width: 150px; height: 150px; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #f8fafc;">
                                        <img :src="settleReceiptPreview" style="width: 100%; height: 100%; object-fit: cover;">
                                        <button type="button" @click="removeSettleFile()" :disabled="settleLoading"
                                                style="position: absolute; top: 6px; right: 6px; width: 22px; height: 22px; background: #dc2626; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white;">
                                            <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <span x-text="settleReceiptFile?.name" style="margin-top: 6px; font-size: 10px; color: #94a3b8; font-weight: 700; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; text-align: center;"></span>
                                </div>
                            </div>

                            <!-- Notifications -->
                            <div x-show="settleErrorMsg" x-text="settleErrorMsg"
                                 style="padding: 11px 14px; font-size: 12px; font-weight: 600; color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px;" x-cloak></div>
                            <div x-show="settleSuccessMsg" x-text="settleSuccessMsg"
                                 style="padding: 11px 14px; font-size: 12px; font-weight: 600; color: #065f46; background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 12px;" x-cloak></div>

                            <!-- Actions -->
                            <div style="display: flex; gap: 10px; padding-top: 14px; border-top: 1px solid #f1f5f9; margin-top: auto;">
                                <button type="button" @click="if (!settleLoading) showSettlePaymentModal = false" :disabled="settleLoading"
                                        style="flex: 1; padding: 12px 16px; font-size: 13px; font-weight: 700; color: #475569; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: all 0.15s;"
                                        onmouseover="this.style.background='#f1f5f9';"
                                        onmouseout="this.style.background='#f8fafc';">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="settleLoading"
                                        style="flex: 2; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; font-size: 13px; font-weight: 800; color: #ffffff; background: #047857; border: none; border-radius: 12px; cursor: pointer; transition: all 0.15s; box-shadow: 0 2px 10px rgba(4,120,87,0.35);"
                                        onmouseover="this.style.background='#065f46';"
                                        onmouseout="this.style.background='#047857';">
                                    <svg x-show="settleLoading" style="width:16px;height:16px;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                                        <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span x-text="settleLoading ? 'Submitting...' : 'Submit Payment'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ═══ COLUMN 2: GOOGLE AI VISION OCR SCAN DETAILS ═══ -->
                    <div style="width: 380px; flex-shrink: 0; border-right: 1px solid #f1f5f9; overflow-y: auto; padding: 28px 24px; display: flex; flex-direction: column; gap: 16px;">
                        
                        <!-- Panel Title -->
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <span style="font-size: 9px; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">Real-time Analysis</span>
                                <h4 style="font-size: 16px; font-weight: 900; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                                    <svg style="width:18px;height:18px;color:#3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    Google AI Vision OCR
                                </h4>
                            </div>
                            <!-- Live Status Badge -->
                            <span x-show="ocrScanning" class="animate-pulse" style="background: #eff6ff; color: #1d4ed8; font-size: 9px; font-weight: 800; padding: 3px 8px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;" x-cloak>
                                <svg class="animate-spin" style="width: 10px; height: 10px;" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Scanning...
                            </span>
                            <span x-show="!ocrScanning && ocrResult" style="background: #ecfdf5; color: #047857; font-size: 9px; font-weight: 800; padding: 3px 8px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em;" x-cloak>
                                Completed
                            </span>
                        </div>

                        <!-- 1. EMPTY STATE -->
                        <div x-show="!ocrScanning && !ocrResult" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border: 2px dashed #f1f5f9; border-radius: 20px; padding: 30px 20px; background: #fafafa; margin-top: 10px;">
                            <div style="background: #f1f5f9; padding: 14px; border-radius: 50%; margin-bottom: 12px; color: #94a3b8;">
                                <svg style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h5 style="font-size: 13px; font-weight: 800; color: #475569; margin: 0 0 6px;">No Scanned Data</h5>
                            <p style="font-size: 11px; color: #94a3b8; max-width: 220px; line-height: 1.5; margin: 0;">Upload a receipt screenshot in the left column to run real-time Google AI OCR scanning.</p>
                        </div>

                        <!-- 2. SCANNING STATE -->
                        <div x-show="ocrScanning" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; border: 2px dashed #e0f2fe; border-radius: 20px; padding: 30px 20px; background: #f0f9ff; margin-top: 10px;" x-cloak>
                            <div class="relative flex items-center justify-center" style="margin-bottom: 16px;">
                                <!-- Spinner ring -->
                                <div class="animate-spin rounded-full border-4 border-blue-500 border-t-transparent" style="width: 56px; height: 56px;"></div>
                                <!-- Inside scanner icon -->
                                <svg style="width: 24px; height: 24px; color: #0284c7; position: absolute;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </div>
                            <h5 style="font-size: 13px; font-weight: 800; color: #0369a1; margin: 0 0 6px;">Google AI Vision OCR</h5>
                            <p style="font-size: 11px; color: #0284c7; max-width: 220px; line-height: 1.5; margin: 0;">Reading receipt, verifying reference numbers, sender names, and amount paid...</p>
                        </div>

                        <!-- 3. RESULTS STATE -->
                        <div x-show="!ocrScanning && ocrResult" style="display: flex; flex-direction: column; gap: 14px;" x-cloak>
                            
                            <!-- Confidence & Quality Indicator -->
                            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">AI Scan Confidence</span>
                                    <span style="font-size: 14px; font-weight: 900;" :style="ocrResult?.confidence >= 0.85 ? 'color: #059669;' : 'color: #d97706;'" x-text="(ocrResult?.confidence ? Math.round(ocrResult.confidence * 100) : 95) + '%'"></span>
                                </div>
                                <div style="height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                                    <div style="height: 100%; border-radius: 10px; transition: width 0.6s ease;" :style="'width: ' + ((ocrResult?.confidence ? ocrResult.confidence * 100 : 95)) + '%;' + (ocrResult?.confidence >= 0.85 ? 'background: #10b981;' : 'background: #f59e0b;')"></div>
                                </div>
                            </div>

                            <!-- Detected Fields List (The 11 fields) -->
                            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 16px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <span style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; display: block; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">Detected Metadata</span>
                                
                                <!-- 1. Reference ID -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4;">
                                    <span style="color: #64748b; font-weight: 700;">Ref / Txn ID</span>
                                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                                        <span x-show="ocrResult?.ref" class="font-mono text-slate-900" style="font-weight: 800; text-transform: uppercase;" x-text="ocrResult?.ref"></span>
                                        <span x-show="!ocrResult?.ref" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                        <span x-show="ocrResult?.ref" style="font-size: 9px; color: #10b981; font-weight: 800; display: inline-flex; align-items: center; gap: 2px;">
                                            <span style="width: 4px; height: 4px; border-radius: 50%; background: #10b981;"></span> Auto-filled
                                        </span>
                                    </div>
                                </div>

                                <!-- 2. Amount Paid -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Amount Paid</span>
                                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                                        <span x-show="ocrResult?.amount" class="text-emerald-700" style="font-weight: 800;" x-text="'₱' + Number(ocrResult.amount).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                        <span x-show="!ocrResult?.amount" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                        <span x-show="ocrResult?.amount" style="font-size: 9px; color: #10b981; font-weight: 800; display: inline-flex; align-items: center; gap: 2px;">
                                            <span style="width: 4px; height: 4px; border-radius: 50%; background: #10b981;"></span> Auto-filled
                                        </span>
                                    </div>
                                </div>

                                <!-- 3. Date & Time -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Date & Time</span>
                                    <div style="text-align: right;">
                                        <span x-show="ocrResult?.date" class="text-slate-900" style="font-weight: 800;" x-text="ocrResult?.date"></span>
                                        <span x-show="!ocrResult?.date" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                    </div>
                                </div>

                                <!-- 4. Sender Name -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Sender Name</span>
                                    <div style="text-align: right;">
                                        <span x-show="ocrResult?.sender" class="text-slate-900" style="font-weight: 800; text-transform: uppercase;" x-text="ocrResult?.sender"></span>
                                        <span x-show="!ocrResult?.sender" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                    </div>
                                </div>

                                <!-- 5. Receiver Name -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Receiver Name</span>
                                    <div style="text-align: right;">
                                        <span x-show="ocrResult?.receiver" class="text-slate-900" style="font-weight: 800; text-transform: uppercase;" x-text="ocrResult?.receiver"></span>
                                        <span x-show="!ocrResult?.receiver" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                    </div>
                                </div>

                                <!-- 6. Payment Method -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Payment Method</span>
                                    <div style="text-align: right;">
                                        <span x-show="ocrResult?.method" class="text-slate-900" style="font-weight: 800; text-transform: uppercase;" x-text="ocrResult?.method"></span>
                                        <span x-show="!ocrResult?.method" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                    </div>
                                </div>

                                <!-- 7. Account / Mobile -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Account / Mobile</span>
                                    <div style="text-align: right;">
                                        <span x-show="ocrResult?.account" class="text-slate-900 font-mono" style="font-weight: 800;" x-text="ocrResult?.account"></span>
                                        <span x-show="!ocrResult?.account" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                    </div>
                                </div>

                                <!-- 8. Merchant Name -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">Merchant Name</span>
                                    <div style="text-align: right;">
                                        <span x-show="ocrResult?.merchant" class="text-slate-900" style="font-weight: 800; text-transform: uppercase;" x-text="ocrResult?.merchant"></span>
                                        <span x-show="!ocrResult?.merchant" style="color: #94a3b8; font-style: italic;">Not detected</span>
                                    </div>
                                </div>

                                <!-- 9. QR Code Hint -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; font-size: 12px; line-height: 1.4; border-top: 1px solid #f8fafc; padding-top: 8px;">
                                    <span style="color: #64748b; font-weight: 700;">QR Code Detected?</span>
                                    <div style="text-align: right;">
                                        <span :class="ocrResult?.has_qr ? 'text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md font-bold' : 'text-slate-500 font-bold'" x-text="ocrResult?.has_qr ? 'Yes' : 'No'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- 11. Full Raw Receipt Text (Scrollable Box) -->
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 12px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; cursor: pointer;" @click="showRawOcrText = !showRawOcrText">
                                    <span style="font-size: 9px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                                        </svg>
                                        Full OCR Scanned Text
                                    </span>
                                    <svg style="width:12px;height:12px;color:#64748b;transition: transform 0.2s;" :style="showRawOcrText ? 'transform: rotate(180deg);' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div x-show="showRawOcrText" style="margin-top: 6px;" x-cloak>
                                    <div class="font-mono text-slate-600 scrollbar-thin" style="font-size: 10px; max-height: 120px; overflow-y: auto; white-space: pre-wrap; background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; line-height: 1.5;" x-text="ocrResult?.raw_text"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ═══ COLUMN 3: PAYMENT DETAILS PANEL ═══ -->
                    <div style="width: 320px; flex-shrink: 0; background: #f8fafc; overflow-y: auto; padding: 28px 22px; display: flex; flex-direction: column; gap: 20px;">

                        <!-- Panel Title -->
                        <div>
                            <span style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">Where to Send Payment</span>
                            <h4 style="font-size: 16px; font-weight: 900; color: #0f172a; margin: 0;"
                                x-text="settleMethod === 'gcash' ? 'GCash Details' : settleMethod === 'bdo' ? 'BDO Bank Details' : 'Remittance Details'"></h4>
                        </div>

                        <!-- ── GCash Panel ── -->
                        <div x-show="settleMethod === 'gcash'" x-cloak style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="background: white; border: 1px solid #dbeafe; border-radius: 16px; padding: 16px; box-shadow: 0 1px 4px rgba(37,99,235,0.07);">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                                    <div style="background: #1e40af; color: white; font-size: 11px; font-weight: 900; padding: 4px 10px; border-radius: 8px; letter-spacing: 0.03em;">GCash</div>
                                    <span style="font-size: 12px; font-weight: 700; color: #1d4ed8;">Mobile Transfer</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <div style="background: #eff6ff; border-radius: 10px; padding: 10px 12px;">
                                        <span style="font-size: 9px; font-weight: 800; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.07em; display: block; margin-bottom: 3px;">Account 1</span>
                                        <span style="font-size: 18px; font-weight: 900; color: #1e40af; display: block; letter-spacing: 0.02em;">(+63) 927 299 1833</span>
                                    </div>
                                    <div style="background: #eff6ff; border-radius: 10px; padding: 10px 12px;">
                                        <span style="font-size: 9px; font-weight: 800; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.07em; display: block; margin-bottom: 3px;">Account 2</span>
                                        <span style="font-size: 18px; font-weight: 900; color: #1e40af; display: block; letter-spacing: 0.02em;">(+63) 995 233 9423</span>
                                    </div>
                                </div>
                                <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #dbeafe;">
                                    <span style="font-size: 9px; font-weight: 800; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.07em; display: block; margin-bottom: 3px;">Account Name</span>
                                    <span style="font-size: 14px; font-weight: 900; color: #1e3a8a; display: block;">CABEL B. NURHASAN</span>
                                </div>
                            </div>
                            <div style="background: #dbeafe; border-radius: 10px; padding: 10px 12px; font-size: 11px; color: #1e40af; font-weight: 600; line-height: 1.6;">
                                &#128242; Send the exact amount and screenshot the successful transaction receipt.
                            </div>
                        </div>

                        <!-- ── BDO Panel ── -->
                        <div x-show="settleMethod === 'bdo'" x-cloak style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="background: white; border: 1px solid #dbeafe; border-radius: 16px; padding: 16px; box-shadow: 0 1px 4px rgba(37,99,235,0.07);">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                                    <div style="background: #1e3a8a; color: white; font-size: 11px; font-weight: 900; padding: 4px 10px; border-radius: 8px;">BDO</div>
                                    <span style="font-size: 12px; font-weight: 700; color: #1d4ed8;">Bank Deposit / Transfer</span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <div style="background: #eff6ff; border-radius: 10px; padding: 10px 12px;">
                                        <span style="font-size: 9px; font-weight: 800; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.07em; display: block; margin-bottom: 2px;">Account 1 — Savings</span>
                                        <span style="font-size: 20px; font-weight: 900; color: #1e40af; display: block; letter-spacing: 0.04em;">010478011996</span>
                                        <span style="font-size: 10px; font-weight: 700; color: #3b82f6; display: block; margin-top: 3px;">AL MUNAWWARA ISLAMIC SCHOOL Inc.</span>
                                    </div>
                                    <div style="background: #eff6ff; border-radius: 10px; padding: 10px 12px;">
                                        <span style="font-size: 9px; font-weight: 800; color: #93c5fd; text-transform: uppercase; letter-spacing: 0.07em; display: block; margin-bottom: 2px;">Account 2 — Current</span>
                                        <span style="font-size: 20px; font-weight: 900; color: #1e40af; display: block; letter-spacing: 0.04em;">010478008782</span>
                                        <span style="font-size: 10px; font-weight: 700; color: #3b82f6; display: block; margin-top: 3px;">CABEL B. NURHASAN</span>
                                    </div>
                                </div>
                            </div>
                            <div style="background: #dbeafe; border-radius: 10px; padding: 10px 12px; font-size: 11px; color: #1e40af; font-weight: 600; line-height: 1.6;">
                                &#127970; Deposit over-the-counter or via online banking. Upload your deposit slip or transfer confirmation.
                            </div>
                        </div>

                        <!-- ── Remittance Panel ── -->
                        <div x-show="settleMethod === 'remittance'" x-cloak style="display: flex; flex-direction: column; gap: 12px;">
                            <div style="background: white; border: 1px solid #fde68a; border-radius: 16px; padding: 16px; box-shadow: 0 1px 4px rgba(217,119,6,0.07);">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                                    <div style="background: #d97706; color: white; font-size: 11px; font-weight: 900; padding: 4px 10px; border-radius: 8px;">REM</div>
                                    <span style="font-size: 12px; font-weight: 700; color: #92400e;">Remittance</span>
                                </div>
                                <div style="background: #fefce8; border-radius: 10px; padding: 12px 14px;">
                                    <span style="font-size: 9px; font-weight: 800; color: #d97706; text-transform: uppercase; letter-spacing: 0.07em; display: block; margin-bottom: 4px;">Send Payment To</span>
                                    <span style="font-size: 18px; font-weight: 900; color: #78350f; display: block;">CABEL B. NURHASAN</span>
                                </div>
                            </div>
                            <div style="background: #fef9c3; border-radius: 10px; padding: 10px 12px; font-size: 11px; color: #92400e; font-weight: 600; line-height: 1.6;">
                                &#128230; Please coordinate with the Finance Office for complete remittance center details before sending.
                            </div>
                        </div>

                        <!-- Reminders -->
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: auto;">
                            <p style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 10px;">&#128203; Reminders</p>
                            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                <li style="display: flex; gap: 7px; align-items: flex-start; font-size: 11px; color: #64748b; font-weight: 500; line-height: 1.5;">
                                    <span style="color: #059669; font-weight: 900; flex-shrink: 0; margin-top: 1px;">&#10003;</span>
                                    Upload a clear, full screenshot of your receipt
                                </li>
                                <li style="display: flex; gap: 7px; align-items: flex-start; font-size: 11px; color: #64748b; font-weight: 500; line-height: 1.5;">
                                    <span style="color: #059669; font-weight: 900; flex-shrink: 0; margin-top: 1px;">&#10003;</span>
                                    Make sure the reference number is visible in the receipt
                                </li>
                                <li style="display: flex; gap: 7px; align-items: flex-start; font-size: 11px; color: #64748b; font-weight: 500; line-height: 1.5;">
                                    <span style="color: #059669; font-weight: 900; flex-shrink: 0; margin-top: 1px;">&#10003;</span>
                                    Finance Office verifies within 1-2 business days
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
