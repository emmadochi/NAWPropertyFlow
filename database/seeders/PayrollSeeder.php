<?php

namespace Database\Seeders;

use App\Models\DisciplinaryRecord;
use App\Models\PayrollDeduction;
use App\Models\PerformanceReview;
use App\Models\SalaryStructure;
use App\Models\StaffCertification;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Database\Seeder;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereNotIn('role', ['customer'])->get()->keyBy('email');
        if ($users->isEmpty()) {
            return;
        }

        // 1. Seed Salary Structures
        $salaryData = [
            'admin@propertyflow.com' => [
                'base_salary' => 650000,
                'housing_allowance' => 200000,
                'transport_allowance' => 100000,
                'other_allowances' => 50000,
                'tax_percent' => 10.0,
                'pension_percent' => 8.0,
                'bank_name' => 'Zenith Bank PLC',
                'account_number' => '1012345678',
                'account_name' => 'Company Administrator',
            ],
            'manager@propertyflow.com' => [
                'base_salary' => 450000,
                'housing_allowance' => 150000,
                'transport_allowance' => 80000,
                'other_allowances' => 30000,
                'tax_percent' => 8.0,
                'pension_percent' => 8.0,
                'bank_name' => 'Zenith Bank PLC',
                'account_number' => '2081122334',
                'account_name' => 'Tunde Bakare',
            ],
            'se1@propertyflow.com' => [
                'base_salary' => 220000,
                'housing_allowance' => 80000,
                'transport_allowance' => 50000,
                'other_allowances' => 20000,
                'tax_percent' => 5.0,
                'pension_percent' => 8.0,
                'bank_name' => 'Guaranty Trust Bank (GTB)',
                'account_number' => '0145566778',
                'account_name' => 'Emeka Okafor',
            ],
            'hr@propertyflow.com' => [
                'base_salary' => 380000,
                'housing_allowance' => 120000,
                'transport_allowance' => 60000,
                'other_allowances' => 25000,
                'tax_percent' => 7.5,
                'pension_percent' => 8.0,
                'bank_name' => 'Access Bank PLC',
                'account_number' => '0098877665',
                'account_name' => 'Zainab Ahmed',
            ],
            'accountant@propertyflow.com' => [
                'base_salary' => 420000,
                'housing_allowance' => 140000,
                'transport_allowance' => 70000,
                'other_allowances' => 30000,
                'tax_percent' => 8.0,
                'pension_percent' => 8.0,
                'bank_name' => 'First Bank of Nigeria',
                'account_number' => '3056677889',
                'account_name' => 'Femi Adeleke',
            ],
            'support@propertyflow.com' => [
                'base_salary' => 180000,
                'housing_allowance' => 60000,
                'transport_allowance' => 40000,
                'other_allowances' => 15000,
                'tax_percent' => 5.0,
                'pension_percent' => 8.0,
                'bank_name' => 'United Bank for Africa (UBA)',
                'account_number' => '2109988776',
                'account_name' => 'Blessing Nnamdi',
            ],
            'media@propertyflow.com' => [
                'base_salary' => 250000,
                'housing_allowance' => 90000,
                'transport_allowance' => 50000,
                'other_allowances' => 20000,
                'tax_percent' => 5.0,
                'pension_percent' => 8.0,
                'bank_name' => 'Stanbic IBTC Bank',
                'account_number' => '0041122334',
                'account_name' => 'David Olatunji',
            ],
        ];

        foreach ($salaryData as $email => $data) {
            if (isset($users[$email])) {
                SalaryStructure::updateOrCreate(
                    ['user_id' => $users[$email]->id],
                    $data
                );
            }
        }

        // 2. Seed Disciplinary Records & Query Letters
        $hrUser = $users['hr@propertyflow.com'] ?? $users->first();
        $seUser = $users['se1@propertyflow.com'] ?? null;
        $mediaUser = $users['media@propertyflow.com'] ?? null;

        if ($seUser) {
            DisciplinaryRecord::updateOrCreate(
                [
                    'user_id' => $seUser->id,
                    'description' => 'Unexcused absence for scheduled VIP site inspection at Guzape Smart Villa with a diaspora buyer.',
                ],
                [
                    'issued_by' => $hrUser->id,
                    'incident_type' => 'query',
                    'incident_date' => now()->subDays(12),
                    'action_taken' => '₦25,000 Disciplinary Salary Penalty & Formal Caution Letter',
                    'status' => 'resolved',
                    'resolution_notes' => 'Staff submitted written defense admitting transport breakdown. Surcharged ₦25,000 and warned regarding future notice protocols.',
                    'resolved_at' => now()->subDays(10),
                ]
            );

            // Seed the corresponding deduction into current month payroll
            PayrollDeduction::updateOrCreate(
                [
                    'user_id' => $seUser->id,
                    'title' => 'Disciplinary Fine – Missed Guzape Client Inspection (Query #104)',
                    'month' => (int) now()->format('n'),
                    'year' => (int) now()->format('Y'),
                ],
                [
                    'deduction_type' => 'fine',
                    'amount' => 25000.00,
                    'created_by' => $hrUser->id,
                ]
            );

            // Performance review
            PerformanceReview::updateOrCreate(
                [
                    'user_id' => $seUser->id,
                    'review_period' => 'Q2-2026',
                ],
                [
                    'reviewed_by' => $hrUser->id,
                    'score' => 84,
                    'rating' => 'exceeds_expectations',
                    'strengths' => 'Exceptional lead prospecting in Guzape and Maitama. Closed ₦180M villa deal.',
                    'areas_for_improvement' => 'Must improve punctuality for scheduled site inspections and documentation upload speed.',
                    'goals_next_period' => 'Target ₦300M in closed transactions and complete Redan Professional Certification.',
                    'manager_comments' => 'High potential sales officer with strong negotiation skills.',
                    'status' => 'submitted',
                ]
            );

            // Certifications
            StaffCertification::updateOrCreate(
                [
                    'user_id' => $seUser->id,
                    'title' => 'Certified Real Estate Sales Professional (CRESP)',
                ],
                [
                    'issuing_body' => 'Real Estate Developers Association of Nigeria (REDAN)',
                    'issued_date' => '2024-03-15',
                    'expiry_date' => '2027-03-15',
                    'certificate_number' => 'REDAN/NG/2024/0982',
                    'notes' => 'Verified professional compliance certification.',
                ]
            );
        }

        if ($mediaUser) {
            DisciplinaryRecord::updateOrCreate(
                [
                    'user_id' => $mediaUser->id,
                    'description' => 'Delayed delivery of 4K drone walkthrough videos for Katampe Hills project by 5 business days.',
                ],
                [
                    'issued_by' => $hrUser->id,
                    'incident_type' => 'warning',
                    'incident_date' => now()->subDays(20),
                    'action_taken' => 'First Written Warning',
                    'status' => 'resolved',
                    'resolution_notes' => 'Production delay caused by weather and editing workstation upgrade. Resolved and footage delivered.',
                    'resolved_at' => now()->subDays(18),
                ]
            );
        }

        // 3. Manager Loan Repayment Deduction
        $managerUser = $users['manager@propertyflow.com'] ?? null;
        if ($managerUser) {
            PayrollDeduction::updateOrCreate(
                [
                    'user_id' => $managerUser->id,
                    'title' => 'Staff Laptop & Equipment Advance (Installment 2 of 4)',
                    'month' => (int) now()->format('n'),
                    'year' => (int) now()->format('Y'),
                ],
                [
                    'deduction_type' => 'loan_repayment',
                    'amount' => 50000.00,
                    'created_by' => $hrUser->id,
                ]
            );
        }

        // 4. Auto-generate the live balanced monthly payroll batch
        /** @var PayrollService $payrollService */
        $payrollService = app(PayrollService::class);
        $payrollService->generateMonthlyPayroll(
            (int) now()->format('n'),
            (int) now()->format('Y'),
            $users->first()->id
        );
    }
}
