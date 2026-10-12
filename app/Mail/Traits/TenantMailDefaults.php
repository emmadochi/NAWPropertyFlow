<?php

namespace App\Mail\Traits;

use App\Models\CompanySetting;
use Illuminate\Mail\Mailables\Address;

trait TenantMailDefaults
{
    /**
     * Resolve the tenant company name dynamically.
     */
    public function resolveCompanyName(): string
    {
        try {
            $company = CompanySetting::first();
            $name = $company?->company_name 
                ?? (function_exists('tenant') && tenant() ? (tenant('company_name') ?? tenant('name')) : null)
                ?? config('mail.from.name') 
                ?? config('app.name', 'Buckcrest Havens Limited');

            if (empty($name) || in_array($name, ['Laravel', 'NAW PropertyFlow', 'NAW Propertyflow CRM', 'NAW PropertyFlow CRM'])) {
                return 'Buckcrest Havens Limited';
            }

            return $name;
        } catch (\Throwable $e) {
            return 'Buckcrest Havens Limited';
        }
    }

    /**
     * Resolve the tenant company sender address.
     */
    public function resolveFromAddress(): Address
    {
        $name = $this->resolveCompanyName();
        try {
            $company = CompanySetting::first();
            $email = $company?->email ?? config('mail.from.address');
            if (empty($email) || $email === 'hello@example.com') {
                $email = config('mail.from.address', 'noreply@nawpropertyflow.com.ng');
            }
        } catch (\Throwable $e) {
            $email = config('mail.from.address', 'noreply@nawpropertyflow.com.ng');
        }

        return new Address($email, $name);
    }
}
