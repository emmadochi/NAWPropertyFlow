<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LeadImportTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::factory()->create([
            'role' => 'company_admin'
        ]);
    }

    /**
     * Test downloading CSV template.
     */
    public function test_download_import_template(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('leads.import-template'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        $this->assertStringContainsString('full_name,phone_number', $content);
    }

    /**
     * Test successful CSV upload.
     */
    public function test_import_leads_successfully(): void
    {
        $csvContent = "full_name,phone_number,whatsapp_number,email,budget_range,preferred_location,lead_source,notes,status\n"
                    . "Aliko Dangote,+2348000000001,+2348000000001,aliko@dangote.com,₦100M+,Lekki,Forbes,High value prospect,New\n"
                    . "Femi Otedola,+2348000000002,,femi@otedola.com,₦100M+,Ikoyi,Instagram,,Contacted";

        $file = UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->actingAs($this->adminUser)
            ->post(route('leads.import'), [
                'csv_file' => $file
            ]);

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('success', 'Successfully imported 2 leads.');

        $this->assertDatabaseHas('leads', [
            'full_name' => 'Aliko Dangote',
            'phone_number' => '+2348000000001',
            'email' => 'aliko@dangote.com',
            'budget_range' => '₦100M+',
            'preferred_location' => 'Lekki',
            'lead_source' => 'Forbes',
            'status' => 'New',
        ]);

        $this->assertDatabaseHas('leads', [
            'full_name' => 'Femi Otedola',
            'phone_number' => '+2348000000002',
            'email' => 'femi@otedola.com',
            'status' => 'Contacted',
        ]);
    }

    /**
     * Test CSV upload validation fails with missing columns.
     */
    public function test_import_leads_validation_missing_columns(): void
    {
        $csvContent = "full_name,email\n"
                    . "Aliko Dangote,aliko@dangote.com";

        $file = UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->actingAs($this->adminUser)
            ->post(route('leads.import'), [
                'csv_file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error']);
    }
}
