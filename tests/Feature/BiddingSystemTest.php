<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Item;
use App\Models\Junkshop;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Carbon\Carbon;

class BiddingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $junkshopOwner;
    protected $junkshop;
    protected $merchant;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable notifications for testing
        Notification::fake();
        
        // Create a junkshop owner user
        $this->junkshopOwner = User::factory()->create([
            'type' => 'junkshop_owner',
            'name' => 'Test Junkshop Owner'
        ]);
        
        // Assign junkshop owner role
        $this->junkshopOwner->assignRole('junkshop_owner');
        
        // Create a junkshop
        $this->junkshop = Junkshop::factory()->create([
            'user_id' => $this->junkshopOwner->ulid,
            'name' => 'Test Junkshop'
        ]);
        
        // Create a merchant user
        $merchantUser = User::factory()->create([
            'type' => 'merchant',
            'name' => 'Test Merchant'
        ]);
        
        // Assign merchant role
        $merchantUser->assignRole('merchant');
        
        // Create a merchant profile
        $this->merchant = Merchant::factory()->create([
            'user_id' => $merchantUser->ulid,
            'name' => 'Test Merchant'
        ]);
        
        // Create an item
        $this->item = Item::factory()->create([
            'name' => 'Aluminum Cans'
        ]);
    }

    /** @test */
    public function junkshop_owner_can_create_bid_with_bidding_enabled()
    {
        $this->actingAs($this->junkshopOwner);
        
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);
        
        $response = $this->postJson('/api/v1/bids', [
            'junkshop_id' => $this->junkshop->ulid,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'price_per_kg' => 15.00,
            'starting_bid' => 20.00,
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'notes' => 'Test bidding',
            'is_bidding_enabled' => true
        ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('bids', [
            'junkshop_id' => $this->junkshop->ulid,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'price_per_kg' => 15.00,
            'starting_bid' => 20.00,
            'is_bidding_enabled' => true,
            'status' => 'pending' // Should be pending until admin approves
        ]);
    }

    /** @test */
    public function admin_can_approve_bid()
    {
        // First create a bid
        $bid = Bid::factory()->create([
            'junkshop_id' => $this->junkshop->ulid,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'price_per_kg' => 15.00,
            'starting_bid' => 20.00,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(7),
            'is_bidding_enabled' => true,
            'status' => 'pending'
        ]);
        
        // Create an admin user
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');
        
        $this->actingAs($adminUser);
        
        // Admin approves the bid
        $response = $this->putJson("/admin/bids/{$bid->id}/status", [
            'status' => 'accepted'
        ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('bids', [
            'id' => $bid->id,
            'status' => 'accepted'
        ]);
    }

    /** @test */
    public function merchant_can_place_bid()
    {
        // First create and approve a bid
        $bid = Bid::factory()->create([
            'junkshop_id' => $this->junkshop->ulid,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'price_per_kg' => 15.00,
            'starting_bid' => 20.00,
            'start_date' => Carbon::now()->subDay(),
            'end_date' => Carbon::now()->addDays(7),
            'is_bidding_enabled' => true,
            'status' => 'accepted',
            'accepted_at' => Carbon::now()->subDay()
        ]);
        
        // Act as merchant
        $merchantUser = User::find($this->merchant->user_id);
        $this->actingAs($merchantUser);
        
        // Place a bid
        $bidAmount = 22.00; // Above the starting bid
        
        $response = $this->postJson("/api/v1/bidding/place-bid/{$bid->ulid}", [
            'bid_amount' => $bidAmount,
            'notes' => 'My first bid'
        ]);
        
        $response->assertStatus(201);
        
        // Check the bid was updated with the new amount
        $this->assertDatabaseHas('bids', [
            'id' => $bid->id,
            'current_bid' => $bidAmount,
            'current_bidder_id' => $this->merchant->ulid
        ]);
        
        // Check bid history was created
        $this->assertDatabaseHas('bid_histories', [
            'bid_id' => $bid->id,
            'merchant_id' => $this->merchant->ulid,
            'bid_amount' => $bidAmount
        ]);
    }

    /** @test */
    public function merchant_cannot_bid_below_minimum_amount()
    {
        // First create and approve a bid
        $bid = Bid::factory()->create([
            'junkshop_id' => $this->junkshop->ulid,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'price_per_kg' => 15.00,
            'starting_bid' => 20.00,
            'current_bid' => 21.00, // Someone already bid
            'start_date' => Carbon::now()->subDay(),
            'end_date' => Carbon::now()->addDays(7),
            'is_bidding_enabled' => true,
            'status' => 'accepted',
            'accepted_at' => Carbon::now()->subDay()
        ]);
        
        // Act as merchant
        $merchantUser = User::find($this->merchant->user_id);
        $this->actingAs($merchantUser);
        
        // Try to place a bid below the minimum (should be 21*1.05 = 22.05)
        $bidAmount = 22.00;
        
        $response = $this->postJson("/api/v1/bidding/place-bid/{$bid->ulid}", [
            'bid_amount' => $bidAmount,
            'notes' => 'My lower bid'
        ]);
        
        $response->assertStatus(422);
    }

    /** @test */
    public function completed_bids_are_processed()
    {
        // Create a bid that's already ended
        $bid = Bid::factory()->create([
            'junkshop_id' => $this->junkshop->ulid,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'price_per_kg' => 15.00,
            'starting_bid' => 20.00,
            'current_bid' => 25.00,
            'current_bidder_id' => $this->merchant->ulid,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(1), // Already ended
            'is_bidding_enabled' => true,
            'bidding_processed' => false,
            'status' => 'accepted',
            'accepted_at' => Carbon::now()->subDays(10)
        ]);
        
        // Run the command
        $this->artisan('app:process-ended-bids')
            ->assertExitCode(0);
            
        // Check bid was marked as processed
        $this->assertDatabaseHas('bids', [
            'id' => $bid->id,
            'bidding_processed' => true
        ]);
    }
}
