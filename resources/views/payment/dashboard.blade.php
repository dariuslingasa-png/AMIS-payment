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
                                @if($remainingBalance > 0)
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

        <!-- Settle Payment Modal -->
        <div x-show="showSettlePaymentModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             x-cloak>
            <div class="relative w-full bg-white rounded-3xl border border-slate-200 shadow-xl max-h-[90vh] overflow-y-auto"
                 @click.away="if (!settleLoading) showSettlePaymentModal = false"
                 style="max-width: 550px !important; width: 95% !important; margin: auto !important; box-sizing: border-box !important;"
                 x-transition:enter="transition ease-out duration-300 transform scale-95"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform scale-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <!-- Modal Accent Top Line -->
                <div class="h-1.5 w-full bg-gradient-to-r from-emerald-600 to-teal-600"></div>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-black text-slate-950">Settle Tuition Payment</h3>
                        <button @click="showSettlePaymentModal = false" :disabled="settleLoading" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Selected Student Banner -->
                    <div class="p-4 mb-6 rounded-2xl bg-emerald-50/50 border border-emerald-100 flex justify-between items-center text-xs">
                        <div>
                            <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block">Student Name</span>
                            <strong x-text="settleStudentName" class="text-slate-800 font-extrabold uppercase"></strong>
                        </div>
                        <div class="text-right">
                            <span class="text-xxs font-bold text-slate-400 uppercase tracking-wider block">Remaining Balance</span>
                            <strong class="text-emerald-700 font-extrabold">₱<span x-text="Number(settleRemainingBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></strong>
                        </div>
                    </div>

                    <!-- Official Accounts Accordion/Details -->
                    <div class="mb-6 space-y-3">
                        <span class="block text-xxs font-bold text-slate-400 uppercase tracking-wider">Official Payment Accounts</span>
                        
                        <!-- GCash & Maya -->
                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl flex items-start gap-3">
                            <div class="flex flex-col gap-1 shrink-0">
                                <div style="display: inline-flex; align-items: center; justify-content: center; background: #ffffff; border-radius: 8px; padding: 4px; border: 1px solid #e2e8f0; width: 44px; height: 26px;">
                                    <span style="color: #1e3a8a; font-weight: 900; font-size: 10px;">GCash</span>
                                </div>
                                <div style="display: inline-flex; align-items: center; justify-content: center; background: #ffffff; border-radius: 8px; padding: 4px; border: 1px solid #e2e8f0; width: 44px; height: 26px;">
                                    <span style="color: #10b981; font-weight: 900; font-size: 10px;">Maya</span>
                                </div>
                            </div>
                            <div class="text-xs text-slate-600">
                                <strong class="text-slate-800 font-semibold block">GCash / Maya Mobile Transfer</strong>
                                <span class="block mt-0.5">Account 1: <strong class="text-slate-700 font-bold">(+63) 927 299 1833</strong></span>
                                <span class="block">Account 2: <strong class="text-slate-700 font-bold">(+63) 995 233 9423</strong></span>
                                <span class="block text-xxs text-slate-400 uppercase font-bold mt-1">Account Name: CABEL B. NURHASAN</span>
                            </div>
                        </div>

                        <!-- BDO Savings -->
                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl flex items-start gap-3">
                            <div class="shrink-0" style="display: inline-flex; align-items: center; justify-content: center; background: #ffffff; border-radius: 8px; padding: 4px; border: 1px solid #e2e8f0; width: 44px; height: 26px;">
                                <span style="color: #1e40af; font-weight: 900; font-size: 11px;">BDO</span>
                            </div>
                            <div class="text-xs text-slate-600">
                                <strong class="text-slate-800 font-semibold block">BDO Bank Deposit / Transfer</strong>
                                <span class="block mt-0.5">Account 1: <strong class="text-slate-700 font-bold">010478011996</strong> (Savings)</span>
                                <span class="block text-xxs text-slate-400 uppercase font-bold">Name: AL MUNAWWARA ISLAMIC SCHOOL Inc.</span>
                                <span class="block mt-1">Account 2: <strong class="text-slate-700 font-bold">010478008782</strong> (Current)</span>
                                <span class="block text-xxs text-slate-400 uppercase font-bold">Name: CABEL B. NURHASAN</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Submission Form -->
                    <form @submit.prevent="submitSettlePayment()" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1.5">Payment Method</label>
                                <select x-model="settleMethod" :disabled="settleLoading"
                                        style="width: 100% !important; border: 1px solid #cbd5e1 !important; border-radius: 12px !important; padding: 10px 14px !important; outline: none !important; background-color: #f8fafc !important; font-family: inherit !important; font-size: 14px !important; font-weight: 600 !important; color: #0f172a !important; cursor: pointer !important; transition: all 0.15s ease-in-out !important; appearance: none !important; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"%2364748b\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"6 9 12 15 18 9\"></polyline></svg>') !important; background-repeat: no-repeat !important; background-position: right 12px center !important; background-size: 16px !important; padding-right: 36px !important;"
                                        onfocus="this.style.borderColor='#059669'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 2px rgba(5, 150, 105, 0.15)';"
                                        onblur="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='none';"
                                        required>
                                    <option value="gcash">GCash</option>
                                    <option value="bdo">BDO Bank Transfer</option>
                                    <option value="remittance">Remittance</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xxs font-bold text-slate-700 uppercase mb-1.5">Amount to Pay (₱)</label>
                                <input type="number" x-model.number="settleAmount" :max="settleRemainingBalance" min="1" step="0.01" :disabled="settleLoading"
                                       style="width: 100% !important; border: 1px solid #cbd5e1 !important; border-radius: 12px !important; padding: 10px 14px !important; outline: none !important; background-color: #f8fafc !important; font-family: inherit !important; font-size: 14px !important; font-weight: 700 !important; color: #0f172a !important; transition: all 0.15s ease-in-out !important;"
                                       onfocus="this.style.borderColor='#059669'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 2px rgba(5, 150, 105, 0.15)';"
                                       onblur="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='none';"
                                       required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xxs font-bold text-slate-700 uppercase mb-1.5">Reference No / Transaction ID</label>
                            <input type="text" x-model="settleReference" placeholder="e.g. Ref # / Trace Code" :disabled="settleLoading"
                                   style="width: 100% !important; border: 1px solid #cbd5e1 !important; border-radius: 12px !important; padding: 10px 14px !important; outline: none !important; background-color: #f8fafc !important; font-family: inherit !important; font-size: 14px !important; font-weight: 700 !important; color: #0f172a !important; text-transform: uppercase !important; transition: all 0.15s ease-in-out !important;"
                                   onfocus="this.style.borderColor='#059669'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 2px rgba(5, 150, 105, 0.15)';"
                                   onblur="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc'; this.style.boxShadow='none';">
                        </div>

                        <!-- 1:1 Aspect Ratio square receipt preview area -->
                        <div>
                            <label class="block text-xxs font-bold text-slate-700 uppercase mb-1.5">Proof of Payment Receipt</label>
                            
                            <!-- Uploader drag/drop area -->
                            <div x-show="!settleReceiptPreview" x-cloak>
                                <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-2xl p-5 bg-slate-50 hover:bg-slate-100/50 cursor-pointer transition">
                                    <svg class="w-8 h-8 text-slate-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs font-bold text-slate-700">Upload Receipt Screenshot</span>
                                    <span class="text-xxs text-slate-400 mt-0.5">JPG, JPEG, or PNG up to 5MB</span>
                                    <input type="file" x-ref="settleFileInput" @change="handleSettleFileChange($event)" accept="image/*" class="hidden" :required="!settleReceiptFile">
                                </label>
                            </div>

                            <!-- 1:1 aspect ratio square preview box -->
                            <div x-show="settleReceiptPreview" class="flex flex-col items-center" x-cloak>
                                <div class="relative w-40 h-40 border border-slate-200 rounded-2xl overflow-hidden shadow-xs bg-slate-50 aspect-square">
                                    <img :src="settleReceiptPreview" class="w-full h-full object-cover">
                                    <button type="button" @click="removeSettleFile()" :disabled="settleLoading"
                                            class="absolute top-2 right-2 bg-rose-600 hover:bg-rose-700 text-white rounded-full p-1 shadow-md transition disabled:opacity-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <span x-text="settleReceiptFile?.name" class="mt-2 text-xxs text-slate-400 font-bold truncate max-w-xs block"></span>
                            </div>
                        </div>

                        <!-- Feedback Notifications -->
                        <div x-show="settleErrorMsg" x-text="settleErrorMsg" class="p-3.5 text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-100 rounded-xl" x-cloak></div>
                        <div x-show="settleSuccessMsg" x-text="settleSuccessMsg" class="p-3.5 text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-100 rounded-xl" x-cloak></div>

                        <!-- Modal Actions -->
                        <div class="flex gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="showSettlePaymentModal = false" :disabled="settleLoading" 
                                    class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="settleLoading" 
                                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 disabled:bg-emerald-700/50 rounded-xl shadow-xs transition">
                                <svg x-show="settleLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="settleLoading ? 'Submitting...' : 'Submit Payment'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
