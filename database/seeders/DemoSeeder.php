<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a demo account with realistic (fictional) applications for screenshots and local demos.
 * Safe to re-run: it rebuilds only the demo user's applications.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => 'password', 'email_verified_at' => now()],
        );

        $user->applications()->delete();

        $applications = [
            ['Northwind Labs', 'Frontend Developer', ApplicationStatus::Wishlist, null, null, 'Remote', null],
            ['Kitebird Apps', 'Vue.js Developer', ApplicationStatus::Wishlist, null, null, 'Manila', null],
            ['Brightpath Health', 'Full-Stack Developer (Laravel)', ApplicationStatus::Applied, 12, -1, 'Remote', '₱90k–₱110k'],
            ['Lumen Analytics', 'Junior Software Engineer', ApplicationStatus::Applied, 9, 0, 'Makati', '₱55k–₱70k'],
            ['Orchard Finance', 'PHP Developer', ApplicationStatus::Applied, 4, 6, 'Taguig', null],
            ['Cobalt Systems', 'Web Developer', ApplicationStatus::Interviewing, 18, 3, 'Hybrid · Pasig', '₱80k'],
            ['Pinecrest Studio', 'UI Engineer', ApplicationStatus::Interviewing, 21, null, 'Remote', null],
            ['Summit Robotics', 'Software Engineer I', ApplicationStatus::Offer, 30, null, 'Quezon City', '₱85k'],
            ['Harborline Logistics', 'Backend Developer', ApplicationStatus::Rejected, 35, null, 'Cebu', null],
        ];

        $positions = [];

        foreach ($applications as [$company, $role, $status, $appliedDaysAgo, $followUpInDays, $location, $salary]) {
            $application = $user->applications()->make([
                'company' => $company,
                'role' => $role,
                'status' => $status,
                'url' => 'https://example.com/careers/'.str($company)->slug(),
                'location' => $location,
                'salary' => $salary,
                'applied_on' => $appliedDaysAgo === null ? null : today()->subDays($appliedDaysAgo),
                'follow_up_on' => $followUpInDays === null ? null : today()->addDays($followUpInDays),
            ]);
            $application->position = $positions[$status->value] = ($positions[$status->value] ?? -1) + 1;
            $application->save();

            if ($company === 'Cobalt Systems') {
                $application->notes()->create(['body' => "First interview with the engineering manager went well.\nThey use Laravel + Vue, same stack as this project."]);
                $application->notes()->create(['body' => 'Technical interview scheduled for next week. Review Eloquent relationships and queues.']);
            }

            if ($company === 'Brightpath Health') {
                $application->notes()->create(['body' => 'Referred by a former classmate. Follow up with the recruiter if no reply.']);
            }

            if ($company === 'Summit Robotics') {
                $application->notes()->create(['body' => 'Offer received! Deadline to respond is Friday.']);
            }
        }
    }
}
