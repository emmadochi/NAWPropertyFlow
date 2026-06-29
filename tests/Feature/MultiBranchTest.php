<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CompanySetting;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiBranchTest extends TestCase
{
    use RefreshDatabase;

    protected $lagosBranch;
    protected $abujaBranch;
    protected $adminUser;
    protected $lagosOfficer;
    protected $abujaOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create branches
        $this->lagosBranch = Branch::create([
            'name' => 'Lagos Branch',
            'city' => 'Lagos',
            'address' => 'Lekki Phase 1',
            'phone' => '+2348011111111',
            'email' => 'lagos@example.com',
        ]);

        $this->abujaBranch = Branch::create([
            'name' => 'Abuja Branch',
            'city' => 'Abuja',
            'address' => 'Maitama',
            'phone' => '+2348022222222',
            'email' => 'abuja@example.com',
        ]);

        // Create users
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->lagosOfficer = User::create([
            'name' => 'Lagos Officer',
            'email' => 'lagos_officer@example.com',
            'password' => bcrypt('password'),
            'role' => 'sales_executive',
            'status' => 'active',
            'branch_id' => $this->lagosBranch->id,
        ]);

        $this->abujaOfficer = User::create([
            'name' => 'Abuja Officer',
            'email' => 'abuja_officer@example.com',
            'password' => bcrypt('password'),
            'role' => 'sales_executive',
            'status' => 'active',
            'branch_id' => $this->abujaBranch->id,
        ]);

        // Setup default CompanySetting
        CompanySetting::create([
            'company_name' => 'NAW Properties Ltd',
            'email' => 'info@nawproperties.com',
            'phone' => '+234 800 000 0000',
            'address' => 'Lekki Phase 1, Lagos, Nigeria',
        ]);
    }

    /** @test */
    public function sales_executive_is_strictly_scoped_to_their_branch_leads_and_properties()
    {
        // Create properties in different branches
        $lagosProperty = Property::create([
            'name' => 'Lagos Luxury Villa',
            'location' => 'Lekki',
            'property_type' => 'Duplex',
            'price' => 50000000.00,
            'available_units' => 5,
            'branch_id' => $this->lagosBranch->id,
        ]);

        $abujaProperty = Property::create([
            'name' => 'Abuja Office Complex',
            'location' => 'Maitama',
            'property_type' => 'Flat',
            'price' => 120000000.00,
            'available_units' => 2,
            'branch_id' => $this->abujaBranch->id,
        ]);

        // Create leads in different branches
        $lagosLead = Lead::create([
            'full_name' => 'Lagos Buyer',
            'phone_number' => '12345',
            'budget_range' => '₦30M - ₦60M',
            'lead_source' => 'Website',
            'status' => 'New',
            'branch_id' => $this->lagosBranch->id,
            'assigned_to' => $this->lagosOfficer->id,
        ]);

        $abujaLead = Lead::create([
            'full_name' => 'Abuja Buyer',
            'phone_number' => '67890',
            'budget_range' => '₦100M+',
            'lead_source' => 'Website',
            'status' => 'New',
            'branch_id' => $this->abujaBranch->id,
            'assigned_to' => $this->abujaOfficer->id,
        ]);

        // Acting as Lagos Officer
        $this->actingAs($this->lagosOfficer);

        // Fetching properties: Lagos Officer should only see Lagos property
        $properties = Property::all();
        $this->assertTrue($properties->contains($lagosProperty));
        $this->assertFalse($properties->contains($abujaProperty));

        // Fetching leads: Lagos Officer should only see Lagos lead
        $leads = Lead::all();
        $this->assertTrue($leads->contains($lagosLead));
        $this->assertFalse($leads->contains($abujaLead));
    }

    /** @test */
    public function admins_can_switch_active_branch_scopes_and_see_all()
    {
        $lagosProperty = Property::create([
            'name' => 'Lagos Luxury Villa',
            'location' => 'Lekki',
            'property_type' => 'Duplex',
            'price' => 50000000.00,
            'available_units' => 5,
            'branch_id' => $this->lagosBranch->id,
        ]);

        $abujaProperty = Property::create([
            'name' => 'Abuja Office Complex',
            'location' => 'Maitama',
            'property_type' => 'Flat',
            'price' => 120000000.00,
            'available_units' => 2,
            'branch_id' => $this->abujaBranch->id,
        ]);

        $this->actingAs($this->adminUser);

        // Admin defaults to seeing all branches
        $properties = Property::all();
        $this->assertTrue($properties->contains($lagosProperty));
        $this->assertTrue($properties->contains($abujaProperty));

        // Admin switches active branch scope to Lagos via request
        $response = $this->get('/dashboard?switch_branch_id=' . $this->lagosBranch->id);
        $response->assertRedirect('/dashboard');
        
        // Assert that selected branch is stored in session
        $this->assertEquals($this->lagosBranch->id, session('selected_branch_id'));

        // Querying properties should now filter to Lagos Branch
        $properties = Property::all();
        $this->assertTrue($properties->contains($lagosProperty));
        $this->assertFalse($properties->contains($abujaProperty));

        // Admin switches to all branches
        $this->get('/dashboard?switch_branch_id=all');
        $this->assertEquals('all', session('selected_branch_id'));

        $properties = Property::all();
        $this->assertTrue($properties->contains($lagosProperty));
        $this->assertTrue($properties->contains($abujaProperty));
    }

    /** @test */
    public function company_settings_can_be_updated_by_admin()
    {
        $this->actingAs($this->adminUser);

        $response = $this->put('/settings/company', [
            'company_name' => 'Updated Properties Ltd',
            'email' => 'billing@updated.com',
            'phone' => '+234 811 111 1111',
            'address' => 'Updated HQ Address, Lagos',
            'letterhead_header' => '<h1>Updated Header HTML</h1>',
            'letterhead_footer' => '<p>Updated Footer HTML</p>',
        ]);

        $response->assertRedirect();
        
        $settings = CompanySetting::first();
        $this->assertEquals('Updated Properties Ltd', $settings->company_name);
        $this->assertEquals('billing@updated.com', $settings->email);
        $this->assertEquals('<h1>Updated Header HTML</h1>', $settings->letterhead_header);
    }
}
